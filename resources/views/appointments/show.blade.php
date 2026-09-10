<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">{{ $appointment->appointment_no }}</h2>
                <p class="text-sm text-gray-500">{{ $appointment->scheduled_date->format('d M Y') }} · token {{ $appointment->token_no }}</p>
            </div>
            <span class="text-xs rounded-full px-3 py-1 bg-gray-100 text-gray-700">{{ $appointment->status_label }}</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <div class="bg-white rounded-lg shadow-sm p-6 grid sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Patient</span>
                    <a href="{{ route('patients.show', $appointment->patient) }}" class="text-teal-600 hover:underline">{{ $appointment->patient->full_name }} ({{ $appointment->patient->uhid }})</a>
                </div>
                <div class="flex justify-between"><span class="text-gray-500">Doctor</span><span>{{ $appointment->doctor->full_name }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Branch</span><span>{{ $appointment->branch->name }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Time</span><span>{{ $appointment->scheduled_time_label }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Type</span><span class="capitalize">{{ $appointment->type }} · {{ str_replace('_',' ',$appointment->source) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Fee</span><span>₹{{ number_format($appointment->consultation_fee, 2) }} · {{ $appointment->fee_paid ? 'paid' : 'unpaid' }}</span></div>
                <div class="flex justify-between sm:col-span-2"><span class="text-gray-500">Reason</span><span>{{ $appointment->reason ?: '—' }}</span></div>
            </div>

            <div class="flex flex-wrap gap-3">
                @can('opd.manage-queue')
                    @if ($appointment->status === 'scheduled')
                        <form method="POST" action="{{ route('opd.check-in', $appointment) }}">@csrf
                            <button class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Check in</button>
                        </form>
                    @endif
                @endcan
                @can('vitals.record')
                    @if ($appointment->isOpen())
                        <a href="{{ route('opd.vitals.edit', $appointment) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">
                            {{ $appointment->vital ? 'Update vitals' : 'Record vitals' }}
                        </a>
                    @endif
                @endcan
                @can('opd.consult')
                    @if (! in_array($appointment->status, ['cancelled','no_show']))
                        <a href="{{ route('consultations.edit', $appointment) }}" class="rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">
                            {{ $appointment->consultation ? 'Open consultation' : 'Start consultation' }}
                        </a>
                    @endif
                @endcan
                @can('appointments.cancel')
                    @if ($appointment->isOpen())
                        <form method="POST" action="{{ route('appointments.cancel', $appointment) }}" onsubmit="return confirm('Cancel this appointment?')">
                            @csrf
                            <button class="rounded-md border border-red-300 text-red-600 px-4 py-2 text-sm hover:bg-red-50">Cancel</button>
                        </form>
                    @endif
                @endcan
                @can('billing.view')
                    @if ($appointment->invoice)
                        <a href="{{ route('billing.invoices.show', $appointment->invoice) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">View bill ({{ $appointment->invoice->invoice_no }})</a>
                    @elseif (! in_array($appointment->status, ['cancelled','no_show']))
                        @can('billing.create')
                            <form method="GET" action="{{ route('billing.invoices.create') }}">
                                <input type="hidden" name="appointment" value="{{ $appointment->id }}">
                                <button class="rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Generate bill</button>
                            </form>
                        @endcan
                    @endif
                @endcan
            </div>

            @if ($appointment->vital)
                @php $v = $appointment->vital; @endphp
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-800 mb-3">Vitals</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                        <div><span class="text-gray-500">BP</span> {{ $v->blood_pressure ?? '—' }}</div>
                        <div><span class="text-gray-500">Pulse</span> {{ $v->pulse_bpm ?? '—' }}</div>
                        <div><span class="text-gray-500">Temp</span> {{ $v->temperature_c ? $v->temperature_c.'°C' : '—' }}</div>
                        <div><span class="text-gray-500">SpO₂</span> {{ $v->spo2 ? $v->spo2.'%' : '—' }}</div>
                        <div><span class="text-gray-500">Height</span> {{ $v->height_cm ? $v->height_cm.' cm' : '—' }}</div>
                        <div><span class="text-gray-500">Weight</span> {{ $v->weight_kg ? $v->weight_kg.' kg' : '—' }}</div>
                        <div><span class="text-gray-500">BMI</span> {{ $v->bmi ?? '—' }}</div>
                        <div><span class="text-gray-500">Sugar</span> {{ $v->blood_sugar ?? '—' }}</div>
                    </div>
                </div>
            @endif

            @if ($appointment->consultation)
                @php $c = $appointment->consultation; @endphp
                <div class="bg-white rounded-lg shadow-sm p-6 space-y-3 text-sm">
                    <h3 class="font-medium text-gray-800">Consultation</h3>
                    @foreach ([
                        'Chief complaint' => $c->chief_complaint,
                        'History' => $c->history_present_illness,
                        'Examination' => $c->examination_findings,
                        'Diagnosis' => $c->diagnosis,
                        'Investigations advised' => $c->investigations_advised,
                        'Advice' => $c->advice,
                    ] as $label => $value)
                        @if ($value)
                            <div><span class="text-gray-500">{{ $label }}:</span> {{ $value }}</div>
                        @endif
                    @endforeach
                    @if ($c->followup_date)
                        <div><span class="text-gray-500">Follow-up:</span> {{ $c->followup_date->format('d M Y') }}</div>
                    @endif

                    @if ($c->items->count())
                        <div class="pt-2">
                            <div class="text-gray-500 mb-1">Prescription</div>
                            <table class="min-w-full text-xs">
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($c->items as $item)
                                        <tr>
                                            <td class="py-1 pr-3 font-medium">{{ $item->drug_name }} {{ $item->strength }}</td>
                                            <td class="py-1 pr-3">{{ $item->dosage }}</td>
                                            <td class="py-1 pr-3">{{ $item->duration_days ? $item->duration_days.' days' : '' }}</td>
                                            <td class="py-1 text-gray-500">{{ $item->instructions }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
