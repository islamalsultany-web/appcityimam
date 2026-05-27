<?php

namespace App\Http\Middleware;

use App\Support\AppAuth;
use App\Support\EmployeeCredentialSecurity;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSecureCredentialsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = AppAuth::user($request);

        if (! $user || ! EmployeeCredentialSecurity::mustChangeCredentials($user)) {
            return $next($request);
        }

        if ($request->routeIs(
            'user.credentials.setup',
            'user.credentials.update',
            'user.info',
            'user.password.update',
            'logout',
            'logout.home',
            'login.form',
            'login.submit'
        )) {
            return $next($request);
        }

        return redirect()
            ->route('user.credentials.setup')
            ->with('warning', EmployeeCredentialSecurity::warningMessage());
    }
}
