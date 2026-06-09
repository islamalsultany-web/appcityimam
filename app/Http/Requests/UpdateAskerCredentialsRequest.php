<?php

namespace App\Http\Requests;

use App\Support\AppAuth;
use App\Support\EmployeeCredentialRules;
use Illuminate\Foundation\Http\FormRequest;

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
            'username' => EmployeeCredentialRules::usernameRules($employeeNumber, $user?->id),
            'password' => EmployeeCredentialRules::passwordRules($employeeNumber),
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return EmployeeCredentialRules::validationMessages();
    }
}
