@extends('users.layout')

@section('title', 'صلاحيات المنتسبين')
@section('page-title', 'صلاحيات المنتسبين')

@section('header-actions')
    <a class="btn primary" href="{{ route('permissions.members.create') }}">إضافة صلاحية</a>
    <a class="btn" href="{{ route('dashboard.responder') }}">رجوع للوحة</a>
@endsection

@section('topbar-actions')
    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
    @php
        $roleLabels = collect(config('permissions.role_templates', []))
            ->mapWithKeys(fn ($config, $name) => [$name => $config['display_name'] ?? $name])
            ->all();
    @endphp

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم المستخدم</th>
                    <th>الدور الحالي</th>
                    <th>الأدوار</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $roleLabels[$user->role] ?? $user->role }}</td>
                        <td>{{ $user->roles->pluck('name')->map(fn ($name) => $roleLabels[$name] ?? $name)->join('، ') ?: '-' }}</td>
                        <td>
                            <a class="btn primary" href="{{ route('permissions.members.edit', $user) }}">تعديل الصلاحيات</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted">لا توجد بيانات منتسبين.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <div class="pager">
            @if ($users->onFirstPage())
                <span class="btn" aria-disabled="true">السابق</span>
            @else
                <a class="btn" href="{{ $users->previousPageUrl() }}">السابق</a>
            @endif

            <span class="btn">صفحة {{ $users->currentPage() }} من {{ $users->lastPage() }}</span>

            @if ($users->hasMorePages())
                <a class="btn" href="{{ $users->nextPageUrl() }}">التالي</a>
            @else
                <span class="btn" aria-disabled="true">التالي</span>
            @endif
        </div>
    @endif
@endsection
