<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Reports — Revenue</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('reports._nav')
            <x-report-filter :r="$r" />

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">₹{{ number_format($billedTotal, 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Billed</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-green-600">₹{{ number_format($collected, 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Collected</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-purple-600">₹{{ number_format($refunded, 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Refunded</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">₹{{ number_format($discountTotal, 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Discount given</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold {{ $outstanding > 0 ? 'text-rose-600' : 'text-gray-800' }}">₹{{ number_format($outstanding, 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Outstanding (all time)</div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-800 mb-3 text-sm">Billed by type</h3>
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500"><tr><th class="py-1 font-medium">Type</th><th class="py-1 font-medium text-right">Invoices</th><th class="py-1 font-medium text-right">Total</th></tr></thead>
                        <tbody>
                            @foreach (['opd','ipd','general'] as $t)
                                <tr class="border-t border-gray-50">
                                    <td class="py-1.5">{{ strtoupper($t) }}</td>
                                    <td class="py-1.5 text-right">{{ $billedByType[$t]->c ?? 0 }}</td>
                                    <td class="py-1.5 text-right">₹{{ number_format($billedByType[$t]->total ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-800 mb-3 text-sm">Collections by mode</h3>
                    @forelse ($byMode as $mode => $row)
                        <div class="flex justify-between text-sm py-1">
                            <span class="text-gray-600 capitalize">{{ str_replace('_',' ',$mode) }} <span class="text-gray-400">({{ $row->c }})</span></span>
                            <span class="font-medium">₹{{ number_format($row->total, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No collections in range.</p>
                    @endforelse
                </div>
            </div>

            <x-mini-bars :data="$daily" :money="true" :height="160">
                <h3 class="font-medium text-gray-800 mb-2 text-sm">Daily collections</h3>
            </x-mini-bars>
        </div>
    </div>
</x-app-layout>
