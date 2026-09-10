<?php

namespace App\Providers;

use App\Support\Tenancy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Tenancy::class);
    }

    public function boot(): void
    {
        // Behind Hostinger's LiteSpeed / any TLS-terminating proxy, force https
        // so route(), asset() and redirects match the canonical scheme.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Super Admin bypasses every permission check.
        Gate::before(function ($user, $ability) {
            return $user->isSuperAdmin() ? true : null;
        });

        // @module('pharmacy') ... @endmodule — hide UI for disabled tenant modules
        Blade::if('module', function (string $module) {
            $hospital = app(Tenancy::class)->hospital();

            return $hospital === null || $hospital->moduleEnabled($module);
        });
    }
}
