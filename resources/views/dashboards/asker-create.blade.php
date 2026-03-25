@extends('users.layout')

@section('title', 'إرسال استفسار جديد')
@section('page-title', 'إرسال استفسار جديد')

@section('topbar-actions')
    <a class="btn" href="{{ route('dashboard.asker') }}">عودة للاستفسارات</a>
    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
    <p class="muted" style="margin-top: 0;">
        املأ الحقول التالية ثم أرسل الاستفسار ليظهر في لوحة المتابعة الخاصة بك.
    </p>

    <form class="form-grid" action="{{ route('asker.inquiries.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="field">
            <label for="title">عنوان الاستفسار</label>
            <input id="title" name="title" type="text" placeholder="مثال: طلب توضيح حول إجراء إداري" value="{{ old('title') }}" required>
        </div>

        <div class="field">
            <label for="priority">الأولوية</label>
            <select id="priority" name="priority" required>
                <option value="normal" @selected(old('priority', 'normal') === 'normal')>عادية</option>
                <option value="urgent" @selected(old('priority') === 'urgent')>مستعجلة</option>
                <option value="very_urgent" @selected(old('priority') === 'very_urgent')>عاجلة جدا</option>
            </select>
        </div>

        <div class="field">
            <label for="preferred_channel">طريقة الرد المفضلة</label>
            <select id="preferred_channel" name="preferred_channel" required>
                <option value="system" @selected(old('preferred_channel', 'system') === 'system')>داخل النظام</option>
                <option value="phone" @selected(old('preferred_channel') === 'phone')>اتصال هاتفي</option>
                <option value="email" @selected(old('preferred_channel') === 'email')>بريد إلكتروني</option>
            </select>
        </div>

        <div class="field full">
            <label for="body">نص الاستفسار</label>
            <textarea id="body" name="body" placeholder="اكتب تفاصيل الاستفسار بشكل واضح مع ذكر كل المعلومات التي تساعد المجيب..." required>{{ old('body') }}</textarea>
        </div>

        <div class="field full">
            <label for="attachment">مرفق (اختياري)</label>
            <input id="attachment" name="attachment" type="file">
            <span class="muted">يمكنك إرفاق مستند أو صورة لتوضيح الاستفسار.</span>
        </div>

        <div class="field full">
            <div class="actions">
                <button class="btn primary" type="submit">إرسال الاستفسار</button>
                <button class="btn" type="reset">مسح الحقول</button>
            </div>
        </div>
    </form>
@endsection
