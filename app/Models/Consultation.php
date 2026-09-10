<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consultation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id', 'appointment_id', 'patient_id', 'doctor_id',
        'chief_complaint', 'history_present_illness', 'examination_findings',
        'diagnosis', 'investigations_advised', 'advice', 'followup_date', 'private_notes',
    ];

    protected function casts(): array
    {
        return ['followup_date' => 'date'];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'doctor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class)->orderBy('sort_order');
    }
}
