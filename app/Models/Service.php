<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Service extends Model
{
    use BelongsToTenant;
    use LogsActivity;
    use SoftDeletes;

    public const CATEGORIES = [
        'consultation' => 'Consultation',
        'procedure' => 'Procedure',
        'investigation' => 'Investigation',
        'nursing' => 'Nursing',
        'room' => 'Room / Bed',
        'package' => 'Package',
        'misc' => 'Miscellaneous',
    ];

    protected $fillable = [
        'hospital_id', 'department_id', 'name', 'code', 'category', 'price', 'gst_rate', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'gst_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'code', 'price', 'gst_rate', 'is_active'])->logOnlyDirty();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
