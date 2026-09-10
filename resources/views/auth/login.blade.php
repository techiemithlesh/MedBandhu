<x-guest-layout>
    <div x-data="{
        set(email, pass) {
            this.$refs.email.value = email;
            this.$refs.password.value = pass;
        }
    }">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('Sign in') }}</h2>
        <p class="mt-1 text-sm text-gray-500">{{ __('Welcome back. Please enter your details.') }}</p>

        <x-auth-session-status class="mt-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input x-ref="email" id="email" class="block mt-1 w-full" type="email" name="email"
                    :value="old('email')" required autofocus autocomplete="username" placeholder="you@hospital.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <x-input-label for="password" :value="__('Password')" />
                    @if (Route::has('password.request'))
                        <a class="text-sm text-teal-600 hover:text-teal-700 hover:underline" href="{{ route('password.request') }}">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>
                <x-text-input x-ref="password" id="password" class="block mt-1 w-full" type="password" name="password"
                    required autocomplete="current-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <label for="remember_me" class="flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-teal-600 shadow-sm focus:ring-teal-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me for 30 days') }}</span>
            </label>

            <button type="submit"
                class="flex w-full justify-center rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2">
                {{ __('Log in') }}
            </button>
        </form>

        @unless (app()->environment('production'))
            <div class="mt-8 rounded-lg border border-dashed border-teal-300 bg-teal-50/60 p-4 text-sm">
                <p class="font-medium text-teal-800">{{ __('Demo access') }}</p>
                @if (config('hms.demo.enabled'))
                    <a href="{{ route('demo.enter') }}"
                       class="mt-2 flex w-full justify-center rounded-md bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                        {{ __('Open the live demo') }} →
                    </a>
                    <p class="mt-3 text-xs text-teal-700/80">{{ __('Or sign in as a specific role — click to fill, then Log in.') }} {{ __('Password:') }} <code class="font-mono">password</code></p>
                @else
                    <p class="mt-0.5 text-xs text-teal-700/80">{{ __('Click a role to fill the login form, then press Log in.') }} {{ __('Password:') }} <code class="font-mono">password</code></p>
                @endif
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button" @click="set('admin@sunrise.test', 'password')"
                        class="rounded-md bg-white px-2.5 py-1 text-xs font-medium text-teal-700 ring-1 ring-teal-200 hover:bg-teal-100">Hospital Admin</button>
                    <button type="button" @click="set('doctor@sunrise.test', 'password')"
                        class="rounded-md bg-white px-2.5 py-1 text-xs font-medium text-teal-700 ring-1 ring-teal-200 hover:bg-teal-100">Doctor</button>
                    <button type="button" @click="set('reception@sunrise.test', 'password')"
                        class="rounded-md bg-white px-2.5 py-1 text-xs font-medium text-teal-700 ring-1 ring-teal-200 hover:bg-teal-100">Receptionist</button>
                    <button type="button" @click="set('pharmacy@sunrise.test', 'password')"
                        class="rounded-md bg-white px-2.5 py-1 text-xs font-medium text-teal-700 ring-1 ring-teal-200 hover:bg-teal-100">Pharmacist</button>
                    <button type="button" @click="set('super@hms.test', 'password')"
                        class="rounded-md bg-white px-2.5 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100">Platform Super Admin</button>
                </div>
            </div>
        @endunless
    </div>
</x-guest-layout>
