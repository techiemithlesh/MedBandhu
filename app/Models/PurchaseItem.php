<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id', 'purchase_id', 'medicine_id', 'batch_number', 'expiry_date',
        'quantity', 'free_quantity', 'purchase_price', 'mrp', 'sale_price',
        'discount_percent', 'gst_rate', 'line_total',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'purchase_price' => 'decimal:2',
            'mrp' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'gst_rate' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
