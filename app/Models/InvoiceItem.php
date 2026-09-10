<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InvoiceItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id', 'invoice_id', 'service_id', 'item_type',
        'source_type', 'source_id', 'description',
        'quantity', 'unit_price', 'discount_percent', 'gst_rate', 'line_total', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'gst_rate' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (InvoiceItem $item) {
            $gross = (float) $item->quantity * (float) $item->unit_price;
            $item->line_total = round($gross - $gross * (float) $item->discount_percent / 100, 2);
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
