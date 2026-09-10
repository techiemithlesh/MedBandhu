<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffDutyRoster;
use App\Support\Tenancy;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DutyRosterController extends Controller
{
    public function index(Request $request, Tenancy $tenancy): View
    {
        $month = CarbonImmutable::parse($request->input('month', now()->format('Y-m')).'-01');
        $branchId = $request->integer('branch') ?: $tenancy->branchId();

        $staff = Staff::active()
            ->where('branch_id', $branchId)
            ->orderBy('type')->orderBy('first_name')
            ->get();

        $entries = StaffDutyRoster::where('branch_id', $branchId)
            ->whereBetween('duty_date', [$month->startOfMonth(), $month->endOfMonth()])
            ->get()
            ->groupBy(fn ($e) => $e->staff_id.'|'.$e->duty_date->day);

        return view('rosters.index', [
            'month' => $month,
            'days' => range(1, $month->daysInMonth),
            'staff' => $staff,
            'entries' => $entries,
            'branchId' => $branchId,
            'branches' => auth()->user()->branches->pluck('name', 'id'),
            'shifts' => StaffDutyRoster::SHIFTS,
        ]);
    }

    public function store(Request $request, Tenancy $tenancy): RedirectResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'branch_id' => ['required', Rule::in(auth()->user()->branches->pluck('id')->all())],
            'roster' => ['array'],
            'roster.*' => ['array'],
            'roster.*.*' => ['nullable', Rule::in(array_keys(StaffDutyRoster::SHIFTS))],
        ]);

        $month = CarbonImmutable::parse($validated['month'].'-01');
        $branchId = (int) $validated['branch_id'];
        $validStaff = Staff::where('branch_id', $branchId)->pluck('id')->flip();

        DB::transaction(function () use ($validated, $month, $branchId, $validStaff) {
            foreach ($validated['roster'] ?? [] as $staffId => $days) {
                if (! $validStaff->has((int) $staffId)) {
                    continue;
                }

                foreach ($days as $day => $shift) {
                    $date = $month->setDay((int) $day)->toDateString();

                    if (blank($shift)) {
                        StaffDutyRoster::where('staff_id', $staffId)->whereDate('duty_date', $date)->delete();

                        continue;
                    }

                    StaffDutyRoster::updateOrCreate(
                        ['staff_id' => $staffId, 'duty_date' => $date],
                        ['branch_id' => $branchId, 'shift' => $shift],
                    );
                }
            }
        });

        return redirect()
            ->route('rosters.index', ['month' => $validated['month'], 'branch' => $branchId])
            ->with('status', 'Duty roster saved.');
    }
}
