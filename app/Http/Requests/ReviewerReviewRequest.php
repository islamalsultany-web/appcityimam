<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesAppPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewerReviewRequest extends FormRequest
{
    use AuthorizesAppPermissions;

    public function authorize(): bool
    {
        $user = \App\Support\AppAuth::user($this);

        if (! $user) {
            return false;
        }

        if (in_array($user->role, ['admin', 'reviewer'], true) || $user->hasAnyRole(['admin', 'reviewer'])) {
            return true;
        }

        return $this->appCan('inquiries.reviewer.review');
    }

    public function rules(): array
    {
        return [
            'review_action' => ['required', Rule::in(['approve', 'return'])],
            'status' => ['nullable', Rule::in(['in_progress', 'answered', 'needs_info', 'closed'])],
            'priority' => ['nullable', Rule::in(['normal', 'urgent', 'very_urgent'])],
            'response_type' => ['nullable', Rule::in(['final', 'partial', 'request_info'])],
            'follow_up_date' => ['nullable', 'date'],
            'response_body' => ['required', 'string', 'max:10000'],
            'internal_note' => ['nullable', 'string', 'max:5000'],
            'review_note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
