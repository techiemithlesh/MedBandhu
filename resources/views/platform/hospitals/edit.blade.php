<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit {{ $hospital->name }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('platform.hospitals.update', $hospital) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="name" value="Hospital name" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $hospital->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="email" value="Contact email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $hospital->email)" />
                    </div>
                    <div>
                        <x-input-label for="phone" value="Phone" />
                        <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $hospital->phone)" />
                    </div>
                    <div>
                        <x-input-label for="city" value="City" />
                        <x-text-input id="city" name="city" class="mt-1 block w-full" :value="old('city', $hospital->city)" />
                    </div>
                    <div>
                        <x-input-label for="state" value="State" />
                        <x-text-input id="state" name="state" class="mt-1 block w-full" :value="old('state', $hospital->state)" />
                    </div>
                    <div>
                        <x-input-label for="custom_domain" value="Custom domain" />
                        <x-text-input id="custom_domain" name="custom_domain" class="mt-1 block w-full" :value="old('custom_domain', $hospital->custom_domain)" placeholder="hms.myhospital.in" />
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-7">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-teal-600" @checked(old('is_active', $hospital->is_active))>
                        Account active
                    </label>
                </div>

                <div>
                    <x-input-label value="Enabled modules (within the plan's allowance)" />
                    <div class="mt-2 grid sm:grid-cols-3 gap-2">
                        @foreach($modules as $module)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="modules[]" value="{{ $module }}" class="rounded border-gray-300 text-teal-600"
                                       @checked(in_array($module, old('modules', $hospital->setting('modules') ?? $modules)))>
                                {{ ucfirst($module) }}
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-1">The plan is managed on the hospital page. This only toggles modules off within what the plan allows.</p>
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Save changes</x-primary-button>
                    <a href="{{ route('platform.hospitals.show', $hospital) }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>

            <form method="POST" action="{{ route('platform.hospitals.destroy', $hospital) }}" class="mt-4"
                  onsubmit="return confirm('Archive this hospital? Its data is retained but access is blocked.')">
                @csrf
                @method('DELETE')
                <button class="text-sm text-red-600 hover:underline">Archive hospital</button>
            </form>
        </div>
    </div>
</x-app-layout>
