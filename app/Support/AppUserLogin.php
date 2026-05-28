<?php

namespace App\Support;

use App\Models\AppUser;

class AppUserLogin
{
    public static function findByLoginId(string $loginId): ?AppUser
    {
        $loginId = trim($loginId);

        if ($loginId === '') {
            return null;
        }

        $byUsername = AppUser::query()->where('username', $loginId)->first();

        if ($byUsername) {
            return $byUsername;
        }

        return AppUser::query()->where('employee_number', $loginId)->first();
    }
}
