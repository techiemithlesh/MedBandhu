<?php

namespace App\Support;

use App\Models\Bed;
use App\Models\IpdAdmission;
use App\Models\IpdBedMovement;
use App\Models\IpdCharge;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IpdService
{
    public function admit(Patient $patient, Bed $bed, array $data): IpdAdmission
    {
        return DB::transaction(function () use ($patient, $bed, $data) {
            $bed = Bed::withoutTenantScope()->lockForUpdate()->find($bed->id);

            if ($patient->currentAdmission) {
                throw ValidationException::withMessages(['patient' => 'This patient already has an active admission.']);
            }
            if (! in_array($bed->status, ['available', 'reserved'], true)) {
                throw ValidationException::withMessages(['bed_id' => 'That bed is no longer available.']);
            }

            $admission = IpdAdmission::create([
                'branch_id' => $bed->branch_id,
                'patient_id' => $patient->id,
                'bed_id' => $bed->id,
                'status' => 'admitted',
                'admitting_doctor_id' => $data['admitting_doctor_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'appointment_id' => $data['appointment_id'] ?? null,
                'source' => $data['source'] ?? 'direct',
                'admitted_at' => $data['admitted_at'] ?? now(),
                'expected_discharge_on' => $data['expected_discharge_on'] ?? null,
                'provisional_diagnosis' => $data['provisional_diagnosis'] ?? null,
                'admission_notes' => $data['admission_notes'] ?? null,
                'attendant_name' => $data['attendant_name'] ?? null,
                'attendant_phone' => $data['attendant_phone'] ?? null,
                'attendant_relation' => $data['attendant_relation'] ?? null,
            ]);

            $admission->bedMovements()->create([
                'bed_id' => $bed->id,
                'ward_id' => $bed->ward_id,
                'daily_charge' => $bed->daily_charge,
                'started_at' => $admission->admitted_at,
            ]);

            $bed->update(['status' => 'occupied']);

            return $admission;
        });
    }

    public function transfer(IpdAdmission $admission, Bed $newBed, ?string $reason = null): void
    {
        DB::transaction(function () use ($admission, $newBed, $reason) {
            abort_unless($admission->isActive(), 422);

            $newBed = Bed::withoutTenantScope()->lockForUpdate()->find($newBed->id);
            if ($newBed->id === $admission->bed_id) {
                throw ValidationException::withMessages(['bed_id' => 'Patient is already in that bed.']);
            }
            if (! in_array($newBed->status, ['available', 'reserved'], true)) {
                throw ValidationException::withMessages(['bed_id' => 'That bed is not available.']);
            }

            $current = $admission->currentMovement;
            $current?->update(['ended_at' => now()]);

            $oldBed = $current?->bed;
            $oldBed?->update(['status' => 'cleaning']);

            $admission->bedMovements()->create([
                'bed_id' => $newBed->id,
                'ward_id' => $newBed->ward_id,
                'daily_charge' => $newBed->daily_charge,
                'started_at' => now(),
                'reason' => $reason,
            ]);

            $admission->update(['bed_id' => $newBed->id]);
            $newBed->update(['status' => 'occupied']);
        });
    }

    public function discharge(IpdAdmission $admission, array $data): void
    {
        DB::transaction(function () use ($admission, $data) {
            abort_unless($admission->isActive(), 422);

            $when = $data['discharged_at'] ?? now();

            $current = $admission->currentMovement;
            $current?->update(['ended_at' => $when]);
            $current?->bed?->update(['status' => 'cleaning']);

            $admission->update([
                'status' => match ($data['discharge_type']) {
                    'lama' => 'lama',
                    'expired' => 'expired',
                    'referral' => 'referred_out',
                    default => 'discharged',
                },
                'discharged_at' => $when,
                'discharge_type' => $data['discharge_type'],
                'discharge_summary' => $data['discharge_summary'] ?? null,
                'discharge_doctor_id' => $data['discharge_doctor_id'] ?? $admission->admitting_doctor_id,
            ]);

            $this->generateBedCharges($admission->fresh(['bedMovements']));
        });
    }

    /** Idempotent: one bed charge per movement, refreshed to current day count. */
    public function generateBedCharges(IpdAdmission $admission): void
    {
        foreach ($admission->bedMovements as $movement) {
            $days = $movement->billableDays();
            $amount = round($days * (float) $movement->daily_charge, 2);

            IpdCharge::updateOrCreate(
                ['ipd_admission_id' => $admission->id, 'bed_movement_id' => $movement->id, 'type' => 'bed'],
                [
                    'charge_date' => $movement->started_at->toDateString(),
                    'description' => "Bed charge — {$movement->ward->name} ({$days} day".($days > 1 ? 's' : '').')',
                    'unit_price' => $movement->daily_charge,
                    'quantity' => $days,
                    'amount' => $amount,
                    'auto_generated' => true,
                ],
            );
        }
    }
}
