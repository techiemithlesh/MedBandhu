<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsActivity, Notifiable;

    protected $fillable = [
        'hospital_id',
        'name',
        'email',
        'phone',
        'designation',
        'password',
        'is_active',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'designation', 'is_active', 'hospital_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function primaryBranch(): ?Branch
    {
        return $this->branches->firstWhere('pivot.is_primary', true)
            ?? $this->branches->first();
    }

    /**
     * Platform-level operator, not bound to any hospital.
     *
     * Deliberately does NOT call hasRole(): under spatie "teams" mode role
     * checks are scoped to the current team, and a Super Admin who has
     * "entered" a hospital would then fail their own check. Every account we
     * create with a null hospital_id is a platform operator by construction;
     * the 'Super Admin' role is still assigned (for auditing / future use).
     */
    public function isSuperAdmin(): bool
    {
        return $this->hospital_id === null;
    }
}
