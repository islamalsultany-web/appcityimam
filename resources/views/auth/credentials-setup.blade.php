@extends('users.layout')

@section('title', 'تحديث بيانات الدخول')
@section('page-title', 'تحديث بيانات الدخول — مطلوب')

@section('topbar-actions')
    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
    <div class="alert" style="background:#fef3c7;border:1px solid #f59e0b;color:#92400e;padding:14px 16px;border-radius:12px;margin-bottom:16px;line-height:1.7;">
        <strong>تنبيه أمني مهم</strong>
        <p style="margin:8px 0 0;">
            {{ \App\Support\EmployeeCredentialSecurity::warningMessage() }}
        </p>
        <p style="margin:8px 0 0;font-size:0.92rem;">
            لن تتمكن من استخدام النظام (لوحتك وصلاحياتك) حتى تكمل التحديث أدناه.
        </p>
    </div>

    @if (session('warning'))
        <div class="alert" style="background:#fee2e2;border:1px solid #ef4444;color:#991b1b;padding:12px;border-radius:10px;margin-bottom:12px;">
            {{ session('warning') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert" style="background:#fee2e2;border:1px solid #ef4444;color:#991b1b;padding:12px;border-radius:10px;margin-bottom:12px;">
            <ul style="margin:0;padding-right:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="list-grid" style="margin-bottom:14px;">
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
            <label for="username">اسم المستخدم الجديد <span style="color:#b91c1c;">*</span></label>
            <input id="username" name="username" value="{{ old('username') }}" required autofocus
                   placeholder="مثال: ahmed.karbala (لا تستخدم الرقم الوظيفي)">
        </div>

        <div class="field">
            <label for="password">كلمة المرور الجديدة <span style="color:#b91c1c;">*</span></label>
            <input id="password" name="password" type="password" required
                   placeholder="8 أحرف على الأقل، حروف وأرقام">
        </div>

        <div class="field">
            <label for="password_confirmation">تأكيد كلمة المرور <span style="color:#b91c1c;">*</span></label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>
        </div>

        <div class="field full">
            <div class="actions">
                <button class="btn primary" type="submit">حفظ والمتابعة إلى النظام</button>
            </div>
        </div>
    </form>
@endsection
