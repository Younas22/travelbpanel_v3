<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->group(base_path('routes/agent.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register SetLocale middleware globally for web requests
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\LicenseCheck::class,   // SaaS license enforcement
            \App\Http\Middleware\B2BGateMiddleware::class,
        ]);

        $middleware->alias([
            'admin'            => \App\Http\Middleware\AdminMiddleware::class,
            'agent'            => \App\Http\Middleware\AgentMiddleware::class,
            'agent.permission' => \App\Http\Middleware\AgentPermissionMiddleware::class,
            'user'             => \App\Http\Middleware\UserMiddleware::class,
            'license.check'    => \App\Http\Middleware\LicenseCheck::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
