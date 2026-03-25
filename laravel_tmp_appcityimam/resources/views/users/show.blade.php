<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض مستخدم</title>
</head>
<body>
    <h1>عرض المستخدم</h1>
    <p><a href="{{ route('users.index') }}">رجوع</a></p>

    <ul>
        <li>اسم المستخدم: {{ $user->username }}</li>
        <li>الرقم الوظيفي: {{ $user->employee_number }}</li>
        <li>رقم الباج: {{ $user->badge_number }}</li>
        <li>الشعبة: {{ $user->division }}</li>
        <li>الوحدة: {{ $user->unit }}</li>
        <li>الدور: {{ $user->role }}</li>
    </ul>
</body>
</html>
