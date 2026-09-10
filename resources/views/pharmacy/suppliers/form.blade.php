<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $supplier->exists ? 'Edit supplier' : 'New supplier' }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />
            <form method="POST" action="{{ $supplier->exists ? route('pharmacy.suppliers.update', $supplier) : route('pharmacy.suppliers.store') }}"
                  class="bg-white rounded-lg shadow-sm p-6 space-y-5">
                @csrf
                @if ($supplier->exists) @method('PUT') @endif

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-input-label for="name" value="Name *" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $supplier->name)" required />
                    </div>
                    <div>
                        <x-input-label for="contact_person" value="Contact person" />
                        <x-text-input id="contact_person" name="contact_person" class="mt-1 block w-full" :value="old('contact_person', $supplier->contact_person)" />
                    </div>
                    <div>
                        <x-input-label for="phone" value="Phone" />
                        <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $supplier->phone)" />
                    </div>
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $supplier->email)" />
                    </div>
                    <div>
                        <x-input-label for="gstin" value="GSTIN" />
                        <x-text-input id="gstin" name="gstin" class="mt-1 block w-full" :value="old('gstin', $supplier->gstin)" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="address" value="Address" />
                        <x-text-input id="address" name="address" class="mt-1 block w-full" :value="old('address', $supplier->address)" />
                    </div>
                    <div>
                        <x-input-label for="drug_license_no" value="Drug licence no." />
                        <x-text-input id="drug_license_no" name="drug_license_no" class="mt-1 block w-full" :value="old('drug_license_no', $supplier->drug_license_no)" />
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-7">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-teal-600" @checked(old('is_active', $supplier->is_active ?? true))>
                        Active
                    </label>
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>{{ $supplier->exists ? 'Save' : 'Create' }}</x-primary-button>
                    <a href="{{ route('pharmacy.suppliers.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
