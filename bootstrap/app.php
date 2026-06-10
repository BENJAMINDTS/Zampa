<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            \App\Http\Middleware\PreserveFlashForAjax::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
        $middleware->alias([
            'role'            => \App\Http\Middleware\EnsureRole::class,
            'role.superadmin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'business.active' => \App\Http\Middleware\EnsureBusinessIsActive::class,
            'can.kitchen'     => \App\Http\Middleware\EnsureCanAccessKitchen::class,
            'can.bar'         => \App\Http\Middleware\EnsureCanAccessBar::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
