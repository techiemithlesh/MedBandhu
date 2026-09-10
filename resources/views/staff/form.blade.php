<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            {{ $staff->exists ? 'Edit '.$staff->full_name : 'Add staff' }}
        </h2>
    </x-slot>

    @php $d = old('doctor', optional($staff->doctorProfile)->toArray() ?? []); @endphp

    <div class="py-8" x-data="{ type: '{{ old('type', $staff->type) }}', login: {{ old('create_login') ? 'true' : 'false' }} }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="POST" enctype="multipart/form-data"
                  action="{{ $staff->exists ? route('staff.update', $staff) : route('staff.store') }}"
                  class="space-y-6">
                @csrf
                @if ($staff->exists) @method('PUT') @endif

                {{-- Identity --}}
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-800 mb-4">Identity</h3>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="employee_code" value="Employee code *" />
                            <x-text-input id="employee_code" name="employee_code" class="mt-1 block w-full" :value="old('employee_code', $staff->employee_code)" required />
                        </div>
                        <div>
                            <x-input-label for="type" value="Staff type *" />
                            <select id="type" name="type" x-model="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                @foreach ($types as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="department_id" value="Department" />
                            <select id="department_id" name="department_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">—</option>
                                @foreach ($departments as $id => $name)
                                    <option value="{{ $id }}" @selected(old('department_id', $staff->department_id) == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="salutation" value="Salutation" />
                            <x-text-input id="salutation" name="salutation" class="mt-1 block w-full" :value="old('salutation', $staff->salutation)" placeholder="Dr / Mr / Ms" />
                        </div>
                        <div>
                            <x-input-label for="first_name" value="First name *" />
                            <x-text-input id="first_name" name="first_name" class="mt-1 block w-full" :value="old('first_name', $staff->first_name)" required />
                        </div>
                        <div>
                            <x-input-label for="last_name" value="Last name" />
                            <x-text-input id="last_name" name="last_name" class="mt-1 block w-full" :value="old('last_name', $staff->last_name)" />
                        </div>
                        <div>
                            <x-input-label for="gender" value="Gender" />
                            <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">—</option>
                                @foreach (['male','female','other'] as $g)
                                    <option value="{{ $g }}" @selected(old('gender', $staff->gender) === $g)>{{ ucfirst($g) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="dob" value="Date of birth" />
                            <x-text-input id="dob" name="dob" type="date" class="mt-1 block w-full" :value="old('dob', optional($staff->dob)->format('Y-m-d'))" />
                        </div>
                        <div>
                            <x-input-label for="blood_group" value="Blood group" />
                            <x-text-input id="blood_group" name="blood_group" class="mt-1 block w-full" :value="old('blood_group', $staff->blood_group)" placeholder="O+" />
                        </div>
                        <div class="sm:col-span-3">
                            <x-input-label for="photo" value="Photo" />
                            <input id="photo" name="photo" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-600">
                            @if ($staff->photo_path)
                                <img src="{{ Storage::url($staff->photo_path) }}" class="mt-2 h-16 w-16 rounded object-cover">
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Contact --}}
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-800 mb-4">Contact</h3>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="phone" value="Phone" />
                            <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $staff->phone)" />
                        </div>
                        <div>
                            <x-input-label for="alt_phone" value="Alternate phone" />
                            <x-text-input id="alt_phone" name="alt_phone" class="mt-1 block w-full" :value="old('alt_phone', $staff->alt_phone)" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $staff->email)" />
                        </div>
                        <div class="sm:col-span-3">
                            <x-input-label for="address" value="Address" />
                            <x-text-input id="address" name="address" class="mt-1 block w-full" :value="old('address', $staff->address)" />
                        </div>
                        <div>
                            <x-input-label for="city" value="City" />
                            <x-text-input id="city" name="city" class="mt-1 block w-full" :value="old('city', $staff->city)" />
                        </div>
                        <div>
                            <x-input-label for="state" value="State" />
                            <x-text-input id="state" name="state" class="mt-1 block w-full" :value="old('state', $staff->state)" />
                        </div>
                        <div>
                            <x-input-label for="pincode" value="PIN code" />
                            <x-text-input id="pincode" name="pincode" class="mt-1 block w-full" :value="old('pincode', $staff->pincode)" />
                        </div>
                    </div>
                </div>

                {{-- Employment --}}
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-800 mb-4">Employment</h3>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="branch_id" value="Branch *" />
                            <select id="branch_id" name="branch_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                @foreach ($branches as $id => $name)
                                    <option value="{{ $id }}" @selected(old('branch_id', $staff->branch_id) == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="designation" value="Designation" />
                            <x-text-input id="designation" name="designation" class="mt-1 block w-full" :value="old('designation', $staff->designation)" />
                        </div>
                        <div>
                            <x-input-label for="employment_type" value="Employment type *" />
                            <select id="employment_type" name="employment_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                @foreach (['permanent','contract','visiting','intern'] as $et)
                                    <option value="{{ $et }}" @selected(old('employment_type', $staff->employment_type) === $et)>{{ ucfirst($et) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="joined_on" value="Joined on" />
                            <x-text-input id="joined_on" name="joined_on" type="date" class="mt-1 block w-full" :value="old('joined_on', optional($staff->joined_on)->format('Y-m-d'))" />
                        </div>
                        <div>
                            <x-input-label for="left_on" value="Left on" />
                            <x-text-input id="left_on" name="left_on" type="date" class="mt-1 block w-full" :value="old('left_on', optional($staff->left_on)->format('Y-m-d'))" />
                        </div>
                        <div>
                            <x-input-label for="status" value="Status *" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                @foreach (['active','on_leave','suspended','resigned'] as $st)
                                    <option value="{{ $st }}" @selected(old('status', $staff->status) === $st)>{{ ucfirst(str_replace('_',' ',$st)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-3">
                            <x-input-label for="notes" value="Notes" />
                            <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('notes', $staff->notes) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Doctor profile --}}
                <div class="bg-white rounded-lg shadow-sm p-6" x-show="type === 'doctor'" x-cloak>
                    <h3 class="font-medium text-gray-800 mb-4">Doctor profile</h3>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="doc_spec" value="Specialization" />
                            <x-text-input id="doc_spec" name="doctor[specialization]" class="mt-1 block w-full" :value="$d['specialization'] ?? ''" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="doc_qual" value="Qualifications" />
                            <x-text-input id="doc_qual" name="doctor[qualifications]" class="mt-1 block w-full" :value="$d['qualifications'] ?? ''" placeholder="MBBS, MD (Medicine)" />
                        </div>
                        <div>
                            <x-input-label for="doc_reg" value="Registration no." />
                            <x-text-input id="doc_reg" name="doctor[registration_no]" class="mt-1 block w-full" :value="$d['registration_no'] ?? ''" />
                        </div>
                        <div>
                            <x-input-label for="doc_council" value="Registration council" />
                            <x-text-input id="doc_council" name="doctor[registration_council]" class="mt-1 block w-full" :value="$d['registration_council'] ?? ''" />
                        </div>
                        <div>
                            <x-input-label for="doc_exp" value="Experience (years)" />
                            <x-text-input id="doc_exp" name="doctor[experience_years]" type="number" min="0" class="mt-1 block w-full" :value="$d['experience_years'] ?? ''" />
                        </div>
                        <div>
                            <x-input-label for="doc_fee" value="Consultation fee (₹)" />
                            <x-text-input id="doc_fee" name="doctor[consultation_fee]" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="$d['consultation_fee'] ?? '0'" />
                        </div>
                        <div>
                            <x-input-label for="doc_ffee" value="Follow-up fee (₹)" />
                            <x-text-input id="doc_ffee" name="doctor[followup_fee]" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="$d['followup_fee'] ?? '0'" />
                        </div>
                        <div>
                            <x-input-label for="doc_fdays" value="Follow-up valid (days)" />
                            <x-text-input id="doc_fdays" name="doctor[followup_valid_days]" type="number" min="0" class="mt-1 block w-full" :value="$d['followup_valid_days'] ?? '7'" />
                        </div>
                        <div>
                            <x-input-label for="doc_dur" value="Appointment slot (min)" />
                            <x-text-input id="doc_dur" name="doctor[appointment_duration_min]" type="number" min="5" class="mt-1 block w-full" :value="$d['appointment_duration_min'] ?? '15'" />
                        </div>
                        <div class="flex items-center gap-4 pt-6">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="doctor[is_surgeon]" value="1" class="rounded border-gray-300 text-teal-600" @checked($d['is_surgeon'] ?? false)> Surgeon
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="doctor[online_consultation]" value="1" class="rounded border-gray-300 text-teal-600" @checked($d['online_consultation'] ?? false)> Online consult
                            </label>
                        </div>
                        <div class="sm:col-span-3">
                            <x-input-label for="doc_bio" value="Bio" />
                            <textarea id="doc_bio" name="doctor[bio]" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ $d['bio'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Login account --}}
                @if (! $staff->user_id)
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-800">
                            <input type="checkbox" name="create_login" value="1" x-model="login" class="rounded border-gray-300 text-teal-600">
                            Create a login account for this person
                        </label>
                        <div x-show="login" x-cloak class="grid sm:grid-cols-3 gap-4 mt-4">
                            <div>
                                <x-input-label for="login_role" value="Role" />
                                <select id="login_role" name="login_role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">—</option>
                                    @foreach ($assignableRoles as $role)
                                        <option value="{{ $role }}" @selected(old('login_role') === $role)>{{ $role }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="login_password" value="Temp password" />
                                <x-text-input id="login_password" name="login_password" class="mt-1 block w-full" />
                            </div>
                            <p class="text-xs text-gray-400 sm:col-span-1 self-end pb-2">
                                Uses the staff email, or a generated one if blank.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm text-gray-500">
                        Linked to login account #{{ $staff->user_id }}.
                    </div>
                @endif

                <div class="flex items-center gap-3">
                    <x-primary-button>{{ $staff->exists ? 'Save changes' : 'Add staff' }}</x-primary-button>
                    <a href="{{ route('staff.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
