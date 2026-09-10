<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Ward extends Model
{
    use BelongsToTenant;
    use LogsActivity;
    use SoftDeletes;

    public const TYPES = [
        'general' => 'General',
        'semi_private' => 'Semi-private',
        'private' => 'Private',
        'icu' => 'ICU',
        'hdu' => 'HDU',
        'maternity' => 'Maternity',
        'pediatric' => 'Pediatric',
        'emergency' => 'Emergency',
    ];

    protected $fillable = [
        'hospital_id', 'branch_id', 'department_id', 'name', 'code', 'type',
        'floor', 'gender_restriction', 'default_daily_charge', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_daily_charge' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'code', 'type', 'default_daily_charge', 'is_active'])->logOnlyDirty();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class)->orderBy('bed_number');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
