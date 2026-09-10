<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Hospital;
use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Binds the tenant context for authenticated requests:
 *   - resolves the current Hospital (or Super Admin mode)
 *   - tells spatie/laravel-permission which team (= hospital) to scope roles to
 *   - resolves the active Branch from the session
 */
class InitializeTenancy
{
    public function __construct(protected Tenancy $tenancy) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        if ($user->hospital_id === null) {
            $this->bootSuperAdmin($request);
        } else {
            $this->bootHospitalUser($request, $user);
        }

        return $next($request);
    }

    protected function bootSuperAdmin(Request $request): void
    {
        $actingId = $request->session()->get('acting_hospital_id');

        if ($actingId && $hospital = Hospital::find($actingId)) {
            $this->tenancy->setHospital($hospital);
            setPermissionsTeamId($hospital->id);

            return;
        }

        $this->tenancy->setSuperAdmin(true);
        setPermissionsTeamId(config('hms.platform_team_id'));
    }

    protected function bootHospitalUser(Request $request, $user): void
    {
        $hospital = $user->hospital;

        abort_if($hospital === null || ! $hospital->is_active, 403, 'This hospital account is inactive.');
        abort_unless($user->is_active, 403, 'Your account has been disabled.');
        // subscription state (suspended / lapsed) is handled by EnforceSubscription,
        // which redirects to the billing page rather than hard-403.

        $this->tenancy->setHospital($hospital);
        setPermissionsTeamId($hospital->id);

        $branches = $user->branches;
        $activeId = $request->session()->get('active_branch_id');

        $branch = $branches->firstWhere('id', $activeId)
            ?? $user->primaryBranch();

        if ($branch) {
            $this->tenancy->setBranch($branch);
            $request->session()->put('active_branch_id', $branch->id);
        }
    }
}
