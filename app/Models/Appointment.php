<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Appointment extends Model
{
    use BelongsToTenant;
    use LogsActivity;

    public const OPEN_STATUSES = ['scheduled', 'checked_in', 'in_consultation'];

    protected $fillable = [
        'hospital_id', 'branch_id', 'patient_id', 'doctor_id', 'department_id',
        'appointment_no', 'scheduled_date', 'scheduled_time', 'slot_minutes', 'token_no',
        'type', 'source', 'status', 'reason', 'consultation_fee', 'fee_paid',
        'checked_in_at', 'consultation_started_at', 'completed_at',
        'cancel_reason', 'cancelled_by', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'consultation_fee' => 'decimal:2',
            'fee_paid' => 'boolean',
            'checked_in_at' => 'datetime',
            'consultation_started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['appointment_no', 'status', 'scheduled_date', 'scheduled_time', 'doctor_id', 'token_no'])
            ->logOnlyDirty();
    }

    protected static function booted(): void
    {
        static::creating(function (Appointment $appt) {
            $appt->appointment_no ??= static::nextNumber();
            $appt->created_by ??= auth()->id();
        });
    }

    public static function nextNumber(): string
    {
        $hospital = app(Tenancy::class)->hospital();

        $seq = DB::transaction(function () use ($hospital) {
            $locked = Hospital::whereKey($hospital->id)->lockForUpdate()->first();
            $locked->increment('appointment_sequence');

            return $locked->appointment_sequence;
        });

        return 'APT-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }

    // ---- relations -----------------------------------------------------

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'doctor_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function vital(): HasOne
    {
        return $this->hasOne(Vital::class);
    }

    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    // ---- scopes -------------------------------------------------------

    public function scopeForDate(Builder $q, $date): Builder
    {
        return $q->whereDate('scheduled_date', $date);
    }

    public function scopeOpen(Builder $q): Builder
    {
        return $q->whereIn('status', self::OPEN_STATUSES);
    }

    // ---- helpers -----------------------------------------------------

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    public function getScheduledTimeLabelAttribute(): string
    {
        return $this->scheduled_time ? substr((string) $this->scheduled_time, 0, 5) : '—';
    }

    public function getStatusLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }
}
