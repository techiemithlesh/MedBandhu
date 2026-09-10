<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PharmacySale extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id', 'branch_id', 'sale_no', 'patient_id', 'consultation_id',
        'prescribed_by', 'customer_name', 'sale_date',
        'subtotal', 'discount', 'tax', 'round_off', 'total',
        'amount_paid', 'payment_mode', 'status', 'served_by',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'round_off' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PharmacySale $s) {
            $s->sale_no ??= static::nextNumber();
            $s->served_by ??= auth()->id();
            $s->sale_date ??= today();
        });
    }

    public static function nextNumber(): string
    {
        $hospital = app(Tenancy::class)->hospital();

        $seq = DB::transaction(function () use ($hospital) {
            $locked = Hospital::whereKey($hospital->id)->lockForUpdate()->first();
            $locked->increment('pharmacy_sale_sequence');

            return $locked->pharmacy_sale_sequence;
        });

        return 'PHB-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function prescriber(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'prescribed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PharmacySaleItem::class);
    }

    public function scopeCompleted(Builder $q): Builder
    {
        return $q->where('status', 'completed');
    }

    public function getCustomerLabelAttribute(): string
    {
        return $this->patient?->full_name ?? $this->customer_name ?? 'Walk-in';
    }
}
