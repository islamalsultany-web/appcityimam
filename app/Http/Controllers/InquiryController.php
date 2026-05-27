<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResponderAnswerRequest;
use App\Http\Requests\ReviewerReviewRequest;
use App\Http\Requests\StoreAskerInquiryRequest;
use App\Models\AppUser;
use App\Models\Inquiry;
use App\Support\AppAuth;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class InquiryController extends Controller
{
    private const STATUS_ORDER_SQL = "CASE WHEN status = 'pending' THEN 0 WHEN status = 'in_progress' THEN 1 WHEN status = 'needs_info' THEN 2 WHEN status = 'answered' THEN 3 ELSE 4 END";

    private const ALLOWED_STATUS_FILTERS = ['pending', 'in_progress', 'needs_info', 'answered', 'closed'];

    private const ALLOWED_INQUIRY_TYPES = ['financial', 'administrative', 'technical', 'warehouse', 'other'];

    private const ALLOWED_REVIEW_FILTERS = ['pending_review', 'approved', 'returned'];

    public function storeFromAsker(StoreAskerInquiryRequest $request): RedirectResponse
    {
        $this->ensureAsker($request);

        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $this->storeUploadedAttachment(
                $request->file('attachment'),
                'inquiries'
            );
        }

        unset($data['attachment']);

        $data['asker_user_id'] = AppAuth::id($request) ?? 0;
        $data['status'] = 'pending';

        Inquiry::create($data);
        AuditLogger::security($request, 'inquiries.asker.store', [
            'inquiry_type' => $data['inquiry_type'] ?? null,
        ]);

        return redirect()->route('dashboard.asker')->with('success', 'تم إرسال الاستفسار بنجاح.');
    }

    public function askerCreate(Request $request): View
    {
        $this->ensureAsker($request);

        return view('dashboards.asker-create');
    }

    public function askerIndex(Request $request): View
    {
        $this->ensureAsker($request);
        $this->validateOptionalDateFilters($request);

        $askerId = AppAuth::id($request) ?? 0;

        $statusFilter = $request->query('status');
        if (! is_string($statusFilter) || ! in_array($statusFilter, self::ALLOWED_STATUS_FILTERS, true)) {
            $statusFilter = null;
        }

        $priorityFilter = $request->query('priority');
        if (! is_string($priorityFilter) || ! in_array($priorityFilter, ['normal', 'urgent', 'very_urgent'], true)) {
            $priorityFilter = null;
        }

        $typeFilter = $request->query('type');
        if (! is_string($typeFilter) || ! in_array($typeFilter, self::ALLOWED_INQUIRY_TYPES, true)) {
            $typeFilter = null;
        }

        $search = [
            'id' => $request->integer('id') ?: null,
            'title' => trim((string) $request->query('title', '')),
            'priority' => $priorityFilter,
            'type' => $typeFilter,
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];

        $inquiries = Inquiry::query()
            ->where('asker_user_id', $askerId)
            ->when($statusFilter, fn (Builder $q) => $this->applyStatusFilter($q, $statusFilter))
            ->when($search['id'], fn (Builder $q) => $q->where('id', $search['id']))
            ->when($search['title'] !== '', fn (Builder $q) => $q->where('title', 'like', '%' . $search['title'] . '%'))
            ->when($search['priority'], fn (Builder $q) => $q->where('priority', $search['priority']))
            ->when($search['type'], fn (Builder $q) => $q->where('inquiry_type', $search['type']))
            ->when($search['date_from'] !== '', fn (Builder $q) => $q->whereDate('created_at', '>=', $search['date_from']))
            ->when($search['date_to'] !== '', fn (Builder $q) => $q->whereDate('created_at', '<=', $search['date_to']))
            ->orderByRaw(self::STATUS_ORDER_SQL)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'all' => Inquiry::query()->where('asker_user_id', $askerId)->count(),
            'pending' => Inquiry::query()->where('asker_user_id', $askerId)->where('status', 'pending')->count(),
            'in_progress' => Inquiry::query()->where('asker_user_id', $askerId)->where('status', 'in_progress')->count(),
            'needs_info' => Inquiry::query()->where('asker_user_id', $askerId)->where('status', 'needs_info')->count(),
            'answered' => $this->applyAnsweredFilter(Inquiry::query()->where('asker_user_id', $askerId))->count(),
            'closed' => Inquiry::query()->where('asker_user_id', $askerId)->where('status', 'closed')->count(),
        ];

        return view('dashboards.asker', compact('inquiries', 'stats', 'statusFilter', 'search'));
    }

    public function askerView(Request $request, Inquiry $inquiry): View
    {
        $this->ensureAsker($request);

        if ((int) $inquiry->asker_user_id !== (int) (AppAuth::id($request) ?? 0)) {
            abort(403);
        }

        $inquiry->load('responder:id,username', 'reviewer:id,username');

        return view('dashboards.asker-view', compact('inquiry'));
    }

    public function askerPrint(Request $request, Inquiry $inquiry): View
    {
        $this->ensureAsker($request);

        if ((int) $inquiry->asker_user_id !== (int) (AppAuth::id($request) ?? 0)) {
            abort(403);
        }

        $inquiry->load('responder:id,username', 'reviewer:id,username');

        return view('dashboards.asker-print', compact('inquiry'));
    }

    public function responderIndex(Request $request): View
    {
        $authUser = $this->ensureResponder($request);
        $this->validateOptionalDateFilters($request);

        $statusFilter = $request->query('status');
        if (! is_string($statusFilter) || ! in_array($statusFilter, self::ALLOWED_STATUS_FILTERS, true)) {
            $statusFilter = null;
        }

        $priorityFilter = $request->query('priority');
        if (! is_string($priorityFilter) || ! in_array($priorityFilter, ['normal', 'urgent', 'very_urgent'], true)) {
            $priorityFilter = null;
        }

        $typeFilter = $request->query('type');
        if (! is_string($typeFilter) || ! in_array($typeFilter, self::ALLOWED_INQUIRY_TYPES, true)) {
            $typeFilter = null;
        }

        $search = [
            'id' => $request->integer('id') ?: null,
            'asker' => trim((string) $request->query('asker', '')),
            'title' => trim((string) $request->query('title', '')),
            'priority' => $priorityFilter,
            'type' => $typeFilter,
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];

        $inquiries = $this->filterInquiryQueryForResponder(Inquiry::query(), $authUser)
            ->with('asker:id,username')
            ->when($statusFilter, fn (Builder $q) => $this->applyStatusFilter($q, $statusFilter))
            ->when($search['id'], fn (Builder $q) => $q->where('id', $search['id']))
            ->when($search['asker'] !== '', fn (Builder $q) => $q->whereHas('asker', fn (Builder $askerQ) => $askerQ->where('username', 'like', '%' . $search['asker'] . '%')))
            ->when($search['title'] !== '', fn (Builder $q) => $q->where('title', 'like', '%' . $search['title'] . '%'))
            ->when($search['priority'], fn (Builder $q) => $q->where('priority', $search['priority']))
            ->when($search['type'], fn (Builder $q) => $q->where('inquiry_type', $search['type']))
            ->when($search['date_from'] !== '', fn (Builder $q) => $q->whereDate('created_at', '>=', $search['date_from']))
            ->when($search['date_to'] !== '', fn (Builder $q) => $q->whereDate('created_at', '<=', $search['date_to']))
            ->orderByRaw(self::STATUS_ORDER_SQL)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'all' => $this->filterInquiryQueryForResponder(Inquiry::query(), $authUser)->count(),
            'pending' => $this->filterInquiryQueryForResponder(Inquiry::query(), $authUser)->where('status', 'pending')->count(),
            'in_progress' => $this->filterInquiryQueryForResponder(Inquiry::query(), $authUser)->where('status', 'in_progress')->count(),
            'needs_info' => $this->filterInquiryQueryForResponder(Inquiry::query(), $authUser)->where('status', 'needs_info')->count(),
            'answered' => $this->applyAnsweredFilter($this->filterInquiryQueryForResponder(Inquiry::query(), $authUser))->count(),
            'closed' => $this->filterInquiryQueryForResponder(Inquiry::query(), $authUser)->where('status', 'closed')->count(),
            'deleted' => $this->filterInquiryQueryForResponder(Inquiry::onlyTrashed(), $authUser)->count(),
        ];

        return view('dashboards.responder', compact('inquiries', 'stats', 'statusFilter', 'search'));
    }

    public function responderShow(Request $request, Inquiry $inquiry): View
    {
        $authUser = $this->ensureResponder($request);
        $this->ensureResponderCanAccessInquiry($authUser, $inquiry);

        $inquiry->load('asker:id,username', 'reviewer:id,username');

        return view('dashboards.responder-answer', compact('inquiry'));
    }

    public function responderView(Request $request, Inquiry $inquiry): View
    {
        $authUser = $this->ensureResponder($request);
        $this->ensureResponderCanAccessInquiry($authUser, $inquiry);

        $inquiry->load('asker:id,username', 'reviewer:id,username');

        return view('dashboards.responder-view', compact('inquiry'));
    }

    public function responderPrint(Request $request, Inquiry $inquiry): View
    {
        $authUser = $this->ensureResponder($request);
        $this->ensureResponderCanAccessInquiry($authUser, $inquiry);

        $inquiry->load('asker:id,username', 'responder:id,username', 'reviewer:id,username');

        return view('dashboards.responder-print', compact('inquiry'));
    }

    public function responderAnswer(ResponderAnswerRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $authUser = $this->ensureResponder($request);
        $this->ensureResponderCanAccessInquiry($authUser, $inquiry);

        $validated = $request->validated();
        $data = collect($validated)->only([
            'status',
            'priority',
            'response_type',
            'follow_up_date',
            'response_body',
            'internal_note',
        ])->all();

        if ($request->hasFile('response_attachment')) {
            $data['response_attachment_path'] = $this->storeUploadedAttachment(
                $request->file('response_attachment'),
                'responses'
            );
        }

        $data['responder_user_id'] = AppAuth::id($request) ?? 0;
        $data['responded_at'] = now();
        $data['review_status'] = 'pending_review';
        $data['review_note'] = null;
        $data['reviewed_by_user_id'] = null;
        $data['reviewed_at'] = null;

        $inquiry->update($data);
        AuditLogger::security($request, 'inquiries.responder.answer', [
            'inquiry_id' => $inquiry->id,
            'status' => $data['status'] ?? null,
            'review_status' => $data['review_status'] ?? null,
        ], targetType: Inquiry::class, targetId: $inquiry->id);

        return redirect()->route('responder.inquiries.show', $inquiry)->with('success', 'تم حفظ الإجابة وإرسالها إلى المدقق بنجاح.');
    }

    public function responderDestroy(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $authUser = $this->ensureResponder($request);
        $this->ensureResponderCanAccessInquiry($authUser, $inquiry);

        $inquiry->delete();
        AuditLogger::security($request, 'inquiries.responder.delete', [
            'inquiry_id' => $inquiry->id,
        ], targetType: Inquiry::class, targetId: $inquiry->id);

        return redirect()->route('dashboard.responder')->with('success', 'تم حذف الاستفسار.');
    }

    public function responderDeleted(Request $request): View
    {
        $authUser = $this->ensureResponder($request);

        $inquiries = $this->filterInquiryQueryForResponder(Inquiry::onlyTrashed(), $authUser)
            ->with('asker:id,username')
            ->latest('deleted_at')
            ->paginate(15);

        return view('dashboards.responder-deleted', compact('inquiries'));
    }

    public function responderRestore(Request $request, int $inquiryId): RedirectResponse
    {
        $authUser = $this->ensureResponder($request);

        $inquiry = Inquiry::onlyTrashed()->findOrFail($inquiryId);
        $this->ensureResponderCanAccessInquiry($authUser, $inquiry);
        $inquiry->restore();
        AuditLogger::security($request, 'inquiries.responder.restore', [
            'inquiry_id' => $inquiry->id,
        ], targetType: Inquiry::class, targetId: $inquiry->id);

        return redirect()->route('responder.inquiries.deleted')->with('success', 'تمت استعادة الاستفسار بنجاح.');
    }

    public function responderPrintReport(Request $request): View
    {
        $authUser = $this->ensureResponder($request);
        $this->validateOptionalDateFilters($request);

        $statusFilter = $request->query('status');
        if (! is_string($statusFilter) || ! in_array($statusFilter, self::ALLOWED_STATUS_FILTERS, true)) {
            $statusFilter = null;
        }

        $priorityFilter = $request->query('priority');
        if (! is_string($priorityFilter) || ! in_array($priorityFilter, ['normal', 'urgent', 'very_urgent'], true)) {
            $priorityFilter = null;
        }

        $typeFilter = $request->query('type');
        if (! is_string($typeFilter) || ! in_array($typeFilter, self::ALLOWED_INQUIRY_TYPES, true)) {
            $typeFilter = null;
        }

        $search = [
            'id' => $request->integer('id') ?: null,
            'asker' => trim((string) $request->query('asker', '')),
            'title' => trim((string) $request->query('title', '')),
            'priority' => $priorityFilter,
            'type' => $typeFilter,
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];

        $inquiries = $this->filterInquiryQueryForResponder(Inquiry::query(), $authUser)
            ->with('asker:id,username')
            ->when($statusFilter, fn (Builder $q) => $this->applyStatusFilter($q, $statusFilter))
            ->when($search['id'], fn (Builder $q) => $q->where('id', $search['id']))
            ->when($search['asker'] !== '', fn (Builder $q) => $q->whereHas('asker', fn (Builder $askerQ) => $askerQ->where('username', 'like', '%' . $search['asker'] . '%')))
            ->when($search['title'] !== '', fn (Builder $q) => $q->where('title', 'like', '%' . $search['title'] . '%'))
            ->when($search['priority'], fn (Builder $q) => $q->where('priority', $search['priority']))
            ->when($search['type'], fn (Builder $q) => $q->where('inquiry_type', $search['type']))
            ->when($search['date_from'] !== '', fn (Builder $q) => $q->whereDate('created_at', '>=', $search['date_from']))
            ->when($search['date_to'] !== '', fn (Builder $q) => $q->whereDate('created_at', '<=', $search['date_to']))
            ->orderByRaw(self::STATUS_ORDER_SQL)
            ->latest()
            ->get();

        return view('dashboards.responder-report-print', compact('inquiries', 'statusFilter'));
    }

    public function reviewerIndex(Request $request): View
    {
        $authUser = $this->ensureReviewer($request);

        $reviewStatusFilter = $request->query('review_status');
        if (! is_string($reviewStatusFilter) || ! in_array($reviewStatusFilter, self::ALLOWED_REVIEW_FILTERS, true)) {
            $reviewStatusFilter = null;
        }

        $typeFilter = $request->query('type');
        if (! is_string($typeFilter) || ! in_array($typeFilter, self::ALLOWED_INQUIRY_TYPES, true)) {
            $typeFilter = null;
        }

        $search = [
            'id' => $request->integer('id') ?: null,
            'asker' => trim((string) $request->query('asker', '')),
            'title' => trim((string) $request->query('title', '')),
            'type' => $typeFilter,
        ];

        $baseQuery = $this->filterInquiryQueryForReviewer(
            Inquiry::query(),
            $authUser
        )
            ->whereNotNull('response_body')
            ->where('response_body', '!=', '');

        $inquiries = (clone $baseQuery)
            ->with('asker:id,username', 'responder:id,username', 'reviewer:id,username')
            ->when($reviewStatusFilter, fn (Builder $q) => $q->where('review_status', $reviewStatusFilter))
            ->when($search['id'], fn (Builder $q) => $q->where('id', $search['id']))
            ->when($search['asker'] !== '', fn (Builder $q) => $q->whereHas('asker', fn (Builder $askerQ) => $askerQ->where('username', 'like', '%' . $search['asker'] . '%')))
            ->when($search['title'] !== '', fn (Builder $q) => $q->where('title', 'like', '%' . $search['title'] . '%'))
            ->when($search['type'], fn (Builder $q) => $q->where('inquiry_type', $search['type']))
            ->orderByRaw("CASE WHEN review_status = 'pending_review' THEN 0 WHEN review_status = 'returned' THEN 1 WHEN review_status = 'approved' THEN 2 ELSE 3 END")
            ->latest('responded_at')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'all' => (clone $baseQuery)->count(),
            'pending_review' => (clone $baseQuery)->where('review_status', 'pending_review')->count(),
            'approved' => (clone $baseQuery)->where('review_status', 'approved')->count(),
            'returned' => (clone $baseQuery)->where('review_status', 'returned')->count(),
        ];

        return view('dashboards.reviewer', compact('inquiries', 'stats', 'reviewStatusFilter', 'search'));
    }

    public function reviewerShow(Request $request, Inquiry $inquiry): View
    {
        $authUser = $this->ensureReviewer($request);
        $this->ensureReviewerCanAccessInquiry($authUser, $inquiry);

        if (! filled($inquiry->response_body)) {
            abort(404);
        }

        $inquiry->load('asker:id,username', 'responder:id,username', 'reviewer:id,username');

        return view('dashboards.reviewer-review', compact('inquiry'));
    }

    public function reviewerReview(ReviewerReviewRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $authUser = $this->ensureReviewer($request);
        $this->ensureReviewerCanAccessInquiry($authUser, $inquiry);

        $validated = $request->validated();
        $reviewAction = $validated['review_action'];

        $data = collect($validated)->only([
            'status',
            'priority',
            'response_type',
            'follow_up_date',
            'response_body',
            'internal_note',
            'review_note',
        ])->all();

        $data['status'] = $data['status'] ?? ($reviewAction === 'return' ? 'needs_info' : ($inquiry->status ?: 'answered'));
        $data['priority'] = $data['priority'] ?? ($inquiry->priority ?: 'normal');

        if ($reviewAction === 'return' && $data['status'] === 'answered') {
            $data['status'] = 'needs_info';
        }

        $data['review_status'] = $reviewAction === 'approve' ? 'approved' : 'returned';
        $data['reviewed_by_user_id'] = $authUser->id;
        $data['reviewed_at'] = now();
        $data['review_note'] = trim((string) ($data['review_note'] ?? '')) !== ''
            ? trim((string) $data['review_note'])
            : null;

        $inquiry->update($data);
        AuditLogger::security($request, 'inquiries.reviewer.review', [
            'inquiry_id' => $inquiry->id,
            'review_action' => $reviewAction,
            'review_status' => $data['review_status'] ?? null,
        ], targetType: Inquiry::class, targetId: $inquiry->id);

        return redirect()
            ->route('dashboard.reviewer')
            ->with('success', $reviewAction === 'approve' ? 'تم اعتماد الإجابة وإتاحتها للمستفسر.' : 'تمت إعادة الإجابة إلى المجيب لإجراء التعديل.');
    }

    private function ensureResponder(Request $request): AppUser
    {
        $authUser = AppAuth::user($request);

        if (! $authUser) {
            abort(403);
        }

        if (! in_array($authUser->role, ['admin', 'responder'], true)
            && ! $authUser->hasAnyRole(['admin', 'responder'])
            && ! $authUser->can('inquiries.responder.view')) {
            abort(403);
        }

        return $authUser;
    }

    private function ensureReviewer(Request $request): AppUser
    {
        $authUser = AppAuth::user($request);

        if (! $authUser) {
            abort(403);
        }

        if (! in_array($authUser->role, ['admin', 'reviewer'], true)
            && ! $authUser->hasAnyRole(['admin', 'reviewer'])
            && ! $authUser->can('inquiries.reviewer.view')) {
            abort(403);
        }

        return $authUser;
    }

    private function filterInquiryQueryForResponder(Builder $query, AppUser $authUser): Builder
    {
        $scopes = $authUser->normalizedResponderScopes();

        if ($scopes === [] || in_array('all', $scopes, true) || $authUser->role === 'admin' || $authUser->hasRole('admin')) {
            return $query;
        }

        return $query->whereIn('inquiry_type', $scopes);
    }

    private function ensureResponderCanAccessInquiry(AppUser $authUser, Inquiry $inquiry): void
    {
        if (! $authUser->canHandleInquiryType($inquiry->inquiry_type)) {
            abort(403);
        }
    }

    private function filterInquiryQueryForReviewer(Builder $query, AppUser $authUser): Builder
    {
        if (
            ! (bool) config('inquiry.reviewer_isolation.enabled', true)
            || $authUser->role === 'admin'
            || $authUser->hasRole('admin')
        ) {
            return $query;
        }

        if ((bool) config('inquiry.reviewer_isolation.by_type', true)) {
            $scopes = $authUser->normalizedResponderScopes();

            if ($scopes !== [] && ! in_array('all', $scopes, true)) {
                $query->whereIn('inquiry_type', $scopes);
            }
        }

        if ((bool) config('inquiry.reviewer_isolation.by_division', true)) {
            $division = trim((string) $authUser->division);

            if ($division !== '') {
                $query->whereHas('asker', fn (Builder $askerQ) => $askerQ->where('division', $division));
            }
        }

        return $query;
    }

    private function ensureReviewerCanAccessInquiry(AppUser $authUser, Inquiry $inquiry): void
    {
        $allowed = $this->filterInquiryQueryForReviewer(
            Inquiry::query()->whereKey($inquiry->id),
            $authUser
        )->exists();

        if (! $allowed) {
            abort(403);
        }
    }

    private function applyStatusFilter(Builder $query, string $statusFilter): Builder
    {
        if ($statusFilter === 'answered') {
            return $this->applyAnsweredFilter($query);
        }

        return $query->where('status', $statusFilter);
    }

    private function applyAnsweredFilter(Builder $query): Builder
    {
        return $query
            ->whereNotNull('response_body')
            ->where('response_body', '!=', '')
            ->where('review_status', 'approved');
    }

    private function ensureAsker(Request $request): void
    {
        $authUser = AppAuth::user($request);

        if (! $authUser) {
            abort(403);
        }

        if (! in_array($authUser->role, ['admin', 'asker'], true)
            && ! $authUser->hasAnyRole(['admin', 'asker'])
            && ! $authUser->can('inquiries.asker.view')) {
            abort(403);
        }
    }

    private function validateOptionalDateFilters(Request $request): void
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);
    }

    private function storeUploadedAttachment(UploadedFile $file, string $directory): string
    {
        $disk = (string) config('inquiry.attachment.disk', 'local');

        return $file->store($directory, $disk);
    }
}
