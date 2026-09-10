<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">{{ $medicine->display_name }}</h2>
            @can('pharmacy.manage-stock')
                <a href="{{ route('pharmacy.medicines.edit', $medicine) }}" class="text-sm text-gray-600 hover:underline">Edit</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8" x-data="{ adjusting: null }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <div class="bg-white rounded-lg shadow-sm p-6 grid sm:grid-cols-3 gap-x-8 gap-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Generic</span><span>{{ $medicine->generic_name ?: '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Form</span><span class="capitalize">{{ $medicine->form }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Category</span><span>{{ $medicine->category?->name ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Manufacturer</span><span>{{ $medicine->manufacturer?->name ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">GST</span><span>{{ rtrim(rtrim(number_format($medicine->gst_rate, 2), '0'), '.') }}%</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Schedule</span><span>{{ $medicine->schedule === 'none' ? '—' : $medicine->schedule }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Reorder level</span><span>{{ $medicine->reorder_level }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">In stock (this branch)</span><span class="font-medium">{{ $medicine->stock }}</span></div>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <div class="px-4 py-3 font-medium text-gray-700">Batches</div>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr><th class="px-4 py-2 font-medium">Batch</th><th class="px-4 py-2 font-medium">Expiry</th><th class="px-4 py-2 font-medium">MRP</th><th class="px-4 py-2 font-medium">Sale ₹</th><th class="px-4 py-2 font-medium">Available</th><th></th></tr>
                    </thead>
                    @forelse ($batches as $b)
                        <tbody class="border-t border-gray-100">
                            <tr class="{{ $b->isExpired() ? 'bg-red-50' : '' }}">
                                <td class="px-4 py-2 font-mono">{{ $b->batch_number }}</td>
                                <td class="px-4 py-2 {{ $b->isExpired() ? 'text-red-600 font-medium' : ($b->expiry_date->lt(now()->addDays(90)) ? 'text-amber-600' : 'text-gray-600') }}">{{ $b->expiry_date->format('M Y') }}</td>
                                <td class="px-4 py-2">₹{{ number_format($b->mrp, 2) }}</td>
                                <td class="px-4 py-2">₹{{ number_format($b->sale_price, 2) }}</td>
                                <td class="px-4 py-2">{{ $b->quantity_available }}</td>
                                <td class="px-4 py-2 text-right">
                                    @can('pharmacy.manage-stock')
                                        <button type="button" @click="adjusting = adjusting === {{ $b->id }} ? null : {{ $b->id }}" class="text-teal-600 hover:underline">Adjust</button>
                                    @endcan
                                </td>
                            </tr>
                            <tr x-show="adjusting === {{ $b->id }}" x-cloak>
                                <td colspan="6" class="px-4 py-3 bg-gray-50">
                                    <form method="POST" action="{{ route('pharmacy.stock.adjust', $b) }}" class="flex flex-wrap items-end gap-3">
                                        @csrf
                                        <div><label class="block text-xs text-gray-500">Change (+/-)</label>
                                            <input name="delta" type="number" required class="w-24 border-gray-300 rounded text-sm" placeholder="-5"></div>
                                        <div><label class="block text-xs text-gray-500">Reason</label>
                                            <select name="type" class="border-gray-300 rounded text-sm">
                                                <option value="adjustment">Count correction</option>
                                                <option value="expiry_writeoff">Expiry write-off</option>
                                            </select></div>
                                        <div class="flex-1 min-w-[160px]"><label class="block text-xs text-gray-500">Note</label>
                                            <input name="note" class="block w-full border-gray-300 rounded text-sm"></div>
                                        <button class="rounded bg-teal-600 text-white text-sm px-3 py-1.5">Apply</button>
                                    </form>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody><tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No stock received yet.</td></tr></tbody>
                    @endforelse
                </table>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <div class="px-4 py-3 font-medium text-gray-700">Recent movements</div>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr><th class="px-4 py-2 font-medium">When</th><th class="px-4 py-2 font-medium">Type</th><th class="px-4 py-2 font-medium">Batch</th><th class="px-4 py-2 font-medium text-right">Qty</th><th class="px-4 py-2 font-medium text-right">Balance</th><th class="px-4 py-2 font-medium">Note</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($movements as $mv)
                            <tr>
                                <td class="px-4 py-2 text-gray-500">{{ $mv->created_at->format('d M, H:i') }}</td>
                                <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $mv->type) }}</td>
                                <td class="px-4 py-2 font-mono text-gray-500">{{ $mv->batch?->batch_number ?? '—' }}</td>
                                <td class="px-4 py-2 text-right {{ $mv->quantity < 0 ? 'text-red-600' : 'text-green-600' }}">{{ $mv->quantity > 0 ? '+' : '' }}{{ $mv->quantity }}</td>
                                <td class="px-4 py-2 text-right">{{ $mv->balance_after }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $mv->note }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No movements.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
