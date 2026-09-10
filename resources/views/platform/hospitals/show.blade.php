<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $hospital->name }}</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('platform.hospitals.edit', $hospital) }}" class="text-sm text-gray-600 hover:underline">Edit</a>
                <form method="POST" action="{{ route('context.hospital.enter', $hospital) }}">
                    @csrf
                    <button class="inline-flex items-center rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Enter hospital &rarr;</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('status'))
                <div class="rounded-md bg-teal-50 border border-teal-200 px-4 py-3 text-sm text-teal-800">{{ session('status') }}</div>
            @endif

            @php $sub = $hospital->subscription; @endphp

            <div class="grid sm:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6 text-sm space-y-2">
                    <div class="flex justify-between"><span class="text-gray-500">Code</span><span class="font-medium">{{ $hospital->code }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Access</span>
                        <span class="text-xs rounded-full px-2 py-0.5 @class([
                            'bg-green-100 text-green-700' => $hospital->access_status === 'active',
                            'bg-amber-100 text-amber-700' => $hospital->access_status === 'restricted',
                            'bg-rose-100 text-rose-700' => $hospital->access_status === 'blocked',
                        ])">{{ ucfirst($hospital->access_status) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Branches</span><span class="font-medium">{{ $hospital->branches_count }} / {{ $hospital->branch_limit }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Contact</span><span class="font-medium">{{ $hospital->email ?: '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Custom domain</span><span class="font-medium">{{ $hospital->custom_domain ?: '—' }}</span></div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 text-sm" x-data="{ open: false }">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-gray-800">Subscription</span>
                        <button @click="open = !open" class="text-xs text-teal-600 hover:underline">{{ $sub ? 'Change' : 'Set up' }}</button>
                    </div>
                    @if ($sub)
                        <div class="space-y-1">
                            <div class="flex justify-between"><span class="text-gray-500">Plan</span><span>{{ $sub->plan->name }} · {{ ucfirst($sub->licence_type) }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Cycle / branches</span><span>{{ ucfirst(str_replace('_','-',$sub->billing_cycle)) }} · {{ $sub->branches }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Amount</span><span>₹{{ number_format($sub->amount, 0) }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">{{ $sub->status === 'trialing' ? 'Trial ends' : ($sub->isPerpetual() ? 'AMC valid till' : 'Renews') }}</span>
                                <span>{{ optional($sub->periodEndsOn())->format('d M Y') ?? '—' }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Status</span><span class="capitalize">{{ str_replace('_',' ',$sub->status) }}</span></div>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <form method="POST" action="{{ route('platform.subscriptions.invoice', $sub) }}">@csrf
                                <button class="rounded border border-gray-300 px-2 py-1 text-xs hover:bg-gray-50">Raise invoice</button>
                            </form>
                            @if (in_array($sub->status, ['suspended']))
                                <form method="POST" action="{{ route('platform.subscriptions.resume', $sub) }}">@csrf
                                    <button class="rounded border border-green-300 text-green-700 px-2 py-1 text-xs hover:bg-green-50">Resume</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('platform.subscriptions.suspend', $sub) }}" onsubmit="return confirm('Suspend? The hospital loses access.')">@csrf
                                    <button class="rounded border border-amber-300 text-amber-700 px-2 py-1 text-xs hover:bg-amber-50">Suspend</button>
                                </form>
                            @endif
                        </div>
                    @else
                        <p class="text-gray-400">No subscription. Set one up to activate billing.</p>
                    @endif

                    <div x-show="open" x-cloak class="mt-4 border-t border-gray-100 pt-4">
                        <form method="POST" action="{{ route('platform.hospitals.subscription.assign', $hospital) }}" class="space-y-2">
                            @csrf
                            <select name="plan_id" class="block w-full border-gray-300 rounded text-sm">
                                @foreach ($plans as $p)
                                    <option value="{{ $p->id }}" @selected($sub?->plan_id === $p->id)>{{ $p->name }} — ₹{{ number_format($p->price_yearly, 0) }}/yr ({{ $p->branch_limit }} br)</option>
                                @endforeach
                            </select>
                            <div class="grid grid-cols-3 gap-2">
                                <select name="licence_type" class="border-gray-300 rounded text-xs">
                                    <option value="subscription">Subscription</option>
                                    <option value="perpetual">Perpetual</option>
                                </select>
                                <select name="billing_cycle" class="border-gray-300 rounded text-xs">
                                    <option value="yearly" @selected($sub?->billing_cycle === 'yearly')>Yearly</option>
                                    <option value="half_yearly" @selected($sub?->billing_cycle === 'half_yearly')>Half-yearly</option>
                                    <option value="monthly" @selected($sub?->billing_cycle === 'monthly')>Monthly</option>
                                </select>
                                <input name="branches" type="number" min="1" value="{{ $sub->branches ?? $hospital->branches_count }}" class="border-gray-300 rounded text-xs" placeholder="branches">
                            </div>
                            <label class="inline-flex items-center gap-1 text-xs text-gray-600">
                                <input type="checkbox" name="trial" value="1" class="rounded text-teal-600"> start with trial
                            </label>
                            <button class="rounded bg-teal-600 text-white text-xs px-3 py-1.5 w-full">Apply</button>
                        </form>
                    </div>
                </div>
            </div>

            @if ($hospital->platformInvoices->isNotEmpty())
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-4 py-3 font-medium text-gray-700 text-sm">Platform invoices</div>
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($hospital->platformInvoices as $inv)
                                <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('platform.billing.invoice', $inv) }}'">
                                    <td class="px-4 py-2 font-mono text-gray-500">{{ $inv->number }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ $inv->description }}</td>
                                    <td class="px-4 py-2 text-gray-500">due {{ $inv->due_date->format('d M') }}</td>
                                    <td class="px-4 py-2 text-right">₹{{ number_format($inv->total, 2) }}</td>
                                    <td class="px-4 py-2 text-right"><span class="text-xs rounded px-1.5 py-0.5 {{ $inv->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">{{ ucfirst($inv->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Branches ({{ $hospital->branches_count }})</h3>
                <ul class="divide-y divide-gray-100 text-sm">
                    @foreach($hospital->branches as $b)
                        <li class="py-2 flex justify-between">
                            <span class="text-gray-700">{{ $b->name }} <span class="text-xs text-gray-400">{{ $b->code }}</span></span>
                            <span class="text-xs text-gray-400 capitalize">{{ $b->type }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Users ({{ $hospital->users_count }})</h3>
                <ul class="divide-y divide-gray-100 text-sm">
                    @foreach($hospital->users as $u)
                        <li class="py-2 flex justify-between">
                            <span class="text-gray-700">{{ $u->name }} <span class="text-xs text-gray-400">{{ $u->email }}</span></span>
                            <span class="text-xs text-gray-400">{{ $u->designation }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
