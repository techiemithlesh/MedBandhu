<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorSchedule extends Model
{
    use BelongsToTenant;

    public const DAYS = [
        0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
        4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday',
    ];

    protected $fillable = [
        'hospital_id', 'staff_id', 'branch_id', 'day_of_week',
        'start_time', 'end_time', 'slot_minutes', 'max_tokens',
        'effective_from', 'effective_to', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getDayLabelAttribute(): string
    {
        return self::DAYS[$this->day_of_week] ?? '—';
    }

    public function getTimeRangeAttribute(): string
    {
        return substr((string) $this->start_time, 0, 5).'–'.substr((string) $this->end_time, 0, 5);
    }
}
