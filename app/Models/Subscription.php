<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    protected $fillable = [
        'hospital_id', 'plan_id', 'licence_type', 'billing_cycle', 'status',
        'branches', 'amount', 'grace_days',
        'trial_ends_at', 'current_period_start', 'current_period_end', 'amc_valid_until',
        'licence_key', 'cancel_at_period_end', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'trial_ends_at' => 'date',
            'current_period_start' => 'date',
            'current_period_end' => 'date',
            'amc_valid_until' => 'date',
            'cancel_at_period_end' => 'boolean',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isPerpetual(): bool
    {
        return $this->licence_type === 'perpetual';
    }

    /** The date access should be cut off if nothing is paid. */
    public function periodEndsOn(): ?Carbon
    {
        if ($this->isPerpetual()) {
            return $this->amc_valid_until;
        }

        return $this->status === 'trialing' ? $this->trial_ends_at : $this->current_period_end;
    }

    public function daysLeft(): ?int
    {
        $end = $this->periodEndsOn();

        return $end ? Carbon::today()->diffInDays($end, false) : null;
    }

    /** active | restricted (in grace) | blocked */
    public function computeAccessStatus(): string
    {
        if (in_array($this->status, ['suspended', 'cancelled'], true)) {
            return 'blocked';
        }

        if ($this->isPerpetual()) {
            // Perpetual keeps running even after AMC lapses; just a soft nudge.
            return 'active';
        }

        $end = $this->periodEndsOn();
        if (! $end) {
            return 'active';
        }

        $daysLeft = Carbon::today()->diffInDays($end, false);
        if ($daysLeft >= 0) {
            return 'active';
        }
        if ($daysLeft >= -$this->grace_days) {
            return 'restricted';
        }

        return 'blocked';
    }
}
