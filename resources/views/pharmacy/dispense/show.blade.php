<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Bill {{ $sale->sale_no }}</h2>
            <div class="flex gap-3">
                <button onclick="window.print()" class="text-sm text-gray-600 hover:underline">Print</button>
                <a href="{{ route('pharmacy.dispense.index') }}" class="text-sm text-gray-500 hover:underline">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <x-flash />

            <div class="bg-white rounded-lg shadow-sm p-8 print:shadow-none" id="bill">
                <div class="text-center border-b border-gray-200 pb-4 mb-4">
                    <div class="font-semibold text-lg">{{ app(\App\Support\Tenancy::class)->hospital()->name }}</div>
                    <div class="text-xs text-gray-500">Pharmacy · {{ app(\App\Support\Tenancy::class)->branch()?->name }}</div>
                </div>

                <div class="flex justify-between text-sm mb-4">
                    <div>
                        <div><span class="text-gray-500">Bill:</span> {{ $sale->sale_no }}</div>
                        <div><span class="text-gray-500">Date:</span> {{ $sale->created_at->format('d M Y, H:i') }}</div>
                    </div>
                    <div class="text-right">
                        <div><span class="text-gray-500">Customer:</span> {{ $sale->customer_label }}</div>
                        @if ($sale->prescriber)<div><span class="text-gray-500">Rx by:</span> {{ $sale->prescriber->full_name }}</div>@endif
                        <div><span class="text-gray-500">Payment:</span> {{ ucfirst($sale->payment_mode) }}</div>
                    </div>
                </div>

                <table class="min-w-full text-sm mb-4">
                    <thead class="text-left text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="py-1 font-medium">Item</th>
                            <th class="py-1 font-medium">Batch</th>
                            <th class="py-1 font-medium text-right">Qty</th>
                            <th class="py-1 font-medium text-right">Rate</th>
                            <th class="py-1 font-medium text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sale->items as $item)
                            <tr class="border-b border-gray-50">
                                <td class="py-1">{{ $item->medicine->display_name }}</td>
                                <td class="py-1 font-mono text-xs">{{ $item->batch_number }} · {{ optional($item->expiry_date)->format('M y') }}</td>
                                <td class="py-1 text-right">{{ $item->quantity }}</td>
                                <td class="py-1 text-right">₹{{ number_format($item->sale_price, 2) }}</td>
                                <td class="py-1 text-right">₹{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="ml-auto w-56 text-sm space-y-1">
                    <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>₹{{ number_format($sale->subtotal, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Discount</span><span>− ₹{{ number_format($sale->discount, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">GST (incl.)</span><span>₹{{ number_format($sale->tax, 2) }}</span></div>
                    @if ((float) $sale->round_off !== 0.0)
                        <div class="flex justify-between"><span class="text-gray-500">Round off</span><span>{{ $sale->round_off > 0 ? '+' : '' }}₹{{ number_format($sale->round_off, 2) }}</span></div>
                    @endif
                    <div class="flex justify-between font-semibold border-t border-gray-200 pt-1"><span>Total</span><span>₹{{ number_format($sale->total, 2) }}</span></div>
                </div>

                <p class="text-center text-xs text-gray-400 mt-6">Served by {{ optional(auth()->user())->name }} · Get well soon</p>
            </div>
        </div>
    </div>
</x-app-layout>
