<?php

namespace App\Http\Middleware;

use App\Support\AdminTwoFactor;
use App\Support\AppAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = AppAuth::user($request);

        if (! $user || ! AdminTwoFactor::appliesTo($user)) {
            return $next($request);
        }

        if ($request->routeIs(
            'user.two-factor.setup',
            'user.two-factor.confirm',
            'user.two-factor.verify',
            'user.two-factor.verify.submit',
            'user.credentials.setup',
            'user.credentials.update',
            'user.info',
            'user.password.update',
            'logout',
            'logout.home'
        )) {
            return $next($request);
        }

        if (! AdminTwoFactor::isConfigured($user)) {
            return redirect()
                ->route('user.two-factor.setup')
                ->with('warning', 'يجب تفعيل المصادقة الثنائية (2FA) لحساب مدير النظام قبل المتابعة.');
        }

        if (! AdminTwoFactor::sessionPassed($request)) {
            return redirect()
                ->route('user.two-factor.verify')
                ->with('warning', 'أدخل رمز المصادقة الثنائية لمتابعة الدخول.');
        }

        return $next($request);
    }
}
