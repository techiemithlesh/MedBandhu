<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VitalController extends Controller
{
    public function edit(Appointment $appointment): View
    {
        $appointment->load(['patient', 'doctor', 'vital']);

        return view('opd.vitals', compact('appointment'));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'height_cm' => ['nullable', 'numeric', 'between:20,300'],
            'weight_kg' => ['nullable', 'numeric', 'between:1,400'],
            'temperature_c' => ['nullable', 'numeric', 'between:30,45'],
            'pulse_bpm' => ['nullable', 'integer', 'between:20,300'],
            'systolic' => ['nullable', 'integer', 'between:40,300'],
            'diastolic' => ['nullable', 'integer', 'between:20,200'],
            'spo2' => ['nullable', 'integer', 'between:40,100'],
            'resp_rate' => ['nullable', 'integer', 'between:5,80'],
            'blood_sugar' => ['nullable', 'integer', 'between:20,800'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $appointment->vital()->updateOrCreate(
            ['appointment_id' => $appointment->id],
            [...$data, 'patient_id' => $appointment->patient_id, 'recorded_at' => now()],
        );

        return redirect()->route('opd.index', ['date' => $appointment->scheduled_date->toDateString()])
            ->with('status', "Vitals recorded for {$appointment->patient->full_name}.");
    }
}
