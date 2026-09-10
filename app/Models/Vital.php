<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vital extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id', 'appointment_id', 'patient_id',
        'height_cm', 'weight_kg', 'bmi', 'temperature_c', 'pulse_bpm',
        'systolic', 'diastolic', 'spo2', 'resp_rate', 'blood_sugar',
        'notes', 'recorded_by', 'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'height_cm' => 'decimal:1',
            'weight_kg' => 'decimal:1',
            'bmi' => 'decimal:1',
            'temperature_c' => 'decimal:1',
            'recorded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Vital $vital) {
            if ($vital->height_cm && $vital->weight_kg) {
                $m = $vital->height_cm / 100;
                $vital->bmi = round($vital->weight_kg / ($m * $m), 1);
            } else {
                $vital->bmi = null;
            }
            $vital->recorded_at ??= now();
            $vital->recorded_by ??= auth()->id();
        });
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function getBloodPressureAttribute(): ?string
    {
        return $this->systolic && $this->diastolic ? "{$this->systolic}/{$this->diastolic}" : null;
    }
}
