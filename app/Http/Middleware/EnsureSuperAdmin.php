<?php

namespace App\Http\Middleware;

use App\Support\AppAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = AppAuth::user($request);

        if (! $user || ! ($user->role === 'admin' || $user->hasRole('admin'))) {
            abort(403, 'غير مصرح لك بهذا الإجراء.');
        }

        return $next($request);
    }
}
