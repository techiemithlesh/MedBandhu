<?php

namespace App\Models;

use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PlatformInvoice extends Model
{
    protected $fillable = [
        'hospital_id', 'subscription_id', 'number', 'description',
        'period_start', 'period_end', 'due_date',
        'subtotal', 'tax', 'total', 'amount_paid', 'status', 'paid_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PlatformInvoice $inv) {
            $inv->number ??= static::nextNumber();
            $inv->created_by ??= auth()->id();
        });
    }

    public static function nextNumber(): string
    {
        return DB::transaction(function () {
            $seq = (int) DB::table('platform_invoices')->lockForUpdate()->count() + 1;
            // guard against gaps from deletes
            $last = DB::table('platform_invoices')->orderByDesc('id')->value('number');
            if ($last && preg_match('/(\d+)$/', $last, $m)) {
                $seq = max($seq, (int) $m[1] + 1);
            }

            return 'SB-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
        });
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PlatformPayment::class);
    }

    public function getBalanceAttribute(): float
    {
        return round((float) $this->total - (float) $this->amount_paid, 2);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'sent' && $this->due_date->isPast();
    }
}
