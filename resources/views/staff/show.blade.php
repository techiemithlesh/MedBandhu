<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">{{ $staff->full_name }}</h2>
            <div class="flex items-center gap-3">
                @if ($staff->is_doctor)
                    @can('schedules.manage')
                        <a href="{{ route('doctors.schedule.edit', $staff) }}" class="text-sm text-gray-600 hover:underline">Manage availability</a>
                    @endcan
                @endif
                @can('staff.update')
                    <a href="{{ route('staff.edit', $staff) }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Edit</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <div class="bg-white rounded-lg shadow-sm p-6 flex gap-6">
                @if ($staff->photo_path)
                    <img src="{{ Storage::url($staff->photo_path) }}" class="h-24 w-24 rounded-lg object-cover shrink-0">
                @else
                    <div class="h-24 w-24 rounded-lg bg-gray-100 flex items-center justify-center text-2xl text-gray-400 shrink-0">
                        {{ Str::of($staff->first_name)->substr(0,1) }}
                    </div>
                @endif
                <div class="grid sm:grid-cols-2 gap-x-8 gap-y-2 text-sm flex-1">
                    <div class="flex justify-between"><span class="text-gray-500">Code</span><span>{{ $staff->employee_code }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Type</span><span>{{ $staff->type_label }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Department</span><span>{{ $staff->department?->name ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Branch</span><span>{{ $staff->branch?->name ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Designation</span><span>{{ $staff->designation ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Employment</span><span>{{ ucfirst($staff->employment_type) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Phone</span><span>{{ $staff->phone ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Email</span><span>{{ $staff->email ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Joined</span><span>{{ optional($staff->joined_on)->format('d M Y') ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Status</span><span class="capitalize">{{ str_replace('_',' ',$staff->status) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Login</span><span>{{ $staff->user ? $staff->user->email : 'none' }}</span></div>
                </div>
            </div>

            @if ($staff->is_doctor && $staff->doctorProfile)
                @php $p = $staff->doctorProfile; @endphp
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-800 mb-3">Doctor profile</h3>
                    <div class="grid sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Specialization</span><span>{{ $p->specialization ?? '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Qualifications</span><span>{{ $p->qualifications ?? '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Registration</span><span>{{ $p->registration_no ?? '—' }} {{ $p->registration_council ? '· '.$p->registration_council : '' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Experience</span><span>{{ $p->experience_years ? $p->experience_years.' yrs' : '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Consultation fee</span><span>₹{{ number_format($p->consultation_fee, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Follow-up fee</span><span>₹{{ number_format($p->followup_fee, 2) }} ({{ $p->followup_valid_days }}d)</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Slot length</span><span>{{ $p->appointment_duration_min }} min</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Flags</span><span>{{ collect([$p->is_surgeon ? 'Surgeon' : null, $p->online_consultation ? 'Online' : null])->filter()->implode(', ') ?: '—' }}</span></div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-medium text-gray-800">Weekly availability</h3>
                        @can('schedules.manage')
                            <a href="{{ route('doctors.schedule.edit', $staff) }}" class="text-sm text-teal-600 hover:underline">Edit</a>
                        @endcan
                    </div>
                    @forelse ($staff->schedules->sortBy('day_of_week') as $s)
                        <div class="flex justify-between text-sm py-1 border-b border-gray-50 last:border-0">
                            <span>{{ $s->day_label }} · {{ $s->branch?->name }}</span>
                            <span class="text-gray-600">
                                {{ $s->time_range }} · {{ $s->slot_minutes }}m{{ $s->max_tokens ? ' · max '.$s->max_tokens : '' }}
                                @unless ($s->is_active) <span class="text-gray-400">(off)</span> @endunless
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No schedule set.</p>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
