@extends('users.layout')

@section('title', 'عرض الاستفسار')
@section('page-title', 'عرض الاستفسار')

@section('topbar-actions')
    <a class="btn" href="{{ route('user.info') }}">معلومات المستخدم</a>
    <a class="btn" href="{{ route('dashboard.responder') }}">عودة للفهرس</a>
    <a class="btn" href="{{ route('responder.inquiries.print', $inquiry) }}" target="_blank">طباعة</a>
    <a class="btn primary" href="{{ route('responder.inquiries.show', $inquiry) }}">تعديل</a>
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
        <li><strong>المستفسر:</strong> {{ $inquiry->asker?->username ?? '-' }}</li>
        <li><strong>الحالة:</strong> {{ $statusLabels[$inquiry->status] ?? $inquiry->status }}</li>
        <li><strong>الأولوية:</strong> {{ $priorityLabels[$inquiry->priority] ?? $inquiry->priority }}</li>
        <li><strong>قناة الرد:</strong> {{ $channelLabels[$inquiry->preferred_channel] ?? $inquiry->preferred_channel }}</li>
        <li><strong>تاريخ الإرسال:</strong> {{ $inquiry->created_at?->format('Y-m-d H:i') }}</li>
        <li class="full" style="grid-column: 1 / -1;"><strong>عنوان الاستفسار:</strong> {{ $inquiry->title }}</li>
        <li class="full" style="grid-column: 1 / -1;"><strong>نص الاستفسار:</strong> {{ $inquiry->body }}</li>
        @if ($inquiry->response_body)
            <li class="full" style="grid-column: 1 / -1;"><strong>نص الإجابة:</strong> {{ $inquiry->response_body }}</li>
        @endif
    </ul>
@endsection
