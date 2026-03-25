@extends('users.layout')

@section('title', 'استيراد وتصدير من اكسل')
@section('page-title', 'استيراد وتصدير بيانات المستخدمين')

@section('header-actions')
    <a class="btn" href="{{ route('users.index') }}">رجوع للمستخدمين</a>
@endsection

@section('content')
    <p class="muted" style="margin-top: 0;">
        يمكنك من هذه الصفحة إنشاء نموذج اكسل مطابق لحقول المستخدم، أو استيراد ملف اكسل، أو تصدير بيانات المستخدمين الحالية.
    </p>

    <div class="actions" style="margin-bottom: 16px;">
        <a class="btn primary" href="{{ route('users.excel.template') }}">إنشاء نموذج</a>
        <a class="btn" href="{{ route('users.excel.export') }}">تصدير</a>
    </div>

    <form method="POST" action="{{ route('users.excel.import') }}" enctype="multipart/form-data" class="form-grid">
        @csrf

        <div class="field full">
            <label for="excel_file">استيراد ملف اكسل</label>
            <input id="excel_file" name="excel_file" type="file" accept=".xlsx,.xls,.csv" required>
            <div class="muted">الملفات المدعومة: xlsx, xls, csv</div>
        </div>

        <div class="field full">
            <div class="actions">
                <button class="btn primary" type="submit">استيراد</button>
            </div>
        </div>
    </form>

    <div class="alert success" style="margin-top: 16px;">
        ملاحظة: أثناء الاستيراد يتم قبول الخلايا الفارغة في الحقول الاختيارية، كما يتم التعامل مع القيم النصية أو الرقمية في الخلايا.
    </div>
@endsection
