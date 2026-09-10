<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">{{ $patient->full_name }}</h2>
                <p class="text-sm text-gray-500 font-mono">{{ $patient->uhid }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if ($patient->currentAdmission)
                    <a href="{{ route('ipd.admissions.show', $patient->currentAdmission) }}" class="rounded-md bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700">Currently admitted →</a>
                @else
                    @can('appointments.create')
                        <a href="{{ route('appointments.create', ['patient' => $patient->id]) }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Book appointment</a>
                    @endcan
                    @can('ipd.admit')
                        <a href="{{ route('ipd.admissions.create', ['patient' => $patient->id]) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Admit</a>
                    @endcan
                @endif
                @can('patients.update')
                    <a href="{{ route('patients.edit', $patient) }}" class="text-sm text-gray-600 hover:underline">Edit</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            @if ($patient->allergies)
                <div class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    <span class="font-semibold">Allergies:</span> {{ $patient->allergies }}
                </div>
            @endif

            <div class="grid sm:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6 sm:col-span-2">
                    <h3 class="font-medium text-gray-800 mb-3">Demographics</h3>
                    <div class="grid sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Age</span><span>{{ $patient->age ?? '—' }}{{ $patient->dob_estimated ? ' (est.)' : '' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Gender</span><span class="capitalize">{{ $patient->gender ?? '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Blood group</span><span>{{ $patient->blood_group ?? '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Marital status</span><span class="capitalize">{{ $patient->marital_status ?? '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Phone</span><span>{{ $patient->phone ?? '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Email</span><span>{{ $patient->email ?? '—' }}</span></div>
                        <div class="flex justify-between sm:col-span-2"><span class="text-gray-500">Address</span><span class="text-right">{{ collect([$patient->address, $patient->city, $patient->state, $patient->pincode])->filter()->implode(', ') ?: '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">ID proof</span><span>{{ $patient->id_proof_type ? $patient->id_proof_type.' · '.$patient->id_proof_number : '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Registered</span><span>{{ $patient->created_at->format('d M Y') }} · {{ $patient->registeredBranch?->name ?? '—' }}</span></div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 text-sm space-y-3">
                    <div>
                        <div class="text-gray-500 mb-1">Chronic conditions</div>
                        <div>{{ $patient->chronic_conditions ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 mb-1">Guardian</div>
                        <div>{{ $patient->guardian_name ? $patient->guardian_name.' ('.$patient->guardian_relation.') · '.$patient->guardian_phone : '—' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 mb-1">Emergency contact</div>
                        <div>{{ $patient->emergency_contact_name ? $patient->emergency_contact_name.' · '.$patient->emergency_contact_phone : '—' }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="font-medium text-gray-800 mb-3">Visit history</h3>
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-500">
                        <tr>
                            <th class="py-2 pr-3 font-medium">Date</th>
                            <th class="py-2 pr-3 font-medium">Appt No.</th>
                            <th class="py-2 pr-3 font-medium">Doctor</th>
                            <th class="py-2 pr-3 font-medium">Type</th>
                            <th class="py-2 pr-3 font-medium">Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($patient->appointments as $appt)
                            <tr>
                                <td class="py-2 pr-3">{{ $appt->scheduled_date->format('d M Y') }}</td>
                                <td class="py-2 pr-3 font-mono text-gray-500">{{ $appt->appointment_no }}</td>
                                <td class="py-2 pr-3">{{ $appt->doctor->full_name }}</td>
                                <td class="py-2 pr-3 capitalize">{{ $appt->type }}</td>
                                <td class="py-2 pr-3">
                                    <span class="text-xs rounded-full px-2 py-0.5 bg-gray-100 text-gray-600">{{ $appt->status_label }}</span>
                                </td>
                                <td class="py-2 text-right">
                                    <a href="{{ route('appointments.show', $appt) }}" class="text-teal-600 hover:underline">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 text-center text-gray-400">No visits yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @can('billing.view')
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-medium text-gray-800">Invoices</h3>
                        @can('billing.create')
                            <a href="{{ route('billing.invoices.create', ['patient' => $patient->id]) }}" class="text-sm text-teal-600 hover:underline">+ New invoice</a>
                        @endcan
                    </div>
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($patient->invoices()->latest('id')->limit(10)->get() as $inv)
                                <tr>
                                    <td class="py-2 font-mono text-gray-500">{{ $inv->invoice_no }}</td>
                                    <td class="py-2">{{ strtoupper($inv->type) }}</td>
                                    <td class="py-2 text-gray-500">{{ $inv->invoice_date->format('d M Y') }}</td>
                                    <td class="py-2 text-right">₹{{ number_format($inv->total, 2) }}</td>
                                    <td class="py-2 text-right {{ $inv->balance > 0 ? 'text-rose-600' : 'text-gray-400' }}">bal ₹{{ number_format($inv->balance, 2) }}</td>
                                    <td class="py-2 text-right"><a href="{{ route('billing.invoices.show', $inv) }}" class="text-teal-600 hover:underline">Open</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-4 text-center text-gray-400">No invoices.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
