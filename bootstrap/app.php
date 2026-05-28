<?php

use App\Http\Middleware\EnsureAppUserAuthenticated;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureSecureCredentialsChanged;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'app.auth' => EnsureAppUserAuthenticated::class,
            'permission' => EnsurePermission::class,
            'secure.credentials' => EnsureSecureCredentialsChanged::class,
            'asker.credentials' => EnsureSecureCredentialsChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
