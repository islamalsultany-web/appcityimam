<?php

namespace App\Support;

use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppAuth
{
    public static function user(Request $request): ?AppUser
    {
        $authUser = Auth::user();

        if ($authUser instanceof AppUser) {
            return $authUser;
        }

        $authUserId = (int) $request->session()->get('auth_app_user_id');

        if ($authUserId <= 0) {
            return null;
        }

        return AppUser::query()->find($authUserId);
    }

    public static function id(Request $request): ?int
    {
        $authUser = self::user($request);

        return $authUser?->id;
    }

    public static function can(Request $request, string $permission): bool
    {
        $user = self::user($request);

        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        foreach (explode('|', $permission) as $candidate) {
            $candidate = trim($candidate);

            if ($candidate === '') {
                continue;
            }

            if ($user->can($candidate) || self::legacyRoleAllowsPermission($user, $candidate)) {
                return true;
            }
        }

        return false;
    }

    private static function legacyRoleAllowsPermission(AppUser $user, string $permission): bool
    {
        if (str_starts_with($permission, 'inquiries.asker.')) {
            return $user->role === 'asker' || $user->hasRole('asker');
        }

        if (str_starts_with($permission, 'inquiries.responder.')) {
            return in_array($user->role, ['responder', 'admin'], true)
                || $user->hasAnyRole(['responder', 'admin']);
        }

        if (str_starts_with($permission, 'inquiries.reviewer.')) {
            return in_array($user->role, ['reviewer', 'admin'], true)
                || $user->hasAnyRole(['reviewer', 'admin']);
        }

        return false;
    }
}
