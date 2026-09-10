<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Bed Board</h2>
            @can('ipd.admit')
                <a href="{{ route('ipd.admissions.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ Admit patient</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                @foreach ([
                    'Total' => [$summary['total'], 'text-gray-800'],
                    'Available' => [$summary['available'], 'text-green-600'],
                    'Occupied' => [$summary['occupied'], 'text-rose-600'],
                    'Cleaning' => [$summary['cleaning'], 'text-amber-600'],
                    'Blocked' => [$summary['blocked'], 'text-gray-400'],
                ] as $label => [$value, $color])
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <div class="text-2xl font-semibold {{ $color }}">{{ $value }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ $label }}</div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap gap-4 text-xs text-gray-500">
                <span><span class="inline-block w-3 h-3 rounded bg-green-100 border border-green-300 align-middle"></span> Available</span>
                <span><span class="inline-block w-3 h-3 rounded bg-rose-100 border border-rose-300 align-middle"></span> Occupied</span>
                <span><span class="inline-block w-3 h-3 rounded bg-amber-100 border border-amber-300 align-middle"></span> Cleaning</span>
                <span><span class="inline-block w-3 h-3 rounded bg-gray-100 border border-gray-300 align-middle"></span> Blocked / reserved</span>
            </div>

            @forelse ($wards as $ward)
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-medium text-gray-800">{{ $ward->name }}
                            <span class="text-xs text-gray-400">{{ $ward->type_label }}{{ $ward->floor ? ' · '.$ward->floor : '' }}</span>
                        </h3>
                        <span class="text-xs text-gray-500">{{ $ward->beds->where('status','available')->count() }} / {{ $ward->beds->count() }} free</span>
                    </div>

                    <div class="grid grid-cols-3 sm:grid-cols-6 lg:grid-cols-8 gap-2">
                        @foreach ($ward->beds as $bed)
                            @php $adm = $bed->currentAdmission; @endphp
                            @php
                                $cls = match ($bed->status) {
                                    'available' => 'bg-green-50 border-green-300 hover:bg-green-100',
                                    'occupied' => 'bg-rose-50 border-rose-300 hover:bg-rose-100',
                                    'cleaning' => 'bg-amber-50 border-amber-300',
                                    default => 'bg-gray-50 border-gray-300',
                                };
                            @endphp
                            @if ($bed->status === 'available' && auth()->user()->can('ipd.admit'))
                                <a href="{{ route('ipd.admissions.create', ['bed' => $bed->id]) }}"
                                   class="block rounded border {{ $cls }} p-2 text-center transition">
                                    <div class="font-semibold text-gray-700 text-sm">{{ $bed->bed_number }}</div>
                                    <div class="text-[10px] text-green-600">available</div>
                                </a>
                            @elseif ($adm)
                                <a href="{{ route('ipd.admissions.show', $adm) }}"
                                   class="block rounded border {{ $cls }} p-2 text-center transition">
                                    <div class="font-semibold text-gray-700 text-sm">{{ $bed->bed_number }}</div>
                                    <div class="text-[10px] text-gray-600 truncate">{{ $adm->patient->first_name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $adm->days_admitted }}d</div>
                                </a>
                            @else
                                <div class="rounded border {{ $cls }} p-2 text-center">
                                    <div class="font-semibold text-gray-600 text-sm">{{ $bed->bed_number }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $bed->status }}</div>
                                </div>
                            @endif
                        @endforeach

                        @if ($ward->beds->isEmpty())
                            <p class="col-span-full text-sm text-gray-400">No beds configured.</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-sm p-10 text-center text-gray-400">
                    No wards yet.
                    @can('beds.manage')<a href="{{ route('wards.create') }}" class="text-teal-600 hover:underline">Create the first ward</a>.@endcan
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
