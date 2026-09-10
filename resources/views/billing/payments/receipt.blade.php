<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Receipt {{ $payment->payment_no }}</h2>
            <div class="flex gap-3">
                <button onclick="window.print()" class="text-sm text-gray-600 hover:underline">Print</button>
                <a href="{{ route('billing.invoices.show', $payment->invoice) }}" class="text-sm text-gray-500 hover:underline">Invoice</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <x-flash />

            <div class="bg-white rounded-lg shadow-sm p-8 print:shadow-none">
                <div class="text-center border-b border-gray-200 pb-4 mb-4">
                    <div class="font-semibold text-lg">{{ app(\App\Support\Tenancy::class)->hospital()->name }}</div>
                    <div class="text-xs text-gray-500">{{ app(\App\Support\Tenancy::class)->branch()?->name }}</div>
                    <div class="text-sm font-medium mt-2">{{ $payment->type === 'refund' ? 'REFUND RECEIPT' : 'PAYMENT RECEIPT' }}</div>
                </div>

                <div class="space-y-1 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Receipt no</span><span class="font-mono">{{ $payment->payment_no }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Date</span><span>{{ $payment->created_at->format('d M Y, H:i') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Patient</span><span>{{ $payment->patient->full_name }} ({{ $payment->patient->uhid }})</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Against invoice</span><span class="font-mono">{{ $payment->invoice->invoice_no }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Mode</span><span class="capitalize">{{ str_replace('_',' ',$payment->mode) }}{{ $payment->reference ? ' · '.$payment->reference : '' }}</span></div>
                </div>

                <div class="mt-6 border-t border-gray-200 pt-4 flex justify-between items-center">
                    <span class="text-gray-500 text-sm">{{ $payment->type === 'refund' ? 'Amount refunded' : 'Amount received' }}</span>
                    <span class="text-2xl font-semibold">₹{{ number_format($payment->amount, 2) }}</span>
                </div>

                <div class="mt-4 text-xs text-gray-500 grid grid-cols-2 gap-2">
                    <div>Invoice total: ₹{{ number_format($payment->invoice->total, 2) }}</div>
                    <div class="text-right">Balance after: ₹{{ number_format($payment->invoice->balance, 2) }}</div>
                </div>

                <p class="text-center text-xs text-gray-400 mt-6">Received by {{ $payment->receiver?->name }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
