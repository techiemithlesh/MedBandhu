<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Stock</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">₹{{ number_format($summary['stock_value'], 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Stock value (at cost)</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold {{ $summary['expiring'] ? 'text-amber-600' : 'text-gray-800' }}">{{ $summary['expiring'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Expiring &le; 90 days</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold {{ $summary['expired'] ? 'text-red-600' : 'text-gray-800' }}">{{ $summary['expired'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Expired (in stock)</div>
                </div>
            </div>

            <form method="GET" class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Search medicine</label>
                    <input name="q" value="{{ request('q') }}" class="border-gray-300 rounded-md shadow-sm text-sm w-56">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Filter</label>
                    <select name="filter" class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All batches</option>
                        <option value="in_stock" @selected(request('filter')==='in_stock')>In stock only</option>
                        <option value="expiring" @selected(request('filter')==='expiring')>Expiring &le; 90d</option>
                        <option value="expired" @selected(request('filter')==='expired')>Expired</option>
                    </select>
                </div>
                <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2">Apply</button>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Medicine</th>
                            <th class="px-4 py-3 font-medium">Batch</th>
                            <th class="px-4 py-3 font-medium">Expiry</th>
                            <th class="px-4 py-3 font-medium text-right">MRP</th>
                            <th class="px-4 py-3 font-medium text-right">Cost</th>
                            <th class="px-4 py-3 font-medium text-right">Available</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($batches as $b)
                            <tr class="{{ $b->isExpired() ? 'bg-red-50' : '' }}">
                                <td class="px-4 py-3">
                                    <a href="{{ route('pharmacy.medicines.show', $b->medicine) }}" class="font-medium text-teal-700 hover:underline">{{ $b->medicine->display_name }}</a>
                                    <div class="text-xs text-gray-400">{{ $b->medicine->category?->name }}</div>
                                </td>
                                <td class="px-4 py-3 font-mono text-gray-600">{{ $b->batch_number }}</td>
                                <td class="px-4 py-3 {{ $b->isExpired() ? 'text-red-600 font-medium' : ($b->expiry_date->lt(now()->addDays(90)) ? 'text-amber-600' : 'text-gray-600') }}">
                                    {{ $b->expiry_date->format('M Y') }}
                                </td>
                                <td class="px-4 py-3 text-right">₹{{ number_format($b->mrp, 2) }}</td>
                                <td class="px-4 py-3 text-right text-gray-500">₹{{ number_format($b->purchase_price, 2) }}</td>
                                <td class="px-4 py-3 text-right font-medium">{{ $b->quantity_available }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No batches match.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $batches->links() }}
        </div>
    </div>
</x-app-layout>
