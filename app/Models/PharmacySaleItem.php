<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacySaleItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id', 'pharmacy_sale_id', 'medicine_id', 'medicine_batch_id',
        'batch_number', 'expiry_date', 'quantity', 'mrp', 'sale_price',
        'discount_percent', 'gst_rate', 'line_total',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'mrp' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'gst_rate' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(PharmacySale::class, 'pharmacy_sale_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicineBatch::class, 'medicine_batch_id');
    }
}
