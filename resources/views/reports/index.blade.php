<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Reports — Overview</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('reports._nav')
            <x-report-filter :r="$r" />

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">{{ $cards['opd_visits'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">OPD visits</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">{{ $cards['ipd_admissions'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">IPD admissions</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold {{ $cards['occupancy'] >= 85 ? 'text-rose-600' : 'text-gray-800' }}">{{ $cards['occupancy'] }}%</div>
                    <div class="text-xs text-gray-500 mt-1">Occupancy now <span class="text-gray-400">· {{ $cards['occupancy_detail'] }}</span></div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-green-600">₹{{ number_format($cards['collected'], 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Collected</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">₹{{ number_format($cards['pharmacy_sales'], 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Pharmacy sales</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">{{ $cards['new_patients'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">New patients</div>
                </div>
            </div>

            <x-mini-bars :data="$dailyVisits" :height="140">
                <h3 class="font-medium text-gray-800 mb-2 text-sm">Daily OPD visits</h3>
            </x-mini-bars>
        </div>
    </div>
</x-app-layout>
