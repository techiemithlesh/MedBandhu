<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Patient extends Model
{
    use BelongsToTenant;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'hospital_id', 'uhid', 'registered_branch_id', 'registered_by',
        'salutation', 'first_name', 'last_name', 'gender', 'dob', 'dob_estimated',
        'blood_group', 'marital_status', 'phone', 'alt_phone', 'email',
        'address', 'city', 'state', 'pincode', 'id_proof_type', 'id_proof_number',
        'guardian_name', 'guardian_relation', 'guardian_phone',
        'emergency_contact_name', 'emergency_contact_phone',
        'allergies', 'chronic_conditions', 'notes', 'photo_path', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'dob_estimated' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['uhid', 'first_name', 'last_name', 'gender', 'dob', 'phone', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function booted(): void
    {
        static::creating(function (Patient $patient) {
            $patient->uhid ??= static::nextUhid();
            $patient->registered_by ??= auth()->id();
            $patient->registered_branch_id ??= app(Tenancy::class)->branchId();
        });
    }

    /**
     * Per-hospital sequential UHID, e.g. "DEMO-000042". Locks the hospital row
     * so concurrent registrations can't collide.
     */
    public static function nextUhid(): string
    {
        $hospital = app(Tenancy::class)->hospital();

        $seq = DB::transaction(function () use ($hospital) {
            $locked = Hospital::whereKey($hospital->id)->lockForUpdate()->first();
            $locked->increment('patient_sequence');

            return $locked->patient_sequence;
        });

        return $hospital->code.'-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }

    // ---- relations -----------------------------------------------------

    public function registeredBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'registered_branch_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class)->latest('scheduled_date');
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class)->latest();
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(IpdAdmission::class)->latest('admitted_at');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->latest('id');
    }

    public function currentAdmission(): HasOne
    {
        return $this->hasOne(IpdAdmission::class)->where('status', 'admitted');
    }

    // ---- helpers ------------------------------------------------------

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(fn ($q) => $q
            ->where('uhid', 'like', $like)
            ->orWhere('first_name', 'like', $like)
            ->orWhere('last_name', 'like', $like)
            ->orWhere('phone', 'like', $like)
            ->orWhereRaw("concat(first_name, ' ', coalesce(last_name, '')) like ?", [$like]));
    }

    public function getFullNameAttribute(): string
    {
        return trim(collect([$this->salutation, $this->first_name, $this->last_name])->filter()->implode(' '));
    }

    public function getAgeAttribute(): ?string
    {
        if (! $this->dob) {
            return null;
        }

        $diff = $this->dob->diff(Carbon::now());

        if ($diff->y >= 2) {
            return $diff->y.' yrs';
        }

        return $diff->y > 0
            ? $diff->y.'y '.$diff->m.'m'
            : $diff->m.'m '.$diff->d.'d';
    }
}
