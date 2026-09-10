<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $service->exists ? 'Edit service' : 'New service' }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />
            <form method="POST" action="{{ $service->exists ? route('billing.services.update', $service) : route('billing.services.store') }}"
                  class="bg-white rounded-lg shadow-sm p-6 space-y-5">
                @csrf
                @if ($service->exists) @method('PUT') @endif

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-input-label for="name" value="Name *" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $service->name)" required />
                    </div>
                    <div>
                        <x-input-label for="code" value="Code *" />
                        <x-text-input id="code" name="code" class="mt-1 block w-full uppercase" :value="old('code', $service->code)" maxlength="20" required />
                    </div>
                    <div>
                        <x-input-label for="category" value="Category *" />
                        <select id="category" name="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach ($categories as $val => $label)
                                <option value="{{ $val }}" @selected(old('category', $service->category) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="department_id" value="Department" />
                        <select id="department_id" name="department_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">—</option>
                            @foreach ($departments as $id => $name)
                                <option value="{{ $id }}" @selected(old('department_id', $service->department_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="price" value="Price (₹) *" />
                        <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('price', $service->price ?? 0)" required />
                    </div>
                    <div>
                        <x-input-label for="gst_rate" value="GST rate (%) *" />
                        <x-text-input id="gst_rate" name="gst_rate" type="number" step="0.01" min="0" max="28" class="mt-1 block w-full" :value="old('gst_rate', $service->gst_rate ?? 0)" required />
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-7">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-teal-600" @checked(old('is_active', $service->is_active ?? true))>
                        Active
                    </label>
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>{{ $service->exists ? 'Save' : 'Create' }}</x-primary-button>
                    <a href="{{ route('billing.services.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
