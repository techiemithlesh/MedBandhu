<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use App\Support\AppointmentSlots;
use App\Support\Tenancy;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->filled('date') ? CarbonImmutable::parse($request->date('date')) : CarbonImmutable::today();

        $appointments = Appointment::query()
            ->with(['patient', 'doctor', 'branch'])
            ->forDate($date)
            ->when($request->filled('doctor'), fn ($q) => $q->where('doctor_id', $request->integer('doctor')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByRaw('scheduled_time is null, scheduled_time')
            ->orderBy('token_no')
            ->get();

        return view('appointments.index', [
            'appointments' => $appointments,
            'date' => $date,
            'doctors' => Staff::doctors()->active()->orderBy('first_name')->get()
                ->mapWithKeys(fn ($d) => [$d->id => $d->full_name]),
        ]);
    }

    public function create(Request $request): View
    {
        return view('appointments.create', [
            'patient' => $request->filled('patient') ? Patient::findOrFail($request->integer('patient')) : null,
            'doctors' => Staff::doctors()->active()->with('doctorProfile')->orderBy('first_name')->get(),
            'presetDoctor' => $request->integer('doctor') ?: null,
            'date' => $request->input('date', CarbonImmutable::today()->toDateString()),
        ]);
    }

    public function slots(Request $request, AppointmentSlots $slots): JsonResponse
    {
        $data = $request->validate([
            'doctor' => ['required', Rule::exists('staff', 'id')->where('type', 'doctor')],
            'date' => ['required', 'date'],
        ]);

        $doctor = Staff::doctors()->findOrFail($data['doctor']);
        $date = CarbonImmutable::parse($data['date']);

        return response()->json([
            'slots' => $slots->for($doctor, $date)->values(),
        ]);
    }

    public function store(Request $request, AppointmentSlots $slots, Tenancy $tenancy): RedirectResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', Rule::exists('patients', 'id')],
            'doctor_id' => ['required', Rule::exists('staff', 'id')->where('type', 'doctor')],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'type' => ['required', Rule::in(['new', 'followup'])],
            'source' => ['required', Rule::in(['booked', 'walk_in'])],
            'reason' => ['nullable', 'string', 'max:255'],
            'fee_paid' => ['boolean'],
        ]);

        $doctor = Staff::doctors()->with('doctorProfile')->findOrFail($data['doctor_id']);
        $date = CarbonImmutable::parse($data['scheduled_date']);

        // Resolve branch + validate the slot is real and free (booked appts only).
        $branchId = $tenancy->branchId();
        if ($data['source'] === 'booked') {
            $available = $slots->for($doctor, $date);
            $match = $available->firstWhere('time', $data['scheduled_time']);

            if (! $data['scheduled_time'] || ! $match || $match['taken']) {
                throw ValidationException::withMessages(['scheduled_time' => 'That slot is not available. Pick another.']);
            }
            $branchId = $match['branch_id'];
        }

        $feePaid = $request->boolean('fee_paid');

        $appointment = DB::transaction(function () use ($data, $doctor, $date, $branchId, $slots, $feePaid) {
            return Appointment::create([
                'branch_id' => $branchId,
                'patient_id' => $data['patient_id'],
                'doctor_id' => $doctor->id,
                'department_id' => $doctor->department_id,
                'scheduled_date' => $date->toDateString(),
                'scheduled_time' => $data['scheduled_time'] ?? null,
                'slot_minutes' => $doctor->doctorProfile->appointment_duration_min ?? 15,
                'token_no' => $slots->nextTokenNo($doctor, $date),
                'type' => $data['type'],
                'source' => $data['source'],
                'status' => $data['source'] === 'walk_in' ? 'checked_in' : 'scheduled',
                'checked_in_at' => $data['source'] === 'walk_in' ? now() : null,
                'reason' => $data['reason'] ?? null,
                'consultation_fee' => $data['type'] === 'followup'
                    ? ($doctor->doctorProfile->followup_fee ?? 0)
                    : ($doctor->doctorProfile->consultation_fee ?? 0),
                'fee_paid' => $feePaid,
            ]);
        });

        return redirect()->route('appointments.show', $appointment)
            ->with('status', "Appointment {$appointment->appointment_no} booked · token {$appointment->token_no}.");
    }

    public function show(Appointment $appointment): View
    {
        $appointment->load(['patient', 'doctor.doctorProfile', 'branch', 'vital', 'consultation.items']);

        return view('appointments.show', compact('appointment'));
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->isOpen(), 422);

        $appointment->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->string('cancel_reason')->toString() ?: null,
            'cancelled_by' => $request->user()->id,
        ]);

        return back()->with('status', "Appointment {$appointment->appointment_no} cancelled.");
    }
}
