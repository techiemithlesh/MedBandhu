<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $medicine->exists ? 'Edit '.$medicine->display_name : 'Add medicine' }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />
            <form method="POST" action="{{ $medicine->exists ? route('pharmacy.medicines.update', $medicine) : route('pharmacy.medicines.store') }}"
                  class="bg-white rounded-lg shadow-sm p-6 space-y-5">
                @csrf
                @if ($medicine->exists) @method('PUT') @endif

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="name" value="Brand / trade name *" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $medicine->name)" required />
                    </div>
                    <div>
                        <x-input-label for="strength" value="Strength" />
                        <x-text-input id="strength" name="strength" class="mt-1 block w-full" :value="old('strength', $medicine->strength)" placeholder="500 mg" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="generic_name" value="Generic name" />
                        <x-text-input id="generic_name" name="generic_name" class="mt-1 block w-full" :value="old('generic_name', $medicine->generic_name)" />
                    </div>
                    <div>
                        <x-input-label for="form" value="Form *" />
                        <select id="form" name="form" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach ($forms as $val => $label)
                                <option value="{{ $val }}" @selected(old('form', $medicine->form) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <x-input-label for="unit" value="Unit *" />
                            <x-text-input id="unit" name="unit" class="mt-1 block w-full" :value="old('unit', $medicine->unit ?? 'tablet')" required />
                        </div>
                        <div>
                            <x-input-label for="pack_size" value="Pack size *" />
                            <x-text-input id="pack_size" name="pack_size" type="number" min="1" class="mt-1 block w-full" :value="old('pack_size', $medicine->pack_size ?? 1)" required />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="drug_category_id" value="Category" />
                        <select id="drug_category_id" name="drug_category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">—</option>
                            @foreach ($categories as $id => $name)
                                <option value="{{ $id }}" @selected(old('drug_category_id', $medicine->drug_category_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="manufacturer_id" value="Manufacturer" />
                        <select id="manufacturer_id" name="manufacturer_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">—</option>
                            @foreach ($manufacturers as $id => $name)
                                <option value="{{ $id }}" @selected(old('manufacturer_id', $medicine->manufacturer_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="hsn_code" value="HSN code" />
                        <x-text-input id="hsn_code" name="hsn_code" class="mt-1 block w-full" :value="old('hsn_code', $medicine->hsn_code)" />
                    </div>
                    <div>
                        <x-input-label for="gst_rate" value="GST rate (%) *" />
                        <x-text-input id="gst_rate" name="gst_rate" type="number" step="0.01" min="0" max="28" class="mt-1 block w-full" :value="old('gst_rate', $medicine->gst_rate ?? 12)" required />
                    </div>
                    <div>
                        <x-input-label for="schedule" value="Schedule" />
                        <select id="schedule" name="schedule" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach (['none' => 'None', 'H' => 'Schedule H', 'H1' => 'Schedule H1', 'X' => 'Schedule X', 'OTC' => 'OTC'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('schedule', $medicine->schedule ?? 'none') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="reorder_level" value="Reorder level *" />
                        <x-text-input id="reorder_level" name="reorder_level" type="number" min="0" class="mt-1 block w-full" :value="old('reorder_level', $medicine->reorder_level ?? 0)" required />
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-7">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-teal-600" @checked(old('is_active', $medicine->is_active ?? true))>
                        Active
                    </label>
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>{{ $medicine->exists ? 'Save' : 'Add medicine' }}</x-primary-button>
                    <a href="{{ route('pharmacy.medicines.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
