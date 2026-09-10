<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards tenant-scoped modules: a request with no bound hospital (a Super Admin
 * who hasn't "entered" one) would otherwise see unscoped data.
 */
class RequireHospitalContext
{
    public function __construct(protected Tenancy $tenancy) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->tenancy->hasHospital()) {
            return redirect()->route('dashboard')
                ->with('error', 'Pick a hospital to work in first.');
        }

        return $next($request);
    }
}
