<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Vitals — {{ $appointment->patient->full_name }}
            <span class="text-sm text-gray-400 font-normal">({{ $appointment->patient->uhid }})</span>
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />
            @php $v = $appointment->vital; @endphp

            <form method="POST" action="{{ route('opd.vitals.update', $appointment) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-5">
                @csrf @method('PUT')

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="height_cm" value="Height (cm)" />
                        <x-text-input id="height_cm" name="height_cm" type="number" step="0.1" class="mt-1 block w-full" :value="old('height_cm', $v?->height_cm)" />
                    </div>
                    <div>
                        <x-input-label for="weight_kg" value="Weight (kg)" />
                        <x-text-input id="weight_kg" name="weight_kg" type="number" step="0.1" class="mt-1 block w-full" :value="old('weight_kg', $v?->weight_kg)" />
                    </div>
                    <div>
                        <x-input-label value="BMI" />
                        <div class="mt-1 py-2 text-gray-500 text-sm">{{ $v?->bmi ?? 'auto' }}</div>
                    </div>
                    <div>
                        <x-input-label for="temperature_c" value="Temp (°C)" />
                        <x-text-input id="temperature_c" name="temperature_c" type="number" step="0.1" class="mt-1 block w-full" :value="old('temperature_c', $v?->temperature_c)" />
                    </div>
                    <div>
                        <x-input-label for="pulse_bpm" value="Pulse (bpm)" />
                        <x-text-input id="pulse_bpm" name="pulse_bpm" type="number" class="mt-1 block w-full" :value="old('pulse_bpm', $v?->pulse_bpm)" />
                    </div>
                    <div>
                        <x-input-label for="resp_rate" value="Resp rate" />
                        <x-text-input id="resp_rate" name="resp_rate" type="number" class="mt-1 block w-full" :value="old('resp_rate', $v?->resp_rate)" />
                    </div>
                    <div>
                        <x-input-label for="systolic" value="BP systolic" />
                        <x-text-input id="systolic" name="systolic" type="number" class="mt-1 block w-full" :value="old('systolic', $v?->systolic)" />
                    </div>
                    <div>
                        <x-input-label for="diastolic" value="BP diastolic" />
                        <x-text-input id="diastolic" name="diastolic" type="number" class="mt-1 block w-full" :value="old('diastolic', $v?->diastolic)" />
                    </div>
                    <div>
                        <x-input-label for="spo2" value="SpO₂ (%)" />
                        <x-text-input id="spo2" name="spo2" type="number" class="mt-1 block w-full" :value="old('spo2', $v?->spo2)" />
                    </div>
                    <div>
                        <x-input-label for="blood_sugar" value="Blood sugar" />
                        <x-text-input id="blood_sugar" name="blood_sugar" type="number" class="mt-1 block w-full" :value="old('blood_sugar', $v?->blood_sugar)" />
                    </div>
                </div>

                <div>
                    <x-input-label for="notes" value="Notes" />
                    <x-text-input id="notes" name="notes" class="mt-1 block w-full" :value="old('notes', $v?->notes)" />
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Save vitals</x-primary-button>
                    <a href="{{ route('opd.index', ['date' => $appointment->scheduled_date->toDateString()]) }}" class="text-sm text-gray-500 hover:underline">Back to queue</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
