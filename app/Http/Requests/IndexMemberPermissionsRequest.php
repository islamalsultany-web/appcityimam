<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesAppPermissions;
use Illuminate\Foundation\Http\FormRequest;

class IndexMemberPermissionsRequest extends FormRequest
{
    use AuthorizesAppPermissions;

    public function authorize(): bool
    {
        return $this->appCan('permissions.members.view|permissions.members.edit');
    }

    public function rules(): array
    {
        return [
            'username' => ['nullable', 'string', 'max:60'],
            'employee_number' => ['nullable', 'string', 'max:40'],
        ];
    }
}
