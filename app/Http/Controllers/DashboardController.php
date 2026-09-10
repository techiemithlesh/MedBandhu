<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\Hospital;
use App\Models\Invoice;
use App\Models\IpdAdmission;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\User;
use App\Support\Tenancy;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, Tenancy $tenancy): View
    {
        $user = $request->user();

        if ($user->isSuperAdmin() && ! $tenancy->hasHospital()) {
            return view('platform.dashboard', [
                'hospitals' => Hospital::withoutGlobalScopes()->withCount(['branches', 'users'])->latest()->get(),
                'totalUsers' => User::count(),
            ]);
        }

        return view('dashboard', [
            'hospital' => $tenancy->hospital(),
            'branch' => $tenancy->branch(),
            'stats' => [
                'branches' => Branch::count(),
                'staff' => Staff::active()->count(),
                'doctors' => Staff::doctors()->active()->count(),
                'patients' => Patient::count(),
                'appts_today' => Appointment::forDate(CarbonImmutable::today())
                    ->whereNotIn('status', ['cancelled'])->count(),
                'inpatients' => IpdAdmission::where('branch_id', $tenancy->branchId())->active()->count(),
                'beds_free' => Bed::where('branch_id', $tenancy->branchId())->where('status', 'available')->count(),
                'outstanding' => Invoice::where('branch_id', $tenancy->branchId())->outstanding()->sum('balance'),
                'collected_today' => Payment::where('branch_id', $tenancy->branchId())
                    ->whereDate('payment_date', CarbonImmutable::today())
                    ->selectRaw("COALESCE(SUM(CASE WHEN type = 'refund' THEN -amount ELSE amount END), 0) net")->value('net'),
            ],
        ]);
    }
}
