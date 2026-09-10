<?php

namespace App\Models\Concerns;

use App\Models\Hospital;
use App\Models\Scopes\TenantScope;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Apply to any model that stores hospital-owned data.
 *
 * - adds a global TenantScope so reads never cross hospitals
 * - auto-fills hospital_id on create from the current tenant context
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (! $model->getAttribute($model->getTenantColumn())) {
                $model->setAttribute($model->getTenantColumn(), app(Tenancy::class)->hospitalId());
            }
        });
    }

    public function getTenantColumn(): string
    {
        return 'hospital_id';
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function scopeWithoutTenantScope($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
