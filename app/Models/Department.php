<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Department extends Model
{
    use BelongsToTenant;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'hospital_id', 'name', 'code', 'description', 'head_staff_id', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'code', 'head_staff_id', 'is_active'])->logOnlyDirty();
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'head_staff_id');
    }
}
