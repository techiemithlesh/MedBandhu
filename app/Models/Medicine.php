<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Medicine extends Model
{
    use BelongsToTenant;
    use LogsActivity;
    use SoftDeletes;

    public const FORMS = [
        'tablet' => 'Tablet', 'capsule' => 'Capsule', 'syrup' => 'Syrup', 'injection' => 'Injection',
        'ointment' => 'Ointment', 'drops' => 'Drops', 'inhaler' => 'Inhaler', 'sachet' => 'Sachet', 'other' => 'Other',
    ];

    protected $fillable = [
        'hospital_id', 'drug_category_id', 'manufacturer_id', 'name', 'generic_name',
        'form', 'strength', 'unit', 'pack_size', 'hsn_code', 'gst_rate',
        'schedule', 'reorder_level', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'gst_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'strength', 'gst_rate', 'reorder_level', 'is_active'])->logOnlyDirty();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DrugCategory::class, 'drug_category_id');
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (blank($term)) {
            return $q;
        }
        $like = '%'.$term.'%';

        return $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('generic_name', 'like', $like));
    }

    /** Units in stock for the current branch. */
    public function getStockAttribute(): int
    {
        return (int) $this->batches()
            ->where('branch_id', app(Tenancy::class)->branchId())
            ->where('quantity_available', '>', 0)
            ->sum('quantity_available');
    }

    public function getDisplayNameAttribute(): string
    {
        return trim($this->name.' '.($this->strength ?? ''));
    }
}
