<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">
        <title>{{ config('app.name', 'HMS') }}</title>
        <x-pwa-head />
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none!important}</style>
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">

            <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
                 class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                   class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-800 text-slate-200 transform transition-transform lg:translate-x-0 lg:static lg:inset-auto overflow-y-auto">
                @include('layouts.sidebar')
            </aside>

            <div class="flex-1 min-w-0 flex flex-col">
                @include('layouts.navigation')

                @if (! empty($demoMode))
                    <div class="flex items-center justify-center gap-3 bg-slate-900 px-4 py-2 text-center text-xs text-white sm:text-sm">
                        <span>
                            <span class="font-semibold">{{ __('Demo mode') }}</span> — {{ __("you're exploring sample data. Changes reset every night.") }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="rounded bg-white/15 px-2 py-0.5 font-medium hover:bg-white/25">{{ __('Exit demo') }}</button>
                        </form>
                    </div>
                @endif

                @php $tnc = app(\App\Support\Tenancy::class)->hospital(); @endphp
                @if ($tnc && $tnc->access_status === 'restricted')
                    <div class="bg-amber-500 text-white text-sm text-center py-2 px-4">
                        {{ __("Your subscription has lapsed — you're in the grace period.") }}
                        <a href="{{ route('billing.subscription.show') }}" class="underline font-medium">{{ __('Renew now') }}</a> {{ __('to keep access.') }}
                    </div>
                @endif

                @isset($header)
                    <header class="bg-white shadow-sm">
                        <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
