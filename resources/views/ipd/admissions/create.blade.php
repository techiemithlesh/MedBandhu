<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Admit patient</h2>
    </x-slot>

    <div class="py-8"
         x-data="admitForm({
            presetPatient: {{ $patient ? Illuminate\Support\Js::from(['id' => $patient->id, 'uhid' => $patient->uhid, 'name' => $patient->full_name, 'phone' => $patient->phone]) : 'null' }},
            presetBed: {{ $presetBed?->id ?? 'null' }}
         })">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="POST" action="{{ route('ipd.admissions.store') }}" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
                @csrf
                <input type="hidden" name="patient_id" :value="patient?.id">

                <div>
                    <x-input-label value="Patient *" />
                    <template x-if="patient">
                        <div class="mt-1 flex items-center justify-between rounded-md border border-teal-200 bg-teal-50 px-3 py-2 text-sm">
                            <span><span class="font-medium" x-text="patient.name"></span> · <span class="font-mono" x-text="patient.uhid"></span></span>
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
                                        <span class="text-gray-400" x-text="' · '+r.uhid"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div>
                    <x-input-label for="bed_id" value="Bed *" />
                    <select id="bed_id" name="bed_id" x-model="bedId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="">— Select an available bed —</option>
                        @foreach ($wards as $ward)
                            <optgroup label="{{ $ward->name }} ({{ $ward->type_label }})">
                                @foreach ($ward->beds as $bed)
                                    <option value="{{ $bed->id }}">{{ $bed->bed_number }}{{ $bed->room_label ? ' · '.$bed->room_label : '' }} — ₹{{ number_format($bed->daily_charge, 0) }}/day</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @if ($wards->isEmpty())
                        <p class="text-xs text-amber-600 mt-1">No available beds. Add wards/beds first.</p>
                    @endif
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="admitting_doctor_id" value="Admitting doctor" />
                        <select id="admitting_doctor_id" name="admitting_doctor_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">—</option>
                            @foreach ($doctors as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="department_id" value="Department" />
                        <select id="department_id" name="department_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">—</option>
                            @foreach ($departments as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="source" value="Source *" />
                        <select id="source" name="source" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach (['direct' => 'Direct', 'opd' => 'From OPD', 'emergency' => 'Emergency', 'referral' => 'Referral'] as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="expected_discharge_on" value="Expected discharge" />
                        <x-text-input id="expected_discharge_on" name="expected_discharge_on" type="date" min="{{ now()->toDateString() }}" class="mt-1 block w-full" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="provisional_diagnosis" value="Provisional diagnosis" />
                        <textarea id="provisional_diagnosis" name="provisional_diagnosis" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('provisional_diagnosis') }}</textarea>
                    </div>
                    <div>
                        <x-input-label for="attendant_name" value="Attendant name" />
                        <x-text-input id="attendant_name" name="attendant_name" class="mt-1 block w-full" />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <x-input-label for="attendant_relation" value="Relation" />
                            <x-text-input id="attendant_relation" name="attendant_relation" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label for="attendant_phone" value="Phone" />
                            <x-text-input id="attendant_phone" name="attendant_phone" class="mt-1 block w-full" />
                        </div>
                    </div>
                </div>

                <button type="submit" :disabled="!patient || !bedId"
                        class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 disabled:opacity-40">
                    Admit patient
                </button>
                <a href="{{ route('ipd.board') }}" class="ml-3 text-sm text-gray-500 hover:underline">Cancel</a>
            </form>
        </div>
    </div>

    <script>
        function admitForm(cfg) {
            return {
                patient: cfg.presetPatient,
                bedId: cfg.presetBed || '',
                query: '', results: [],
                lookup() {
                    if (this.query.length < 2) { this.results = []; return; }
                    fetch(`{{ route('patients.search') }}?q=${encodeURIComponent(this.query)}`)
                        .then(r => r.json()).then(d => this.results = d.patients);
                },
                pick(r) { this.patient = r; this.results = []; this.query = ''; },
            }
        }
    </script>
</x-app-layout>
