<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id', 'branch_id', 'medicine_id', 'medicine_batch_id',
        'type', 'quantity', 'balance_after', 'reference_type', 'reference_id',
        'note', 'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(fn (StockMovement $m) => $m->created_by ??= auth()->id());
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicineBatch::class, 'medicine_batch_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
