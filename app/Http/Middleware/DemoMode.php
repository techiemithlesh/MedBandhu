<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Belt-and-braces guard for the public demo account. The "Demo" role already
 * withholds every destructive / admin permission; this also freezes the
 * profile + password routes (which have no permission middleware) and exposes
 * a `$demoMode` flag to every view for the banner.
 */
class DemoMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $isDemo = $user && $user->email === config('hms.demo.email');

        View::share('demoMode', $isDemo);

        if ($isDemo && ! $request->isMethodSafe()) {
            $name = (string) optional($request->route())->getName();

            $frozen = str_starts_with($name, 'profile.')
                || str_starts_with($name, 'password.')
                || $name === 'user-profile-information.update';

            if ($frozen) {
                return redirect()->back()->with('error', 'That action is disabled in the demo.');
            }
        }

        return $next($request);
    }
}
