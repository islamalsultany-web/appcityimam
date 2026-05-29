@extends('users.layout')

@section('title', 'سجل التدقيق الأمني')
@section('page-title', 'سجل التدقيق الأمني')

@section('topbar-actions')
    <a class="btn" href="{{ route('home') }}">الرئيسية</a>
    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
    <p class="muted" style="margin-top: 0;">
        هذه الصفحة متاحة فقط للسوبر أدمن، وتعرض العمليات الحساسة داخل النظام.
    </p>

    <form method="GET" action="{{ route('security.audit-logs') }}" class="form-grid" style="margin-bottom: 14px;">
        <div class="field">
            <label for="actor">المستخدم المنفذ</label>
            <input id="actor" name="actor" value="{{ $filters['actor'] ?? '' }}">
        </div>

        <div class="field">
            <label for="action">الإجراء</label>
            <input id="action" name="action" value="{{ $filters['action'] ?? '' }}" placeholder="مثال: تسجيل دخول أو auth.login">
        </div>

        <div class="field">
            <label for="target">الهدف</label>
            <input id="target" name="target" value="{{ $filters['target'] ?? '' }}" placeholder="مثال: المستخدم أو الاستفسار">
        </div>

        <div class="field">
            <label for="ip">عنوان IP</label>
            <input id="ip" name="ip" value="{{ $filters['ip'] ?? '' }}">
        </div>

        <div class="field">
            <label for="date_from">من تاريخ</label>
            <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}">
        </div>

        <div class="field">
            <label for="date_to">إلى تاريخ</label>
            <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}">
        </div>

        <div class="field full">
            <div class="actions">
                <button type="submit" class="btn primary">بحث</button>
                <a href="{{ route('security.audit-logs') }}" class="btn">إعادة ضبط</a>
            </div>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>الوقت</th>
                <th>المنفذ</th>
                <th>الإجراء</th>
                <th>الهدف</th>
                <th>IP</th>
                <th>الوصف</th>
            </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>
                        @if ($log->created_at)
                            @php($baghdadAt = $log->created_at->timezone('Asia/Baghdad'))
                            {{ $baghdadAt->format('Y-m-d h:i') }} {{ $baghdadAt->format('A') === 'AM' ? 'ص' : 'م' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $log->actor_username ?? '-' }}</td>
                    <td>
                        <span class="role-chip status-neutral">
                            {{ $actionLabels[$log->action] ?? $log->action }}
                        </span>
                    </td>
                    <td>
                        @if ($log->target_type)
                            {{ $targetTypeLabels[$log->target_type] ?? class_basename($log->target_type) }}#{{ $log->target_id ?? '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $log->ip_address ?? '-' }}</td>
                    <td style="white-space: normal; max-width: 420px;">{{ \App\Support\AuditLogger::displayDescription($log) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="muted">لا توجد سجلات مطابقة.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pager">
        {{ $logs->links() }}
    </div>
@endsection
