<?php

namespace App\Support;

use App\Models\AppUser;
use Illuminate\Http\Request;

class AdminTwoFactor
{
    public static function isEnabled(): bool
    {
        return (bool) config('security.admin_two_factor.enabled', true);
    }

    public static function appliesTo(AppUser $user): bool
    {
        return self::isEnabled() && self::isAdmin($user);
    }

    public static function isAdmin(AppUser $user): bool
    {
        return $user->role === 'admin' || $user->hasRole('admin');
    }

    public static function isConfigured(AppUser $user): bool
    {
        return filled($user->two_factor_secret) && $user->two_factor_confirmed_at !== null;
    }

    public static function sessionPassed(Request $request): bool
    {
        return (bool) $request->session()->get('admin_two_factor_passed', false);
    }

    public static function markSessionPassed(Request $request): void
    {
        $request->session()->put('admin_two_factor_passed', true);
    }

    public static function clearSession(Request $request): void
    {
        $request->session()->forget([
            'admin_two_factor_passed',
            'pending_two_factor_secret',
        ]);
    }

    public static function issuer(): string
    {
        return (string) config('security.admin_two_factor.issuer', 'City Imam Portal');
    }
}
