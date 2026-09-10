<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates a hospital's access based on its subscription:
 *   - blocked  -> only the billing page, profile and logout are reachable
 *   - restricted (in grace) -> everything works, a banner is shown by the layout
 *
 * Super Admins (incl. while "acting in" a hospital) are never gated.
 */
class EnforceSubscription
{
    /** Route-name prefixes always reachable regardless of subscription state. */
    protected array $allowlist = [
        'billing.subscription.', 'logout', 'profile.', 'context.', 'dashboard',
    ];

    public function __construct(protected Tenancy $tenancy) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || $user->isSuperAdmin() || ! $this->tenancy->hasHospital()) {
            return $next($request);
        }

        if ($this->tenancy->hospital()->access_status !== 'blocked') {
            return $next($request);
        }

        $name = $request->route()?->getName() ?? '';
        foreach ($this->allowlist as $prefix) {
            if (Str::startsWith($name, $prefix)) {
                return $next($request);
            }
        }

        return redirect()->route('billing.subscription.show')
            ->with('error', 'This hospital\'s subscription is inactive. Renew to restore access.');
    }
}
