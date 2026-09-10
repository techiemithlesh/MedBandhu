<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffDutyRoster extends Model
{
    use BelongsToTenant;

    public const SHIFTS = [
        'morning' => 'Morning',
        'evening' => 'Evening',
        'night' => 'Night',
        'general' => 'General',
        'off' => 'Off',
    ];

    protected $fillable = [
        'hospital_id', 'branch_id', 'staff_id', 'duty_date',
        'shift', 'start_time', 'end_time', 'notes',
    ];

    protected function casts(): array
    {
        return ['duty_date' => 'date'];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
