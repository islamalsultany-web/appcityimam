<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\AppUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InquiryController extends Controller
{
    private const STATUS_ORDER_SQL = "CASE WHEN status = 'pending' THEN 0 WHEN status = 'in_progress' THEN 1 WHEN status = 'needs_info' THEN 2 WHEN status = 'answered' THEN 3 ELSE 4 END";

    private const ALLOWED_STATUS_FILTERS = ['pending', 'in_progress', 'needs_info', 'answered', 'closed'];

    public function storeFromAsker(Request $request): RedirectResponse
    {
        $this->ensureAsker($request);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:normal,urgent,very_urgent'],
            'preferred_channel' => ['required', 'in:system,phone,email'],
            'body' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('inquiries', 'public');
        }

        $data['asker_user_id'] = (int) $request->session()->get('auth_app_user_id');
        $data['status'] = 'pending';

        Inquiry::create($data);

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

        $askerId = (int) $request->session()->get('auth_app_user_id');

        $statusFilter = $request->query('status');
        if (! is_string($statusFilter) || ! in_array($statusFilter, self::ALLOWED_STATUS_FILTERS, true)) {
            $statusFilter = null;
        }

        $priorityFilter = $request->query('priority');
        if (! is_string($priorityFilter) || ! in_array($priorityFilter, ['normal', 'urgent', 'very_urgent'], true)) {
            $priorityFilter = null;
        }

        $search = [
            'id' => $request->integer('id') ?: null,
            'title' => trim((string) $request->query('title', '')),
            'priority' => $priorityFilter,
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];

        $inquiries = Inquiry::query()
            ->where('asker_user_id', $askerId)
            ->when($statusFilter, fn (Builder $q) => $this->applyStatusFilter($q, $statusFilter))
            ->when($search['id'], fn (Builder $q) => $q->where('id', $search['id']))
            ->when($search['title'] !== '', fn (Builder $q) => $q->where('title', 'like', '%' . $search['title'] . '%'))
            ->when($search['priority'], fn (Builder $q) => $q->where('priority', $search['priority']))
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

        if ((int) $inquiry->asker_user_id !== (int) $request->session()->get('auth_app_user_id')) {
            abort(403);
        }

        $inquiry->load('responder:id,username');

        return view('dashboards.asker-view', compact('inquiry'));
    }

    public function askerPrint(Request $request, Inquiry $inquiry): View
    {
        $this->ensureAsker($request);

        if ((int) $inquiry->asker_user_id !== (int) $request->session()->get('auth_app_user_id')) {
            abort(403);
        }

        $inquiry->load('responder:id,username');

        return view('dashboards.asker-print', compact('inquiry'));
    }

    public function responderIndex(Request $request): View
    {
        $this->ensureResponder($request);

        $statusFilter = $request->query('status');
        if (! is_string($statusFilter) || ! in_array($statusFilter, self::ALLOWED_STATUS_FILTERS, true)) {
            $statusFilter = null;
        }

        $priorityFilter = $request->query('priority');
        if (! is_string($priorityFilter) || ! in_array($priorityFilter, ['normal', 'urgent', 'very_urgent'], true)) {
            $priorityFilter = null;
        }

        $search = [
            'id' => $request->integer('id') ?: null,
            'asker' => trim((string) $request->query('asker', '')),
            'title' => trim((string) $request->query('title', '')),
            'priority' => $priorityFilter,
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];

        $inquiries = Inquiry::query()
            ->with('asker:id,username')
            ->when($statusFilter, fn (Builder $q) => $this->applyStatusFilter($q, $statusFilter))
            ->when($search['id'], fn (Builder $q) => $q->where('id', $search['id']))
            ->when($search['asker'] !== '', fn (Builder $q) => $q->whereHas('asker', fn (Builder $askerQ) => $askerQ->where('username', 'like', '%' . $search['asker'] . '%')))
            ->when($search['title'] !== '', fn (Builder $q) => $q->where('title', 'like', '%' . $search['title'] . '%'))
            ->when($search['priority'], fn (Builder $q) => $q->where('priority', $search['priority']))
            ->when($search['date_from'] !== '', fn (Builder $q) => $q->whereDate('created_at', '>=', $search['date_from']))
            ->when($search['date_to'] !== '', fn (Builder $q) => $q->whereDate('created_at', '<=', $search['date_to']))
            ->orderByRaw(self::STATUS_ORDER_SQL)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'all' => Inquiry::query()->count(),
            'pending' => Inquiry::query()->where('status', 'pending')->count(),
            'in_progress' => Inquiry::query()->where('status', 'in_progress')->count(),
            'needs_info' => Inquiry::query()->where('status', 'needs_info')->count(),
            'answered' => $this->applyAnsweredFilter(Inquiry::query())->count(),
            'closed' => Inquiry::query()->where('status', 'closed')->count(),
            'deleted' => Inquiry::onlyTrashed()->count(),
        ];

        return view('dashboards.responder', compact('inquiries', 'stats', 'statusFilter', 'search'));
    }

    public function responderShow(Request $request, Inquiry $inquiry): View
    {
        $this->ensureResponder($request);

        $inquiry->load('asker:id,username');

        return view('dashboards.responder-answer', compact('inquiry'));
    }

    public function responderView(Request $request, Inquiry $inquiry): View
    {
        $this->ensureResponder($request);

        $inquiry->load('asker:id,username');

        return view('dashboards.responder-view', compact('inquiry'));
    }

    public function responderPrint(Request $request, Inquiry $inquiry): View
    {
        $this->ensureResponder($request);

        $inquiry->load('asker:id,username', 'responder:id,username');

        return view('dashboards.responder-print', compact('inquiry'));
    }

    public function responderAnswer(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $this->ensureResponder($request);

        $data = $request->validate([
            'status' => ['required', 'in:in_progress,answered,needs_info,closed'],
            'priority' => ['required', 'in:normal,urgent,very_urgent'],
            'response_type' => ['required', 'in:final,partial,request_info'],
            'follow_up_date' => ['nullable', 'date'],
            'response_body' => ['required', 'string'],
            'internal_note' => ['nullable', 'string'],
            'response_attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        if ($request->hasFile('response_attachment')) {
            $data['response_attachment_path'] = $request->file('response_attachment')->store('responses', 'public');
        }

        $data['responder_user_id'] = (int) $request->session()->get('auth_app_user_id');
        $data['responded_at'] = now();

        $inquiry->update($data);

        return redirect()->route('responder.inquiries.show', $inquiry)->with('success', 'تم حفظ الإجابة بنجاح.');
    }

    public function responderDestroy(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $this->ensureResponder($request);

        $inquiry->delete();

        return redirect()->route('dashboard.responder')->with('success', 'تم حذف الاستفسار.');
    }

    public function responderDeleted(Request $request): View
    {
        $this->ensureResponder($request);

        $inquiries = Inquiry::onlyTrashed()
            ->with('asker:id,username')
            ->latest('deleted_at')
            ->paginate(15);

        return view('dashboards.responder-deleted', compact('inquiries'));
    }

    public function responderRestore(Request $request, int $inquiryId): RedirectResponse
    {
        $this->ensureResponder($request);

        $inquiry = Inquiry::onlyTrashed()->findOrFail($inquiryId);
        $inquiry->restore();

        return redirect()->route('responder.inquiries.deleted')->with('success', 'تمت استعادة الاستفسار بنجاح.');
    }

    public function responderPrintReport(Request $request): View
    {
        $this->ensureResponder($request);

        $statusFilter = $request->query('status');
        if (! is_string($statusFilter) || ! in_array($statusFilter, self::ALLOWED_STATUS_FILTERS, true)) {
            $statusFilter = null;
        }

        $priorityFilter = $request->query('priority');
        if (! is_string($priorityFilter) || ! in_array($priorityFilter, ['normal', 'urgent', 'very_urgent'], true)) {
            $priorityFilter = null;
        }

        $search = [
            'id' => $request->integer('id') ?: null,
            'asker' => trim((string) $request->query('asker', '')),
            'title' => trim((string) $request->query('title', '')),
            'priority' => $priorityFilter,
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];

        $inquiries = Inquiry::query()
            ->with('asker:id,username')
            ->when($statusFilter, fn (Builder $q) => $this->applyStatusFilter($q, $statusFilter))
            ->when($search['id'], fn (Builder $q) => $q->where('id', $search['id']))
            ->when($search['asker'] !== '', fn (Builder $q) => $q->whereHas('asker', fn (Builder $askerQ) => $askerQ->where('username', 'like', '%' . $search['asker'] . '%')))
            ->when($search['title'] !== '', fn (Builder $q) => $q->where('title', 'like', '%' . $search['title'] . '%'))
            ->when($search['priority'], fn (Builder $q) => $q->where('priority', $search['priority']))
            ->when($search['date_from'] !== '', fn (Builder $q) => $q->whereDate('created_at', '>=', $search['date_from']))
            ->when($search['date_to'] !== '', fn (Builder $q) => $q->whereDate('created_at', '<=', $search['date_to']))
            ->orderByRaw(self::STATUS_ORDER_SQL)
            ->latest()
            ->get();

        return view('dashboards.responder-report-print', compact('inquiries', 'statusFilter'));
    }

    private function ensureResponder(Request $request): void
    {
        $authUserId = (int) $request->session()->get('auth_app_user_id');
        $authUser = AppUser::query()->find($authUserId);

        if (! $authUser) {
            abort(403);
        }

        if (! $authUser->hasAnyRole(['admin', 'responder']) && ! $authUser->can('inquiries.responder.view')) {
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
            ->where('response_body', '!=', '');
    }

    private function ensureAsker(Request $request): void
    {
        $authUserId = (int) $request->session()->get('auth_app_user_id');
        $authUser = AppUser::query()->find($authUserId);

        if (! $authUser) {
            abort(403);
        }

        if (! $authUser->hasAnyRole(['admin', 'asker']) && ! $authUser->can('inquiries.asker.view')) {
            abort(403);
        }
    }
}
