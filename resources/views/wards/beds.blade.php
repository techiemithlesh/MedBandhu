<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">{{ $ward->name }} — Beds</h2>
            <a href="{{ route('wards.index') }}" class="text-sm text-gray-500 hover:underline">Back to wards</a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ mode: 'single', editing: null }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <form method="POST" action="{{ route('beds.store', $ward) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                @csrf
                <div class="flex gap-4 text-sm">
                    <label class="inline-flex items-center gap-2"><input type="radio" name="mode" value="single" x-model="mode" class="text-teal-600"> Single bed</label>
                    <label class="inline-flex items-center gap-2"><input type="radio" name="mode" value="bulk" x-model="mode" class="text-teal-600"> Add several</label>
                </div>

                <div class="grid sm:grid-cols-4 gap-3">
                    <div x-show="mode === 'single'">
                        <x-input-label for="bed_number" value="Bed number" />
                        <x-text-input id="bed_number" name="bed_number" class="mt-1 block w-full" placeholder="G-01" />
                    </div>
                    <div x-show="mode === 'bulk'">
                        <x-input-label for="prefix" value="Prefix" />
                        <x-text-input id="prefix" name="prefix" class="mt-1 block w-full" placeholder="G-" />
                    </div>
                    <div x-show="mode === 'bulk'">
                        <x-input-label for="start_from" value="Start from" />
                        <x-text-input id="start_from" name="start_from" type="number" min="1" value="1" class="mt-1 block w-full" />
                    </div>
                    <div x-show="mode === 'bulk'">
                        <x-input-label for="count" value="How many" />
                        <x-text-input id="count" name="count" type="number" min="1" max="60" class="mt-1 block w-full" placeholder="10" />
                    </div>
                    <div>
                        <x-input-label for="room_label" value="Room (optional)" />
                        <x-text-input id="room_label" name="room_label" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="daily_charge" value="Daily charge (₹)" />
                        <x-text-input id="daily_charge" name="daily_charge" type="number" step="0.01" min="0"
                                      class="mt-1 block w-full" :value="$ward->default_daily_charge" />
                    </div>
                </div>

                <x-primary-button>Add bed(s)</x-primary-button>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Bed</th>
                            <th class="px-4 py-3 font-medium">Room</th>
                            <th class="px-4 py-3 font-medium">Daily charge</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Patient</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    @forelse ($ward->beds as $bed)
                        <tbody class="border-t border-gray-100">
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $bed->bed_number }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $bed->room_label ?: '—' }}</td>
                                <td class="px-4 py-3">₹{{ number_format($bed->daily_charge, 0) }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs rounded-full px-2 py-0.5
                                        @class([
                                            'bg-green-100 text-green-700' => $bed->status === 'available',
                                            'bg-rose-100 text-rose-700' => $bed->status === 'occupied',
                                            'bg-amber-100 text-amber-700' => $bed->status === 'cleaning',
                                            'bg-gray-100 text-gray-500' => in_array($bed->status, ['blocked','reserved']),
                                        ])">{{ ucfirst($bed->status) }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $bed->currentAdmission?->patient?->full_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" @click="editing = (editing === {{ $bed->id }} ? null : {{ $bed->id }})" class="text-teal-600 hover:underline">Edit</button>
                                </td>
                            </tr>
                            <tr x-show="editing === {{ $bed->id }}" x-cloak>
                                <td colspan="6" class="px-4 py-4 bg-gray-50">
                                    <form method="POST" action="{{ route('beds.update', $bed) }}" class="flex flex-wrap items-end gap-3">
                                        @csrf @method('PUT')
                                        <div><label class="block text-xs text-gray-500">Bed no.</label>
                                            <input name="bed_number" value="{{ $bed->bed_number }}" class="w-24 border-gray-300 rounded text-sm"></div>
                                        <div><label class="block text-xs text-gray-500">Room</label>
                                            <input name="room_label" value="{{ $bed->room_label }}" class="w-28 border-gray-300 rounded text-sm"></div>
                                        <div><label class="block text-xs text-gray-500">Daily ₹</label>
                                            <input name="daily_charge" type="number" step="0.01" value="{{ $bed->daily_charge }}" class="w-28 border-gray-300 rounded text-sm"></div>
                                        <div><label class="block text-xs text-gray-500">Status</label>
                                            <select name="status" class="border-gray-300 rounded text-sm" {{ $bed->currentAdmission ? 'disabled' : '' }}>
                                                @foreach (\App\Models\Bed::STATUSES as $s)
                                                    <option value="{{ $s }}" @selected($bed->status === $s)>{{ ucfirst($s) }}</option>
                                                @endforeach
                                            </select>
                                            @if ($bed->currentAdmission)<input type="hidden" name="status" value="occupied">@endif
                                        </div>
                                        <label class="inline-flex items-center gap-1 text-xs text-gray-600">
                                            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-teal-600" @checked($bed->is_active)> Active
                                        </label>
                                        <button class="rounded bg-teal-600 text-white text-sm px-3 py-1.5">Save</button>
                                    </form>
                                    @unless ($bed->currentAdmission)
                                        <form method="POST" action="{{ route('beds.destroy', $bed) }}" class="mt-2" onsubmit="return confirm('Remove bed {{ $bed->bed_number }}?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-500 text-sm hover:underline">Delete bed</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody><tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No beds in this ward yet.</td></tr></tbody>
                    @endforelse
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
