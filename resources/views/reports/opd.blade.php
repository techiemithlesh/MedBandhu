<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Reports — OPD</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('reports._nav')
            <x-report-filter :r="$r" />

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">{{ $total }}</div>
                    <div class="text-xs text-gray-500 mt-1">Total appointments</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">{{ $newCount }} / {{ $followupCount }}</div>
                    <div class="text-xs text-gray-500 mt-1">New / Follow-up</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">{{ $walkinCount }}</div>
                    <div class="text-xs text-gray-500 mt-1">Walk-ins</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold {{ $noShowRate > 15 ? 'text-rose-600' : 'text-gray-800' }}">{{ $noShowRate }}%</div>
                    <div class="text-xs text-gray-500 mt-1">No-show rate</div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-800 mb-3 text-sm">By status</h3>
                    @foreach (['scheduled','checked_in','in_consultation','completed','cancelled','no_show'] as $st)
                        <div class="flex justify-between text-sm py-1">
                            <span class="text-gray-600 capitalize">{{ str_replace('_',' ',$st) }}</span>
                            <span class="font-medium">{{ $byStatus[$st] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
                <x-mini-bars :data="$daily" :height="180">
                    <h3 class="font-medium text-gray-800 mb-2 text-sm">Daily visits</h3>
                </x-mini-bars>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <div class="px-4 py-3 font-medium text-gray-700 text-sm">By doctor</div>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr><th class="px-4 py-2 font-medium">Doctor</th><th class="px-4 py-2 font-medium text-right">Appointments</th><th class="px-4 py-2 font-medium text-right">Completed</th><th class="px-4 py-2 font-medium text-right">Completion %</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($byDoctor as $d)
                            <tr>
                                <td class="px-4 py-2">{{ trim($d->name) }}</td>
                                <td class="px-4 py-2 text-right">{{ $d->total }}</td>
                                <td class="px-4 py-2 text-right">{{ $d->completed }}</td>
                                <td class="px-4 py-2 text-right">{{ $d->total ? round($d->completed / $d->total * 100) : 0 }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No appointments in range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
