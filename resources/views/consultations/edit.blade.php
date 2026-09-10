<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Consultation — {{ $appointment->patient->full_name }}</h2>
                <p class="text-sm text-gray-500">
                    {{ $appointment->patient->uhid }} ·
                    {{ $appointment->patient->age ?? '—' }}{{ $appointment->patient->gender ? ' / '.ucfirst($appointment->patient->gender[0]) : '' }} ·
                    token {{ $appointment->token_no }} · {{ $appointment->status_label }}
                </p>
            </div>
            <a href="{{ route('opd.index', ['date' => $appointment->scheduled_date->toDateString()]) }}" class="text-sm text-gray-500 hover:underline">Back to queue</a>
        </div>
    </x-slot>

    @php
        $items = old('items', $consultation->items->map->only(['drug_name','strength','form','dosage','duration_days','instructions','quantity'])->toArray());
        if (empty($items)) $items = [['drug_name'=>'','strength'=>'','form'=>'','dosage'=>'','duration_days'=>'','instructions'=>'','quantity'=>'']];
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-4">
                <x-flash />

                @if ($appointment->status === 'completed' && $consultation->exists && $consultation->items->isNotEmpty())
                    @can('pharmacy.dispense')
                        @module('pharmacy')
                            <div class="rounded-md bg-teal-50 border border-teal-200 px-4 py-2 text-sm text-teal-800 flex items-center justify-between">
                                <span>Prescription ready.</span>
                                <a href="{{ route('pharmacy.dispense.create', ['consultation' => $consultation->id]) }}" class="font-medium hover:underline">Dispense at pharmacy →</a>
                            </div>
                        @endmodule
                    @endcan
                @endif

                @if ($appointment->patient->allergies)
                    <div class="rounded-md bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-800">
                        <span class="font-semibold">Allergies:</span> {{ $appointment->patient->allergies }}
                    </div>
                @endif

                @if ($appointment->vital)
                    @php $v = $appointment->vital; @endphp
                    <div class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap gap-x-6 gap-y-1 text-sm">
                        <span><span class="text-gray-400">BP</span> {{ $v->blood_pressure ?? '—' }}</span>
                        <span><span class="text-gray-400">Pulse</span> {{ $v->pulse_bpm ?? '—' }}</span>
                        <span><span class="text-gray-400">Temp</span> {{ $v->temperature_c ? $v->temperature_c.'°' : '—' }}</span>
                        <span><span class="text-gray-400">SpO₂</span> {{ $v->spo2 ? $v->spo2.'%' : '—' }}</span>
                        <span><span class="text-gray-400">Wt</span> {{ $v->weight_kg ? $v->weight_kg.'kg' : '—' }}</span>
                        <span><span class="text-gray-400">BMI</span> {{ $v->bmi ?? '—' }}</span>
                    </div>
                @else
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-700">
                        No vitals recorded.
                        @can('vitals.record')
                            <a href="{{ route('opd.vitals.edit', $appointment) }}" class="underline">Add vitals</a>
                        @endcan
                    </div>
                @endif

                <form method="POST" action="{{ route('consultations.update', $appointment) }}" class="space-y-4"
                      x-data="{ items: {{ Illuminate\Support\Js::from($items) }} }">
                    @csrf @method('PUT')

                    <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                        @foreach ([
                            'chief_complaint' => 'Chief complaint',
                            'history_present_illness' => 'History of present illness',
                            'examination_findings' => 'Examination findings',
                            'diagnosis' => 'Diagnosis',
                            'investigations_advised' => 'Investigations advised',
                            'advice' => 'Advice',
                        ] as $field => $label)
                            <div>
                                <x-input-label :for="$field" :value="$label" />
                                <textarea id="{{ $field }}" name="{{ $field }}" rows="2"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old($field, $consultation->$field) }}</textarea>
                            </div>
                        @endforeach

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="followup_date" value="Follow-up date" />
                                <x-text-input id="followup_date" name="followup_date" type="date" class="mt-1 block w-full"
                                              :value="old('followup_date', optional($consultation->followup_date)->format('Y-m-d'))" min="{{ now()->addDay()->toDateString() }}" />
                            </div>
                            <div>
                                <x-input-label for="private_notes" value="Private notes (not printed)" />
                                <x-text-input id="private_notes" name="private_notes" class="mt-1 block w-full" :value="old('private_notes', $consultation->private_notes)" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="font-medium text-gray-800 mb-3">Prescription</h3>
                        <div class="space-y-2">
                            <template x-for="(item, i) in items" :key="i">
                                <div class="grid grid-cols-12 gap-2 items-center">
                                    <input :name="`items[${i}][drug_name]`" x-model="item.drug_name" placeholder="Drug" class="col-span-3 border-gray-300 rounded-md text-sm">
                                    <input :name="`items[${i}][strength]`" x-model="item.strength" placeholder="500 mg" class="col-span-2 border-gray-300 rounded-md text-sm">
                                    <input :name="`items[${i}][dosage]`" x-model="item.dosage" placeholder="1-0-1" class="col-span-2 border-gray-300 rounded-md text-sm">
                                    <input :name="`items[${i}][duration_days]`" x-model="item.duration_days" type="number" min="1" placeholder="days" class="col-span-1 border-gray-300 rounded-md text-sm">
                                    <input :name="`items[${i}][instructions]`" x-model="item.instructions" placeholder="after food" class="col-span-3 border-gray-300 rounded-md text-sm">
                                    <button type="button" @click="items.splice(i,1)" class="col-span-1 text-red-500 text-xs hover:underline">×</button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="items.push({drug_name:'',strength:'',form:'',dosage:'',duration_days:'',instructions:'',quantity:''})"
                                class="mt-3 text-sm text-teal-600 hover:underline">+ Add drug</button>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" name="action" value="save" class="rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Save draft</button>
                        @can('opd.consult')
                            <button type="submit" name="action" value="complete" class="rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">
                                {{ $appointment->status === 'completed' ? 'Save' : 'Complete consultation' }}
                            </button>
                        @endcan
                    </div>
                </form>
            </div>

            {{-- History --}}
            <div class="space-y-3">
                <h3 class="font-medium text-gray-800">Past visits</h3>
                @forelse ($history as $h)
                    <div class="bg-white rounded-lg shadow-sm p-4 text-sm">
                        <div class="flex justify-between text-gray-400 text-xs mb-1">
                            <span>{{ $h->created_at->format('d M Y') }}</span>
                            <span>{{ $h->doctor->full_name }}</span>
                        </div>
                        @if ($h->diagnosis)<div class="font-medium text-gray-700">{{ $h->diagnosis }}</div>@endif
                        @if ($h->chief_complaint)<div class="text-gray-500">{{ Str::limit($h->chief_complaint, 120) }}</div>@endif
                        @if ($h->items->count())
                            <ul class="mt-1 text-xs text-gray-500 list-disc list-inside">
                                @foreach ($h->items as $it)
                                    <li>{{ $it->drug_name }} {{ $it->strength }} {{ $it->dosage }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No previous visits.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
