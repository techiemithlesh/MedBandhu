<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Reports — Patients</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('reports._nav')
            <x-report-filter :r="$r" />

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">{{ $total }}</div>
                    <div class="text-xs text-gray-500 mt-1">New registrations</div>
                </div>
                @foreach (['male' => 'Male', 'female' => 'Female'] as $g => $label)
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <div class="text-2xl font-semibold text-gray-800">{{ $byGender[$g] ?? 0 }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ $label }}</div>
                    </div>
                @endforeach
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">{{ ($byGender['other'] ?? 0) + ($byGender['unknown'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Other / not recorded</div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-800 mb-3 text-sm">Age distribution</h3>
                    @php $maxAge = max(1, max($byAge)); @endphp
                    @foreach ($byAge as $band => $count)
                        <div class="flex items-center gap-3 text-sm py-1">
                            <span class="w-16 text-gray-600">{{ $band }}</span>
                            <div class="flex-1 bg-gray-100 rounded h-4">
                                <div class="bg-teal-500 h-4 rounded" style="width: {{ round($count / $maxAge * 100) }}%"></div>
                            </div>
                            <span class="w-8 text-right font-medium">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-4 py-3 font-medium text-gray-700 text-sm">Top cities</div>
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($byCity as $c)
                                <tr>
                                    <td class="px-4 py-2 text-gray-700">{{ $c->city }}</td>
                                    <td class="px-4 py-2 text-right font-medium">{{ $c->c }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-4 py-6 text-center text-gray-400">No city data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-mini-bars :data="$daily" :height="140">
                <h3 class="font-medium text-gray-800 mb-2 text-sm">Daily registrations</h3>
            </x-mini-bars>
        </div>
    </div>
</x-app-layout>
