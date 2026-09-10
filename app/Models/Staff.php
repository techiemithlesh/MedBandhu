<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Staff extends Model
{
    use BelongsToTenant;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'staff';

    public const TYPES = [
        'doctor' => 'Doctor',
        'nurse' => 'Nurse',
        'pharmacist' => 'Pharmacist',
        'receptionist' => 'Receptionist',
        'lab_technician' => 'Lab Technician',
        'radiologist' => 'Radiologist',
        'accountant' => 'Accountant',
        'administrator' => 'Administrator',
        'support' => 'Support Staff',
    ];

    protected $fillable = [
        'hospital_id', 'branch_id', 'department_id', 'user_id',
        'employee_code', 'type', 'salutation', 'first_name', 'last_name',
        'gender', 'dob', 'blood_group', 'phone', 'alt_phone', 'email',
        'address', 'city', 'state', 'pincode', 'photo_path', 'designation',
        'employment_type', 'joined_on', 'left_on', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'joined_on' => 'date',
            'left_on' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['employee_code', 'type', 'first_name', 'last_name', 'department_id', 'designation', 'status', 'user_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ---- relations -------------------------------------------------------

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctorProfile(): HasOne
    {
        return $this->hasOne(DoctorProfile::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function dutyRosters(): HasMany
    {
        return $this->hasMany(StaffDutyRoster::class);
    }

    /** Appointments where this staff member is the doctor. */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'doctor_id');
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(IpdAdmission::class, 'admitting_doctor_id');
    }

    // ---- helpers --------------------------------------------------------

    public function scopeDoctors(Builder $query): Builder
    {
        return $query->where('type', 'doctor');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function getIsDoctorAttribute(): bool
    {
        return $this->type === 'doctor';
    }

    public function getFullNameAttribute(): string
    {
        return trim(collect([$this->salutation, $this->first_name, $this->last_name])->filter()->implode(' '));
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
