<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Subscription &amp; billing</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            @if ($hospital->access_status === 'blocked')
                <div class="rounded-md bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">
                    Your subscription is inactive — the rest of the app is locked until a pending invoice is paid.
                </div>
            @elseif ($hospital->access_status === 'restricted')
                <div class="rounded-md bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                    Your subscription has lapsed and you're in the grace period. Please settle the pending invoice to avoid a lockout.
                </div>
            @endif

            @if ($subscription)
                <div class="bg-white rounded-lg shadow-sm p-6 grid sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Plan</span><span class="font-medium">{{ $subscription->plan->name }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Type</span><span class="capitalize">{{ $subscription->licence_type }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Billing</span><span class="capitalize">{{ $subscription->billing_cycle }} · ₹{{ number_format($subscription->amount, 0) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Branches</span><span>{{ $hospital->branchesUsed() }} / {{ $hospital->branch_limit }}</span></div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ $subscription->status === 'trialing' ? 'Trial ends' : ($subscription->isPerpetual() ? 'AMC valid till' : 'Renews on') }}</span>
                        <span class="font-medium">{{ optional($subscription->periodEndsOn())->format('d M Y') ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between"><span class="text-gray-500">Status</span><span class="capitalize">{{ str_replace('_',' ',$subscription->status) }}</span></div>
                    @if ($subscription->licence_key)
                        <div class="flex justify-between sm:col-span-2"><span class="text-gray-500">Licence key</span><span class="font-mono text-xs">{{ $subscription->licence_key }}</span></div>
                    @endif
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="text-sm text-gray-500 mb-2">Plan includes</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($subscription->plan->moduleList() ?? config('hms.modules') as $m)
                            <span class="text-xs bg-teal-50 text-teal-700 rounded px-2 py-0.5">{{ ucfirst($m) }}</span>
                        @endforeach
                        @foreach (['ai' => 'AI', 'custom_domain' => 'Custom domain', 'sms' => 'SMS', 'priority_support' => 'Priority support'] as $f => $label)
                            @if ($subscription->plan->hasFeature($f))
                                <span class="text-xs bg-purple-50 text-purple-700 rounded px-2 py-0.5">{{ $label }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm p-6 text-sm text-gray-500">
                    No subscription is attached to this hospital yet. Please contact support.
                </div>
            @endif

            @php
                $due = $invoices->whereIn('status', ['sent', 'draft'])->where('balance', '>', 0);
                $dueTotal = $due->sum('balance');
                $waBase = 'https://wa.me/'.preg_replace('/\D/', '', config('hms.contact.whatsapp'));
            @endphp

            {{-- How to pay — manual / offline flow --}}
            @if ($due->isNotEmpty() && ! $razorpayEnabled)
                <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                    <div>
                        <div class="text-sm font-semibold text-gray-800">How to pay ₹{{ number_format($dueTotal, 2) }}</div>
                        <p class="mt-1 text-sm text-gray-500">Pay by UPI or bank transfer, then tell us on WhatsApp with the reference number. We activate your account and email a receipt the same day.</p>
                    </div>

                    @if (! empty($payTo))
                        <dl class="grid sm:grid-cols-2 gap-x-8 gap-y-2 text-sm rounded-md bg-gray-50 p-4">
                            @isset($payTo['upi'])
                                <div class="flex justify-between sm:col-span-2"><dt class="text-gray-500">UPI ID</dt><dd class="font-mono font-medium">{{ $payTo['upi'] }}</dd></div>
                            @endisset
                            @isset($payTo['account_name'])
                                <div class="flex justify-between"><dt class="text-gray-500">Account name</dt><dd class="font-medium">{{ $payTo['account_name'] }}</dd></div>
                            @endisset
                            @isset($payTo['bank_name'])
                                <div class="flex justify-between"><dt class="text-gray-500">Bank</dt><dd class="font-medium">{{ $payTo['bank_name'] }}</dd></div>
                            @endisset
                            @isset($payTo['account_number'])
                                <div class="flex justify-between"><dt class="text-gray-500">Account no.</dt><dd class="font-mono font-medium">{{ $payTo['account_number'] }}</dd></div>
                            @endisset
                            @isset($payTo['ifsc'])
                                <div class="flex justify-between"><dt class="text-gray-500">IFSC</dt><dd class="font-mono font-medium">{{ $payTo['ifsc'] }}</dd></div>
                            @endisset
                        </dl>
                    @endif

                    @can('subscription.pay')
                        <a href="{{ $waBase.'?text='.rawurlencode('Hi, I have paid for '.$hospital->name.' — invoice '.$due->pluck('number')->implode(', ').'. Reference: ') }}"
                           target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 rounded-md bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                            I've paid — notify us on WhatsApp
                        </a>
                    @endcan
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-4 py-3 font-medium text-gray-700 text-sm">Invoices</div>
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($invoices as $inv)
                            <tr>
                                <td class="px-4 py-2 font-mono text-gray-500">{{ $inv->number }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $inv->description }}</td>
                                <td class="px-4 py-2 text-gray-500">due {{ $inv->due_date->format('d M Y') }}</td>
                                <td class="px-4 py-2 text-right">₹{{ number_format($inv->total, 2) }}</td>
                                <td class="px-4 py-2 text-right whitespace-nowrap">
                                    @if ($inv->status === 'paid')
                                        <span class="text-xs text-green-600">Paid</span>
                                        @if ($inv->payments->isNotEmpty())
                                            <a href="{{ route('billing.subscription.receipt', $inv->payments->last()) }}" target="_blank" class="ml-2 text-xs text-teal-600 hover:underline">Receipt</a>
                                        @endif
                                    @elseif ($inv->status === 'void')
                                        <span class="text-xs text-gray-400">Void</span>
                                    @else
                                        <span class="text-xs text-rose-600">₹{{ number_format($inv->balance, 2) }} due</span>
                                        @can('subscription.pay')
                                            @if ($razorpayEnabled)
                                                <a href="{{ route('billing.subscription.pay', $inv) }}" class="ml-2 rounded bg-teal-600 text-white text-xs px-2 py-1 hover:bg-teal-700">Pay now</a>
                                            @endif
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No invoices yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
