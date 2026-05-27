<?php

namespace App\Http\Middleware;

use App\Support\AppAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! AppAuth::can($request, $permission)) {
            abort(403, 'غير مصرح لك بهذا الإجراء.');
        }

        return $next($request);
    }
}
