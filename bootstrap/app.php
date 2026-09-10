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
