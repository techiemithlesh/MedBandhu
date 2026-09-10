<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Book appointment</h2>
    </x-slot>

    <div class="py-8"
         x-data="bookAppointment({
            presetPatient: {{ $patient ? Illuminate\Support\Js::from(['id' => $patient->id, 'uhid' => $patient->uhid, 'name' => $patient->full_name, 'phone' => $patient->phone]) : 'null' }},
            presetDoctor: {{ $presetDoctor ?? 'null' }},
            date: '{{ $date }}'
         })">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="POST" action="{{ route('appointments.store') }}" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
                @csrf
                <input type="hidden" name="patient_id" :value="patient?.id">
                <input type="hidden" name="scheduled_time" :value="computedSource === 'walk_in' ? '' : form.time">
                <input type="hidden" name="source" :value="computedSource">

                {{-- Patient --}}
                <div>
                    <x-input-label value="Patient *" />
                    <template x-if="patient">
                        <div class="mt-1 flex items-center justify-between rounded-md border border-teal-200 bg-teal-50 px-3 py-2 text-sm">
                            <span><span class="font-medium" x-text="patient.name"></span> · <span class="font-mono" x-text="patient.uhid"></span> <span x-text="patient.phone ? '· '+patient.phone : ''"></span></span>
                            <button type="button" @click="patient = null" class="text-teal-600 text-xs hover:underline">change</button>
                        </div>
                    </template>
                    <template x-if="!patient">
                        <div class="mt-1 relative">
                            <input type="text" x-model="query" @input.debounce.300ms="lookup()" placeholder="Search UHID, name or phone"
                                   class="block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <div x-show="results.length" class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg text-sm">
                                <template x-for="r in results" :key="r.id">
                                    <button type="button" @click="pick(r)" class="block w-full text-left px-3 py-2 hover:bg-gray-50">
                                        <span class="font-medium" x-text="r.name"></span>
                                        <span class="text-gray-400" x-text="' · '+r.uhid+(r.phone ? ' · '+r.phone : '')"></span>
                                    </button>
                                </template>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Not registered? <a href="{{ route('patients.create') }}" class="text-teal-600 hover:underline">Register a new patient</a> first.</p>
                        </div>
                    </template>
                </div>

                {{-- Doctor + date --}}
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="doctor_id" value="Doctor *" />
                        <select id="doctor_id" name="doctor_id" x-model="form.doctor" @change="loadSlots()" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">— Select —</option>
                            @foreach ($doctors as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->full_name }}@if($doc->doctorProfile?->specialization) — {{ $doc->doctorProfile->specialization }}@endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="scheduled_date" value="Date *" />
                        <input id="scheduled_date" name="scheduled_date" type="date" x-model="form.date" @change="loadSlots()"
                               min="{{ now()->toDateString() }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>

                {{-- Slots --}}
                <div x-show="form.doctor">
                    <x-input-label value="Available slots" />
                    <div class="mt-2">
                        <p x-show="loading" class="text-sm text-gray-400">Loading…</p>
                        <p x-show="!loading && slots.length === 0" class="text-sm text-gray-400">
                            No slots — this doctor has no schedule for that day.
                            <label class="ml-2 inline-flex items-center gap-1 text-gray-600">
                                <input type="checkbox" x-model="walkIn" class="rounded border-gray-300 text-teal-600"> Book as walk-in anyway
                            </label>
                        </p>
                        <div x-show="!loading && slots.length" class="flex flex-wrap gap-2">
                            <template x-for="s in slots" :key="s.time">
                                <button type="button" :disabled="s.taken"
                                        @click="form.time = s.time; form.source = 'booked'"
                                        :class="{
                                            'bg-teal-600 text-white': form.time === s.time,
                                            'bg-gray-100 text-gray-400 line-through cursor-not-allowed': s.taken,
                                            'bg-white border border-gray-300 text-gray-700 hover:border-teal-400': !s.taken && form.time !== s.time
                                        }"
                                        class="rounded px-3 py-1.5 text-sm" x-text="s.label"></button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Type / reason --}}
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="type" value="Visit type" />
                        <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="new">New</option>
                            <option value="followup">Follow-up</option>
                        </select>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-7">
                        <input type="checkbox" name="fee_paid" value="1" class="rounded border-gray-300 text-teal-600"> Consultation fee collected
                    </label>
                    <div class="sm:col-span-2">
                        <x-input-label for="reason" value="Reason / chief complaint" />
                        <x-text-input id="reason" name="reason" class="mt-1 block w-full" />
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" :disabled="!patient || !form.doctor || (!form.time && !walkIn)"
                            class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 disabled:opacity-40">
                        Book appointment
                    </button>
                    <span x-show="walkIn && !form.time" class="text-xs text-amber-600">Will be booked as an unslotted walk-in.</span>
                    <a href="{{ route('appointments.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bookAppointment(cfg) {
            return {
                patient: cfg.presetPatient,
                query: '', results: [],
                slots: [], loading: false, walkIn: false,
                form: { doctor: cfg.presetDoctor || '', date: cfg.date, time: '', source: 'booked' },
                init() { if (this.form.doctor) this.loadSlots(); },
                lookup() {
                    if (this.query.length < 2) { this.results = []; return; }
                    fetch(`{{ route('patients.search') }}?q=${encodeURIComponent(this.query)}`)
                        .then(r => r.json()).then(d => this.results = d.patients);
                },
                pick(r) { this.patient = r; this.results = []; this.query = ''; },
                loadSlots() {
                    this.form.time = ''; this.slots = [];
                    if (!this.form.doctor || !this.form.date) return;
                    this.loading = true;
                    fetch(`{{ route('appointments.slots') }}?doctor=${this.form.doctor}&date=${this.form.date}`)
                        .then(r => r.json()).then(d => { this.slots = d.slots; this.loading = false; })
                        .catch(() => this.loading = false);
                },
                get computedSource() { return this.walkIn && !this.form.time ? 'walk_in' : 'booked'; },
            }
        }
    </script>
</x-app-layout>
