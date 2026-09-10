<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Staff;
use App\Support\Tenancy;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpdController extends Controller
{
    public function index(Request $request, Tenancy $tenancy): View
    {
        $date = $request->filled('date') ? CarbonImmutable::parse($request->date('date')) : CarbonImmutable::today();

        $queue = Appointment::query()
            ->with(['patient', 'doctor', 'vital', 'consultation'])
            ->where('branch_id', $tenancy->branchId())
            ->forDate($date)
            ->when($request->filled('doctor'), fn ($q) => $q->where('doctor_id', $request->integer('doctor')))
            ->orderByRaw("field(status, 'in_consultation', 'checked_in', 'scheduled', 'completed', 'no_show', 'cancelled')")
            ->orderBy('token_no')
            ->get()
            ->groupBy(fn ($a) => $a->doctor->full_name);

        return view('opd.index', [
            'queue' => $queue,
            'date' => $date,
            'doctors' => Staff::doctors()->active()->orderBy('first_name')->get()
                ->mapWithKeys(fn ($d) => [$d->id => $d->full_name]),
            'stats' => [
                'waiting' => $queue->flatten()->where('status', 'checked_in')->count(),
                'in_consult' => $queue->flatten()->where('status', 'in_consultation')->count(),
                'done' => $queue->flatten()->where('status', 'completed')->count(),
            ],
        ]);
    }

    public function checkIn(Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->status === 'scheduled', 422);

        $appointment->update(['status' => 'checked_in', 'checked_in_at' => now()]);

        return back()->with('status', "{$appointment->patient->full_name} checked in.");
    }

    public function noShow(Appointment $appointment): RedirectResponse
    {
        abort_unless(in_array($appointment->status, ['scheduled', 'checked_in'], true), 422);

        $appointment->update(['status' => 'no_show']);

        return back()->with('status', 'Marked as no-show.');
    }
}
