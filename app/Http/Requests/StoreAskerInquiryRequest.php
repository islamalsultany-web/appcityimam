<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesAppPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAskerInquiryRequest extends FormRequest
{
    use AuthorizesAppPermissions;

    private const INQUIRY_TYPES = ['financial', 'administrative', 'technical', 'warehouse', 'other'];

    public function authorize(): bool
    {
        $user = \App\Support\AppAuth::user($this);

        if (! $user) {
            return false;
        }

        if (in_array($user->role, ['admin', 'asker'], true) || $user->hasAnyRole(['admin', 'asker'])) {
            return true;
        }

        return $this->appCan('inquiries.asker.create');
    }

    public function rules(): array
    {
        $maxKb = (int) config('inquiry.attachment.max_kilobytes', 5120);
        $mimes = (string) config('inquiry.attachment.mimes', 'pdf,jpg,jpeg,png,doc,docx,xls,xlsx');

        return [
            'title' => ['required', 'string', 'max:255'],
            'inquiry_type' => ['required', Rule::in(self::INQUIRY_TYPES)],
            'priority' => ['required', Rule::in(['normal', 'urgent', 'very_urgent'])],
            'body' => ['required', 'string', 'max:10000'],
            'attachment' => ['nullable', 'file', 'max:' . $maxKb, 'mimes:' . $mimes],
        ];
    }
}
