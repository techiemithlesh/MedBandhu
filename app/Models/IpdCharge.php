<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpdCharge extends Model
{
    use BelongsToTenant;

    public const TYPES = ['bed', 'service', 'procedure', 'consumable', 'other'];

    protected $fillable = [
        'hospital_id', 'ipd_admission_id', 'type', 'charge_date', 'description',
        'unit_price', 'quantity', 'amount', 'auto_generated', 'bed_movement_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'charge_date' => 'date',
            'unit_price' => 'decimal:2',
            'quantity' => 'decimal:2',
            'amount' => 'decimal:2',
            'auto_generated' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (IpdCharge $c) {
            $c->created_by ??= auth()->id();
            if (empty($c->amount)) {
                $c->amount = round((float) $c->unit_price * (float) $c->quantity, 2);
            }
        });
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'ipd_admission_id');
    }
}
