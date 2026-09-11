<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
@php
    $app = config('app.name');
    $base = rtrim(config('app.url'), '/');
    $canonical = $base.'/hospital-management-software';
    $seoTitle = 'Hospital Management Software Near You — Cities We Serve | '.$app;
    $seoDesc = $app.' hospital management software for hospitals and nursing homes across Bihar, Jharkhand, Chhattisgarh and West Bengal. Find your city.';
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDesc }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="robots" content="index, follow">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $app }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDesc }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $base }}/icons/og-image.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
    <x-pwa-head />
</head>
<body class="font-sans text-slate-700 antialiased bg-white">

@php
    $wa = 'https://wa.me/'.preg_replace('/\D/', '', $contact['whatsapp'] ?? '').'?text='.rawurlencode('Hi, I would like a demo of '.config('app.name'));
@endphp

<div x-data="{ mobile: false }">
    <header class="sticky top-0 z-40 border-b border-slate-100 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-extrabold tracking-tight text-slate-900">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-teal-600 text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                </span>
                {{ $app }}
            </a>
            <div class="hidden items-center gap-3 md:flex">
                <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-teal-700">{{ __('Log in') }}</a>
                <a href="{{ $wa }}" target="_blank" rel="noopener" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-teal-700">{{ __('Book a demo') }}</a>
            </div>
        </div>
    </header>

    <section class="bg-gradient-to-b from-teal-50/70 to-white py-16">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
            <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">Hospital management software near you</h1>
            <p class="mt-5 text-lg text-slate-600">{{ $app }} works the same way everywhere — Hindi or English, live the same day, no per-user fee. Find your city below for local details and to talk to someone who knows it.</p>
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 py-16 sm:px-6">
        @foreach ($cities as $state => $list)
            <div class="mb-12">
                <h2 class="mb-4 text-lg font-bold text-slate-900">{{ $state }}</h2>
                <div class="grid gap-3 sm:grid-cols-3 md:grid-cols-4">
                    @foreach ($list as $c)
                        <a href="{{ route('marketing.city', $c['slug']) }}"
                           class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm hover:border-teal-300 hover:text-teal-700">
                            {{ $c['name'] }}
                            <svg class="h-4 w-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
        <p class="mt-4 text-sm text-slate-500">Don't see your town? {{ $app }} works anywhere in India — <a href="{{ $wa }}" target="_blank" rel="noopener" class="font-semibold text-teal-700 hover:underline">message us on WhatsApp</a> and we'll set your hospital up regardless.</p>
    </section>

    <footer class="border-t border-slate-100 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-10 text-center text-xs text-slate-400 sm:px-6">
            &copy; {{ date('Y') }} {{ $app }}. All rights reserved. &nbsp;·&nbsp; <a href="{{ route('home') }}" class="hover:text-teal-700">{{ __('Home') }}</a>
        </div>
    </footer>
</div>
</body>
</html>
