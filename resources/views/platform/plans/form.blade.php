<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $plan->exists ? 'Edit '.$plan->name : 'New plan' }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />
            <form method="POST" action="{{ $plan->exists ? route('platform.plans.update', $plan) : route('platform.plans.store') }}"
                  class="bg-white rounded-lg shadow-sm p-6 space-y-5">
                @csrf
                @if ($plan->exists) @method('PUT') @endif

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="name" value="Name *" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $plan->name)" required />
                    </div>
                    <div>
                        <x-input-label for="code" value="Code *" />
                        <x-text-input id="code" name="code" class="mt-1 block w-full" :value="old('code', $plan->code)" maxlength="30" required />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="description" value="Description" />
                        <x-text-input id="description" name="description" class="mt-1 block w-full" :value="old('description', $plan->description)" />
                    </div>
                    <div>
                        <x-input-label for="price_monthly" value="Price / month (₹) *" />
                        <x-text-input id="price_monthly" name="price_monthly" type="number" step="1" min="0" class="mt-1 block w-full" :value="old('price_monthly', $plan->price_monthly ?? 0)" required />
                    </div>
                    <div>
                        <x-input-label for="price_yearly" value="Price / year (₹) *" />
                        <x-text-input id="price_yearly" name="price_yearly" type="number" step="1" min="0" class="mt-1 block w-full" :value="old('price_yearly', $plan->price_yearly ?? 0)" required />
                    </div>
                    <div>
                        <x-input-label for="price_extra_branch" value="Extra branch / cycle (₹) *" />
                        <x-text-input id="price_extra_branch" name="price_extra_branch" type="number" step="1" min="0" class="mt-1 block w-full" :value="old('price_extra_branch', $plan->price_extra_branch ?? 0)" required />
                    </div>
                    <div>
                        <x-input-label for="branch_limit" value="Branch limit *" />
                        <x-text-input id="branch_limit" name="branch_limit" type="number" min="1" class="mt-1 block w-full" :value="old('branch_limit', $plan->branch_limit ?? 1)" required />
                    </div>
                    <div>
                        <x-input-label for="trial_days" value="Trial days *" />
                        <x-text-input id="trial_days" name="trial_days" type="number" min="0" max="90" class="mt-1 block w-full" :value="old('trial_days', $plan->trial_days ?? 14)" required />
                    </div>
                    <div>
                        <x-input-label for="sort_order" value="Sort order" />
                        <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 block w-full" :value="old('sort_order', $plan->sort_order ?? 0)" />
                    </div>
                </div>

                <div>
                    <x-input-label value="Included modules (none checked = all modules)" />
                    <div class="mt-2 grid sm:grid-cols-3 gap-2">
                        @php $sel = old('modules', $plan->modules ?? []); @endphp
                        @foreach ($allModules as $m)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="modules[]" value="{{ $m }}" class="rounded border-gray-300 text-teal-600" @checked(in_array($m, $sel))>
                                {{ ucfirst($m) }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <x-input-label value="Features" />
                    <div class="mt-2 grid sm:grid-cols-2 gap-2">
                        @foreach (['ai' => 'AI features', 'custom_domain' => 'Custom domain', 'sms' => 'SMS / WhatsApp', 'priority_support' => 'Priority support'] as $f => $label)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="features[{{ $f }}]" value="1" class="rounded border-gray-300 text-teal-600" @checked(old("features.$f", data_get($plan->features, $f, false)))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-6">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-teal-600" @checked(old('is_active', $plan->is_active ?? true))> Active
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_public" value="1" class="rounded border-gray-300 text-teal-600" @checked(old('is_public', $plan->is_public ?? true))> Public (shown on pricing)
                    </label>
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>{{ $plan->exists ? 'Save' : 'Create plan' }}</x-primary-button>
                    <a href="{{ route('platform.plans.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
