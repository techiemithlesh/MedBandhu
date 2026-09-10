<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $branch->exists ? 'Edit '.$branch->name : 'Add branch' }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />
            <form method="POST" action="{{ $branch->exists ? route('branches.update', $branch) : route('branches.store') }}"
                  class="bg-white rounded-lg shadow-sm p-6 space-y-5">
                @csrf
                @if ($branch->exists) @method('PUT') @endif

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="name" value="Branch name *" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $branch->name)" required />
                    </div>
                    <div>
                        <x-input-label for="code" value="Code *" />
                        <x-text-input id="code" name="code" class="mt-1 block w-full uppercase" :value="old('code', $branch->code)" maxlength="20" required />
                    </div>
                    <div>
                        <x-input-label for="type" value="Type *" />
                        <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach (['main' => 'Main', 'clinic' => 'Clinic', 'daycare' => 'Daycare', 'diagnostic' => 'Diagnostic'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('type', $branch->type) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="phone" value="Phone" />
                        <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $branch->phone)" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="address" value="Address" />
                        <x-text-input id="address" name="address" class="mt-1 block w-full" :value="old('address', $branch->address)" />
                    </div>
                    <div>
                        <x-input-label for="city" value="City" />
                        <x-text-input id="city" name="city" class="mt-1 block w-full" :value="old('city', $branch->city)" />
                    </div>
                    <div>
                        <x-input-label for="state" value="State" />
                        <x-text-input id="state" name="state" class="mt-1 block w-full" :value="old('state', $branch->state)" />
                    </div>
                    <div>
                        <x-input-label for="pincode" value="PIN code" />
                        <x-text-input id="pincode" name="pincode" class="mt-1 block w-full" :value="old('pincode', $branch->pincode)" />
                    </div>
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $branch->email)" />
                    </div>
                    @if ($branch->exists)
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-7">
                            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-teal-600" @checked(old('is_active', $branch->is_active))>
                            Active
                        </label>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>{{ $branch->exists ? 'Save' : 'Add branch' }}</x-primary-button>
                    <a href="{{ route('branches.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
