<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class EmployeeCredentialRules
{
    /**
     * @return list<\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    public static function usernameRules(?string $employeeNumber, ?int $ignoreUserId = null): array
    {
        $employeeNumber = trim((string) $employeeNumber);

        $rules = [
            'required',
            'string',
            'max:60',
        ];

        if ($ignoreUserId !== null) {
            $rules[] = Rule::unique('app_users', 'username')->ignore($ignoreUserId);
        } else {
            $rules[] = Rule::unique('app_users', 'username');
        }

        if ($employeeNumber !== '') {
            $rules[] = Rule::notIn([$employeeNumber]);
            $rules[] = function (string $attribute, mixed $value, \Closure $fail) use ($employeeNumber): void {
                if (trim((string) $value) === $employeeNumber) {
                    $fail('لا يمكن استخدام الرقم الوظيفي كاسم مستخدم.');
                }
            };
        }

        return $rules;
    }

    /**
     * @return list<\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    public static function passwordRules(?string $employeeNumber, bool $required = true): array
    {
        $employeeNumber = trim((string) $employeeNumber);

        $rules = [
            $required ? 'required' : 'nullable',
            'string',
            'confirmed',
            Password::defaults(),
        ];

        if ($employeeNumber !== '') {
            $rules[] = function (string $attribute, mixed $value, \Closure $fail) use ($employeeNumber): void {
                if ((string) $value === $employeeNumber) {
                    $fail('لا يمكن استخدام الرقم الوظيفي ككلمة مرور.');
                }
            };
        }

        return $rules;
    }

    public static function validationMessages(): array
    {
        return [
            'username.not_in' => 'لا يمكن استخدام الرقم الوظيفي كاسم مستخدم.',
            'username.unique' => 'اسم المستخدم مستخدم مسبقاً، اختر اسماً آخر.',
        ];
    }
}
