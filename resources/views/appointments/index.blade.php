<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">{{ __("Appointments") }}</h2>
            @can('appointments.create')
                <a href="{{ route('appointments.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ {{ __("Book appointment") }}</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="GET" class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __("Date") }}</label>
                    <input type="date" name="date" value="{{ $date->toDateString() }}" class="border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __("Doctor") }}</label>
                    <select name="doctor" class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">{{ __("All") }}</option>
                        @foreach ($doctors as $id => $name)
                            <option value="{{ $id }}" @selected((string) request('doctor') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __("Status") }}</label>
                    <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">{{ __("All") }}</option>
                        @foreach (['scheduled','checked_in','in_consultation','completed','cancelled','no_show'] as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucwords(str_replace('_',' ',$st)) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2">{{ __("Show") }}</button>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">{{ __("Token") }}</th>
                            <th class="px-4 py-3 font-medium">{{ __("Time") }}</th>
                            <th class="px-4 py-3 font-medium">{{ __("Patient") }}</th>
                            <th class="px-4 py-3 font-medium">{{ __("Doctor") }}</th>
                            <th class="px-4 py-3 font-medium">{{ __("Type") }}</th>
                            <th class="px-4 py-3 font-medium">{{ __("Status") }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($appointments as $appt)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-gray-700">{{ $appt->token_no }}</td>
                                <td class="px-4 py-3">{{ $appt->scheduled_time_label }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $appt->patient->full_name }}</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $appt->patient->uhid }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $appt->doctor->full_name }}</td>
                                <td class="px-4 py-3 capitalize">{{ $appt->type }}{{ $appt->source === 'walk_in' ? ' · walk-in' : '' }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs rounded-full px-2 py-0.5
                                        @class([
                                            'bg-blue-100 text-blue-700' => $appt->status === 'scheduled',
                                            'bg-amber-100 text-amber-700' => $appt->status === 'checked_in',
                                            'bg-purple-100 text-purple-700' => $appt->status === 'in_consultation',
                                            'bg-green-100 text-green-700' => $appt->status === 'completed',
                                            'bg-gray-100 text-gray-500' => in_array($appt->status, ['cancelled','no_show']),
                                        ])">
                                        {{ $appt->status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('appointments.show', $appt) }}" class="text-teal-600 hover:underline">{{ __("Open") }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">{{ __("No appointments for :date", ["date" => $date->format("d M Y")]) }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
