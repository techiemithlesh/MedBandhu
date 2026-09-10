<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Reports — IPD &amp; occupancy</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('reports._nav')
            <x-report-filter :r="$r" />

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">{{ $admissions }}</div>
                    <div class="text-xs text-gray-500 mt-1">Admissions</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">{{ $dischargeCount }}</div>
                    <div class="text-xs text-gray-500 mt-1">Discharges</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-rose-600">{{ $currentCensus }}</div>
                    <div class="text-xs text-gray-500 mt-1">Current inpatients</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">{{ $alos }}</div>
                    <div class="text-xs text-gray-500 mt-1">Avg length of stay (days)</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold {{ $occupancyPct >= 85 ? 'text-rose-600' : 'text-gray-800' }}">{{ $occupancyPct }}%</div>
                    <div class="text-xs text-gray-500 mt-1">Bed occupancy <span class="text-gray-400">· {{ $usedBedDays }}/{{ $bedDays }} bed-days</span></div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-800 mb-3 text-sm">Discharge types (in range)</h3>
                    @forelse ($dischargeTypes as $type => $count)
                        <div class="flex justify-between text-sm py-1">
                            <span class="text-gray-600 capitalize">{{ $type ?? '—' }}</span>
                            <span class="font-medium">{{ $count }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No discharges in range.</p>
                    @endforelse
                </div>

                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-4 py-3 font-medium text-gray-700 text-sm">Ward occupancy (now)</div>
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-500">
                            <tr><th class="px-4 py-2 font-medium">Ward</th><th class="px-4 py-2 font-medium text-right">Beds</th><th class="px-4 py-2 font-medium text-right">Occupied</th><th class="px-4 py-2 font-medium text-right">%</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($byWard as $w)
                                <tr>
                                    <td class="px-4 py-2">{{ $w->name }}</td>
                                    <td class="px-4 py-2 text-right">{{ $w->total }}</td>
                                    <td class="px-4 py-2 text-right">{{ $w->occupied ?? 0 }}</td>
                                    <td class="px-4 py-2 text-right">{{ $w->total ? round(($w->occupied ?? 0) / $w->total * 100) : 0 }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No wards.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
