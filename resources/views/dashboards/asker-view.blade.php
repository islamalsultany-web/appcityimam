@extends('users.layout')

@section('title', 'عرض الاستفسار')
@section('page-title', 'عرض استفساري')

@section('topbar-actions')
    <a class="btn" href="{{ route('dashboard.asker') }}">عودة للفهرس</a>
    <a class="btn" href="{{ route('asker.inquiries.print', $inquiry) }}" target="_blank">طباعة</a>
    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
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
    @php($channelLabels = [
        'system' => 'داخل النظام',
        'phone' => 'اتصال هاتفي',
        'email' => 'بريد إلكتروني',
    ])

    <ul class="list-grid" style="margin-bottom: 14px;">
        <li><strong>رقم الاستفسار:</strong> #{{ $inquiry->id }}</li>
        <li><strong>الحالة:</strong> {{ $statusLabels[$inquiry->status] ?? $inquiry->status }}</li>
        <li><strong>الأولوية:</strong> {{ $priorityLabels[$inquiry->priority] ?? $inquiry->priority }}</li>
        <li><strong>قناة الرد:</strong> {{ $channelLabels[$inquiry->preferred_channel] ?? $inquiry->preferred_channel }}</li>
        <li><strong>المجيب:</strong> {{ $inquiry->responder?->username ?? '-' }}</li>
        <li><strong>تاريخ الإرسال:</strong> {{ $inquiry->created_at?->format('Y-m-d H:i') }}</li>
        <li><strong>تاريخ الرد:</strong> {{ $inquiry->responded_at?->format('Y-m-d H:i') ?? '-' }}</li>
        <li class="full" style="grid-column: 1 / -1;"><strong>عنوان الاستفسار:</strong> {{ $inquiry->title }}</li>
        <li class="full" style="grid-column: 1 / -1;"><strong>نص الاستفسار:</strong> {{ $inquiry->body }}</li>
        <li class="full" style="grid-column: 1 / -1;"><strong>الإجابة:</strong> {{ $inquiry->response_body ?: 'لا توجد إجابة حتى الآن.' }}</li>
    </ul>
@endsection
