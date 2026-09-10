<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">{{ $invoice->number }}</h2>
            <div class="flex gap-3">
                <button onclick="window.print()" class="text-sm text-gray-600 hover:underline">Print</button>
                <a href="{{ route('platform.billing.invoices') }}" class="text-sm text-gray-500 hover:underline">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <div class="bg-white rounded-lg shadow-sm p-6 text-sm space-y-2">
                <div class="flex justify-between"><span class="text-gray-500">Hospital</span><span>{{ $invoice->hospital->name }} ({{ $invoice->hospital->code }})</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Description</span><span>{{ $invoice->description }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Period</span><span>{{ optional($invoice->period_start)->format('d M Y') }} → {{ optional($invoice->period_end)->format('d M Y') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Due date</span><span class="{{ $invoice->isOverdue() ? 'text-rose-600 font-medium' : '' }}">{{ $invoice->due_date->format('d M Y') }}</span></div>
                <div class="border-t border-gray-100 pt-2 mt-2 space-y-1">
                    <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>₹{{ number_format($invoice->subtotal, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">GST (18%)</span><span>₹{{ number_format($invoice->tax, 2) }}</span></div>
                    <div class="flex justify-between font-semibold"><span>Total</span><span>₹{{ number_format($invoice->total, 2) }}</span></div>
                    <div class="flex justify-between text-green-600"><span>Paid</span><span>₹{{ number_format($invoice->amount_paid, 2) }}</span></div>
                    <div class="flex justify-between font-medium {{ $invoice->balance > 0 ? 'text-rose-600' : '' }}"><span>Balance</span><span>₹{{ number_format($invoice->balance, 2) }}</span></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="font-medium text-gray-800 mb-3 text-sm">Payments</h3>
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($invoice->payments as $p)
                            <tr>
                                <td class="py-2 font-mono text-gray-500">{{ $p->number }}</td>
                                <td class="py-2 capitalize">{{ str_replace('_',' ',$p->method) }} {{ $p->reference ? '· '.$p->reference : '' }}</td>
                                <td class="py-2 text-gray-500">{{ $p->paid_at->format('d M Y') }}</td>
                                <td class="py-2 text-right">₹{{ number_format($p->amount, 2) }}</td>
                                <td class="py-2 text-right"><a href="{{ route('platform.billing.payment.receipt', $p) }}" target="_blank" class="text-xs text-teal-600 hover:underline">Receipt</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-3 text-center text-gray-400">No payments.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($invoice->balance > 0 && $invoice->status !== 'void')
                    <form method="POST" action="{{ route('platform.platform-invoices.payment', $invoice) }}" class="mt-4 flex flex-wrap items-end gap-2 text-sm border-t border-gray-100 pt-4">
                        @csrf
                        <div><label class="block text-xs text-gray-500">Amount</label>
                            <input name="amount" type="number" step="0.01" min="0.01" max="{{ $invoice->balance }}" value="{{ $invoice->balance }}" required class="w-28 border-gray-300 rounded text-sm"></div>
                        <div><label class="block text-xs text-gray-500">Method</label>
                            <select name="method" class="border-gray-300 rounded text-sm">
                                @foreach (['bank_transfer','upi','cash','cheque','adjustment'] as $m)<option value="{{ $m }}">{{ ucfirst(str_replace('_',' ',$m)) }}</option>@endforeach
                            </select></div>
                        <div><label class="block text-xs text-gray-500">Reference</label>
                            <input name="reference" class="w-32 border-gray-300 rounded text-sm"></div>
                        <button class="rounded bg-teal-600 text-white px-3 py-1.5">Record payment</button>
                    </form>
                    <p class="mt-2 text-xs text-gray-400">Recording payment in full renews the subscription, reactivates a held/suspended hospital, and emails the hospital a receipt.</p>
                    @if ($invoice->amount_paid == 0)
                        <form method="POST" action="{{ route('platform.platform-invoices.void', $invoice) }}" class="mt-2" onsubmit="return confirm('Void this invoice?')">
                            @csrf
                            <button class="text-red-500 text-xs hover:underline">Void invoice</button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
