<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MedicineBatch extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id', 'branch_id', 'medicine_id', 'purchase_item_id',
        'batch_number', 'expiry_date', 'mrp', 'purchase_price', 'sale_price',
        'quantity_received', 'quantity_available',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'mrp' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    /** In stock, not expired — ordered first-expiry-first-out. */
    public function scopeDispensable(Builder $q): Builder
    {
        return $q->where('quantity_available', '>', 0)
            ->whereDate('expiry_date', '>=', today())
            ->orderBy('expiry_date');
    }

    public function scopeExpiringWithin(Builder $q, int $days): Builder
    {
        return $q->where('quantity_available', '>', 0)
            ->whereBetween('expiry_date', [today(), today()->addDays($days)]);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->lt(Carbon::today());
    }
}
