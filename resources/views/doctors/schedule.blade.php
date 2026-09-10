<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Availability — {{ $doctor->full_name }}</h2>
            <a href="{{ route('doctors.show', $doctor) }}" class="text-sm text-gray-500 hover:underline">Back</a>
        </div>
    </x-slot>

    @php
        $initial = $doctor->schedules->map(fn ($s) => [
            'day_of_week' => (int) $s->day_of_week,
            'branch_id' => (int) $s->branch_id,
            'start_time' => \Illuminate\Support\Str::of($s->start_time)->before('.')->substr(0,5),
            'end_time' => \Illuminate\Support\Str::of($s->end_time)->before('.')->substr(0,5),
            'slot_minutes' => (int) $s->slot_minutes,
            'max_tokens' => $s->max_tokens,
            'is_active' => (bool) $s->is_active,
        ])->values();
    @endphp

    @php $defaultBranch = (int) array_key_first($branches->toArray()); $defaultSlot = $doctor->doctorProfile->appointment_duration_min ?? 15; @endphp

    <div class="py-8"
         x-data="{
            rows: {{ Illuminate\Support\Js::from($initial) }},
            add() { this.rows.push({ day_of_week: 1, branch_id: {{ $defaultBranch }}, start_time: '09:00', end_time: '13:00', slot_minutes: {{ $defaultSlot }}, max_tokens: null, is_active: true }); }
         }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="POST" action="{{ route('doctors.schedule.update', $doctor) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                @csrf @method('PUT')

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2 pr-3 font-medium">Day</th>
                                <th class="py-2 pr-3 font-medium">Branch</th>
                                <th class="py-2 pr-3 font-medium">Start</th>
                                <th class="py-2 pr-3 font-medium">End</th>
                                <th class="py-2 pr-3 font-medium">Slot (min)</th>
                                <th class="py-2 pr-3 font-medium">Max tokens</th>
                                <th class="py-2 pr-3 font-medium">Active</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, i) in rows" :key="i">
                                <tr class="border-t border-gray-100">
                                    <td class="py-2 pr-3">
                                        <select :name="`rows[${i}][day_of_week]`" x-model.number="row.day_of_week" class="border-gray-300 rounded-md text-sm">
                                            @foreach (\App\Models\DoctorSchedule::DAYS as $n => $label)
                                                <option value="{{ $n }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-2 pr-3">
                                        <select :name="`rows[${i}][branch_id]`" x-model.number="row.branch_id" class="border-gray-300 rounded-md text-sm">
                                            @foreach ($branches as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-2 pr-3"><input type="time" :name="`rows[${i}][start_time]`" x-model="row.start_time" class="border-gray-300 rounded-md text-sm"></td>
                                    <td class="py-2 pr-3"><input type="time" :name="`rows[${i}][end_time]`" x-model="row.end_time" class="border-gray-300 rounded-md text-sm"></td>
                                    <td class="py-2 pr-3"><input type="number" min="5" :name="`rows[${i}][slot_minutes]`" x-model.number="row.slot_minutes" class="border-gray-300 rounded-md text-sm w-20"></td>
                                    <td class="py-2 pr-3"><input type="number" min="1" :name="`rows[${i}][max_tokens]`" x-model="row.max_tokens" class="border-gray-300 rounded-md text-sm w-20" placeholder="—"></td>
                                    <td class="py-2 pr-3">
                                        <input type="hidden" :name="`rows[${i}][is_active]`" :value="row.is_active ? 1 : 0">
                                        <input type="checkbox" x-model="row.is_active" class="rounded border-gray-300 text-teal-600">
                                    </td>
                                    <td class="py-2"><button type="button" @click="rows.splice(i,1)" class="text-red-500 hover:underline text-xs">remove</button></td>
                                </tr>
                            </template>
                            <tr x-show="rows.length === 0"><td colspan="8" class="py-6 text-center text-gray-400">No sessions. Add one below.</td></tr>
                        </tbody>
                    </table>
                </div>

                <button type="button" @click="add()" class="text-sm text-teal-600 hover:underline">+ Add session</button>

                <div class="flex items-center gap-3 border-t border-gray-100 pt-4">
                    <x-primary-button>Save availability</x-primary-button>
                    <a href="{{ route('doctors.show', $doctor) }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
