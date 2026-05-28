<?php

namespace App\Http\Requests;

use App\Models\AppUser;
use App\Support\AppAuth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAskerCredentialsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = AppAuth::user($this);

        return $user !== null;
    }

    public function rules(): array
    {
        $user = AppAuth::user($this);
        $employeeNumber = trim((string) ($user?->employee_number ?? ''));

        return [
            'username' => [
                'required',
                'string',
                'max:60',
                Rule::unique('app_users', 'username')->ignore($user?->id),
                Rule::notIn($employeeNumber !== '' ? [$employeeNumber] : []),
                function (string $attribute, mixed $value, \Closure $fail) use ($employeeNumber): void {
                    if ($employeeNumber !== '' && trim((string) $value) === $employeeNumber) {
                        $fail('لا يمكن استخدام الرقم الوظيفي كاسم مستخدم.');
                    }
                },
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::defaults(),
                function (string $attribute, mixed $value, \Closure $fail) use ($employeeNumber): void {
                    if ($employeeNumber !== '' && (string) $value === $employeeNumber) {
                        $fail('لا يمكن استخدام الرقم الوظيفي ككلمة مرور.');
                    }
                },
            ],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.not_in' => 'لا يمكن استخدام الرقم الوظيفي كاسم مستخدم.',
            'username.unique' => 'اسم المستخدم مستخدم مسبقاً، اختر اسماً آخر.',
        ];
    }
}
