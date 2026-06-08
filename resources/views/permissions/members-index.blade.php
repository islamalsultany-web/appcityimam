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
        $scopeLabels = \App\Models\AppUser::RESPONDER_SCOPE_LABELS;
    @endphp

    <form method="GET" action="{{ route('permissions.members.index') }}" class="form-grid" style="margin-bottom: 14px;">
        <div class="field">
            <label for="username">اسم المستخدم</label>
            <input id="username" name="username" value="{{ $filters['username'] ?? '' }}" placeholder="ابحث باسم المستخدم">
        </div>

        <div class="field">
            <label for="employee_number">الرقم الوظيفي</label>
            <input id="employee_number" name="employee_number" value="{{ $filters['employee_number'] ?? '' }}" placeholder="ابحث بالرقم الوظيفي">
        </div>

        <div class="field full">
            <div class="actions">
                <button class="btn primary" type="submit">بحث</button>
                <a class="btn" href="{{ route('permissions.members.index') }}">مسح البحث</a>
            </div>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم المستخدم</th>
                    <th>الرقم الوظيفي</th>
                    <th>الدور الحالي</th>
                    <th>اختصاصات المجيب</th>
                    <th>الأدوار</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->employee_number ?: '-' }}</td>
                        <td>{{ $roleLabels[$user->role] ?? $user->role }}</td>
                        <td>
                            @if (in_array($user->role, ['responder', 'admin'], true))
                                {{ collect($user->normalizedResponderScopes())->map(fn ($scope) => $scopeLabels[$scope] ?? $scope)->join('، ') ?: '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $user->roles->pluck('name')->map(fn ($name) => $roleLabels[$name] ?? $name)->join('، ') ?: '-' }}</td>
                        <td>
                            <a class="btn primary" href="{{ route('permissions.members.edit', $user) }}">تعديل الصلاحيات</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="muted">لا توجد بيانات مطابقة.</td>
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
