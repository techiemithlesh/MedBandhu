<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PlatformPayment extends Model
{
    public const METHODS = ['razorpay', 'cash', 'upi', 'bank_transfer', 'cheque', 'adjustment'];

    protected $fillable = [
        'hospital_id', 'platform_invoice_id', 'number', 'amount', 'method',
        'gateway_order_id', 'gateway_payment_id', 'gateway_signature',
        'reference', 'notes', 'paid_at', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PlatformPayment $p) {
            $p->number ??= static::nextNumber();
            $p->recorded_by ??= auth()->id();
            $p->paid_at ??= now();
        });
    }

    public static function nextNumber(): string
    {
        return DB::transaction(function () {
            $last = DB::table('platform_payments')->orderByDesc('id')->value('number');
            $seq = $last && preg_match('/(\d+)$/', $last, $m) ? (int) $m[1] + 1 : 1;

            return 'PR-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PlatformInvoice::class, 'platform_invoice_id');
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }
}
