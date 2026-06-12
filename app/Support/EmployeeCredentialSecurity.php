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
        if ($user->must_change_credentials) {
            return true;
        }

        if (self::isAdmin($user)) {
            return false;
        }

        if (! self::hasProtectedRole($user)) {
            return false;
        }

        return self::usesInsecureEmployeeCredentials($user);
    }

    public static function isImportPasswordAcceptable(string $password, ?string $employeeNumber): bool
    {
        $password = trim($password);
        $employeeNumber = trim((string) $employeeNumber);

        if ($password === '' || strlen($password) < 8) {
            return false;
        }

        if (! preg_match('/\p{L}/u', $password) || ! preg_match('/\d/', $password)) {
            return false;
        }

        return true;
    }

    public static function importRequiresCredentialChange(string $username, string $plainPassword, ?string $employeeNumber): bool
    {
        $employeeNumber = trim((string) $employeeNumber);
        $username = trim($username);
        $plainPassword = trim($plainPassword);

        if ($employeeNumber === '') {
            return false;
        }

        return $username === $employeeNumber || $plainPassword === $employeeNumber;
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
