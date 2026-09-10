<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class IpdAdmission extends Model
{
    use BelongsToTenant;
    use LogsActivity;

    protected $fillable = [
        'hospital_id', 'branch_id', 'admission_no', 'patient_id', 'bed_id',
        'admitting_doctor_id', 'department_id', 'appointment_id', 'source',
        'admitted_at', 'expected_discharge_on', 'provisional_diagnosis', 'admission_notes',
        'attendant_name', 'attendant_phone', 'attendant_relation',
        'status', 'discharged_at', 'discharge_type', 'discharge_summary', 'discharge_doctor_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'admitted_at' => 'datetime',
            'discharged_at' => 'datetime',
            'expected_discharge_on' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['admission_no', 'status', 'bed_id', 'admitting_doctor_id', 'discharge_type'])
            ->logOnlyDirty();
    }

    protected static function booted(): void
    {
        static::creating(function (IpdAdmission $a) {
            $a->admission_no ??= static::nextNumber();
            $a->created_by ??= auth()->id();
            $a->admitted_at ??= now();
        });
    }

    public static function nextNumber(): string
    {
        $hospital = app(Tenancy::class)->hospital();

        $seq = DB::transaction(function () use ($hospital) {
            $locked = Hospital::whereKey($hospital->id)->lockForUpdate()->first();
            $locked->increment('ipd_sequence');

            return $locked->ipd_sequence;
        });

        return 'IPD-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }

    // ---- relations ---------------------------------------------------------

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function admittingDoctor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'admitting_doctor_id');
    }

    public function dischargeDoctor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'discharge_doctor_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function bedMovements(): HasMany
    {
        return $this->hasMany(IpdBedMovement::class)->orderBy('started_at');
    }

    public function currentMovement(): HasOne
    {
        return $this->hasOne(IpdBedMovement::class)->whereNull('ended_at');
    }

    public function nursingNotes(): HasMany
    {
        return $this->hasMany(NursingNote::class)->latest('recorded_at');
    }

    public function charges(): HasMany
    {
        return $this->hasMany(IpdCharge::class)->orderBy('charge_date');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'ipd_admission_id')->latest('id');
    }

    // ---- scopes / helpers -------------------------------------------------

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'admitted');
    }

    public function isActive(): bool
    {
        return $this->status === 'admitted';
    }

    public function getWardAttribute(): ?Ward
    {
        return $this->bed?->ward;
    }

    public function getDaysAdmittedAttribute(): int
    {
        $end = $this->discharged_at ?? Carbon::now();

        // Same-day admissions count as 1 day.
        return max(1, $this->admitted_at->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1);
    }

    public function chargesTotal(): float
    {
        return (float) $this->charges()->sum('amount');
    }
}
