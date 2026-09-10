<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">New invoice</h2>
    </x-slot>

    <div class="py-8" x-data="{
        patient: {{ $patient ? Illuminate\Support\Js::from(['id' => $patient->id, 'uhid' => $patient->uhid, 'name' => $patient->full_name]) : 'null' }},
        q: '', results: [],
        lookup() {
            if (this.q.length < 2) { this.results = []; return; }
            fetch(`{{ route('patients.search') }}?q=${encodeURIComponent(this.q)}`).then(r => r.json()).then(d => this.results = d.patients);
        }
    }">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="POST" action="{{ route('billing.invoices.store') }}" class="bg-white rounded-lg shadow-sm p-6 space-y-5">
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
                            <input type="text" x-model="q" @input.debounce.300ms="lookup()" placeholder="Search UHID, name or phone" class="block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <div x-show="results.length" class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg text-sm">
                                <template x-for="r in results" :key="r.id">
                                    <button type="button" @click="patient = r; results = []; q = ''" class="block w-full text-left px-3 py-2 hover:bg-gray-50">
                                        <span class="font-medium" x-text="r.name"></span><span class="text-gray-400" x-text="' · '+r.uhid"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div>
                    <x-input-label for="notes" value="Notes" />
                    <x-text-input id="notes" name="notes" class="mt-1 block w-full" />
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" :disabled="!patient" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 disabled:opacity-40">
                        Create draft
                    </button>
                    <a href="{{ route('billing.invoices.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
                <p class="text-xs text-gray-400">Tip: OPD and IPD bills are started from the appointment or admission page.</p>
            </form>
        </div>
    </div>
</x-app-layout>
