<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\Inquiry;
use App\Support\AppAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InquiryAttachmentController extends Controller
{
    public function download(Request $request, Inquiry $inquiry, string $field): StreamedResponse
    {
        $pathColumn = match ($field) {
            'attachment' => 'attachment_path',
            'response' => 'response_attachment_path',
            default => null,
        };

        if ($pathColumn === null) {
            abort(404);
        }

        $path = $inquiry->{$pathColumn};

        if (! is_string($path) || $path === '') {
            abort(404);
        }

        $this->authorizeDownload($request, $inquiry, $field);

        $disk = (string) config('inquiry.attachment.disk', 'local');

        if (! Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        return Storage::disk($disk)->download($path, basename($path));
    }

    private function authorizeDownload(Request $request, Inquiry $inquiry, string $field): void
    {
        $user = AppAuth::user($request);

        if (! $user) {
            abort(403);
        }

        if ($user->role === 'admin' || $user->hasRole('admin')) {
            return;
        }

        if ($field === 'attachment') {
            if (
                ($user->role === 'asker' || $user->hasRole('asker'))
                && (int) $inquiry->asker_user_id === (int) $user->id
            ) {
                return;
            }

            if ($this->canAccessAsResponder($user, $inquiry)) {
                return;
            }

            if ($this->canAccessAsReviewer($user)) {
                return;
            }

            abort(403);
        }

        if ($field === 'response') {
            if ($this->canAccessAsResponder($user, $inquiry)) {
                return;
            }

            if ($this->canAccessAsReviewer($user)) {
                return;
            }

            if (
                ($user->role === 'asker' || $user->hasRole('asker'))
                && (int) $inquiry->asker_user_id === (int) $user->id
                && $inquiry->isResponseApproved()
            ) {
                return;
            }

            abort(403);
        }

        abort(403);
    }

    private function canAccessAsResponder(AppUser $user, Inquiry $inquiry): bool
    {
        if (! in_array($user->role, ['responder', 'admin'], true) && ! $user->hasAnyRole(['responder', 'admin'])) {
            if (! $user->can('inquiries.responder.view') && ! $user->can('inquiries.responder.manage')) {
                return false;
            }
        }

        return $user->canHandleInquiryType($inquiry->inquiry_type);
    }

    private function canAccessAsReviewer(AppUser $user): bool
    {
        return in_array($user->role, ['reviewer', 'admin'], true)
            || $user->hasAnyRole(['reviewer', 'admin'])
            || $user->can('inquiries.reviewer.view')
            || $user->can('inquiries.reviewer.manage');
    }
}
