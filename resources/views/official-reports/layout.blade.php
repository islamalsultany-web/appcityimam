<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'برنامج التقارير الرسمية') — مدينة الإمام الحسين للزائرين</title>
    <link rel="stylesheet" href="{{ asset('css/cairo-font.css') }}">
    <link rel="stylesheet" href="{{ asset('css/report-program.css') }}">
    @stack('head')
</head>
<body>
<div class="program-shell">
    <aside class="program-sidebar">
        <a class="program-brand" href="{{ route('home') }}">العتبة الحسينية المقدسة<strong>مدينة الإمام الحسين<br>(عليه السلام) للزائرين</strong></a>
        <div class="program-label">برنامج التقارير الرسمية</div>
        <nav aria-label="التنقل"><a href="{{ route('official-reports.index') }}">▤ التقارير</a><a href="{{ route('official-reports.create') }}">＋ إنشاء تقرير</a><a href="{{ route('home') }}">← بوابة البرامج</a></nav>
        <div class="program-user">
            @if($reportUser = \App\Support\AppAuth::user(request()))
                {{ $reportUser->username }}<small>{{ \App\Support\AdminRoleGuard::isAdmin($reportUser) ? 'مدير النظام' : 'حساب المنتسب' }}</small>
                <form method="post" action="{{ route('logout') }}">@csrf<button type="submit">تسجيل الخروج</button></form>
            @else
                زائر<small>الدخول المباشر متاح</small>
                <p class="hint">تقاريرك مرتبطة بجلسة هذا المتصفح. احفظ نسخة PDF للاحتفاظ بها بعد انتهاء الجلسة أو مسح بيانات المتصفح.</p>
            @endif
        </div>
    </aside>
    <div class="program-main"><main>
        @if(session('status'))<p class="program-notice" role="status">{{ session('status') }}</p>@endif
        @yield('content')
    </main></div>
</div>
<script src="{{ asset('js/report-charts.js') }}" defer></script>
<script src="{{ asset('js/report-program.js') }}" defer></script>
@stack('scripts')
</body>
</html>
