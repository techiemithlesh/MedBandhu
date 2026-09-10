<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use BelongsToTenant;
    use LogsActivity;

    public const EDITABLE_STATUSES = ['draft'];

    protected $fillable = [
        'hospital_id', 'branch_id', 'patient_id', 'invoice_no', 'type',
        'appointment_id', 'ipd_admission_id', 'invoice_date', 'status',
        'subtotal', 'discount', 'tax', 'round_off', 'total', 'amount_paid', 'balance',
        'notes', 'finalized_at', 'finalized_by', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'finalized_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'round_off' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['invoice_no', 'status', 'total', 'amount_paid', 'discount'])
            ->logOnlyDirty();
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $i) {
            $i->invoice_no ??= static::nextNumber();
            $i->created_by ??= auth()->id();
            $i->invoice_date ??= today();
        });
    }

    public static function nextNumber(): string
    {
        $hospital = app(Tenancy::class)->hospital();

        $seq = DB::transaction(function () use ($hospital) {
            $locked = Hospital::whereKey($hospital->id)->lockForUpdate()->first();
            $locked->increment('invoice_sequence');

            return $locked->invoice_sequence;
        });

        return 'INV-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'ipd_admission_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('id');
    }

    public function scopeOutstanding(Builder $q): Builder
    {
        return $q->whereIn('status', ['finalized', 'partially_paid'])->where('balance', '>', 0);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, self::EDITABLE_STATUSES, true);
    }
}
