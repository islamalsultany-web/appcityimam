<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesAppPermissions;
use App\Models\AppUser;
use App\Support\AdminRoleGuard;
use App\Support\AppAuth;
use App\Support\EmployeeCredentialRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppUserRequest extends FormRequest
{
    use AuthorizesAppPermissions;

    public function authorize(): bool
    {
        return $this->appCan('users.store');
    }

    public function rules(): array
    {
        $employeeNumber = trim((string) $this->input('employee_number', ''));

        return [
            'username' => EmployeeCredentialRules::usernameRules($employeeNumber),
            'password' => EmployeeCredentialRules::passwordRules($employeeNumber),
            'password_confirmation' => ['required', 'string'],
            'employee_number' => ['nullable', 'string', 'max:40'],
            'badge_number' => ['nullable', 'string', 'max:40'],
            'division' => ['nullable', 'string', 'max:80'],
            'unit' => ['nullable', 'string', 'max:80'],
            'role' => ['required', Rule::in(AdminRoleGuard::assignableRoleNames(AppAuth::user($this)))],
            'responder_scopes' => ['nullable', 'array'],
            'responder_scopes.*' => ['string', Rule::in(AppUser::RESPONDER_SCOPE_OPTIONS)],
        ];
    }

    public function messages(): array
    {
        return EmployeeCredentialRules::validationMessages();
    }
}
