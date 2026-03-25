@extends('users.layout')

@section('title', 'صفحة المجيب')
@section('page-title', 'الاستفسارات الواردة')

@section('header-actions')
    <a class="btn" href="{{ route('responder.inquiries.deleted') }}">المحذوف مؤخرا</a>
    <a class="btn" href="{{ route('responder.inquiries.report.print', request()->only(['status', 'id', 'asker', 'title', 'priority', 'date_from', 'date_to'])) }}" target="_blank">طباعة تقرير</a>
    <button class="btn" id="toggleColumnsBtn" type="button">تحديد أعمدة</button>
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
        .stat-deleted { background: linear-gradient(135deg, #ffe1f1, #fff2f8); }

        .stat-card.active {
            outline: 2px solid #0f56d4;
        }

        .column-picker {
            display: none;
            border: 1px solid var(--stroke);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.85);
            padding: 10px 12px;
            margin-bottom: 12px;
        }

        .column-picker.open {
            display: block;
        }

        .column-picker label {
            margin-inline-end: 14px;
            font-size: 0.9rem;
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

    @php($searchParams = request()->only(['id', 'asker', 'title', 'priority', 'date_from', 'date_to']))

    <p class="muted" style="margin-top: 0;">
        هذه الصفحة تعرض جميع الاستفسارات المرسلة من المستفسرين. اضغط "إجابة" لفتح صفحة الرد.
    </p>

    <form method="GET" action="{{ route('dashboard.responder') }}" class="search-grid">
        @if ($statusFilter)
            <input type="hidden" name="status" value="{{ $statusFilter }}">
        @endif

        <div class="field">
            <label for="search_id">رقم الاستفسار</label>
            <input id="search_id" name="id" type="number" min="1" value="{{ $search['id'] ?? '' }}" placeholder="مثال: 15">
        </div>

        <div class="field">
            <label for="search_asker">اسم المستفسر</label>
            <input id="search_asker" name="asker" type="text" value="{{ $search['asker'] ?? '' }}" placeholder="ابحث بالاسم">
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
            <label for="search_date_from">من تاريخ</label>
            <input id="search_date_from" name="date_from" type="date" value="{{ $search['date_from'] ?? '' }}">
        </div>

        <div class="field">
            <label for="search_date_to">إلى تاريخ</label>
            <input id="search_date_to" name="date_to" type="date" value="{{ $search['date_to'] ?? '' }}">
        </div>

        <div class="actions">
            <button class="btn primary" type="submit">بحث</button>
            <a class="btn" href="{{ route('dashboard.responder', $statusFilter ? ['status' => $statusFilter] : []) }}">إعادة ضبط</a>
        </div>
    </form>

    <div class="stats-grid">
        <a class="stat-card stat-all {{ $statusFilter === null ? 'active' : '' }}" href="{{ route('dashboard.responder', $searchParams) }}">
            <span>كل الاستفسارات</span>
            <strong>{{ $stats['all'] ?? 0 }}</strong>
        </a>
        <a class="stat-card stat-pending {{ $statusFilter === 'pending' ? 'active' : '' }}" href="{{ route('dashboard.responder', array_merge($searchParams, ['status' => 'pending'])) }}">
            <span>بانتظار الرد</span>
            <strong>{{ $stats['pending'] ?? 0 }}</strong>
        </a>
        <a class="stat-card stat-progress {{ $statusFilter === 'in_progress' ? 'active' : '' }}" href="{{ route('dashboard.responder', array_merge($searchParams, ['status' => 'in_progress'])) }}">
            <span>قيد المعالجة</span>
            <strong>{{ $stats['in_progress'] ?? 0 }}</strong>
        </a>
        <a class="stat-card stat-needs {{ $statusFilter === 'needs_info' ? 'active' : '' }}" href="{{ route('dashboard.responder', array_merge($searchParams, ['status' => 'needs_info'])) }}">
            <span>بحاجة معلومات</span>
            <strong>{{ $stats['needs_info'] ?? 0 }}</strong>
        </a>
        <a class="stat-card stat-answered {{ $statusFilter === 'answered' ? 'active' : '' }}" href="{{ route('dashboard.responder', array_merge($searchParams, ['status' => 'answered'])) }}">
            <span>تمت الإجابة</span>
            <strong>{{ $stats['answered'] ?? 0 }}</strong>
        </a>
        <a class="stat-card stat-closed {{ $statusFilter === 'closed' ? 'active' : '' }}" href="{{ route('dashboard.responder', array_merge($searchParams, ['status' => 'closed'])) }}">
            <span>مغلق</span>
            <strong>{{ $stats['closed'] ?? 0 }}</strong>
        </a>
        <a class="stat-card stat-deleted" href="{{ route('responder.inquiries.deleted') }}">
            <span>محذوف مؤخرا</span>
            <strong>{{ $stats['deleted'] ?? 0 }}</strong>
        </a>
    </div>

    <div id="columnsPanel" class="column-picker" aria-live="polite">
        <strong style="display:block; margin-bottom:8px;">تحديد الأعمدة الظاهرة</strong>
        <label><input type="checkbox" data-col="col-id" checked> رقم</label>
        <label><input type="checkbox" data-col="col-asker" checked> المستفسر</label>
        <label><input type="checkbox" data-col="col-title" checked> عنوان الاستفسار</label>
        <label><input type="checkbox" data-col="col-priority" checked> الأولوية</label>
        <label><input type="checkbox" data-col="col-status" checked> الحالة</label>
        <label><input type="checkbox" data-col="col-date" checked> تاريخ الإرسال</label>
        <label><input type="checkbox" data-col="col-actions" checked> إجراء</label>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th class="col-id">#</th>
                    <th class="col-asker">المستفسر</th>
                    <th class="col-title">عنوان الاستفسار</th>
                    <th class="col-priority">الأولوية</th>
                    <th class="col-status">الحالة</th>
                    <th class="col-date">تاريخ الإرسال</th>
                    <th class="col-actions">إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inquiries as $inquiry)
                    <tr>
                        <td class="col-id">{{ $inquiry->id }}</td>
                        <td class="col-asker">{{ $inquiry->asker?->username ?? '-' }}</td>
                        <td class="col-title">{{ $inquiry->title }}</td>
                        <td class="col-priority">{{ $priorityLabels[$inquiry->priority] ?? $inquiry->priority }}</td>
                        <td class="col-status"><span class="role-chip">{{ $statusLabels[$inquiry->status] ?? $inquiry->status }}</span></td>
                        <td class="col-date">{{ $inquiry->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="col-actions">
                            <div class="actions">
                                <a class="btn primary" href="{{ route('responder.inquiries.show', $inquiry) }}">إجابة</a>
                                <a class="btn" href="{{ route('responder.inquiries.view', $inquiry) }}">عرض</a>
                                <a class="btn" href="{{ route('responder.inquiries.print', $inquiry) }}" target="_blank">طباعة</a>
                                <a class="btn primary" href="{{ route('responder.inquiries.show', $inquiry) }}">تعديل</a>
                                <form method="POST" action="{{ route('responder.inquiries.destroy', $inquiry) }}" style="margin:0;" onsubmit="return confirm('تأكيد حذف الاستفسار؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn warn" type="submit">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="muted">لا توجد استفسارات حالية.</td>
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

    <script>
        (function () {
            var toggleBtn = document.getElementById('toggleColumnsBtn');
            var panel = document.getElementById('columnsPanel');
            if (!toggleBtn || !panel) {
                return;
            }

            toggleBtn.addEventListener('click', function () {
                panel.classList.toggle('open');
            });

            panel.querySelectorAll('input[type="checkbox"][data-col]').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    var colClass = checkbox.getAttribute('data-col');
                    if (!colClass) {
                        return;
                    }

                    document.querySelectorAll('.' + colClass).forEach(function (cell) {
                        cell.style.display = checkbox.checked ? '' : 'none';
                    });
                });
            });
        })();
    </script>
@endsection
