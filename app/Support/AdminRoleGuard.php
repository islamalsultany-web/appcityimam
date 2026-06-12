<?php

namespace App\Support;

use App\Models\AppUser;

class AdminRoleGuard
{
    public static function isAdmin(?AppUser $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->role === 'admin' || $user->hasRole('admin');
    }

    /**
     * @return list<string>
     */
    public static function assignableRoleNames(?AppUser $actor): array
    {
        if (self::isAdmin($actor)) {
            return AppUser::ROLE_OPTIONS;
        }

        return array_values(array_filter(
            AppUser::ROLE_OPTIONS,
            static fn (string $role): bool => $role !== 'admin'
        ));
    }

    /**
     * @param  list<string>  $roleNames
     */
    public static function assertCanGrantAdmin(?AppUser $actor, ?string $legacyRole, array $roleNames, ?AppUser $target = null): void
    {
        if (self::isAdmin($actor)) {
            return;
        }

        if ($target !== null && self::isAdmin($target)) {
            if ($legacyRole !== null && $legacyRole !== '' && $legacyRole !== $target->role) {
                abort(403, 'فقط مسؤول النظام يمكنه تعديل دور المسؤول.');
            }

            if (! in_array('admin', $roleNames, true)) {
                abort(403, 'فقط مسؤول النظام يمكنه تعديل دور المسؤول.');
            }

            return;
        }

        if ($legacyRole === 'admin') {
            abort(403, 'فقط مسؤول النظام يمكنه منح دور المسؤول.');
        }

        if (in_array('admin', $roleNames, true)) {
            abort(403, 'فقط مسؤول النظام يمكنه منح دور المسؤول.');
        }
    }
}
