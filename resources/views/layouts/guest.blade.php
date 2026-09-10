<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ config('app.name', 'HMS') }}</title>
        <x-pwa-head />

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none!important}</style>
    </head>
    <body class="font-sans text-gray-900 antialiased h-full">
        <div class="min-h-full lg:grid lg:grid-cols-2">

            {{-- Brand panel --}}
            <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-gradient-to-br from-teal-600 via-teal-700 to-slate-800 p-12 text-white">
                <div aria-hidden="true" class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/10 blur-2xl"></div>
                <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-teal-400/20 blur-2xl"></div>

                <a href="/" class="relative flex items-center gap-3 text-lg font-bold tracking-tight">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-white/15 ring-1 ring-white/25">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    </span>
                    {{ config('app.name', 'HMS') }}
                </a>

                <div class="relative max-w-md">
                    <h1 class="text-3xl font-bold leading-tight">{{ __('Hospital management, made simple.') }}</h1>
                    <p class="mt-4 text-teal-50/90">
                        {{ __('Patients, appointments, beds, pharmacy and billing for every branch of your hospital — in one place.') }}
                    </p>
                    <ul class="mt-8 space-y-3 text-sm text-teal-50/90">
                        @foreach (['OPD & appointments with token queue', 'Live bed availability across branches', 'Pharmacy stock with expiry tracking', 'GST billing, payments & daily reports'] as $point)
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-5 w-5 flex-none text-teal-200" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0L3.3 9.7a1 1 0 0 1 1.4-1.4l3.1 3.1 6.8-6.8a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                                <span>{{ __($point) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <p class="relative text-xs text-teal-100/70">&copy; {{ date('Y') }} {{ config('app.name', 'HMS') }}. {{ __('All rights reserved.') }}</p>
            </div>

            {{-- Form panel --}}
            <div class="relative flex min-h-full items-center justify-center px-6 py-12 bg-gray-50">
                <div class="absolute right-4 top-4">
                    <x-lang-switcher variant="light" />
                </div>
                <div class="w-full max-w-sm">
                    {{-- mobile logo --}}
                    <a href="/" class="lg:hidden mb-8 flex items-center justify-center gap-2 text-lg font-bold tracking-tight text-teal-700">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-teal-600 text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        </span>
                        {{ config('app.name', 'HMS') }}
                    </a>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
