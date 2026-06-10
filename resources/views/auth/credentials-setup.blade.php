@extends('users.layout')

@section('title', 'تحديث بيانات الدخول')
@section('page-title', 'تحديث بيانات الدخول — مطلوب')

@section('topbar-actions')
    <form method="POST" action="{{ route('logout') }}" class="no-margin">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
    <div class="alert alert-warn">
        <strong>تنبيه أمني مهم</strong>
        <p>
            {{ \App\Support\EmployeeCredentialSecurity::warningMessage() }}
        </p>
        <p class="note">
            لن تتمكن من استخدام النظام (لوحتك وصلاحياتك) حتى تكمل التحديث أدناه.
        </p>
    </div>

    @if (session('warning'))
        <div class="alert alert-danger-box">
            {{ session('warning') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger-box">
            <ul class="error-list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="list-grid mb-14">
        <div class="field">
            <label>الرقم الوظيفي (للمرجع فقط)</label>
            <input value="{{ $user->employee_number ?: '-' }}" disabled>
        </div>
        <div class="field">
            <label>اسم المستخدم الحالي</label>
            <input value="{{ $user->username }}" disabled>
        </div>
    </div>

    <form method="POST" action="{{ route('user.credentials.update') }}" class="form-grid">
        @csrf

        <div class="field full">
            <label for="username">اسم المستخدم الجديد <span class="text-required">*</span></label>
            <input id="username" name="username" value="{{ old('username') }}" required autofocus
                   placeholder="مثال: ahmed.karbala (لا تستخدم الرقم الوظيفي)">
        </div>

        <div class="field">
            <label for="password">كلمة المرور الجديدة <span class="text-required">*</span></label>
            <input id="password" name="password" type="password" required
                   placeholder="8 أحرف على الأقل، حروف وأرقام">
        </div>

        <div class="field">
            <label for="password_confirmation">تأكيد كلمة المرور <span class="text-required">*</span></label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>
        </div>

        <div class="field full">
            <div class="actions">
                <button class="btn primary" type="submit">حفظ والمتابعة إلى النظام</button>
            </div>
        </div>
    </form>
@endsection
