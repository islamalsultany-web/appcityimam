<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الاستفسارات</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; margin: 20px; color: #111; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: right; }
        th { background: #f4f4f4; }
        .print-btn { margin-bottom: 12px; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    @php($statusLabels = [
        'pending' => 'بانتظار الرد',
        'in_progress' => 'قيد المعالجة',
        'answered' => 'تمت الإجابة',
        'needs_info' => 'بحاجة معلومات إضافية',
        'closed' => 'مغلق',
    ])
    @php($priorityLabels = [
        'normal' => 'عادية',
        'urgent' => 'مستعجلة',
        'very_urgent' => 'عاجلة جدا',
    ])
    @php($typeLabels = [
        'financial' => 'مالي',
        'administrative' => 'إداري',
        'technical' => 'تقني',
        'warehouse' => 'مخزني',
        'other' => 'أخرى',
    ])

    <button class="print-btn" onclick="window.print()">طباعة</button>
    <h2>تقرير الاستفسارات {{ $statusFilter ? '(' . ($statusLabels[$statusFilter] ?? $statusFilter) . ')' : '(كل الحالات)' }}</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>المستفسر</th>
                <th>العنوان</th>
                <th>نوع الاستفسار</th>
                <th>الأولوية</th>
                <th>الحالة</th>
                <th>تاريخ الإرسال</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($inquiries as $inquiry)
                <tr>
                    <td>{{ $inquiry->id }}</td>
                    <td>{{ $inquiry->asker?->username ?? '-' }}</td>
                    <td>{{ $inquiry->title }}</td>
                    <td>{{ $typeLabels[$inquiry->inquiry_type] ?? $inquiry->inquiry_type }}</td>
                    <td>{{ $priorityLabels[$inquiry->priority] ?? $inquiry->priority }}</td>
                    <td>{{ $statusLabels[$inquiry->status] ?? $inquiry->status }}</td>
                    <td>{{ $inquiry->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">لا توجد بيانات للتقرير.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>
