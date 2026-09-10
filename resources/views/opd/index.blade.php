<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">{{ __("OPD Queue") }}</h2>
            @can('appointments.create')
                <a href="{{ route('appointments.create', ['date' => $date->toDateString()]) }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ {{ __("Walk-in / book") }}</a>
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
                <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2">{{ __("Show") }}</button>
                <div class="ml-auto flex gap-4 text-sm">
                    <span class="text-amber-600">{{ $stats["waiting"] }} {{ __("waiting") }}</span>
                    <span class="text-purple-600">{{ $stats["in_consult"] }} {{ __("in consult") }}</span>
                    <span class="text-green-600">{{ $stats["done"] }} {{ __("done") }}</span>
                </div>
            </form>

            @forelse ($queue as $doctorName => $appointments)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 font-medium text-gray-700">{{ $doctorName }}</div>
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($appointments as $appt)
                                <tr class="@if($appt->status==='in_consultation') bg-purple-50 @elseif(in_array($appt->status,['completed','no_show','cancelled'])) opacity-60 @endif">
                                    <td class="px-4 py-3 w-14 font-semibold text-gray-700">#{{ $appt->token_no }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('patients.show', $appt->patient) }}" class="font-medium text-gray-800 hover:underline">{{ $appt->patient->full_name }}</a>
                                        <div class="text-xs text-gray-400">{{ $appt->patient->uhid }} · {{ $appt->scheduled_time_label }} · {{ ucfirst($appt->type) }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs rounded-full px-2 py-0.5
                                            @class([
                                                'bg-blue-100 text-blue-700' => $appt->status === 'scheduled',
                                                'bg-amber-100 text-amber-700' => $appt->status === 'checked_in',
                                                'bg-purple-100 text-purple-700' => $appt->status === 'in_consultation',
                                                'bg-green-100 text-green-700' => $appt->status === 'completed',
                                                'bg-gray-100 text-gray-500' => in_array($appt->status, ['cancelled','no_show']),
                                            ])">{{ $appt->status_label }}</span>
                                        @if ($appt->vital)<span class="ml-2 text-xs text-teal-600">{{ __("vitals") }} ✓</span>@endif
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                        @can('opd.manage-queue')
                                            @if ($appt->status === 'scheduled')
                                                <form method="POST" action="{{ route('opd.check-in', $appt) }}" class="inline">@csrf
                                                    <button class="text-teal-600 hover:underline">{{ __("Check in") }}</button>
                                                </form>
                                                <form method="POST" action="{{ route('opd.no-show', $appt) }}" class="inline" onsubmit="return confirm((__("Mark no-show?")))">@csrf
                                                    <button class="text-gray-400 hover:underline">{{ __("No-show") }}</button>
                                                </form>
                                            @endif
                                        @endcan
                                        @can('vitals.record')
                                            @if ($appt->isOpen())
                                                <a href="{{ route('opd.vitals.edit', $appt) }}" class="text-gray-600 hover:underline">{{ $appt->vital ? __("Vitals") : "+ ".__("Vitals") }}</a>
                                            @endif
                                        @endcan
                                        @can('opd.consult')
                                            @if (in_array($appt->status, ['checked_in','in_consultation']))
                                                <a href="{{ route('consultations.edit', $appt) }}" class="text-purple-600 font-medium hover:underline">{{ __("Consult") }}</a>
                                            @elseif ($appt->status === 'completed')
                                                <a href="{{ route('consultations.edit', $appt) }}" class="text-gray-500 hover:underline">{{ __("View") }}</a>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-sm p-10 text-center text-gray-400">{{ __("No patients in the queue for :date", ["date" => $date->format("d M Y")]) }}</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
