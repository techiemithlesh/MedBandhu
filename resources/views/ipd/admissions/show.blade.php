<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">{{ $admission->patient->full_name }}
                    <span class="text-sm text-gray-400 font-mono">{{ $admission->admission_no }}</span>
                </h2>
                <p class="text-sm text-gray-500">
                    {{ $admission->patient->uhid }} ·
                    {{ $admission->patient->age ?? '—' }}{{ $admission->patient->gender ? ' / '.ucfirst($admission->patient->gender[0]) : '' }} ·
                    <span class="capitalize">{{ $admission->status }}</span>
                </p>
            </div>
            <a href="{{ route('ipd.board') }}" class="text-sm text-gray-500 hover:underline">Bed board</a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ tab: 'overview', panel: null }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            @if ($admission->patient->allergies)
                <div class="rounded-md bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-800">
                    <span class="font-semibold">Allergies:</span> {{ $admission->patient->allergies }}
                </div>
            @endif

            {{-- Summary bar --}}
            <div class="bg-white rounded-lg shadow-sm p-5 grid sm:grid-cols-4 gap-4 text-sm">
                <div><div class="text-gray-400">Bed</div><div class="font-medium">{{ $admission->bed?->ward?->name ?? '—' }} / {{ $admission->bed?->bed_number ?? '—' }}</div></div>
                <div><div class="text-gray-400">Admitting doctor</div><div class="font-medium">{{ $admission->admittingDoctor?->full_name ?? '—' }}</div></div>
                <div><div class="text-gray-400">Admitted</div><div class="font-medium">{{ $admission->admitted_at->format('d M Y, H:i') }}</div></div>
                <div><div class="text-gray-400">Day count</div><div class="font-medium">{{ $admission->days_admitted }} day(s)</div></div>
            </div>

            {{-- Action buttons --}}
            @if ($admission->isActive())
                <div class="flex flex-wrap gap-3">
                    @can('ipd.transfer')
                        <button @click="panel = panel === 'transfer' ? null : 'transfer'" class="rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Transfer bed</button>
                    @endcan
                    @can('ipd.discharge')
                        <button @click="panel = panel === 'discharge' ? null : 'discharge'" class="rounded-md bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700">Discharge</button>
                    @endcan
                </div>

                @can('ipd.transfer')
                    <div x-show="panel === 'transfer'" x-cloak class="bg-white rounded-lg shadow-sm p-6">
                        <form method="POST" action="{{ route('ipd.admissions.transfer', $admission) }}" class="flex flex-wrap items-end gap-3">
                            @csrf
                            <div>
                                <x-input-label for="t_bed" value="Move to bed" />
                                <select id="t_bed" name="bed_id" class="mt-1 border-gray-300 rounded-md text-sm" required>
                                    <option value="">— Select —</option>
                                    @foreach ($freeBeds as $ward)
                                        <optgroup label="{{ $ward->name }}">
                                            @foreach ($ward->beds as $bed)
                                                <option value="{{ $bed->id }}">{{ $bed->bed_number }} — ₹{{ number_format($bed->daily_charge, 0) }}/day</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1 min-w-[200px]">
                                <x-input-label for="t_reason" value="Reason" />
                                <x-text-input id="t_reason" name="reason" class="mt-1 block w-full" />
                            </div>
                            <x-primary-button>Confirm transfer</x-primary-button>
                        </form>
                    </div>
                @endcan

                @can('ipd.discharge')
                    <div x-show="panel === 'discharge'" x-cloak class="bg-white rounded-lg shadow-sm p-6">
                        <form method="POST" action="{{ route('ipd.admissions.discharge', $admission) }}" class="space-y-4">
                            @csrf
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="discharge_type" value="Discharge type *" />
                                    <select id="discharge_type" name="discharge_type" class="mt-1 block w-full border-gray-300 rounded-md text-sm">
                                        @foreach (['routine' => 'Routine', 'lama' => 'LAMA (against advice)', 'referral' => 'Referred out', 'expired' => 'Expired'] as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="discharge_doctor_id" value="Discharge doctor" />
                                    <select id="discharge_doctor_id" name="discharge_doctor_id" class="mt-1 block w-full border-gray-300 rounded-md text-sm">
                                        <option value="">Same as admitting</option>
                                        @foreach ($doctors as $id => $name)
                                            <option value="{{ $id }}" @selected($admission->admitting_doctor_id == $id)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="discharged_at" value="Discharge time" />
                                    <x-text-input id="discharged_at" name="discharged_at" type="datetime-local" class="mt-1 block w-full" :value="now()->format('Y-m-d\TH:i')" />
                                </div>
                            </div>
                            <div>
                                <x-input-label for="discharge_summary" value="Discharge summary" />
                                <textarea id="discharge_summary" name="discharge_summary" rows="4" class="mt-1 block w-full border-gray-300 rounded-md text-sm">{{ $admission->provisional_diagnosis }}</textarea>
                            </div>
                            <div class="rounded bg-amber-50 border border-amber-200 px-3 py-2 text-xs text-amber-700">
                                Bed charges will be posted: {{ $admission->days_admitted }} day(s) so far. Estimated total so far: ₹{{ number_format($admission->days_admitted * ($admission->bed?->daily_charge ?? 0), 0) }}.
                            </div>
                            <button class="rounded-md bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700"
                                    onclick="return confirm('Discharge this patient?')">Confirm discharge</button>
                        </form>
                    </div>
                @endcan
            @else
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm text-gray-600">
                    Discharged {{ $admission->discharged_at?->format('d M Y, H:i') }} · {{ ucfirst($admission->discharge_type) }}
                    @if ($admission->dischargeDoctor) · {{ $admission->dischargeDoctor->full_name }} @endif
                </div>
            @endif

            {{-- Tabs --}}
            <div class="border-b border-gray-200 flex gap-6 text-sm">
                @foreach (['overview' => 'Overview', 'notes' => 'Nursing notes ('.$admission->nursingNotes->count().')', 'charges' => 'Charges', 'timeline' => 'Bed timeline'] as $key => $label)
                    <button @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'border-teal-600 text-teal-700' : 'border-transparent text-gray-500'"
                            class="pb-2 border-b-2 font-medium">{{ $label }}</button>
                @endforeach
            </div>

            {{-- Overview --}}
            <div x-show="tab === 'overview'" class="bg-white rounded-lg shadow-sm p-6 grid sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Source</span><span class="capitalize">{{ str_replace('_',' ',$admission->source) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Department</span><span>{{ $admission->department?->name ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Expected discharge</span><span>{{ optional($admission->expected_discharge_on)->format('d M Y') ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Attendant</span><span>{{ $admission->attendant_name ? $admission->attendant_name.' ('.$admission->attendant_relation.') · '.$admission->attendant_phone : '—' }}</span></div>
                <div class="sm:col-span-2 pt-2"><span class="text-gray-500">Provisional diagnosis</span><p class="mt-1">{{ $admission->provisional_diagnosis ?: '—' }}</p></div>
                @if ($admission->discharge_summary)
                    <div class="sm:col-span-2 pt-2"><span class="text-gray-500">Discharge summary</span><p class="mt-1 whitespace-pre-line">{{ $admission->discharge_summary }}</p></div>
                @endif
            </div>

            {{-- Nursing notes --}}
            <div x-show="tab === 'notes'" x-cloak class="space-y-4">
                @can('nursing.notes')
                    <form method="POST" action="{{ route('ipd.notes.store', $admission) }}" class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap items-end gap-3">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Category</label>
                            <select name="category" class="border-gray-300 rounded-md text-sm">
                                @foreach (\App\Models\NursingNote::CATEGORIES as $c)
                                    <option value="{{ $c }}">{{ ucfirst($c) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-[240px]">
                            <label class="block text-xs text-gray-500 mb-1">Note</label>
                            <input name="note" required class="block w-full border-gray-300 rounded-md text-sm" placeholder="Observation, medication given, procedure…">
                        </div>
                        <x-primary-button>Add note</x-primary-button>
                    </form>
                @endcan

                <div class="bg-white rounded-lg shadow-sm divide-y divide-gray-100">
                    @forelse ($admission->nursingNotes as $note)
                        <div class="p-4 text-sm flex justify-between gap-4">
                            <div>
                                <span class="text-xs rounded bg-gray-100 text-gray-600 px-1.5 py-0.5">{{ ucfirst($note->category) }}</span>
                                <span class="ml-2">{{ $note->note }}</span>
                            </div>
                            <div class="text-xs text-gray-400 whitespace-nowrap text-right">
                                {{ $note->recorded_at->format('d M, H:i') }}<br>{{ $note->recorder?->name }}
                                @if ($note->recorded_by === auth()->id())
                                    <form method="POST" action="{{ route('ipd.notes.destroy', [$admission, $note]) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="text-red-400 hover:underline">delete</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="p-6 text-center text-gray-400 text-sm">No nursing notes yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Charges --}}
            <div x-show="tab === 'charges'" x-cloak class="space-y-4">
                <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-500">
                            <tr><th class="px-4 py-2 font-medium">Date</th><th class="px-4 py-2 font-medium">Type</th><th class="px-4 py-2 font-medium">Description</th><th class="px-4 py-2 font-medium text-right">Qty</th><th class="px-4 py-2 font-medium text-right">Rate</th><th class="px-4 py-2 font-medium text-right">Amount</th><th></th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($admission->charges as $charge)
                                <tr>
                                    <td class="px-4 py-2">{{ $charge->charge_date->format('d M') }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $charge->type }}</td>
                                    <td class="px-4 py-2">{{ $charge->description }}</td>
                                    <td class="px-4 py-2 text-right">{{ rtrim(rtrim(number_format($charge->quantity, 2), '0'), '.') }}</td>
                                    <td class="px-4 py-2 text-right">₹{{ number_format($charge->unit_price, 2) }}</td>
                                    <td class="px-4 py-2 text-right">₹{{ number_format($charge->amount, 2) }}</td>
                                    <td class="px-4 py-2 text-right">
                                        @if (! $charge->auto_generated)
                                            @can('ipd.discharge')
                                                <form method="POST" action="{{ route('ipd.charges.destroy', [$admission, $charge]) }}" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button class="text-red-400 hover:underline text-xs">remove</button>
                                                </form>
                                            @endcan
                                        @else
                                            <span class="text-xs text-gray-300">auto</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">No charges posted yet.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-gray-200 font-semibold">
                                <td colspan="5" class="px-4 py-2 text-right">Total</td>
                                <td class="px-4 py-2 text-right">₹{{ number_format($admission->chargesTotal(), 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @can('billing.view')
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($admission->invoices->isNotEmpty())
                            <a href="{{ route('billing.invoices.show', $admission->invoices->first()) }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                                Open bill ({{ $admission->invoices->first()->invoice_no }})
                            </a>
                        @endif
                        @can('billing.create')
                            <form method="GET" action="{{ route('billing.invoices.create') }}" class="flex items-center gap-2">
                                <input type="hidden" name="admission" value="{{ $admission->id }}">
                                <label class="inline-flex items-center gap-1 text-xs text-gray-600">
                                    <input type="checkbox" name="pharmacy" value="1" class="rounded border-gray-300 text-teal-600"> include credit pharmacy bills
                                </label>
                                <button class="rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">
                                    {{ $admission->invoices->isNotEmpty() ? 'Rebuild bill from charges' : 'Generate final bill' }}
                                </button>
                            </form>
                        @endcan
                    </div>
                @endcan

                @can('ipd.discharge')
                    <div class="flex flex-wrap items-start gap-4">
                        <form method="POST" action="{{ route('ipd.admissions.charges.generate', $admission) }}">
                            @csrf
                            <button class="rounded-md border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50">Refresh bed charges</button>
                        </form>

                        <form method="POST" action="{{ route('ipd.charges.store', $admission) }}" class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap items-end gap-3">
                            @csrf
                            <div><label class="block text-xs text-gray-500 mb-1">Type</label>
                                <select name="type" class="border-gray-300 rounded-md text-sm">
                                    @foreach (['service','procedure','consumable','other'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach
                                </select></div>
                            <div><label class="block text-xs text-gray-500 mb-1">Date</label>
                                <input type="date" name="charge_date" value="{{ now()->toDateString() }}" class="border-gray-300 rounded-md text-sm"></div>
                            <div class="flex-1 min-w-[160px]"><label class="block text-xs text-gray-500 mb-1">Description</label>
                                <input name="description" required class="block w-full border-gray-300 rounded-md text-sm"></div>
                            <div><label class="block text-xs text-gray-500 mb-1">Rate ₹</label>
                                <input name="unit_price" type="number" step="0.01" min="0" required class="w-24 border-gray-300 rounded-md text-sm"></div>
                            <div><label class="block text-xs text-gray-500 mb-1">Qty</label>
                                <input name="quantity" type="number" step="0.01" min="0.01" value="1" required class="w-20 border-gray-300 rounded-md text-sm"></div>
                            <x-primary-button>Add</x-primary-button>
                        </form>
                    </div>
                @endcan
            </div>

            {{-- Bed timeline --}}
            <div x-show="tab === 'timeline'" x-cloak class="bg-white rounded-lg shadow-sm p-6">
                <ol class="relative border-l border-gray-200 ml-2 space-y-4">
                    @foreach ($admission->bedMovements as $m)
                        <li class="ml-4 text-sm">
                            <div class="absolute -left-1.5 w-3 h-3 rounded-full {{ $m->ended_at ? 'bg-gray-300' : 'bg-teal-500' }}"></div>
                            <div class="font-medium text-gray-800">{{ $m->ward->name }} — {{ $m->bed->bed_number }}</div>
                            <div class="text-gray-500">
                                {{ $m->started_at->format('d M Y, H:i') }}
                                → {{ $m->ended_at ? $m->ended_at->format('d M Y, H:i') : 'current' }}
                                · ₹{{ number_format($m->daily_charge, 0) }}/day · {{ $m->billableDays() }} day(s)
                            </div>
                            @if ($m->reason)<div class="text-xs text-gray-400">{{ $m->reason }}</div>@endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</x-app-layout>
