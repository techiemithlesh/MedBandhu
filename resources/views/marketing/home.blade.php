<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
@php
    $app = config('app.name');
    $base = rtrim(config('app.url'), '/');
    $canonical = $base.'/';
    $ogImage = $base.'/icons/og-image.png';
    $seoTitle = $app.' — Hospital Management Software (HMS) for India | OPD, IPD, Pharmacy & Billing';
    $seoDesc = $app.' is cloud hospital management software for hospitals and nursing homes in India — patient registration & OPD, appointments, IPD & bed management, pharmacy stock, GST billing and reports across every branch. Hindi & English. 14-day free trial.';
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDesc }}">
    <meta name="keywords" content="hospital management software, HMS software India, hospital software, nursing home software, OPD software, IPD management, pharmacy management software, GST billing hospital, clinic management software, {{ strtolower($app) }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
    <meta name="author" content="{{ $app }}">

    {{-- Language alternates (locale is switchable via ?lang=) --}}
    <link rel="alternate" hreflang="en-IN" href="{{ $canonical }}?lang=en">
    <link rel="alternate" hreflang="hi-IN" href="{{ $canonical }}?lang=hi">
    <link rel="alternate" hreflang="x-default" href="{{ $canonical }}">

    {{-- Open Graph / social --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $app }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDesc }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="{{ app()->getLocale() === 'hi' ? 'hi_IN' : 'en_IN' }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
    <x-pwa-head />

    {{-- Structured data for search engines & AI answer engines --}}
    <script type="application/ld+json">
    @php
        $offers = $plans->map(fn ($p) => [
            '@type' => 'Offer',
            'name' => $p->name.' plan',
            'price' => (string) (int) $p->price_monthly,
            'priceCurrency' => 'INR',
            'description' => $p->description,
            'url' => $canonical.'#pricing',
        ])->values();

        $ld = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => $base.'/#org',
                    'name' => $app,
                    'url' => $canonical,
                    'logo' => $base.'/icons/icon-512.png',
                    'description' => $seoDesc,
                    'areaServed' => 'IN',
                    'contactPoint' => [[
                        '@type' => 'ContactPoint',
                        'contactType' => 'sales',
                        'telephone' => $contact['phone'] ?? null,
                        'email' => $contact['email'] ?? null,
                        'availableLanguage' => ['en', 'hi'],
                    ]],
                ],
                [
                    '@type' => 'SoftwareApplication',
                    '@id' => $base.'/#software',
                    'name' => $app,
                    'applicationCategory' => 'BusinessApplication',
                    'applicationSubCategory' => 'Hospital Management Software',
                    'operatingSystem' => 'Web, Android, Windows',
                    'url' => $canonical,
                    'description' => $seoDesc,
                    'inLanguage' => ['en', 'hi'],
                    'publisher' => ['@id' => $base.'/#org'],
                    'featureList' => collect($features)->pluck(0)->implode(', '),
                    'offers' => [
                        '@type' => 'AggregateOffer',
                        'priceCurrency' => 'INR',
                        'lowPrice' => (string) (int) $plans->min('price_monthly'),
                        'highPrice' => (string) (int) $plans->max('price_monthly'),
                        'offerCount' => $plans->count(),
                        'offers' => $offers,
                    ],
                ],
                [
                    '@type' => 'FAQPage',
                    '@id' => $base.'/#faq',
                    'mainEntity' => collect($faqs)->map(fn ($f) => [
                        '@type' => 'Question',
                        'name' => $f[0],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
                    ])->values(),
                ],
            ],
        ];
    @endphp
    {!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
</head>
<body class="font-sans text-slate-700 antialiased bg-white">

@php
    $wa = 'https://wa.me/'.preg_replace('/\D/', '', $contact['whatsapp'] ?? '').'?text='.rawurlencode('Hi, I would like a demo of '.config('app.name'));
@endphp

<div x-data="{ mobile: false }">

    {{-- ================= Header ================= --}}
    <header class="sticky top-0 z-40 border-b border-slate-100 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="/" class="flex items-center gap-2 text-lg font-extrabold tracking-tight text-slate-900">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-teal-600 text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                </span>
                {{ config('app.name') }}
            </a>

            <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
                <a href="#features" class="hover:text-teal-700">{{ __('Features') }}</a>
                <a href="#how" class="hover:text-teal-700">{{ __('How it works') }}</a>
                <a href="#pricing" class="hover:text-teal-700">{{ __('Pricing') }}</a>
                <a href="#faq" class="hover:text-teal-700">{{ __('FAQ') }}</a>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                <x-install-button variant="light" />
                <x-lang-switcher variant="light" />
                <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-teal-700">{{ __('Log in') }}</a>
                <a href="{{ $wa }}" target="_blank" rel="noopener"
                   class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-teal-700">{{ __('Book a demo') }}</a>
            </div>

            <button @click="mobile = !mobile" class="md:hidden p-2 -mr-2 text-slate-700" aria-label="Menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <div x-show="mobile" x-cloak class="border-t border-slate-100 bg-white px-4 py-4 md:hidden">
            <div class="flex flex-col gap-3 text-sm font-medium text-slate-700">
                <a href="#features" @click="mobile=false">{{ __('Features') }}</a>
                <a href="#how" @click="mobile=false">{{ __('How it works') }}</a>
                <a href="#pricing" @click="mobile=false">{{ __('Pricing') }}</a>
                <a href="#faq" @click="mobile=false">{{ __('FAQ') }}</a>
                <hr class="border-slate-100">
                <a href="{{ route('login') }}" class="font-semibold">{{ __('Log in') }}</a>
                <a href="{{ $wa }}" target="_blank" rel="noopener" class="rounded-lg bg-teal-600 px-4 py-2 text-center font-semibold text-white">{{ __('Book a demo') }}</a>
            </div>
        </div>
    </header>

    {{-- ================= Hero ================= --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-teal-50/70 to-white">
        <div class="mx-auto grid max-w-6xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:py-24">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold text-teal-800">
                    {{ __('Cloud hospital management software · made in India') }}
                </span>
                <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-5xl">
                    {!! __('Run your whole hospital from <span class="text-teal-600">one simple screen</span>.') !!}
                </h1>
                <p class="mt-5 text-lg text-slate-600">
                    {{ __(':app is hospital management software that handles patients & OPD, doctors & staff, IPD beds, pharmacy stock, GST billing and daily reports — for every branch, in Hindi or English, without the paperwork.', ['app' => config('app.name')]) }}
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('demo.enter') }}"
                       class="rounded-lg bg-teal-600 px-6 py-3 text-center text-sm font-semibold text-white shadow-sm hover:bg-teal-700">{{ __('Try the live demo') }}</a>
                    <a href="{{ $wa }}" target="_blank" rel="noopener"
                       class="rounded-lg border border-slate-300 bg-white px-6 py-3 text-center text-sm font-semibold text-slate-700 hover:border-teal-400 hover:text-teal-700">{{ __('Talk to us on WhatsApp') }}</a>
                </div>
                <p class="mt-4 text-sm text-slate-500">{{ __('14-day free trial · no card needed · your data stays yours') }}</p>
            </div>

            {{-- product mock --}}
            <div class="relative">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60">
                    <div class="flex items-center gap-1.5 border-b border-slate-100 px-4 py-3">
                        <span class="h-2.5 w-2.5 rounded-full bg-slate-200"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-slate-200"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-slate-200"></span>
                        <span class="ml-3 text-xs text-slate-400">{{ config('app.name') }} · Sunrise Hospital</span>
                    </div>
                    <div class="grid grid-cols-3 gap-3 p-4 text-xs">
                        <div class="rounded-lg bg-teal-50 p-3">
                            <div class="text-slate-500">OPD today</div>
                            <div class="mt-1 text-xl font-bold text-slate-900">128</div>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3">
                            <div class="text-slate-500">Beds free</div>
                            <div class="mt-1 text-xl font-bold text-slate-900">14<span class="text-sm font-medium text-slate-400">/60</span></div>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3">
                            <div class="text-slate-500">Collected</div>
                            <div class="mt-1 text-xl font-bold text-slate-900">₹1.9L</div>
                        </div>
                        <div class="col-span-3 rounded-lg border border-slate-100 p-3">
                            <div class="mb-2 font-medium text-slate-600">Bed board — 2nd floor</div>
                            <div class="grid grid-cols-8 gap-1.5">
                                @foreach (['t','t','o','o','o','t','o','c','o','o','t','o','o','o','c','o'] as $s)
                                    <span @class([
                                        'h-5 rounded',
                                        'bg-teal-500' => $s === 't',
                                        'bg-rose-400' => $s === 'o',
                                        'bg-amber-300' => $s === 'c',
                                    ])></span>
                                @endforeach
                            </div>
                            <div class="mt-2 flex gap-3 text-[10px] text-slate-400">
                                <span><span class="mr-1 inline-block h-2 w-2 rounded bg-teal-500"></span>Free</span>
                                <span><span class="mr-1 inline-block h-2 w-2 rounded bg-rose-400"></span>Occupied</span>
                                <span><span class="mr-1 inline-block h-2 w-2 rounded bg-amber-300"></span>Cleaning</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div aria-hidden="true" class="pointer-events-none absolute -right-6 -top-6 -z-10 h-40 w-40 rounded-full bg-teal-200/40 blur-2xl"></div>
            </div>
        </div>
    </section>

    {{-- ================= Features ================= --}}
    <section id="features" class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ __('Everything your hospital runs on') }}</h2>
            <p class="mt-3 text-slate-600">Six connected modules. One login. No more registers, spreadsheets or WhatsApp groups for records.</p>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($features as [$title, $body, $icon])
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition hover:border-teal-200 hover:shadow-md">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-teal-50 text-teal-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    </span>
                    <h3 class="mt-4 font-bold text-slate-900">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $body }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= Why MedBandhu ================= --}}
    <section class="bg-slate-50 py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ __('Why hospitals choose :app', ['app' => config('app.name')]) }}</h2>
            </div>
            <div class="mt-12 grid gap-x-8 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($whys as [$t, $b])
                    <div class="flex gap-4">
                        <svg class="h-6 w-6 flex-none text-teal-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.1 3.1 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        <div>
                            <h3 class="font-bold text-slate-900">{{ $t }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ $b }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= How it works ================= --}}
    <section id="how" class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ __('Live in a day, not a month') }}</h2>
        </div>
        <div class="mt-14 grid gap-8 sm:grid-cols-3">
            @foreach ($steps as [$n, $t, $b])
                <div class="relative rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-teal-600 text-sm font-bold text-white">{{ $n }}</span>
                    <h3 class="mt-4 font-bold text-slate-900">{{ $t }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $b }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= Pricing ================= --}}
    @php
        $hospitalPlan = $plans->firstWhere('code', 'hospital');
        $guarantees = [
            __('No per-user fee — pay the same for 4 staff or 40'),
            __('No per-appointment or per-booking charges, ever'),
            __('Free setup and free data migration from your registers'),
            __('Export your patients, bills and stock any day'),
            __('Hosted in India · DPDP-ready · your data stays yours'),
            __('WhatsApp &amp; phone support in Hindi and English'),
        ];
    @endphp
    <section id="pricing" class="bg-slate-50 py-20" x-data="{ yearly: true }">
        <div class="mx-auto max-w-5xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ __('Simple, flat pricing') }}</h2>
                <p class="mt-3 text-slate-600">{{ __('Unlimited patients, unlimited staff logins, free setup and free migration on every plan.') }}</p>

                <div class="mt-6 inline-flex items-center gap-2 rounded-full bg-white p-1 text-sm font-semibold shadow-sm ring-1 ring-slate-200">
                    <button @click="yearly = true" :class="yearly ? 'bg-teal-600 text-white' : 'text-slate-600'" class="rounded-full px-4 py-1.5 transition">{{ __('Yearly') }} <span class="text-xs font-normal">· {{ __('best value') }}</span></button>
                    <button @click="yearly = false" :class="!yearly ? 'bg-teal-600 text-white' : 'text-slate-600'" class="rounded-full px-4 py-1.5 transition">{{ __('Monthly') }}</button>
                </div>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2">
                @foreach ($plans as $plan)
                    @php
                        $featured = $plan->code === 'hospital';
                        $modules = $plan->moduleList() ?? config('hms.modules');
                    @endphp
                    <div @class([
                        'flex flex-col rounded-2xl bg-white p-7 shadow-sm',
                        'ring-2 ring-teal-600 shadow-lg' => $featured,
                        'ring-1 ring-slate-200' => ! $featured,
                    ])>
                        @if ($featured)
                            <span class="mb-3 inline-block w-max rounded-full bg-teal-100 px-3 py-0.5 text-xs font-semibold text-teal-800">{{ __('Most popular') }}</span>
                        @endif
                        <h3 class="text-lg font-bold text-slate-900">{{ $plan->name }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $plan->description }}</p>

                        <div class="mt-5">
                            <template x-if="yearly">
                                <div>
                                    <span class="text-3xl font-extrabold text-slate-900">₹{{ number_format($plan->price_yearly, 0) }}</span>
                                    <span class="text-sm text-slate-500">/{{ __('year') }}</span>
                                    <div class="text-xs text-slate-400">{{ __('or') }} ₹{{ number_format($plan->price_half_yearly, 0) }} {{ __('half-yearly') }}</div>
                                </div>
                            </template>
                            <template x-if="!yearly">
                                <div>
                                    <span class="text-3xl font-extrabold text-slate-900">₹{{ number_format($plan->price_monthly, 0) }}</span>
                                    <span class="text-sm text-slate-500">/{{ __('month') }}</span>
                                    <div class="text-xs text-slate-400">{{ __('Save') }} ₹{{ number_format($plan->price_monthly * 12 - $plan->price_yearly, 0) }} {{ __('a year by paying yearly') }}</div>
                                </div>
                            </template>
                        </div>

                        @if ($plan->price_extra_branch > 0)
                            <p class="mt-2 text-xs text-slate-500">{{ __('1 branch · add more at') }} ₹{{ number_format($plan->price_extra_branch, 0) }}/{{ __('year each') }}</p>
                        @else
                            <p class="mt-2 text-xs text-slate-500">{{ __('Single branch') }}</p>
                        @endif

                        <a href="{{ $wa }}" target="_blank" rel="noopener"
                           @class([
                               'mt-6 rounded-lg px-4 py-2.5 text-center text-sm font-semibold',
                               'bg-teal-600 text-white hover:bg-teal-700' => $featured,
                               'bg-slate-100 text-slate-800 hover:bg-slate-200' => ! $featured,
                           ])>{{ __('Start 14-day free trial') }}</a>

                        @if ($featured)
                            <p class="mt-3 rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                <strong>{{ __('Founder offer') }}</strong> — {{ __('first 25 hospitals:') }} ₹11,999/{{ __('year') }}, {{ __('locked for 2 years, free setup.') }}
                                <a href="{{ $wa }}" target="_blank" rel="noopener" class="font-semibold underline">{{ __('WhatsApp us') }}</a>
                            </p>
                        @endif

                        <ul class="mt-6 space-y-2 text-sm text-slate-600">
                            @foreach ($modules as $m)
                                <li class="flex items-start gap-2">
                                    <svg class="mt-0.5 h-4 w-4 flex-none text-teal-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.1 3.1 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                                    <span class="capitalize">{{ $m === 'opd' ? __('OPD Queue') : ($m === 'ipd' ? 'IPD / '.__('Admissions') : ucfirst($m)) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            {{-- guarantees --}}
            <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6">
                <div class="text-sm font-semibold text-slate-800">{{ __('Every plan, always') }}</div>
                <ul class="mt-3 grid gap-x-8 gap-y-2 text-sm text-slate-600 sm:grid-cols-2">
                    @foreach ($guarantees as $g)
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-none text-teal-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.1 3.1 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                            <span>{!! $g !!}</span>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-4 text-xs text-slate-500">
                    {{ __('Add-ons:') }} SMS / WhatsApp ₹499/{{ __('mo') }} · {{ __('custom domain') }} ₹2,000 + ₹500/{{ __('mo') }} · {{ __('priority support') }} ₹999/{{ __('mo') }}.
                </p>
            </div>

            <p class="mt-8 text-center text-sm text-slate-500">
                {{ __('Prefer to own it?') }} {{ __('One-time perpetual licence') }}
                @if ($hospitalPlan && $hospitalPlan->price_perpetual)
                    — ₹{{ number_format($hospitalPlan->price_perpetual, 0) }} + ₹{{ number_format($hospitalPlan->price_amc, 0) }}/{{ __('year AMC') }} —
                @endif
                {{ __('also available.') }}
                {{ __('Running a chain?') }}
                <a href="{{ $wa }}" target="_blank" rel="noopener" class="font-semibold text-teal-700 hover:underline">{{ __('Ask about Enterprise') }}</a>.
            </p>
        </div>
    </section>

    {{-- ================= Demo CTA ================= --}}
    <section id="demo" class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
        <div class="overflow-hidden rounded-3xl bg-gradient-to-br from-teal-600 to-slate-800 px-8 py-14 text-center text-white sm:px-16">
            <h2 class="text-3xl font-extrabold tracking-tight">{{ __('See it with your own eyes') }}</h2>
            <p class="mx-auto mt-3 max-w-xl text-teal-50/90">
                Open the live demo hospital and click around as a doctor, receptionist or pharmacist — no signup.
                Or message us and we’ll walk your team through it on a call.
            </p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('demo.enter') }}" class="rounded-lg bg-white px-6 py-3 text-sm font-semibold text-teal-700 hover:bg-teal-50">{{ __('Open the live demo') }}</a>
                <a href="{{ $wa }}" target="_blank" rel="noopener" class="rounded-lg border border-white/40 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10">{{ __('Book a guided demo') }}</a>
            </div>
        </div>
    </section>

    {{-- ================= FAQ ================= --}}
    <section id="faq" class="bg-slate-50 py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <h2 class="text-center text-3xl font-extrabold tracking-tight text-slate-900">{{ __('Questions hospitals ask us') }}</h2>
            <div class="mt-12 divide-y divide-slate-200 rounded-2xl bg-white px-6 shadow-sm">
                @foreach ($faqs as $i => [$q, $a])
                    <div x-data="{ open: {{ $i === 0 ? 'true' : 'false' }} }" class="py-5">
                        <button @click="open = !open" class="flex w-full items-center justify-between text-left">
                            <span class="font-semibold text-slate-900">{{ $q }}</span>
                            <svg class="h-5 w-5 flex-none text-slate-400 transition" :class="open && 'rotate-45'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                        </button>
                        <p x-show="open" x-cloak x-transition.opacity class="mt-3 text-sm text-slate-600">{{ $a }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= Footer ================= --}}
    <footer class="border-t border-slate-100 bg-white">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-3">
            <div>
                <div class="flex items-center gap-2 text-lg font-extrabold tracking-tight text-slate-900">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-teal-600 text-white">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    </span>
                    {{ config('app.name') }}
                </div>
                <p class="mt-3 text-sm text-slate-500">Cloud hospital management software (HMS) for growing hospitals and nursing homes across India — with roots in Bihar, Jharkhand and Chhattisgarh. Available in Hindi and English.</p>
            </div>
            <div class="text-sm">
                <div class="font-semibold text-slate-900">Product</div>
                <ul class="mt-3 space-y-2 text-slate-600">
                    <li><a href="#features" class="hover:text-teal-700">{{ __('Features') }}</a></li>
                    <li><a href="#pricing" class="hover:text-teal-700">{{ __('Pricing') }}</a></li>
                    <li><a href="{{ route('demo.enter') }}" class="hover:text-teal-700">Live demo</a></li>
                    <li><a href="{{ route('marketing.cities') }}" class="hover:text-teal-700">Cities we serve</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-teal-700">{{ __('Log in') }}</a></li>
                </ul>
            </div>
            <div class="text-sm">
                <div class="font-semibold text-slate-900">Talk to us</div>
                <ul class="mt-3 space-y-2 text-slate-600">
                    <li><a href="{{ $wa }}" target="_blank" rel="noopener" class="hover:text-teal-700">WhatsApp: {{ $contact['phone'] }}</a></li>
                    <li><a href="tel:{{ preg_replace('/\s/', '', $contact['phone']) }}" class="hover:text-teal-700">Call: {{ $contact['phone'] }}</a></li>
                    <li><a href="mailto:{{ $contact['email'] }}" class="hover:text-teal-700">{{ $contact['email'] }}</a></li>
                    <li>{{ $contact['address'] }}</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-100 py-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </footer>
</div>

</body>
</html>
