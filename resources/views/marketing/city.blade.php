<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
@php
    $app = config('app.name');
    $base = rtrim(config('app.url'), '/');
    $canonical = $base.'/hospital-management-software/'.$city['slug'];
    $ogImage = $base.'/icons/og-image.png';
    $seoTitle = 'Hospital Management Software in '.$city['name'].' | '.$app;
    $seoDesc = $app.' — cloud hospital management software for hospitals and nursing homes in '.$city['name'].', '.$city['state'].'. OPD, IPD & beds, pharmacy, GST billing and reports, in Hindi or English. 14-day free trial, free setup.';
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDesc }}">
    <meta name="keywords" content="hospital management software {{ strtolower($city['name']) }}, hospital software {{ strtolower($city['name']) }}, nursing home software {{ strtolower($city['name']) }}, clinic management software {{ strtolower($city['name']) }}, OPD software {{ strtolower($city['name']) }}, {{ strtolower($app) }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
    <meta name="author" content="{{ $app }}">

    <link rel="alternate" hreflang="en-IN" href="{{ $canonical }}?lang=en">
    <link rel="alternate" hreflang="hi-IN" href="{{ $canonical }}?lang=hi">
    <link rel="alternate" hreflang="x-default" href="{{ $canonical }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $app }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDesc }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
    <x-pwa-head />

    <script type="application/ld+json">
    @php
        $ld = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'SoftwareApplication',
                    '@id' => $canonical.'#software',
                    'name' => $app,
                    'applicationCategory' => 'BusinessApplication',
                    'applicationSubCategory' => 'Hospital Management Software',
                    'operatingSystem' => 'Web, Android, Windows',
                    'url' => $canonical,
                    'description' => $seoDesc,
                    'areaServed' => ['@type' => 'City', 'name' => $city['name']],
                    'offers' => [
                        '@type' => 'AggregateOffer',
                        'priceCurrency' => 'INR',
                        'lowPrice' => (string) (int) $plans->min('price_monthly'),
                        'highPrice' => (string) (int) $plans->max('price_monthly'),
                        'offerCount' => $plans->count(),
                    ],
                ],
                [
                    '@type' => 'FAQPage',
                    '@id' => $canonical.'#faq',
                    'mainEntity' => collect($faqs)->map(fn ($f) => [
                        '@type' => 'Question',
                        'name' => $f[0],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
                    ])->values(),
                ],
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $base.'/'],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Hospital management software', 'item' => $base.'/hospital-management-software'],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => $city['name'], 'item' => $canonical],
                    ],
                ],
            ],
        ];
    @endphp
    {!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
</head>
<body class="font-sans text-slate-700 antialiased bg-white">

@php
    $wa = 'https://wa.me/'.preg_replace('/\D/', '', $contact['whatsapp'] ?? '').'?text='.rawurlencode('Hi, I would like a demo of '.config('app.name').' for our hospital in '.$city['name']);
@endphp

<div x-data="{ mobile: false }">

    {{-- ================= Header ================= --}}
    <header class="sticky top-0 z-40 border-b border-slate-100 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-extrabold tracking-tight text-slate-900">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-teal-600 text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                </span>
                {{ $app }}
            </a>

            <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
                <a href="{{ route('home') }}#features" class="hover:text-teal-700">{{ __('Features') }}</a>
                <a href="{{ route('home') }}#pricing" class="hover:text-teal-700">{{ __('Pricing') }}</a>
                <a href="{{ route('marketing.cities') }}" class="hover:text-teal-700">Cities</a>
                <a href="{{ route('home') }}#faq" class="hover:text-teal-700">{{ __('FAQ') }}</a>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
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
                <a href="{{ route('home') }}#features" @click="mobile=false">{{ __('Features') }}</a>
                <a href="{{ route('home') }}#pricing" @click="mobile=false">{{ __('Pricing') }}</a>
                <a href="{{ route('marketing.cities') }}" @click="mobile=false">Cities</a>
                <hr class="border-slate-100">
                <a href="{{ route('login') }}" class="font-semibold">{{ __('Log in') }}</a>
                <a href="{{ $wa }}" target="_blank" rel="noopener" class="rounded-lg bg-teal-600 px-4 py-2 text-center font-semibold text-white">{{ __('Book a demo') }}</a>
            </div>
        </div>
    </header>

    {{-- ================= Breadcrumb ================= --}}
    <div class="mx-auto max-w-6xl px-4 pt-4 text-xs text-slate-400 sm:px-6">
        <a href="{{ route('home') }}" class="hover:text-teal-700">{{ __('Home') }}</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('marketing.cities') }}" class="hover:text-teal-700">Cities</a>
        <span class="mx-1.5">/</span>
        <span class="text-slate-600">{{ $city['name'] }}</span>
    </div>

    {{-- ================= Hero ================= --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-teal-50/70 to-white">
        <div class="mx-auto grid max-w-6xl items-center gap-12 px-4 py-14 sm:px-6 lg:grid-cols-2 lg:py-20">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold text-teal-800">
                    {{ $city['name'] }}, {{ $city['state'] }}
                </span>
                <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-5xl">
                    Hospital management software in <span class="text-teal-600">{{ $city['name'] }}</span>
                </h1>
                <p class="mt-5 text-lg text-slate-600">
                    {{ $app }} runs your whole hospital in {{ $city['name'] }} — OPD, appointments, IPD &amp; beds, pharmacy stock and GST billing, on one screen, in Hindi or English. Free setup, live the same day.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('demo.enter') }}"
                       class="rounded-lg bg-teal-600 px-6 py-3 text-center text-sm font-semibold text-white shadow-sm hover:bg-teal-700">{{ __('Try the live demo') }}</a>
                    <a href="{{ $wa }}" target="_blank" rel="noopener"
                       class="rounded-lg border border-slate-300 bg-white px-6 py-3 text-center text-sm font-semibold text-slate-700 hover:border-teal-400 hover:text-teal-700">{{ __('Talk to us on WhatsApp') }}</a>
                </div>
                <p class="mt-4 text-sm text-slate-500">14-day free trial · no card needed · setup free for hospitals in {{ $city['name'] }}</p>
            </div>

            <div class="relative">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60">
                    <div class="flex items-center gap-1.5 border-b border-slate-100 px-4 py-3">
                        <span class="h-2.5 w-2.5 rounded-full bg-slate-200"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-slate-200"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-slate-200"></span>
                        <span class="ml-3 text-xs text-slate-400">{{ $app }} · {{ $city['name'] }} Hospital</span>
                    </div>
                    <div class="grid grid-cols-3 gap-3 p-4 text-xs">
                        <div class="rounded-lg bg-teal-50 p-3">
                            <div class="text-slate-500">OPD today</div>
                            <div class="mt-1 text-xl font-bold text-slate-900">86</div>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3">
                            <div class="text-slate-500">Beds free</div>
                            <div class="mt-1 text-xl font-bold text-slate-900">9<span class="text-sm font-medium text-slate-400">/30</span></div>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3">
                            <div class="text-slate-500">Collected</div>
                            <div class="mt-1 text-xl font-bold text-slate-900">₹64k</div>
                        </div>
                        <div class="col-span-3 rounded-lg border border-slate-100 p-3">
                            <div class="mb-2 font-medium text-slate-600">Bed board</div>
                            <div class="grid grid-cols-8 gap-1.5">
                                @foreach (['t','t','o','t','o','o','t','c','o','t','t','o','o','t','c','t'] as $s)
                                    <span @class([
                                        'h-5 rounded',
                                        'bg-teal-500' => $s === 't',
                                        'bg-rose-400' => $s === 'o',
                                        'bg-amber-300' => $s === 'c',
                                    ])></span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div aria-hidden="true" class="pointer-events-none absolute -right-6 -top-6 -z-10 h-40 w-40 rounded-full bg-teal-200/40 blur-2xl"></div>
            </div>
        </div>
    </section>

    {{-- ================= Local intro ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Built for hospitals and nursing homes in {{ $city['name'] }}</h2>
            <p class="mt-4 text-slate-600">
                {{ $city['name'] }} hospitals and nursing homes are still mostly run on registers or old one-time software with no support or backups. {{ $app }} gives {{ $city['name'] }} hospitals a proper cloud system — patient records that never get lost, a live bed board, pharmacy stock that stops selling expired medicine, and GST-ready billing — for a fraction of what national software companies charge, with a real person answering on WhatsApp in Hindi.
            </p>
        </div>
    </section>

    {{-- ================= Features ================= --}}
    <section id="features" class="bg-slate-50 py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <h2 class="text-center text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Everything your {{ $city['name'] }} hospital runs on</h2>
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($features as [$title, $body, $icon])
                    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-teal-50 text-teal-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        </span>
                        <h3 class="mt-4 font-bold text-slate-900">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= Why ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <h2 class="text-center text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Why {{ $city['name'] }} hospitals choose {{ $app }}</h2>
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
    </section>

    {{-- ================= Pricing (compact) ================= --}}
    <section id="pricing" class="bg-slate-50 py-16">
        <div class="mx-auto max-w-4xl px-4 sm:px-6">
            <h2 class="text-center text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Pricing for {{ $city['name'] }} hospitals</h2>
            <p class="mt-3 text-center text-slate-600">Flat yearly plans. No per-user fee, free setup, free migration.</p>
            <div class="mt-10 grid gap-6 sm:grid-cols-2">
                @foreach ($plans->take(2) as $plan)
                    @php $featured = $plan->code === 'hospital'; @endphp
                    <div @class(['rounded-2xl bg-white p-7 shadow-sm', 'ring-2 ring-teal-600 shadow-lg' => $featured, 'ring-1 ring-slate-200' => ! $featured])>
                        @if ($featured)<span class="mb-3 inline-block rounded-full bg-teal-100 px-3 py-0.5 text-xs font-semibold text-teal-800">Most popular</span>@endif
                        <h3 class="text-lg font-bold text-slate-900">{{ $plan->name }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $plan->description }}</p>
                        <div class="mt-4"><span class="text-3xl font-extrabold text-slate-900">₹{{ number_format($plan->price_yearly, 0) }}</span><span class="text-sm text-slate-500">/year</span></div>
                        <a href="{{ $wa }}" target="_blank" rel="noopener"
                           @class(['mt-6 block rounded-lg px-4 py-2.5 text-center text-sm font-semibold', 'bg-teal-600 text-white hover:bg-teal-700' => $featured, 'bg-slate-100 text-slate-800 hover:bg-slate-200' => ! $featured])>
                           Start free trial in {{ $city['name'] }}
                        </a>
                    </div>
                @endforeach
            </div>
            <p class="mt-8 text-center text-sm text-slate-500">See full pricing, add-ons and the perpetual licence on the <a href="{{ route('home') }}#pricing" class="font-semibold text-teal-700 hover:underline">main pricing page</a>.</p>
        </div>
    </section>

    {{-- ================= FAQ ================= --}}
    <section id="faq" class="mx-auto max-w-3xl px-4 py-16 sm:px-6">
        <h2 class="text-center text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Questions from {{ $city['name'] }} hospitals</h2>
        <div class="mt-10 divide-y divide-slate-200 rounded-2xl bg-white px-6 shadow-sm ring-1 ring-slate-100">
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
    </section>

    {{-- ================= Demo CTA ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <div class="overflow-hidden rounded-3xl bg-gradient-to-br from-teal-600 to-slate-800 px-8 py-14 text-center text-white sm:px-16">
            <h2 class="text-3xl font-extrabold tracking-tight">See it running, before you decide</h2>
            <p class="mx-auto mt-3 max-w-xl text-teal-50/90">Open the live demo hospital, or message us and we'll set up a call or a visit to your hospital in {{ $city['name'] }}.</p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('demo.enter') }}" class="rounded-lg bg-white px-6 py-3 text-sm font-semibold text-teal-700 hover:bg-teal-50">{{ __('Open the live demo') }}</a>
                <a href="{{ $wa }}" target="_blank" rel="noopener" class="rounded-lg border border-white/40 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10">Talk to us about {{ $city['name'] }}</a>
            </div>
        </div>
    </section>

    {{-- ================= Other cities (internal links) ================= --}}
    <section class="mx-auto max-w-6xl px-4 pb-16 sm:px-6">
        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-6">
            <div class="text-sm font-semibold text-slate-700">Also serving hospitals in</div>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($otherCities->take(12) as $c)
                    <a href="{{ route('marketing.city', $c['slug']) }}" class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200 hover:text-teal-700 hover:ring-teal-300">{{ $c['name'] }}</a>
                @endforeach
                <a href="{{ route('marketing.cities') }}" class="rounded-full px-3 py-1 text-xs font-semibold text-teal-700 hover:underline">See all cities →</a>
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
                    {{ $app }}
                </div>
                <p class="mt-3 text-sm text-slate-500">Cloud hospital management software for growing hospitals and nursing homes across India — with roots in Bihar, Jharkhand and Chhattisgarh.</p>
            </div>
            <div class="text-sm">
                <div class="font-semibold text-slate-900">Product</div>
                <ul class="mt-3 space-y-2 text-slate-600">
                    <li><a href="{{ route('home') }}#features" class="hover:text-teal-700">{{ __('Features') }}</a></li>
                    <li><a href="{{ route('home') }}#pricing" class="hover:text-teal-700">{{ __('Pricing') }}</a></li>
                    <li><a href="{{ route('demo.enter') }}" class="hover:text-teal-700">Live demo</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-teal-700">{{ __('Log in') }}</a></li>
                </ul>
            </div>
            <div class="text-sm">
                <div class="font-semibold text-slate-900">Talk to us</div>
                <ul class="mt-3 space-y-2 text-slate-600">
                    <li><a href="{{ $wa }}" target="_blank" rel="noopener" class="hover:text-teal-700">WhatsApp: {{ $contact['phone'] }}</a></li>
                    <li><a href="mailto:{{ $contact['email'] }}" class="hover:text-teal-700">{{ $contact['email'] }}</a></li>
                    <li>{{ $contact['address'] }}</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-100 py-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} {{ $app }}. All rights reserved.
        </div>
    </footer>
</div>

</body>
</html>
