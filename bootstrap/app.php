<?php

use App\Http\Middleware\DemoMode;
use App\Http\Middleware\EnforceSubscription;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\InitializeTenancy;
use App\Http\Middleware\RequireHospitalContext;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Shared hosting / LiteSpeed sits in front of the app.
        $middleware->trustProxies(at: '*', headers:
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->web(append: [
            SetLocale::class,
            InitializeTenancy::class,
            EnforceSubscription::class,
            DemoMode::class,
        ]);

        // `web(append:)` places these after the group's default SubstituteBindings,
        // so implicit route-model binding (e.g. `Patient $patient`) was resolving
        // BEFORE InitializeTenancy set the tenant — the per-model TenantScope was
        // a no-op at bind time, so any hospital's user could load any other
        // hospital's record by guessing its numeric ID. Explicit priority forces
        // tenant context to be bound first on every request.
        $middleware->priority([
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            SetLocale::class,
            InitializeTenancy::class,
            EnforceSubscription::class,
            DemoMode::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);

        // Payment-gateway callbacks arrive without our CSRF token.
        $middleware->validateCsrfTokens(except: [
            'billing/subscription/invoices/*/callback',
            'webhooks/razorpay',
        ]);

        $middleware->alias([
            'super-admin' => EnsureSuperAdmin::class,
            'hospital' => RequireHospitalContext::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
