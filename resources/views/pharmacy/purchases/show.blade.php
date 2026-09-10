<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">{{ $purchase->purchase_no }}</h2>
            <a href="{{ route('pharmacy.purchases.index') }}" class="text-sm text-gray-500 hover:underline">Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <div class="bg-white rounded-lg shadow-sm p-6 grid sm:grid-cols-4 gap-4 text-sm">
                <div><div class="text-gray-400">Supplier</div><div class="font-medium">{{ $purchase->supplier->name }}</div></div>
                <div><div class="text-gray-400">Invoice</div><div class="font-medium">{{ $purchase->invoice_number ?: '—' }}</div></div>
                <div><div class="text-gray-400">Invoice date</div><div class="font-medium">{{ optional($purchase->invoice_date)->format('d M Y') ?? '—' }}</div></div>
                <div><div class="text-gray-400">Received</div><div class="font-medium">{{ $purchase->received_date->format('d M Y') }}</div></div>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-2 font-medium">Medicine</th>
                            <th class="px-4 py-2 font-medium">Batch</th>
                            <th class="px-4 py-2 font-medium">Expiry</th>
                            <th class="px-4 py-2 font-medium text-right">Qty</th>
                            <th class="px-4 py-2 font-medium text-right">Free</th>
                            <th class="px-4 py-2 font-medium text-right">Cost</th>
                            <th class="px-4 py-2 font-medium text-right">MRP</th>
                            <th class="px-4 py-2 font-medium text-right">GST%</th>
                            <th class="px-4 py-2 font-medium text-right">Line ₹</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($purchase->items as $item)
                            <tr>
                                <td class="px-4 py-2 font-medium">{{ $item->medicine->display_name }}</td>
                                <td class="px-4 py-2 font-mono">{{ $item->batch_number }}</td>
                                <td class="px-4 py-2">{{ $item->expiry_date->format('M Y') }}</td>
                                <td class="px-4 py-2 text-right">{{ $item->quantity }}</td>
                                <td class="px-4 py-2 text-right">{{ $item->free_quantity ?: '—' }}</td>
                                <td class="px-4 py-2 text-right">₹{{ number_format($item->purchase_price, 2) }}</td>
                                <td class="px-4 py-2 text-right">₹{{ number_format($item->mrp, 2) }}</td>
                                <td class="px-4 py-2 text-right">{{ rtrim(rtrim(number_format($item->gst_rate, 2), '0'), '.') }}</td>
                                <td class="px-4 py-2 text-right">₹{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="text-sm">
                        <tr><td colspan="8" class="px-4 py-1 text-right text-gray-500">Taxable</td><td class="px-4 py-1 text-right">₹{{ number_format($purchase->subtotal, 2) }}</td></tr>
                        <tr><td colspan="8" class="px-4 py-1 text-right text-gray-500">GST</td><td class="px-4 py-1 text-right">₹{{ number_format($purchase->tax, 2) }}</td></tr>
                        <tr><td colspan="8" class="px-4 py-1 text-right text-gray-500">Discount</td><td class="px-4 py-1 text-right">− ₹{{ number_format($purchase->discount, 2) }}</td></tr>
                        <tr class="font-semibold border-t border-gray-200"><td colspan="8" class="px-4 py-2 text-right">Total</td><td class="px-4 py-2 text-right">₹{{ number_format($purchase->total, 2) }}</td></tr>
                    </tfoot>
                </table>
            </div>

            @if ($purchase->notes)
                <p class="text-sm text-gray-500">{{ $purchase->notes }}</p>
            @endif
        </div>
    </div>
</x-app-layout>
