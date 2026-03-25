@extends('users.layout')

@section('title', 'الإجابة على الاستفسار')
@section('page-title', 'نموذج الإجابة على الاستفسار')

@section('topbar-actions')
    <a class="btn" href="{{ route('user.info') }}">معلومات المستخدم</a>
    <a class="btn" href="{{ route('dashboard.responder') }}">عودة للفهرس</a>
    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
    @php($priorityLabels = [
        'normal' => 'عادية',
        'urgent' => 'مستعجلة',
        'very_urgent' => 'عاجلة جدا',
    ])

    <p class="muted" style="margin-top: 0;">
        رقم الاستفسار: <strong>#{{ $inquiry->id }}</strong>
    </p>

    <ul class="list-grid" style="margin-bottom: 14px;">
        <li><strong>المستفسر:</strong> {{ $inquiry->asker?->username ?? '-' }}</li>
        <li><strong>الأولوية:</strong> {{ $priorityLabels[$inquiry->priority] ?? $inquiry->priority }}</li>
        <li><strong>قناة الرد:</strong> {{ $inquiry->preferred_channel }}</li>
        <li class="full" style="grid-column: 1 / -1;"><strong>عنوان الاستفسار:</strong> {{ $inquiry->title }}</li>
        <li class="full" style="grid-column: 1 / -1;"><strong>نص الاستفسار:</strong> {{ $inquiry->body }}</li>
    </ul>

    <form class="form-grid" action="{{ route('responder.inquiries.answer', $inquiry) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="field">
            <label for="status">حالة المعالجة</label>
            <select id="status" name="status" required>
                <option value="in_progress" @selected(old('status', $inquiry->status) === 'in_progress')>قيد المعالجة</option>
                <option value="answered" @selected(old('status', $inquiry->status) === 'answered')>تمت الإجابة</option>
                <option value="needs_info" @selected(old('status', $inquiry->status) === 'needs_info')>بحاجة معلومات إضافية</option>
                <option value="closed" @selected(old('status', $inquiry->status) === 'closed')>مغلق</option>
            </select>
        </div>

        <div class="field">
            <label for="priority">أولوية الرد</label>
            <select id="priority" name="priority" required>
                <option value="normal" @selected(old('priority', $inquiry->priority) === 'normal')>عادية</option>
                <option value="urgent" @selected(old('priority', $inquiry->priority) === 'urgent')>مستعجلة</option>
                <option value="very_urgent" @selected(old('priority', $inquiry->priority) === 'very_urgent')>عاجلة جدا</option>
            </select>
        </div>

        <div class="field">
            <label for="response_type">نوع الإجابة</label>
            <select id="response_type" name="response_type" required>
                <option value="final" @selected(old('response_type', $inquiry->response_type) === 'final')>إجابة نهائية</option>
                <option value="partial" @selected(old('response_type', $inquiry->response_type) === 'partial')>إجابة أولية</option>
                <option value="request_info" @selected(old('response_type', $inquiry->response_type) === 'request_info')>طلب استكمال معلومات</option>
            </select>
        </div>

        <div class="field">
            <label for="follow_up_date">تاريخ متابعة (اختياري)</label>
            <input id="follow_up_date" name="follow_up_date" type="date" value="{{ old('follow_up_date', optional($inquiry->follow_up_date)->format('Y-m-d')) }}">
        </div>

        <div class="field full">
            <label for="response_body">نص الإجابة</label>
            <textarea id="response_body" name="response_body" placeholder="اكتب الإجابة التفصيلية للمستفسر مع الخطوات المطلوبة أو التوجيهات الرسمية..." required>{{ old('response_body', $inquiry->response_body) }}</textarea>
        </div>

        <div class="field full">
            <label for="internal_note">ملاحظة داخلية للمجيب (اختياري)</label>
            <textarea id="internal_note" name="internal_note" placeholder="ملاحظة داخلية لا تظهر للمستفسر.">{{ old('internal_note', $inquiry->internal_note) }}</textarea>
        </div>

        <div class="field full">
            <label for="response_attachment">مرفق مع الإجابة (اختياري)</label>
            <input id="response_attachment" name="response_attachment" type="file">
            <span class="muted">يمكن إرفاق كتاب رسمي أو صورة توضيحية مع الرد.</span>
        </div>

        <div class="field full">
            <div class="actions">
                <button class="btn primary" type="submit">إرسال الإجابة</button>
                <button class="btn warn" type="reset">مسح الحقول</button>
            </div>
        </div>
    </form>
@endsection
