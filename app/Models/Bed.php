<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bed extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    public const STATUSES = ['available', 'occupied', 'reserved', 'cleaning', 'blocked'];

    protected $fillable = [
        'hospital_id', 'branch_id', 'ward_id',
        'bed_number', 'room_label', 'daily_charge', 'status', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'daily_charge' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(IpdBedMovement::class);
    }

    /** The admission currently occupying this bed, if any. */
    public function currentAdmission(): HasOne
    {
        return $this->hasOne(IpdAdmission::class)->where('status', 'admitted');
    }

    public function scopeAvailable(Builder $q): Builder
    {
        return $q->where('status', 'available')->where('is_active', true);
    }

    public function getLabelAttribute(): string
    {
        return $this->room_label ? "{$this->room_label} / {$this->bed_number}" : $this->bed_number;
    }
}
