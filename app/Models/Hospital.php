<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Hospital extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'slug', 'email', 'phone', 'address', 'city', 'state', 'pincode',
        'logo_path', 'subscription_plan', 'subscription_status', 'trial_ends_at',
        'access_status', 'branch_limit', 'custom_domain',
        'settings', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'trial_ends_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Hospital $hospital) {
            $hospital->slug ??= Str::slug($hospital->name);
            $hospital->code = Str::upper($hospital->code);
        });
    }

    public function branches(): HasMany
    {
        // A hospital's branches are already constrained by hospital_id here;
        // the tenant scope (current-hospital filter) would wrongly hide them
        // in cross-tenant platform views, so drop it on this relation.
        return $this->hasMany(Branch::class)->withoutGlobalScope(TenantScope::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function platformInvoices(): HasMany
    {
        return $this->hasMany(PlatformInvoice::class)->latest('id');
    }

    public function setting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * A module is on when both the plan includes it AND the tenant hasn't
     * switched it off in settings. No plan / no lists => everything on.
     */
    public function moduleEnabled(string $module): bool
    {
        $planModules = $this->relationLoaded('subscription')
            ? $this->subscription?->plan?->moduleList()
            : $this->subscription()->with('plan')->first()?->plan?->moduleList();

        if ($planModules !== null && ! in_array($module, $planModules, true)) {
            return false;
        }

        $tenantModules = $this->setting('modules');

        return $tenantModules === null || in_array($module, (array) $tenantModules, true);
    }

    public function planFeature(string $feature): bool
    {
        return (bool) $this->subscription?->plan?->hasFeature($feature);
    }

    public function branchesUsed(): int
    {
        return $this->branches()->count();
    }

    public function canAddBranch(): bool
    {
        return $this->branchesUsed() < $this->branch_limit;
    }
}
