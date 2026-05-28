<?php

namespace App\Support;

use App\Models\AppUser;
use Illuminate\Support\Facades\Hash;

class EmployeeCredentialSecurity
{
    /** @var list<string> */
    private const PROTECTED_ROLES = ['asker', 'responder', 'reviewer'];

    public static function mustChangeCredentials(AppUser $user): bool
    {
        if (self::isAdmin($user)) {
            return false;
        }

        if (! self::hasProtectedRole($user)) {
            return false;
        }

        return self::usesInsecureEmployeeCredentials($user);
    }

    public static function usesInsecureEmployeeCredentials(AppUser $user): bool
    {
        $employeeNumber = trim((string) $user->employee_number);

        if ($employeeNumber === '') {
            return false;
        }

        $username = trim((string) $user->username);
        $usernameIsEmployeeNumber = $username === $employeeNumber;
        $passwordIsEmployeeNumber = Hash::check($employeeNumber, (string) $user->password);

        return $usernameIsEmployeeNumber || $passwordIsEmployeeNumber;
    }

    public static function warningMessage(): string
    {
        return 'لأسباب أمنية، يجب عليك تغيير اسم المستخدم وكلمة المرور فوراً. '
            . 'لا تستخدم الرقم الوظيفي كاسم مستخدم أو كلمة مرور — اختر بيانات دخول خاصة بك لا يعرفها غيرك.';
    }

    private static function isAdmin(AppUser $user): bool
    {
        return $user->role === 'admin' || $user->hasRole('admin');
    }

    private static function hasProtectedRole(AppUser $user): bool
    {
        if (in_array($user->role, self::PROTECTED_ROLES, true)) {
            return true;
        }

        return $user->hasAnyRole(self::PROTECTED_ROLES);
    }
}
