<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class MarketingController extends Controller
{
    public function home()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $plans = Plan::query()
            ->where('is_active', true)
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->get();

        return view('marketing.home', [
            'plans' => $plans,
            'contact' => config('hms.contact'),
            'features' => $this->features(),
            'whys' => $this->whys(),
            'steps' => $this->steps(),
            'faqs' => $this->faqs(),
        ]);
    }

    /** Local-SEO index page linking to every city page (also keeps them crawlable). */
    public function cities()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('marketing.cities', [
            'cities' => collect(config('hms.cities'))->groupBy('state'),
            'contact' => config('hms.contact'),
        ]);
    }

    /** "Hospital management software in {city}" — same product, local framing. */
    public function city(string $citySlug)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $city = collect(config('hms.cities'))->firstWhere('slug', $citySlug);
        abort_unless($city, 404);

        $plans = Plan::query()
            ->where('is_active', true)
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->get();

        return view('marketing.city', [
            'city' => $city,
            'otherCities' => collect(config('hms.cities'))->reject(fn ($c) => $c['slug'] === $citySlug)->values(),
            'plans' => $plans,
            'contact' => config('hms.contact'),
            'features' => $this->features(),
            'whys' => $this->whys(),
            'faqs' => $this->faqs($city),
        ]);
    }

    public function robots(): Response
    {
        $base = rtrim(config('app.url'), '/');

        $body = implode("\n", [
            '# '.config('app.name').' — robots.txt',
            '',
            'User-agent: *',
            'Allow: /$',
            'Allow: /icons/',
            'Disallow: /dashboard',
            'Disallow: /login',
            'Disallow: /demo',
            'Disallow: /locale',
            'Disallow: /profile',
            'Disallow: /platform/',
            'Disallow: /billing/',
            'Disallow: /patients',
            'Disallow: /appointments',
            'Disallow: /opd',
            'Disallow: /ipd/',
            'Disallow: /pharmacy/',
            'Disallow: /reports/',
            '',
            '# AI answer engines are welcome to read the public pages',
            'User-agent: GPTBot',
            'Allow: /',
            'User-agent: OAI-SearchBot',
            'Allow: /',
            'User-agent: ChatGPT-User',
            'Allow: /',
            'User-agent: ClaudeBot',
            'Allow: /',
            'User-agent: Claude-Web',
            'Allow: /',
            'User-agent: PerplexityBot',
            'Allow: /',
            'User-agent: Google-Extended',
            'Allow: /',
            'User-agent: Applebot-Extended',
            'Allow: /',
            '',
            'Sitemap: '.$base.'/sitemap.xml',
            '',
        ]);

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        $base = rtrim(config('app.url'), '/');
        $today = now()->toDateString();

        $urls = [
            ['loc' => $base.'/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => $base.'/hospital-management-software', 'priority' => '0.8', 'changefreq' => 'weekly'],
        ];

        foreach (config('hms.cities') as $city) {
            $urls[] = [
                'loc' => $base.'/hospital-management-software/'.$city['slug'],
                'priority' => '0.7',
                'changefreq' => 'monthly',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ($urls as $u) {
            $xml .= '  <url>'."\n"
                .'    <loc>'.$u['loc'].'</loc>'."\n"
                .'    <lastmod>'.$today.'</lastmod>'."\n"
                .'    <changefreq>'.$u['changefreq'].'</changefreq>'."\n"
                .'    <priority>'.$u['priority'].'</priority>'."\n"
                .'    <xhtml:link rel="alternate" hreflang="en-IN" href="'.$u['loc'].'?lang=en"/>'."\n"
                .'    <xhtml:link rel="alternate" hreflang="hi-IN" href="'.$u['loc'].'?lang=hi"/>'."\n"
                .'    <xhtml:link rel="alternate" hreflang="x-default" href="'.$u['loc'].'"/>'."\n"
                .'  </url>'."\n";
        }

        $xml .= '</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * llms.txt (llmstxt.org): only the H1 is required; everything before the
     * final H2 sections must be heading-free body content, and every H2
     * section must be a markdown link list, not prose — so feature/pricing/
     * FAQ copy stays as plain paragraphs and only "## Links" uses headings.
     */
    public function llms(): Response
    {
        $app = config('app.name');
        $base = rtrim(config('app.url'), '/');
        $cities = collect(config('hms.cities'))->pluck('name')->implode(', ');

        $lines = ["# {$app}", '',
            "> {$app} is cloud-based hospital management software (HMS) for hospitals and nursing homes in India. It covers patient registration and OPD, appointments, IPD and bed management, pharmacy and stock, GST billing, and reports — across multiple branches, with a Hindi or English interface.",
            '',
            '**What it does**',
        ];
        foreach ($this->features() as [$title, $body]) {
            $lines[] = "- {$title}: {$body}";
        }
        $lines[] = '';
        $lines[] = '**Pricing** (flat, no per-user fee)';
        foreach (Plan::where('is_active', true)->where('is_public', true)->orderBy('sort_order')->get() as $p) {
            $lines[] = "- {$p->name} — ₹".number_format($p->price_monthly, 0)."/month or ₹".number_format($p->price_yearly, 0)."/year. ".rtrim($p->description, '.').'.';
        }
        $lines[] = '- Billed monthly or yearly via UPI or bank transfer, with a GST invoice; a one-time perpetual licence is also available. Card payment is not yet supported.';
        $lines[] = '';
        $lines[] = '**Who it is for**';
        $lines[] = "Single and multi-branch hospitals, nursing homes and polyclinics in India — especially tier-2/3 cities. Founder-onboarded, not self-signup: a hospital talks to the team (WhatsApp or a visit) before going live. Serving hospitals in {$cities} and anywhere else in India on request.";
        $lines[] = '';
        $lines[] = '## Links';
        $lines[] = "- Homepage: {$base}/";
        $lines[] = "- Live demo (explore real screens, no signup): {$base}/demo";
        $lines[] = "- Pricing: {$base}/#pricing";
        $lines[] = "- Frequently asked questions: {$base}/#faq";
        $lines[] = "- Cities served: {$base}/hospital-management-software";

        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** @return array<int, array{0:string,1:string,2:string}> title, body, icon path */
    private function features(): array
    {
        return [
            ['Patients & OPD', 'Register patients with a permanent UHID, book appointments with a token queue, record vitals and consultations.', 'M9 12h6m-3-3v6M4 6h16M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2z'],
            ['Doctors & Staff', 'Departments, doctor schedules, duty rosters and role-based access for every team member.', 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-2a4 4 0 100-8 4 4 0 000 8z'],
            ['IPD & Bed board', 'Live ward map, admissions, transfers and discharge — with bed charges that add up automatically.', 'M3 12h18M3 12v6m18-6v6M5 12V8a2 2 0 012-2h4a2 2 0 012 2v4'],
            ['Pharmacy & stock', 'Purchases, batch and expiry tracking, and first-expiry-first-out dispensing that can never over-sell.', 'M19 7l-1.5 12.5a2 2 0 01-2 1.5H8.5a2 2 0 01-2-1.5L5 7m3 0V5a2 2 0 012-2h4a2 2 0 012 2v2M4 7h16'],
            ['Billing & GST', 'Patient invoices, part-payments, refunds and GST-ready receipts — plus your subscription billing in one place.', 'M9 7h6m-6 4h6m-6 4h4M5 3h14a2 2 0 012 2v14l-3-2-2 2-2-2-2 2-2-2-3 2V5a2 2 0 012-2z'],
            ['Reports & insights', 'OPD, IPD, revenue, pharmacy and patient reports — filter by branch and date, export anytime.', 'M9 19v-6m4 6V9m4 10V5M4 21h16'],
        ];
    }

    /** @return array<int, array{0:string,1:string}> */
    private function whys(): array
    {
        return [
            ['Works on slow internet', 'Light pages that load on a 2G connection and an entry-level phone. No servers or special hardware to buy.'],
            ['Hindi and English', 'Switch the whole interface to Hindi so your reception and nursing staff are comfortable from day one.'],
            ['Multi-branch from the start', 'One hospital, many branches — switch between them in a click and see the whole picture together.'],
            ['Honest, flat pricing', 'Simple monthly or yearly plans. No per-user charges, no setup fee, no surprise bills, cancel anytime.'],
            ['Your data stays yours', 'Each hospital’s data is isolated. Export your patients, bills and stock whenever you want. We never sell or share it.'],
            ['Real support on WhatsApp', 'Message us in Hindi or English and get a person, not a ticket number.'],
        ];
    }

    /** @return array<int, array{0:string,1:string,2:string}> */
    private function steps(): array
    {
        return [
            ['1', 'We set up your hospital', 'Send us your branches, departments and doctor list. We load it for you — free.'],
            ['2', 'Your team logs in', 'Reception, doctors, nurses, pharmacy and accounts each get their own role and screen.'],
            ['3', 'You see everything', 'From the first day: today’s OPD, free beds, low stock and money collected — on one dashboard.'],
        ];
    }

    /**
     * @param  array{slug:string,name:string,state:string}|null  $city
     * @return array<int, array{0:string,1:string}> question, answer
     */
    private function faqs(?array $city = null): array
    {
        $app = config('app.name');

        $list = [
            ['What is '.$app.'?', $app.' is cloud-based hospital management software (HMS) for hospitals and nursing homes in India. It covers patient registration and OPD, appointments, IPD and bed management, pharmacy and stock, GST billing and reports — across multiple branches, in Hindi or English.'],
            ['How much does '.$app.' cost?', 'Plans start at ₹799 per month (or ₹7,999 per year) for a single-branch clinic and ₹1,499 per month for a full hospital with IPD, plus 18% GST. Every plan includes unlimited users, free setup and WhatsApp support, with no card needed to get started — talk to us and we set your hospital up personally.'],
            ['Do we need to buy servers or install anything?', 'No. '.$app.' runs in the cloud. Any computer or phone with a web browser works — even at the reception desk. It can also be installed as an app on a phone or laptop.'],
            ['Can we move our old paper records in?', 'Yes. Send us your patient list, doctor list and current stock in any format — Excel, or even photos of registers — and we load it during setup, free of charge.'],
            ['What if the internet goes down?', 'Pages are built to be light and to recover quickly, and records sync as soon as you are back online. The installable app keeps the last screens available offline.'],
            ['Is our patient data safe?', 'Each hospital’s data is isolated so only your logged-in staff can see it. You can export everything at any time, and we never sell or share your data.'],
            ['How do we pay?', 'UPI or bank transfer, with a proper GST invoice — message us the reference and we confirm it the same day. Monthly or yearly, your choice. A one-time perpetual licence is also available for hospitals that prefer to own the software.'],
            ['Which areas do you serve?', $app.' works for hospitals anywhere in India. We started with hospitals across Bihar, Jharkhand, Chhattisgarh and eastern India, and support is available in Hindi and English.'],
        ];

        if ($city) {
            $list[] = [
                "Do you support hospitals in {$city['name']}?",
                "Yes — {$app} is used by hospitals and nursing homes in {$city['name']}, {$city['state']}, and we can set your hospital up the same day. Support is available on WhatsApp and phone in Hindi and English, and we'll come to your hospital to help with setup where we can.",
            ];
        }

        return $list;
    }
}
