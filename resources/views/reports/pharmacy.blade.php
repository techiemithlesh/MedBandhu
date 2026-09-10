<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Reports — Pharmacy</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('reports._nav')
            <x-report-filter :r="$r" />

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">{{ $salesCount }}</div>
                    <div class="text-xs text-gray-500 mt-1">Sales</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-green-600">₹{{ number_format($revenue, 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Sales revenue</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">₹{{ number_format($margin, 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Est. gross margin <span class="text-gray-400">· COGS ₹{{ number_format($cogs, 0) }}</span></div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">₹{{ number_format($purchaseSpend, 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Purchase spend</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold {{ $expiring->c > 0 ? 'text-amber-600' : 'text-gray-800' }}">{{ $expiring->c }}</div>
                    <div class="text-xs text-gray-500 mt-1">Batches expiring &le;90d <span class="text-gray-400">· ₹{{ number_format($expiring->v, 0) }}</span></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4 text-sm text-gray-500">
                Current stock value (at cost): <span class="font-medium text-gray-800">₹{{ number_format($stockValue, 2) }}</span>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <div class="px-4 py-3 font-medium text-gray-700 text-sm">Top 10 medicines by revenue (in range)</div>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr><th class="px-4 py-2 font-medium">Medicine</th><th class="px-4 py-2 font-medium text-right">Units sold</th><th class="px-4 py-2 font-medium text-right">Revenue</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($top as $t)
                            <tr>
                                <td class="px-4 py-2">{{ trim($t->name.' '.$t->strength) }}</td>
                                <td class="px-4 py-2 text-right">{{ (int) $t->qty }}</td>
                                <td class="px-4 py-2 text-right">₹{{ number_format($t->revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">No sales in range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
