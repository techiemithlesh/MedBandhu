<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">{{ $doctor->full_name }}</h2>
            <div class="flex items-center gap-3">
                @can('staff.view')
                    <a href="{{ route('staff.show', $doctor) }}" class="text-sm text-gray-600 hover:underline">Full HR record</a>
                @endcan
                @can('schedules.manage')
                    <a href="{{ route('doctors.schedule.edit', $doctor) }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Manage availability</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            @php $p = $doctor->doctorProfile; @endphp
            <div class="bg-white rounded-lg shadow-sm p-6 grid sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Specialization</span><span>{{ $p?->specialization ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Qualifications</span><span>{{ $p?->qualifications ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Department</span><span>{{ $doctor->department?->name ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Registration</span><span>{{ $p?->registration_no ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Experience</span><span>{{ $p?->experience_years ? $p->experience_years.' yrs' : '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Consultation fee</span><span>₹{{ number_format($p?->consultation_fee ?? 0, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Primary branch</span><span>{{ $doctor->branch?->name ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Slot length</span><span>{{ $p?->appointment_duration_min ?? 15 }} min</span></div>
            </div>

            @if ($p?->bio)
                <div class="bg-white rounded-lg shadow-sm p-6 text-sm text-gray-600">{{ $p->bio }}</div>
            @endif

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="font-medium text-gray-800 mb-3">Weekly availability</h3>
                @forelse ($doctor->schedules->sortBy('day_of_week') as $s)
                    <div class="flex justify-between text-sm py-1 border-b border-gray-50 last:border-0">
                        <span>{{ $s->day_label }} · {{ $s->branch?->name }}</span>
                        <span class="text-gray-600">
                            {{ $s->time_range }} · {{ $s->slot_minutes }}m{{ $s->max_tokens ? ' · max '.$s->max_tokens : '' }}
                            @unless ($s->is_active) <span class="text-gray-400">(off)</span> @endunless
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No schedule set. This doctor won't be bookable in Sprint 2 until a schedule exists.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
