<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $ward->exists ? 'Edit ward' : 'New ward' }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="POST" action="{{ $ward->exists ? route('wards.update', $ward) : route('wards.store') }}"
                  class="bg-white rounded-lg shadow-sm p-6 space-y-5">
                @csrf
                @if ($ward->exists) @method('PUT') @endif

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="name" value="Ward name *" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $ward->name)" required />
                    </div>
                    <div>
                        <x-input-label for="code" value="Code *" />
                        <x-text-input id="code" name="code" class="mt-1 block w-full uppercase" :value="old('code', $ward->code)" maxlength="20" required />
                    </div>
                    <div>
                        <x-input-label for="type" value="Type *" />
                        <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach ($types as $val => $label)
                                <option value="{{ $val }}" @selected(old('type', $ward->type) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="department_id" value="Department" />
                        <select id="department_id" name="department_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">—</option>
                            @foreach ($departments as $id => $name)
                                <option value="{{ $id }}" @selected(old('department_id', $ward->department_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="floor" value="Floor" />
                        <x-text-input id="floor" name="floor" class="mt-1 block w-full" :value="old('floor', $ward->floor)" placeholder="Ground / 1st" />
                    </div>
                    <div>
                        <x-input-label for="gender_restriction" value="Gender restriction" />
                        <select id="gender_restriction" name="gender_restriction" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach (['any' => 'No restriction', 'male' => 'Male only', 'female' => 'Female only'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('gender_restriction', $ward->gender_restriction) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="default_daily_charge" value="Default daily charge (₹) *" />
                        <x-text-input id="default_daily_charge" name="default_daily_charge" type="number" step="0.01" min="0"
                                      class="mt-1 block w-full" :value="old('default_daily_charge', $ward->default_daily_charge ?? 0)" required />
                        <p class="text-xs text-gray-400 mt-1">Used as the default when adding beds.</p>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-7">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-teal-600" @checked(old('is_active', $ward->is_active ?? true))>
                        Active
                    </label>
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>{{ $ward->exists ? 'Save' : 'Create ward' }}</x-primary-button>
                    <a href="{{ route('wards.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
