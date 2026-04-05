@extends('users.layout')

@section('title', 'صفحة المستفسر')
@section('page-title', 'استفساراتي')

@section('header-actions')
    <a class="btn" href="{{ route('asker.inquiries.create') }}">إرسال استفسار جديد</a>
@endsection

@section('topbar-actions')
    <a class="btn" href="{{ route('user.info') }}">معلومات المستخدم</a>
    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
    @php($statusLabels = [
        'pending' => 'بانتظار الرد',
        'in_progress' => 'قيد المعالجة',
        'answered' => 'تمت الإجابة',
        'needs_info' => 'بحاجة معلومات إضافية',
        'closed' => 'مغلق',
    ])
    @php($priorityLabels = [
        'normal' => 'عادية',
        'urgent' => 'مستعجلة',
        'very_urgent' => 'عاجلة جدا',
    ])
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
            transition: transform 120ms ease, box-shadow 120ms ease;
        }

        .stat-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 18px rgba(16, 24, 40, 0.12);
        }

        .stat-card strong {
            font-size: 1.2rem;
        }

        .stat-all { background: linear-gradient(135deg, #e8f0ff, #f3f7ff); }
        .stat-pending { background: linear-gradient(135deg, #fff7da, #fffaf0); }
        .stat-progress { background: linear-gradient(135deg, #e6f6ff, #f1fbff); }
        .stat-needs { background: linear-gradient(135deg, #ffe9e9, #fff4f4); }
        .stat-answered { background: linear-gradient(135deg, #e9ffe8, #f4fff3); }
        .stat-closed { background: linear-gradient(135deg, #f1f1f1, #fafafa); }

        .stat-card.active {
            outline: 2px solid #0f56d4;
        }

        .search-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(180px, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }

        .search-grid .actions {
            grid-column: 1 / -1;
        }

        @media (max-width: 820px) {
            .search-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @php($searchParams = request()->only(['id', 'title', 'priority', 'type', 'date_from', 'date_to']))

    <p class="muted" style="margin-top: 0;">
        مرحبا {{ session('auth_app_username') }}، هنا تظهر كل استفساراتك وحالة الإجابة عليها.
    </p>

    <form method="GET" action="{{ route('dashboard.asker') }}" class="search-grid">
        @if ($statusFilter)
            <input type="hidden" name="status" value="{{ $statusFilter }}">
        @endif

        <div class="field">
            <label for="search_id">رقم الاستفسار</label>
            <input id="search_id" name="id" type="number" min="1" value="{{ $search['id'] ?? '' }}" placeholder="مثال: 15">
        </div>

        <div class="field">
            <label for="search_title">عنوان الاستفسار</label>
            <input id="search_title" name="title" type="text" value="{{ $search['title'] ?? '' }}" placeholder="ابحث في العنوان">
        </div>

        <div class="field">
            <label for="search_priority">الأولوية</label>
            <select id="search_priority" name="priority">
                <option value="">الكل</option>
                <option value="normal" @selected(($search['priority'] ?? null) === 'normal')>عادية</option>
                <option value="urgent" @selected(($search['priority'] ?? null) === 'urgent')>مستعجلة</option>
                <option value="very_urgent" @selected(($search['priority'] ?? null) === 'very_urgent')>عاجلة جدا</option>
            </select>
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

        <div class="field">
            <label for="search_date_from">من تاريخ</label>
            <input id="search_date_from" name="date_from" type="date" value="{{ $search['date_from'] ?? '' }}">
        </div>

        <div class="field">
            <label for="search_date_to">إلى تاريخ</label>
            <input id="search_date_to" name="date_to" type="date" value="{{ $search['date_to'] ?? '' }}">
        </div>

        <div class="actions">
            <button class="btn primary" type="submit">بحث</button>
            <a class="btn" href="{{ route('dashboard.asker', $statusFilter ? ['status' => $statusFilter] : []) }}">إعادة ضبط</a>
        </div>
    </form>

    <div class="stats-grid">
        <a class="stat-card stat-all {{ $statusFilter === null ? 'active' : '' }}" href="{{ route('dashboard.asker', $searchParams) }}">
            <span>كل استفساراتي</span>
            <strong>{{ $stats['all'] ?? 0 }}</strong>
        </a>
        <a class="stat-card stat-pending {{ $statusFilter === 'pending' ? 'active' : '' }}" href="{{ route('dashboard.asker', array_merge($searchParams, ['status' => 'pending'])) }}">
            <span>بانتظار الرد</span>
            <strong>{{ $stats['pending'] ?? 0 }}</strong>
        </a>
        <a class="stat-card stat-progress {{ $statusFilter === 'in_progress' ? 'active' : '' }}" href="{{ route('dashboard.asker', array_merge($searchParams, ['status' => 'in_progress'])) }}">
            <span>قيد المعالجة</span>
            <strong>{{ $stats['in_progress'] ?? 0 }}</strong>
        </a>
        <a class="stat-card stat-needs {{ $statusFilter === 'needs_info' ? 'active' : '' }}" href="{{ route('dashboard.asker', array_merge($searchParams, ['status' => 'needs_info'])) }}">
            <span>بحاجة معلومات</span>
            <strong>{{ $stats['needs_info'] ?? 0 }}</strong>
        </a>
        <a class="stat-card stat-answered {{ $statusFilter === 'answered' ? 'active' : '' }}" href="{{ route('dashboard.asker', array_merge($searchParams, ['status' => 'answered'])) }}">
            <span>تمت الإجابة</span>
            <strong>{{ $stats['answered'] ?? 0 }}</strong>
        </a>
        <a class="stat-card stat-closed {{ $statusFilter === 'closed' ? 'active' : '' }}" href="{{ route('dashboard.asker', array_merge($searchParams, ['status' => 'closed'])) }}">
            <span>مغلق</span>
            <strong>{{ $stats['closed'] ?? 0 }}</strong>
        </a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>عنوان الاستفسار</th>
                    <th>نوع الاستفسار</th>
                    <th>الأولوية</th>
                    <th>الحالة</th>
                    <th>تاريخ الإرسال</th>
                    <th>تاريخ الرد</th>
                    <th>ملخص الإجابة</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inquiries as $inquiry)
                    <tr>
                        <td>{{ $inquiry->id }}</td>
                        <td>{{ $inquiry->title }}</td>
                        <td>{{ $typeLabels[$inquiry->inquiry_type] ?? $inquiry->inquiry_type }}</td>
                        <td>{{ $priorityLabels[$inquiry->priority] ?? $inquiry->priority }}</td>
                        @php($displayStatus = $inquiry->review_status === 'pending_review' ? 'قيد التدقيق' : ($statusLabels[$inquiry->status] ?? $inquiry->status))
                        <td><span class="role-chip">{{ $displayStatus }}</span></td>
                        <td>{{ $inquiry->created_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $inquiry->responded_at?->format('Y-m-d H:i') ?? '-' }}</td>
                        <td>{{ $inquiry->publicResponseBody() ? \Illuminate\Support\Str::limit($inquiry->publicResponseBody(), 70) : $inquiry->publicResponsePlaceholder() }}</td>
                        <td>
                            <div class="actions">
                                <a class="btn" href="{{ route('asker.inquiries.view', $inquiry) }}">عرض</a>
                                <a class="btn" href="{{ route('asker.inquiries.print', $inquiry) }}" target="_blank">طباعة</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="muted">لا توجد استفسارات حتى الآن.</td>
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
