@extends('users.layout')

@section('title', 'تفعيل المصادقة الثنائية')
@section('page-title', 'تفعيل المصادقة الثنائية (2FA) — مطلوب لمدير النظام')

@section('topbar-actions')
    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
    <div class="alert" style="background:#fef3c7;border:1px solid #f59e0b;color:#92400e;padding:14px 16px;border-radius:12px;margin-bottom:16px;line-height:1.7;">
        <strong>حماية إضافية لحساب المدير</strong>
        <p style="margin:8px 0 0;">امسح الرمز أو أدخل المفتاح يدوياً في تطبيق Google Authenticator أو Microsoft Authenticator، ثم أدخل رمز التحقق للتأكيد.</p>
    </div>

    @if ($errors->any())
        <div class="alert" style="background:#fee2e2;border:1px solid #ef4444;color:#991b1b;padding:12px;border-radius:10px;margin-bottom:12px;">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="list-grid" style="margin-bottom:14px;">
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
