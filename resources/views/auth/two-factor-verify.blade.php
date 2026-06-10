@extends('users.layout')

@section('title', 'التحقق الثنائي')
@section('page-title', 'أدخل رمز المصادقة الثنائية')

@section('topbar-actions')
    <form method="POST" action="{{ route('logout') }}" class="no-margin">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
    @if (session('warning'))
        <div class="alert alert-warn mb-12">
            {{ session('warning') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger-box">
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
