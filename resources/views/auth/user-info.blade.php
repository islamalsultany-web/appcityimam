@extends('users.layout')

@section('title', 'معلومات المستخدم')
@section('page-title', 'معلومات المستخدم')

@section('header-actions')
    <a class="btn" href="{{ route('dashboard.asker') }}">رجوع للوحة المستفسر</a>
@endsection

@section('topbar-actions')
    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
    <div class="list-grid" style="margin-bottom: 14px;">
        <div class="field">
            <label>اسم المستخدم</label>
            <input value="{{ $user->username }}" disabled>
        </div>
        <div class="field">
            <label>الدور</label>
            <input value="{{ $user->role }}" disabled>
        </div>
        <div class="field">
            <label>الرقم الوظيفي</label>
            <input value="{{ $user->employee_number ?: '-' }}" disabled>
        </div>
        <div class="field">
            <label>رقم الباج</label>
            <input value="{{ $user->badge_number ?: '-' }}" disabled>
        </div>
    </div>

    <form method="POST" action="{{ route('user.password.update') }}" class="form-grid">
        @csrf

        <div class="field">
            <label for="current_password">كلمة المرور الحالية</label>
            <input id="current_password" name="current_password" type="password" required>
        </div>

        <div class="field">
            <label for="password">كلمة المرور الجديدة</label>
            <input id="password" name="password" type="password" required>
        </div>

        <div class="field">
            <label for="password_confirmation">تأكيد كلمة المرور الجديدة</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>
        </div>

        <div class="field full">
            <div class="actions">
                <button class="btn primary" type="submit">حفظ كلمة المرور</button>
            </div>
        </div>
    </form>
@endsection
