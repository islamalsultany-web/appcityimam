<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesAppPermissions;
use App\Rules\SecureAttachment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResponderAnswerRequest extends FormRequest
{
    use AuthorizesAppPermissions;

    public function authorize(): bool
    {
        $user = \App\Support\AppAuth::user($this);

        if (! $user) {
            return false;
        }

        if (in_array($user->role, ['admin', 'responder'], true) || $user->hasAnyRole(['admin', 'responder'])) {
            return true;
        }

        return $this->appCan('inquiries.responder.answer');
    }

    public function rules(): array
    {
        $maxKb = (int) config('inquiry.attachment.max_kilobytes', 5120);
        $mimes = (string) config('inquiry.attachment.mimes', 'pdf,jpg,jpeg,png,doc,docx,xls,xlsx');

        return [
            'status' => ['required', Rule::in(['in_progress', 'answered', 'needs_info', 'closed'])],
            'priority' => ['required', Rule::in(['normal', 'urgent', 'very_urgent'])],
            'response_type' => ['required', Rule::in(['final', 'partial', 'request_info'])],
            'follow_up_date' => ['nullable', 'date'],
            'response_body' => ['required', 'string', 'max:10000'],
            'internal_note' => ['nullable', 'string', 'max:5000'],
            'response_attachment' => ['nullable', 'file', 'max:' . $maxKb, new SecureAttachment($mimes)],
        ];
    }
}
