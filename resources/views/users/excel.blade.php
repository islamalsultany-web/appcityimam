@extends('users.layout')

@section('title', 'استيراد وتصدير من اكسل')
@section('page-title', 'استيراد وتصدير بيانات المستخدمين')

@section('header-actions')
    <a class="btn" href="{{ route('users.index') }}">رجوع للمستخدمين</a>
@endsection

@section('content')
    <p class="muted no-mt">
        يمكنك من هذه الصفحة إنشاء نموذج اكسل مطابق لحقول المستخدم، أو استيراد ملف اكسل، أو تصدير بيانات المستخدمين الحالية.
    </p>

    <div class="actions mb-16">
        <a class="btn primary" href="{{ route('users.excel.template') }}">إنشاء نموذج</a>
        <a class="btn" href="{{ route('users.excel.export') }}">تصدير</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger-box mb-14">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if (session('success'))
        <div class="alert success mb-14">{{ session('success') }}</div>
    @endif

    @if (session('import_temporary_passwords'))
        <div class="alert alert-danger-box mb-14">
            <strong>كلمات مرور مؤقتة — اعرضها مرة واحدة فقط وسلّمها للمنتسبين بشكل آمن:</strong>
            <p class="muted">سيُجبر كل منتسب على تغيير اسم المستخدم وكلمة المرور عند أول دخول.</p>
            <table class="table mt-8">
                <thead>
                    <tr>
                        <th>الرقم الوظيفي</th>
                        <th>اسم المستخدم</th>
                        <th>كلمة المرور المؤقتة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (session('import_temporary_passwords') as $row)
                        <tr>
                            <td>{{ $row['employee_number'] ?? '—' }}</td>
                            <td>{{ $row['username'] }}</td>
                            <td><code>{{ $row['temporary_password'] }}</code></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

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

    <div class="alert success mt-16">
        ملاحظة: أثناء الاستيراد يتم قبول الخلايا الفارغة في الحقول الاختيارية، كما يتم التعامل مع القيم النصية أو الرقمية في الخلايا.
        عند إعادة الاستيراد يُحدَّث المستخدم المطابق <strong>بالرقم الوظيفي</strong> — ضع <strong>الاسم الكامل</strong> في عمود «اسم المستخدم» ليظهر في القائمة. إذا تركت كلمة المرور فارغة للمستخدم الموجود لن تتغير كلمة مروره.
        <br>للمستخدم <strong>الجديد</strong> بدون كلمة مرور في الملف: تُنشأ كلمة مرور <strong>عشوائية قوية</strong> (ليست الرقم الوظيفي) وتُعرض لك بعد الاستيراد — سلّمها للمنتسب مرة واحدة؛ عند أول دخول سيُجبر على تغيير بيانات الدخول.
        <br>إذا وضعت كلمة مرور ضعيفة أو مساوية للرقم الوظيفي، يُولَّد بديل عشوائي أو يُجبر المستخدم على التغيير عند الدخول.
        <br>ترتيب الأعمدة إلزامي: <strong>اسم المستخدم | كلمة المرور | تأكيد كلمة المرور | الرقم الوظيفي | رقم الباج | الشعبة | الوحدة | الدور</strong> — استخدم «إنشاء نموذج» ولا تغيّر ترتيب الأعمدة.
        <br>الدور في الاستيراد: <strong>asker</strong> أو <strong>responder</strong> أو <strong>reviewer</strong> فقط — لا يمكن إنشاء <strong>admin</strong> من ملف Excel.
    </div>
@endsection
