<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function edit(Appointment $appointment): View
    {
        abort_if(in_array($appointment->status, ['cancelled', 'no_show'], true), 422, 'This appointment is closed.');

        // Move into the consult room on first open.
        if ($appointment->status === 'checked_in') {
            $appointment->update(['status' => 'in_consultation', 'consultation_started_at' => now()]);
        } elseif ($appointment->status === 'scheduled') {
            $appointment->update([
                'status' => 'in_consultation',
                'checked_in_at' => now(),
                'consultation_started_at' => now(),
            ]);
        }

        $appointment->load(['patient', 'doctor.doctorProfile', 'vital']);

        $consultation = $appointment->consultation()->with('items')->firstOrNew([]);

        $history = Consultation::where('patient_id', $appointment->patient_id)
            ->where('id', '!=', $consultation->id ?? 0)
            ->with(['items', 'doctor'])
            ->latest()
            ->take(10)
            ->get();

        return view('consultations.edit', compact('appointment', 'consultation', 'history'));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_if(in_array($appointment->status, ['cancelled', 'no_show'], true), 422);

        $data = $request->validate([
            'chief_complaint' => ['nullable', 'string'],
            'history_present_illness' => ['nullable', 'string'],
            'examination_findings' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'investigations_advised' => ['nullable', 'string'],
            'advice' => ['nullable', 'string'],
            'followup_date' => ['nullable', 'date', 'after:today'],
            'private_notes' => ['nullable', 'string'],
            'items' => ['array'],
            'items.*.drug_name' => ['nullable', 'string', 'max:255'],
            'items.*.strength' => ['nullable', 'string', 'max:60'],
            'items.*.form' => ['nullable', 'string', 'max:40'],
            'items.*.dosage' => ['nullable', 'string', 'max:60'],
            'items.*.duration_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'items.*.instructions' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        DB::transaction(function () use ($appointment, $data, $request) {
            $consultation = $appointment->consultation()->updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'chief_complaint' => $data['chief_complaint'] ?? null,
                    'history_present_illness' => $data['history_present_illness'] ?? null,
                    'examination_findings' => $data['examination_findings'] ?? null,
                    'diagnosis' => $data['diagnosis'] ?? null,
                    'investigations_advised' => $data['investigations_advised'] ?? null,
                    'advice' => $data['advice'] ?? null,
                    'followup_date' => $data['followup_date'] ?? null,
                    'private_notes' => $data['private_notes'] ?? null,
                ],
            );

            $consultation->items()->delete();

            foreach (array_values($data['items'] ?? []) as $i => $item) {
                if (blank($item['drug_name'] ?? null)) {
                    continue;
                }
                $consultation->items()->create([
                    'drug_name' => $item['drug_name'],
                    'strength' => $item['strength'] ?? null,
                    'form' => $item['form'] ?? null,
                    'dosage' => $item['dosage'] ?? null,
                    'duration_days' => $item['duration_days'] ?? null,
                    'instructions' => $item['instructions'] ?? null,
                    'quantity' => $item['quantity'] ?? null,
                    'sort_order' => $i,
                ]);
            }

            if ($request->input('action') === 'complete' && $appointment->status !== 'completed') {
                $appointment->update(['status' => 'completed', 'completed_at' => now()]);
            }
        });

        if ($request->input('action') === 'complete') {
            return redirect()->route('opd.index', ['date' => $appointment->scheduled_date->toDateString()])
                ->with('status', "Consultation completed for {$appointment->patient->full_name}.");
        }

        return back()->with('status', 'Consultation saved as draft.');
    }
}
