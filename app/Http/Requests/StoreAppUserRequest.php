<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesAppPermissions;
use App\Models\AppUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreAppUserRequest extends FormRequest
{
    use AuthorizesAppPermissions;

    public function authorize(): bool
    {
        return $this->appCan('users.store');
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:60', 'unique:app_users,username'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'password_confirmation' => ['required', 'string'],
            'employee_number' => ['nullable', 'string', 'max:40'],
            'badge_number' => ['nullable', 'string', 'max:40'],
            'division' => ['nullable', 'string', 'max:80'],
            'unit' => ['nullable', 'string', 'max:80'],
            'role' => ['required', Rule::in(AppUser::ROLE_OPTIONS)],
            'responder_scopes' => ['nullable', 'array'],
            'responder_scopes.*' => ['string', Rule::in(AppUser::RESPONDER_SCOPE_OPTIONS)],
        ];
    }
}
