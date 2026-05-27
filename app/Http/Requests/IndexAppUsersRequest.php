<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesAppPermissions;
use App\Models\AppUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAppUsersRequest extends FormRequest
{
    use AuthorizesAppPermissions;

    public function authorize(): bool
    {
        return $this->appCan('users.index|users.view');
    }

    public function rules(): array
    {
        return [
            'username' => ['nullable', 'string', 'max:60'],
            'employee_number' => ['nullable', 'string', 'max:40'],
            'badge_number' => ['nullable', 'string', 'max:40'],
            'division' => ['nullable', 'string', 'max:80'],
            'unit' => ['nullable', 'string', 'max:80'],
            'role' => ['nullable', Rule::in(AppUser::ROLE_OPTIONS)],
        ];
    }
}
