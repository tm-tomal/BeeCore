<?php

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
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'super-admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'tenant-context' => \App\Http\Middleware\TenantContext::class,
            'tenant.module' => \App\Http\Middleware\EnsureTenantModule::class,
        ]);

        // bKash server calls this URL back after a customer finishes in their
        // wallet page; it does not carry a session CSRF token.
        $middleware->validateCsrfTokens(except: ['bee-pay/bkash/callback']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
