<?php

namespace App\Models\Scopes;

use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Constrains every query on a tenant-owned model to the current hospital.
 *
 * When no hospital is bound (platform Super Admin, console without context),
 * the scope is a no-op — callers that need "everything" get it, callers that
 * need isolation must ensure a hospital is bound first.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenancy = app(Tenancy::class);

        if (! $tenancy->shouldScope()) {
            return;
        }

        $builder->where(
            $model->getTable().'.'.$model->getTenantColumn(),
            $tenancy->hospitalId()
        );
    }
}
