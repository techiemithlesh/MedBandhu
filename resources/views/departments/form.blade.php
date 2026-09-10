<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            {{ $department->exists ? 'Edit department' : 'New department' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="POST"
                  action="{{ $department->exists ? route('departments.update', $department) : route('departments.store') }}"
                  class="bg-white rounded-lg shadow-sm p-6 space-y-5">
                @csrf
                @if ($department->exists) @method('PUT') @endif

                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $department->name)" required />
                </div>

                <div>
                    <x-input-label for="code" value="Code" />
                    <x-text-input id="code" name="code" class="mt-1 block w-full uppercase" :value="old('code', $department->code)" maxlength="20" required />
                    <p class="text-xs text-gray-400 mt-1">Short unique code, e.g. CARD, ORTHO, GEN-MED.</p>
                </div>

                <div>
                    <x-input-label for="description" value="Description" />
                    <x-text-input id="description" name="description" class="mt-1 block w-full" :value="old('description', $department->description)" />
                </div>

                <div>
                    <x-input-label for="head_staff_id" value="Head of department" />
                    <select id="head_staff_id" name="head_staff_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">— None —</option>
                        @foreach ($heads as $head)
                            <option value="{{ $head->id }}" @selected(old('head_staff_id', $department->head_staff_id) == $head->id)>
                                {{ $head->full_name }} ({{ $head->type_label }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-teal-600"
                           @checked(old('is_active', $department->is_active ?? true))>
                    Active
                </label>

                <div class="flex items-center gap-3 pt-2">
                    <x-primary-button>{{ $department->exists ? 'Save' : 'Create' }}</x-primary-button>
                    <a href="{{ route('departments.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
