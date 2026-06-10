@extends('users.layout')

@section('title', 'تفعيل المصادقة الثنائية')
@section('page-title', 'تفعيل المصادقة الثنائية (2FA) — مطلوب لمدير النظام')

@section('topbar-actions')
    <form method="POST" action="{{ route('logout') }}" class="no-margin">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
    <div class="alert alert-warn">
        <strong>حماية إضافية لحساب المدير</strong>
        <p>امسح الرمز أو أدخل المفتاح يدوياً في تطبيق Google Authenticator أو Microsoft Authenticator، ثم أدخل رمز التحقق للتأكيد.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger-box">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="list-grid mb-14">
        <div class="field full">
            <label>مفتاح التطبيق (يدوياً)</label>
            <input value="{{ $secret }}" readonly>
        </div>
        <div class="field full">
            <label>رابط الإعداد السريع</label>
            <input value="{{ $provisioningUri }}" readonly>
        </div>
        <div class="field">
            <label>الحساب</label>
            <input value="{{ $issuer }}:{{ $user->username }}" readonly>
        </div>
    </div>

    <form method="POST" action="{{ route('user.two-factor.confirm') }}" class="form-grid">
        @csrf
        <div class="field">
            <label for="code">رمز التحقق (6 أرقام)</label>
            <input id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus>
        </div>
        <div class="field full">
            <div class="actions">
                <button class="btn primary" type="submit">تأكيد وتفعيل 2FA</button>
            </div>
        </div>
    </form>
@endsection
