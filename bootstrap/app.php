<?php

use App\Http\Middleware\AddCspNonce;
use App\Http\Middleware\EnsureAdminTwoFactor;
use App\Http\Middleware\EnsureAppUserAuthenticated;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureSecureCredentialsChanged;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as FrameworkVerifyCsrfToken;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->replace(FrameworkVerifyCsrfToken::class, VerifyCsrfToken::class);

        $middleware->web(prepend: [
            AddCspNonce::class,
        ]);

        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'app.auth' => EnsureAppUserAuthenticated::class,
            'admin.two.factor' => EnsureAdminTwoFactor::class,
            'permission' => EnsurePermission::class,
            'secure.credentials' => EnsureSecureCredentialsChanged::class,
            'asker.credentials' => EnsureSecureCredentialsChanged::class,
            'super.admin' => EnsureSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
