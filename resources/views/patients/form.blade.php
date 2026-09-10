<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            {{ $patient->exists ? 'Edit '.$patient->full_name.' ('.$patient->uhid.')' : 'Register patient' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="POST" enctype="multipart/form-data"
                  action="{{ $patient->exists ? route('patients.update', $patient) : route('patients.store') }}"
                  class="space-y-6">
                @csrf
                @if ($patient->exists) @method('PUT') @endif

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-800 mb-4">Identity</h3>
                    <div class="grid sm:grid-cols-4 gap-4">
                        <div>
                            <x-input-label for="salutation" value="Salutation" />
                            <x-text-input id="salutation" name="salutation" class="mt-1 block w-full" :value="old('salutation', $patient->salutation)" placeholder="Mr / Ms" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="first_name" value="First name *" />
                            <x-text-input id="first_name" name="first_name" class="mt-1 block w-full" :value="old('first_name', $patient->first_name)" required />
                        </div>
                        <div>
                            <x-input-label for="last_name" value="Last name" />
                            <x-text-input id="last_name" name="last_name" class="mt-1 block w-full" :value="old('last_name', $patient->last_name)" />
                        </div>
                        <div>
                            <x-input-label for="gender" value="Gender" />
                            <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">—</option>
                                @foreach (['male','female','other'] as $g)
                                    <option value="{{ $g }}" @selected(old('gender', $patient->gender) === $g)>{{ ucfirst($g) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="dob" value="Date of birth" />
                            <x-text-input id="dob" name="dob" type="date" class="mt-1 block w-full" :value="old('dob', optional($patient->dob)->format('Y-m-d'))" />
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600 mt-7">
                            <input type="checkbox" name="dob_estimated" value="1" class="rounded border-gray-300 text-teal-600" @checked(old('dob_estimated', $patient->dob_estimated))>
                            DOB estimated
                        </label>
                        <div>
                            <x-input-label for="blood_group" value="Blood group" />
                            <x-text-input id="blood_group" name="blood_group" class="mt-1 block w-full" :value="old('blood_group', $patient->blood_group)" placeholder="O+" />
                        </div>
                        <div>
                            <x-input-label for="marital_status" value="Marital status" />
                            <select id="marital_status" name="marital_status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">—</option>
                                @foreach (['single','married','other'] as $m)
                                    <option value="{{ $m }}" @selected(old('marital_status', $patient->marital_status) === $m)>{{ ucfirst($m) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-4">
                            <x-input-label for="photo" value="Photo" />
                            <input id="photo" name="photo" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-600">
                            @if ($patient->photo_path)
                                <img src="{{ Storage::url($patient->photo_path) }}" class="mt-2 h-16 w-16 rounded object-cover">
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-800 mb-4">Contact &amp; address</h3>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="phone" value="Phone" />
                            <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $patient->phone)" />
                        </div>
                        <div>
                            <x-input-label for="alt_phone" value="Alternate phone" />
                            <x-text-input id="alt_phone" name="alt_phone" class="mt-1 block w-full" :value="old('alt_phone', $patient->alt_phone)" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $patient->email)" />
                        </div>
                        <div class="sm:col-span-3">
                            <x-input-label for="address" value="Address" />
                            <x-text-input id="address" name="address" class="mt-1 block w-full" :value="old('address', $patient->address)" />
                        </div>
                        <div>
                            <x-input-label for="city" value="City" />
                            <x-text-input id="city" name="city" class="mt-1 block w-full" :value="old('city', $patient->city)" />
                        </div>
                        <div>
                            <x-input-label for="state" value="State" />
                            <x-text-input id="state" name="state" class="mt-1 block w-full" :value="old('state', $patient->state)" />
                        </div>
                        <div>
                            <x-input-label for="pincode" value="PIN code" />
                            <x-text-input id="pincode" name="pincode" class="mt-1 block w-full" :value="old('pincode', $patient->pincode)" />
                        </div>
                        <div>
                            <x-input-label for="id_proof_type" value="ID proof type" />
                            <x-text-input id="id_proof_type" name="id_proof_type" class="mt-1 block w-full" :value="old('id_proof_type', $patient->id_proof_type)" placeholder="Aadhaar / Voter ID" />
                        </div>
                        <div>
                            <x-input-label for="id_proof_number" value="ID proof number" />
                            <x-text-input id="id_proof_number" name="id_proof_number" class="mt-1 block w-full" :value="old('id_proof_number', $patient->id_proof_number)" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-800 mb-4">Guardian &amp; emergency contact</h3>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="guardian_name" value="Guardian name" />
                            <x-text-input id="guardian_name" name="guardian_name" class="mt-1 block w-full" :value="old('guardian_name', $patient->guardian_name)" />
                        </div>
                        <div>
                            <x-input-label for="guardian_relation" value="Relation" />
                            <x-text-input id="guardian_relation" name="guardian_relation" class="mt-1 block w-full" :value="old('guardian_relation', $patient->guardian_relation)" />
                        </div>
                        <div>
                            <x-input-label for="guardian_phone" value="Guardian phone" />
                            <x-text-input id="guardian_phone" name="guardian_phone" class="mt-1 block w-full" :value="old('guardian_phone', $patient->guardian_phone)" />
                        </div>
                        <div>
                            <x-input-label for="emergency_contact_name" value="Emergency contact" />
                            <x-text-input id="emergency_contact_name" name="emergency_contact_name" class="mt-1 block w-full" :value="old('emergency_contact_name', $patient->emergency_contact_name)" />
                        </div>
                        <div>
                            <x-input-label for="emergency_contact_phone" value="Emergency phone" />
                            <x-text-input id="emergency_contact_phone" name="emergency_contact_phone" class="mt-1 block w-full" :value="old('emergency_contact_phone', $patient->emergency_contact_phone)" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-800 mb-4">Medical background</h3>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="allergies" value="Allergies" />
                            <textarea id="allergies" name="allergies" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('allergies', $patient->allergies) }}</textarea>
                        </div>
                        <div>
                            <x-input-label for="chronic_conditions" value="Chronic conditions" />
                            <textarea id="chronic_conditions" name="chronic_conditions" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('chronic_conditions', $patient->chronic_conditions) }}</textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="notes" value="Notes" />
                            <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('notes', $patient->notes) }}</textarea>
                        </div>
                        @if ($patient->exists)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-teal-600" @checked(old('is_active', $patient->is_active))>
                                Active patient
                            </label>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>{{ $patient->exists ? 'Save changes' : 'Register patient' }}</x-primary-button>
                    <a href="{{ $patient->exists ? route('patients.show', $patient) : route('patients.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
