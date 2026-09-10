<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id', 'branch_id', 'supplier_id', 'purchase_no',
        'invoice_number', 'invoice_date', 'received_date',
        'subtotal', 'discount', 'tax', 'total', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'received_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Purchase $p) {
            $p->purchase_no ??= static::nextNumber();
            $p->created_by ??= auth()->id();
            $p->received_date ??= today();
        });
    }

    public static function nextNumber(): string
    {
        $hospital = app(Tenancy::class)->hospital();

        $seq = DB::transaction(function () use ($hospital) {
            $locked = Hospital::whereKey($hospital->id)->lockForUpdate()->first();
            $locked->increment('purchase_sequence');

            return $locked->purchase_sequence;
        });

        return 'PUR-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
