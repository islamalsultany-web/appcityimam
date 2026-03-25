<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المستخدمون</title>
</head>
<body>
    <h1>المستخدمون</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <p><a href="{{ route('users.create') }}">إنشاء مستخدم جديد</a></p>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>اسم المستخدم</th>
                <th>الرقم الوظيفي</th>
                <th>رقم الباج</th>
                <th>الشعبة</th>
                <th>الوحدة</th>
                <th>الدور</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->employee_number }}</td>
                    <td>{{ $user->badge_number }}</td>
                    <td>{{ $user->division }}</td>
                    <td>{{ $user->unit }}</td>
                    <td>{{ $user->role }}</td>
                    <td>
                        <a href="{{ route('users.show', $user) }}">عرض</a>
                        <a href="{{ route('users.edit', $user) }}">تعديل</a>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit">حذف</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">لا توجد بيانات</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $users->links() }}
</body>
</html>
