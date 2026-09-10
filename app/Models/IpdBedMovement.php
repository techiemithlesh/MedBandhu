<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class IpdBedMovement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id', 'ipd_admission_id', 'bed_id', 'ward_id',
        'daily_charge', 'started_at', 'ended_at', 'reason', 'moved_by',
    ];

    protected function casts(): array
    {
        return [
            'daily_charge' => 'decimal:2',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (IpdBedMovement $m) => $m->moved_by ??= auth()->id());
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'ipd_admission_id');
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    /** Calendar days this bed was occupied; open movements run to now. Min 1. */
    public function billableDays(): int
    {
        $end = $this->ended_at ?? Carbon::now();

        return max(1, $this->started_at->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1);
    }
}
