@extends('users.layout')

@section('title', 'لوحة المدقق')
@section('page-title', 'تدقيق إجابات المجيبين')

@section('topbar-actions')
    <a class="btn" href="{{ route('user.info') }}">معلومات المستخدم</a>
    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
    @php($reviewStatusLabels = \App\Models\Inquiry::REVIEW_STATUS_LABELS)
    @php($typeLabels = [
        'financial' => 'مالي',
        'administrative' => 'إداري',
        'technical' => 'تقني',
        'warehouse' => 'مخزني',
        'other' => 'أخرى',
    ])

    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(165px, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }

        .stat-card {
            border-radius: 14px;
            border: 1px solid var(--stroke);
            padding: 10px 12px;
            text-decoration: none;
            color: var(--ink);
            background: #fff;
            display: grid;
            gap: 5px;
            box-shadow: 0 6px 14px rgba(16, 24, 40, 0.08);
        }

        .stat-card strong { font-size: 1.2rem; }
        .stat-card.active { outline: 2px solid #0f56d4; }
        .stat-all { background: linear-gradient(135deg, #e8f0ff, #f3f7ff); }
        .stat-reviewing { background: linear-gradient(135deg, #fff0db, #fff8ee); }
        .stat-approved { background: linear-gradient(135deg, #e9ffe8, #f4fff3); }
        .stat-returned { background: linear-gradient(135deg, #ffe9e9, #fff4f4); }
        .search-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(160px, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }
        .search-grid .actions { grid-column: 1 / -1; }
        @media (max-width: 820px) { .search-grid { grid-template-columns: 1fr; } }
    </style>

    @php($searchParams = request()->only(['id', 'asker', 'title', 'type']))

    <p class="muted" style="margin-top: 0;">
        تظهر هنا إجابات المجيبين ليقوم المدقق باعتمادها أو إعادتها أو تعديلها قبل إرسالها إلى المستفسر.
    </p>

    <form method="GET" action="{{ route('dashboard.reviewer') }}" class="search-grid">
        @if ($reviewStatusFilter)
            <input type="hidden" name="review_status" value="{{ $reviewStatusFilter }}">
        @endif

        <div class="field">
            <label for="search_id">رقم الاستفسار</label>
            <input id="search_id" name="id" type="number" min="1" value="{{ $search['id'] ?? '' }}">
        </div>

        <div class="field">
            <label for="search_asker">اسم المستفسر</label>
            <input id="search_asker" name="asker" type="text" value="{{ $search['asker'] ?? '' }}">
        </div>

        <div class="field">
            <label for="search_title">عنوان الاستفسار</label>
            <input id="search_title" name="title" type="text" value="{{ $search['title'] ?? '' }}">
        </div>

        <div class="field">
            <label for="search_type">نوع الاستفسار</label>
            <select id="search_type" name="type">
                <option value="">الكل</option>
                <option value="financial" @selected(($search['type'] ?? null) === 'financial')>مالي</option>
                <option value="administrative" @selected(($search['type'] ?? null) === 'administrative')>إداري</option>
                <option value="technical" @selected(($search['type'] ?? null) === 'technical')>تقني</option>
                <option value="warehouse" @selected(($search['type'] ?? null) === 'warehouse')>مخزني</option>
                <option value="other" @selected(($search['type'] ?? null) === 'other')>أخرى</option>
            </select>
        </div>

        <div class="actions">
            <button class="btn primary" type="submit">بحث</button>
            <a class="btn" href="{{ route('dashboard.reviewer', $reviewStatusFilter ? ['review_status' => $reviewStatusFilter] : []) }}">إعادة ضبط</a>
        </div>
    </form>

    <div class="stats-grid">
        <a class="stat-card stat-all {{ $reviewStatusFilter === null ? 'active' : '' }}" href="{{ route('dashboard.reviewer', $searchParams) }}">
            <span>كل الإجابات</span>
            <strong>{{ $stats['all'] ?? 0 }}</strong>
        </a>
        <a class="stat-card stat-reviewing {{ $reviewStatusFilter === 'pending_review' ? 'active' : '' }}" href="{{ route('dashboard.reviewer', array_merge($searchParams, ['review_status' => 'pending_review'])) }}">
            <span>بانتظار التدقيق</span>
            <strong>{{ $stats['pending_review'] ?? 0 }}</strong>
        </a>
        <a class="stat-card stat-approved {{ $reviewStatusFilter === 'approved' ? 'active' : '' }}" href="{{ route('dashboard.reviewer', array_merge($searchParams, ['review_status' => 'approved'])) }}">
            <span>معتمد</span>
            <strong>{{ $stats['approved'] ?? 0 }}</strong>
        </a>
        <a class="stat-card stat-returned {{ $reviewStatusFilter === 'returned' ? 'active' : '' }}" href="{{ route('dashboard.reviewer', array_merge($searchParams, ['review_status' => 'returned'])) }}">
            <span>معاد للمجيب</span>
            <strong>{{ $stats['returned'] ?? 0 }}</strong>
        </a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>المستفسر</th>
                    <th>المجيب</th>
                    <th>العنوان</th>
                    <th>النوع</th>
                    <th>حالة التدقيق</th>
                    <th>تاريخ الرد</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inquiries as $inquiry)
                    <tr>
                        <td>{{ $inquiry->id }}</td>
                        <td>{{ $inquiry->asker?->username ?? '-' }}</td>
                        <td>{{ $inquiry->responder?->username ?? '-' }}</td>
                        <td>{{ $inquiry->title }}</td>
                        <td>{{ $typeLabels[$inquiry->inquiry_type] ?? $inquiry->inquiry_type }}</td>
                        <td><span class="role-chip {{ $inquiry->reviewStatusBadgeClass() }}">{{ $inquiry->reviewStatusLabel() }}</span></td>
                        <td>{{ $inquiry->responded_at?->format('Y-m-d H:i') ?? '-' }}</td>
                        <td>
                            <a class="btn primary" href="{{ route('reviewer.inquiries.show', $inquiry) }}">تدقيق</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="muted">لا توجد إجابات بانتظار التدقيق حالياً.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($inquiries->hasPages())
        <div class="pager">
            @if ($inquiries->onFirstPage())
                <span class="btn" aria-disabled="true">السابق</span>
            @else
                <a class="btn" href="{{ $inquiries->previousPageUrl() }}">السابق</a>
            @endif

            <span class="btn">صفحة {{ $inquiries->currentPage() }} من {{ $inquiries->lastPage() }}</span>

            @if ($inquiries->hasMorePages())
                <a class="btn" href="{{ $inquiries->nextPageUrl() }}">التالي</a>
            @else
                <span class="btn" aria-disabled="true">التالي</span>
            @endif
        </div>
    @endif
@endsection
