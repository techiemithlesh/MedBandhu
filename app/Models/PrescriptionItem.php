<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'hospital_id', 'consultation_id',
        'drug_name', 'strength', 'form', 'dosage', 'duration_days',
        'instructions', 'quantity', 'sort_order',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
}
