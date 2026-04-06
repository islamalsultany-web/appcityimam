@extends('users.layout')

@section('title', 'تدقيق الإجابة')
@section('page-title', 'تدقيق إجابة المجيب')

@section('topbar-actions')
    <a class="btn" href="{{ route('user.info') }}">معلومات المستخدم</a>
    <a class="btn" href="{{ route('dashboard.reviewer') }}">عودة للوحة المدقق</a>
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
    @php($typeLabels = [
        'financial' => 'مالي',
        'administrative' => 'إداري',
        'technical' => 'تقني',
        'warehouse' => 'مخزني',
        'other' => 'أخرى',
    ])
    @php($reviewStatusLabels = \App\Models\Inquiry::REVIEW_STATUS_LABELS)

    <p class="muted" style="margin-top: 0;">
        رقم الاستفسار: <strong>#{{ $inquiry->id }}</strong> — لن تظهر الإجابة للمستفسر حتى يتم اعتمادها من هذه الصفحة.
    </p>

    <ul class="list-grid" style="margin-bottom: 14px;">
        <li><strong>المستفسر:</strong> {{ $inquiry->asker?->username ?? '-' }}</li>
        <li><strong>المجيب:</strong> {{ $inquiry->responder?->username ?? '-' }}</li>
        <li><strong>الحالة:</strong> <span class="role-chip {{ $inquiry->statusBadgeClass() }}">{{ $inquiry->statusLabel() }}</span></li>
        <li><strong>الأولوية:</strong> {{ $priorityLabels[$inquiry->priority] ?? $inquiry->priority }}</li>
        <li><strong>نوع الاستفسار:</strong> {{ $typeLabels[$inquiry->inquiry_type] ?? $inquiry->inquiry_type }}</li>
        <li><strong>حالة التدقيق:</strong> <span class="role-chip {{ $inquiry->reviewStatusBadgeClass() }}">{{ $inquiry->reviewStatusLabel() }}</span></li>
        <li><strong>تاريخ الرد:</strong> {{ $inquiry->responded_at?->format('Y-m-d H:i') ?? '-' }}</li>
        <li><strong>آخر مدقق:</strong> {{ $inquiry->reviewer?->username ?? '-' }}</li>
        <li class="full" style="grid-column: 1 / -1;"><strong>عنوان الاستفسار:</strong> {{ $inquiry->title }}</li>
        <li class="full" style="grid-column: 1 / -1;"><strong>نص الاستفسار:</strong> {{ $inquiry->body }}</li>
        @if ($inquiry->review_note)
            <li class="full" style="grid-column: 1 / -1;"><strong>ملاحظة التدقيق الحالية:</strong> {{ $inquiry->review_note }}</li>
        @endif
    </ul>

    <form class="form-grid" action="{{ route('reviewer.inquiries.review', $inquiry) }}" method="POST">
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
            <label for="priority">الأولوية</label>
            <select id="priority" name="priority" required>
                <option value="normal" @selected(old('priority', $inquiry->priority) === 'normal')>عادية</option>
                <option value="urgent" @selected(old('priority', $inquiry->priority) === 'urgent')>مستعجلة</option>
                <option value="very_urgent" @selected(old('priority', $inquiry->priority) === 'very_urgent')>عاجلة جدا</option>
            </select>
        </div>

        <div class="field">
            <label for="response_type">نوع الإجابة</label>
            <select id="response_type" name="response_type">
                <option value="">-- بدون تغيير --</option>
                <option value="final" @selected(old('response_type', $inquiry->response_type) === 'final')>إجابة نهائية</option>
                <option value="partial" @selected(old('response_type', $inquiry->response_type) === 'partial')>إجابة أولية</option>
                <option value="request_info" @selected(old('response_type', $inquiry->response_type) === 'request_info')>طلب استكمال معلومات</option>
            </select>
        </div>

        <div class="field">
            <label for="follow_up_date">تاريخ متابعة</label>
            <input id="follow_up_date" name="follow_up_date" type="date" value="{{ old('follow_up_date', optional($inquiry->follow_up_date)->format('Y-m-d')) }}">
        </div>

        <div class="field full">
            <label for="response_body">نص الإجابة بعد التدقيق</label>
            <textarea id="response_body" name="response_body" required>{{ old('response_body', $inquiry->response_body) }}</textarea>
        </div>

        <div class="field full">
            <label for="internal_note">ملاحظة داخلية</label>
            <textarea id="internal_note" name="internal_note">{{ old('internal_note', $inquiry->internal_note) }}</textarea>
        </div>

        <div class="field full">
            <label for="review_note">ملاحظة المدقق</label>
            <textarea id="review_note" name="review_note" placeholder="اكتب سبب الإرجاع أو ملاحظة الاعتماد...">{{ old('review_note', $inquiry->review_note) }}</textarea>
        </div>

        <div class="field full">
            <div class="actions">
                <button class="btn primary" type="submit" name="review_action" value="approve">اعتماد الإجابة</button>
                <button class="btn warn" type="submit" name="review_action" value="return">إعادة للمجيب</button>
            </div>
        </div>
    </form>
@endsection
