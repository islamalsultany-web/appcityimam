<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() || $this->session()->has('auth_app_user_id');
    }

    public function rules(): array
    {
        $user = \App\Support\AppAuth::user($this);
        $employeeNumber = trim((string) ($user?->employee_number ?? ''));

        return [
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
        ];
    }
}
