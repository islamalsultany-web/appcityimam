<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppUserAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() && ! $request->session()->has('auth_app_user_id')) {
            return redirect()->route('login.form');
        }

        return $next($request);
    }
}
