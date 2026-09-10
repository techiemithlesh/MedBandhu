<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add hospital</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('platform.hospitals.store') }}" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
                @csrf

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="name" value="Hospital name" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name')" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="code" value="Short code (unique)" />
                        <x-text-input id="code" name="code" class="mt-1 block w-full uppercase" :value="old('code')" maxlength="20" required />
                        <x-input-error :messages="$errors->get('code')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="email" value="Contact email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="phone" value="Phone" />
                        <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone')" />
                    </div>
                    <div>
                        <x-input-label for="city" value="City" />
                        <x-text-input id="city" name="city" class="mt-1 block w-full" :value="old('city')" />
                    </div>
                    <div>
                        <x-input-label for="state" value="State" />
                        <x-text-input id="state" name="state" class="mt-1 block w-full" :value="old('state')" />
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h3 class="font-medium text-gray-800 mb-3">Plan &amp; subscription</h3>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="plan_id" value="Plan *" />
                            <select id="plan_id" name="plan_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                @foreach ($plans as $p)
                                    <option value="{{ $p->id }}" @selected(old('plan_id') == $p->id)>
                                        {{ $p->name }} — ₹{{ number_format($p->price_yearly, 0) }}/yr · {{ $p->branch_limit }} branch(es)
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('plan_id')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="billing_cycle" value="Billing cycle *" />
                            <select id="billing_cycle" name="billing_cycle" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="yearly">Yearly</option>
                                <option value="half_yearly">Half-yearly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="licence_type" value="Licence type *" />
                            <select id="licence_type" name="licence_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="subscription">Subscription</option>
                                <option value="perpetual">Perpetual (one-time + AMC)</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="activation" value="Activation *" />
                            <select id="activation" name="activation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="trial" @selected(old('activation', 'trial') === 'trial')>14-day free trial (access now)</option>
                                <option value="invoice" @selected(old('activation') === 'invoice')>Hold for payment — raise first invoice, unlock when paid</option>
                                <option value="active" @selected(old('activation') === 'active')>Activate now (already paid offline)</option>
                            </select>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">"Hold for payment" locks the hospital to the billing page until you record their payment; they then get an activation email + receipt.</p>
                </div>

                <div class="border-t pt-6">
                    <h3 class="font-medium text-gray-800 mb-1">First Hospital Admin</h3>
                    <p class="text-sm text-gray-500 mb-4">This account can then create the rest of the hospital's staff.</p>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="admin_name" value="Name" />
                            <x-text-input id="admin_name" name="admin_name" class="mt-1 block w-full" :value="old('admin_name')" required />
                            <x-input-error :messages="$errors->get('admin_name')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="admin_email" value="Email" />
                            <x-text-input id="admin_email" name="admin_email" type="email" class="mt-1 block w-full" :value="old('admin_email')" required />
                            <x-input-error :messages="$errors->get('admin_email')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="admin_password" value="Temp password" />
                            <x-text-input id="admin_password" name="admin_password" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('admin_password')" class="mt-1" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Create hospital</x-primary-button>
                    <a href="{{ route('platform.hospitals.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
