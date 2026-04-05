@extends('users.layout')

@section('title', 'المستخدمون')
@section('page-title', 'إدارة المستخدمين')

@section('header-actions')
    <form action="{{ route('users.destroyAll') }}" method="POST" onsubmit="return confirm('تأكيد حذف جميع المستخدمين؟ هذا الإجراء لا يمكن التراجع عنه.');" style="margin:0;">
        @csrf
        @method('DELETE')
        <button class="btn warn" type="submit">حذف الكل</button>
    </form>
    <a class="btn" href="{{ route('users.excel') }}">استيراد وتصدير من اكسل</a>
    <a class="btn primary" href="{{ route('users.create') }}">+ إنشاء مستخدم جديد</a>
@endsection

@section('content')
    <form method="GET" action="{{ route('users.index') }}" class="form-grid" style="margin-bottom: 14px;">
        <div class="field">
            <label for="username">اسم المستخدم</label>
            <input id="username" name="username" value="{{ request('username') }}" placeholder="ابحث باسم المستخدم">
        </div>

        <div class="field">
            <label for="employee_number">الرقم الوظيفي</label>
            <input id="employee_number" name="employee_number" value="{{ request('employee_number') }}" placeholder="ابحث بالرقم الوظيفي">
        </div>

        <div class="field">
            <label for="badge_number">رقم الباج</label>
            <input id="badge_number" name="badge_number" value="{{ request('badge_number') }}" placeholder="ابحث برقم الباج">
        </div>

        <div class="field">
            <label for="division">الشعبة</label>
            <input id="division" name="division" value="{{ request('division') }}" placeholder="ابحث بالشعبة">
        </div>

        <div class="field">
            <label for="unit">الوحدة</label>
            <input id="unit" name="unit" value="{{ request('unit') }}" placeholder="ابحث بالوحدة">
        </div>

        <div class="field">
            <label for="role">الدور</label>
            <select id="role" name="role">
                <option value="">الكل</option>
                <option value="asker" @selected(request('role') === 'asker')>مستفسر</option>
                <option value="responder" @selected(request('role') === 'responder')>مجيب</option>
                <option value="reviewer" @selected(request('role') === 'reviewer')>مدقق</option>
                <option value="admin" @selected(request('role') === 'admin')>مدير</option>
            </select>
        </div>

        <div class="field full">
            <div class="actions">
                <button class="btn primary" type="submit">بحث</button>
                <a class="btn" href="{{ route('users.index') }}">مسح البحث</a>
            </div>
        </div>
    </form>

    <div class="muted" style="margin-bottom:10px;">عدد المستخدمين في هذه الصفحة: {{ $users->count() }}</div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>اسم المستخدم</th>
                    <th>الرقم الوظيفي</th>
                    <th>رقم الباج</th>
                    <th>الشعبة</th>
                    <th>الوحدة</th>
                    <th>الدور</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->employee_number ?: '-' }}</td>
                        <td>{{ $user->badge_number ?: '-' }}</td>
                        <td>{{ $user->division ?: '-' }}</td>
                        <td>{{ $user->unit ?: '-' }}</td>
                        <td><span class="role-chip">{{ \App\Models\AppUser::ROLE_LABELS[$user->role] ?? $user->role }}</span></td>
                        <td>
                            <div class="actions">
                                <a class="btn" href="{{ route('users.show', $user) }}">عرض</a>
                                <a class="btn" href="{{ route('users.edit', $user) }}">تعديل</a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('تأكيد حذف المستخدم؟');" style="margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn warn" type="submit">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="muted">لا توجد بيانات مستخدمين حتى الآن.</td>
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
