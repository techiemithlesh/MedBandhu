<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NursingNote extends Model
{
    use BelongsToTenant;

    public const CATEGORIES = ['general', 'observation', 'medication', 'procedure', 'diet'];

    protected $fillable = [
        'hospital_id', 'ipd_admission_id', 'patient_id', 'category', 'note', 'recorded_by', 'recorded_at',
    ];

    protected function casts(): array
    {
        return ['recorded_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (NursingNote $n) {
            $n->recorded_at ??= now();
            $n->recorded_by ??= auth()->id();
        });
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'ipd_admission_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
