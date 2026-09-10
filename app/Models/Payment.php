<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use BelongsToTenant;

    public const MODES = ['cash', 'card', 'upi', 'cheque', 'bank_transfer'];

    protected $fillable = [
        'hospital_id', 'branch_id', 'invoice_id', 'patient_id', 'payment_no',
        'type', 'amount', 'mode', 'reference', 'payment_date', 'notes', 'received_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Payment $p) {
            $p->payment_no ??= static::nextNumber();
            $p->received_by ??= auth()->id();
            $p->payment_date ??= today();
        });
    }

    public static function nextNumber(): string
    {
        $hospital = app(Tenancy::class)->hospital();

        $seq = DB::transaction(function () use ($hospital) {
            $locked = Hospital::whereKey($hospital->id)->lockForUpdate()->first();
            $locked->increment('payment_sequence');

            return $locked->payment_sequence;
        });

        return 'RCPT-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function scopePayments(Builder $q): Builder
    {
        return $q->where('type', 'payment');
    }

    /** Signed amount: +ve for payments, -ve for refunds. */
    public function getSignedAmountAttribute(): float
    {
        return $this->type === 'refund' ? -(float) $this->amount : (float) $this->amount;
    }
}
