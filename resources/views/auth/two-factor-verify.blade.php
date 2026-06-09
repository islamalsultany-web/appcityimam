@extends('users.layout')

@section('title', 'التحقق الثنائي')
@section('page-title', 'أدخل رمز المصادقة الثنائية')

@section('topbar-actions')
    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
    @if (session('warning'))
        <div class="alert" style="background:#fef3c7;border:1px solid #f59e0b;color:#92400e;padding:12px;border-radius:10px;margin-bottom:12px;">
            {{ session('warning') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert" style="background:#fee2e2;border:1px solid #ef4444;color:#991b1b;padding:12px;border-radius:10px;margin-bottom:12px;">
            {{ $errors->first() }}
        </div>
    @endif

    <p class="muted">مرحباً {{ $user->username }} — أدخل الرمز الحالي من تطبيق المصادقة.</p>

    <form method="POST" action="{{ route('user.two-factor.verify.submit') }}" class="form-grid">
        @csrf
        <div class="field">
            <label for="code">رمز التحقق (6 أرقام)</label>
            <input id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus>
        </div>
        <div class="field full">
            <div class="actions">
                <button class="btn primary" type="submit">تحقق ومتابعة</button>
            </div>
        </div>
    </form>
@endsection
