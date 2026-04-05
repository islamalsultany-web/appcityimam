<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة استفساري #{{ $inquiry->id }}</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; margin: 24px; color: #111; }
        h1 { margin: 0 0 16px; }
        .meta { display: grid; grid-template-columns: repeat(2, minmax(220px, 1fr)); gap: 10px; margin-bottom: 16px; }
        .box { border: 1px solid #ccc; border-radius: 8px; padding: 10px; }
        .full { grid-column: 1 / -1; }
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
    <h1>تقرير الاستفسار #{{ $inquiry->id }}</h1>

    <div class="meta">
        <div class="box"><strong>الحالة:</strong> {{ $inquiry->review_status === 'pending_review' ? 'قيد التدقيق' : ($statusLabels[$inquiry->status] ?? $inquiry->status) }}</div>
        <div class="box"><strong>الأولوية:</strong> {{ $priorityLabels[$inquiry->priority] ?? $inquiry->priority }}</div>
        <div class="box"><strong>نوع الاستفسار:</strong> {{ $typeLabels[$inquiry->inquiry_type] ?? $inquiry->inquiry_type }}</div>
        <div class="box"><strong>المجيب:</strong> {{ $inquiry->responder?->username ?? '-' }}</div>
        <div class="box"><strong>تاريخ الرد:</strong> {{ $inquiry->responded_at?->format('Y-m-d H:i') ?? '-' }}</div>
        <div class="box"><strong>حالة التدقيق:</strong> {{ $inquiry->reviewStatusLabel() }}</div>
        <div class="box full"><strong>العنوان:</strong> {{ $inquiry->title }}</div>
        <div class="box full"><strong>نص الاستفسار:</strong><br>{{ $inquiry->body }}</div>
        <div class="box full"><strong>الإجابة:</strong><br>{{ $inquiry->publicResponseBody() ?: $inquiry->publicResponsePlaceholder() }}</div>
    </div>

    <script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>
