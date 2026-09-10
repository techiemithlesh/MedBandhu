<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorProfile extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id', 'staff_id', 'specialization', 'qualifications',
        'registration_no', 'registration_council', 'experience_years',
        'consultation_fee', 'followup_fee', 'followup_valid_days',
        'appointment_duration_min', 'is_surgeon', 'online_consultation', 'bio',
    ];

    protected function casts(): array
    {
        return [
            'consultation_fee' => 'decimal:2',
            'followup_fee' => 'decimal:2',
            'is_surgeon' => 'boolean',
            'online_consultation' => 'boolean',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
