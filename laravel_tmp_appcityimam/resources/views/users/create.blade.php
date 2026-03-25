<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء مستخدم</title>
</head>
<body>
    <h1>إنشاء مستخدم</h1>
    <p><a href="{{ route('users.index') }}">رجوع</a></p>

    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        @include('users.partials.form', ['user' => null])
        <button type="submit">حفظ</button>
    </form>
</body>
</html>
