<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the UI language for the request: a signed-in user's saved
 * preference wins, then the guest's session/cookie choice, then the app default.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = array_keys(config('hms.locales', ['en' => 'English']));

        // ?lang= makes locale shareable (used by hreflang / sitemap) and sticky.
        if (in_array($request->query('lang'), $allowed, true)) {
            $request->session()->put('locale', $request->query('lang'));
        }

        $locale = $request->user()?->locale
            ?? $request->session()->get('locale')
            ?? $request->cookie('locale');

        if (in_array($locale, $allowed, true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
