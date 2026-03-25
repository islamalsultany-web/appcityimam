<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل مستخدم</title>
</head>
<body>
    <h1>تعديل مستخدم</h1>
    <p><a href="{{ route('users.index') }}">رجوع</a></p>

    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PUT')
        @include('users.partials.form', ['user' => $user])
        <button type="submit">تحديث</button>
    </form>
</body>
</html>
