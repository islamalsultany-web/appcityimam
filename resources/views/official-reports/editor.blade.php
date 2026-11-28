@extends('official-reports.layout')
@section('title', 'منشئ التقارير الرسمية')
@push('head')
<link rel="stylesheet" href="{{ asset('css/official-reports.css') }}">
<link rel="stylesheet" href="{{ asset('css/report-workflow.css') }}">
@endpush
@section('content')
<div id="report-builder">
<div class="builder-toolbar"><div><a href="{{ route('official-reports.index') }}" class="text-sm text-slate-500">التقارير الرسمية / محرر التقرير</a><h1 class="text-2xl font-extrabold text-navy-900 mt-2">صمّم تقريرًا يليق بعملك</h1></div><div class="flex gap-2"><button type="button" id="save-report" class="primary">حفظ التقرير</button><button type="button" id="print-report">طباعة / PDF</button></div></div>
<p id="builder-status" role="status" aria-live="polite"></p>
@include('official-reports.workflow')
<div class="builder-grid">
<div class="builder-controls">
@include('official-reports.excel-import')
<section class="control-card" data-workflow="writing"><h2>اجعل التقرير مفيدًا لاتخاذ القرار</h2><p class="hint">اكتب النتيجة، ما يدعمها من أرقام، ثم الإجراء المطلوب والمسؤول وموعد المتابعة. هذه الأزرار تضيف فقرات فارغة لتعبئتها بوقائعك.</p><div class="section-actions"><button type="button" data-add-outline="summary">+ ملخص تنفيذي</button><button type="button" data-add-outline="findings">+ نتائج رئيسية</button><button type="button" data-add-outline="actions">+ خطة إجراءات</button></div><button type="button" id="generate-executive-summary" {{ $aiEnabled ? '' : 'disabled' }}>اقتراح ملخص من محتوى التقرير بالذكاء الاصطناعي</button><p class="hint">يُرسل المحتوى الظاهر والمؤشرات إلى المزود عند الطلب، ويظهر الملخص للمراجعة قبل إضافته.</p></section>
<section class="control-card" data-workflow="source"><h2>مكتبة قوالب التقارير</h2><p class="hint">اختر قالبًا لكل تقرير جديد. تُجلب أحدث نسخة منشورة عند فتح المكتبة أو تحديثها.</p><button type="button" id="refresh-templates">تحديث المكتبة</button><p id="template-status" role="status" aria-live="polite"></p><div id="template-library"></div><label>طريقة التطبيق<select id="template-mode"><option value="design" {{ $report ? 'selected' : '' }}>تغيير المظهر فقط مع الاحتفاظ بالبيانات</option><option value="full" {{ $report ? '' : 'selected' }}>تطبيق الفقرات والجداول أيضًا</option></select></label><label>وصف قالب جديد بالذكاء الاصطناعي<textarea id="template-prompt" maxlength="1000" rows="3" placeholder="تقرير أسبوعي للصيانة يتضمن جدول الأعطال والمتابعة"></textarea></label><p class="hint">يُرسل الوصف فقط إلى مزود الذكاء الاصطناعي الحالي. راجع القالب قبل استخدامه أو تنزيله.</p><button type="button" id="generate-template" {{ $aiEnabled ? '' : 'disabled' }}>إنشاء قالب بالذكاء الاصطناعي</button><div id="generated-template"></div></section>
<section class="control-card" data-workflow="details"><h2> بيانات التقرير</h2>
<label>عنوان التقرير<input id="field-title" required maxlength="255"></label>
<label>الشعبة / الوحدة<input id="field-division" required maxlength="255" placeholder="مثال: وحدة الاتصالات والإنترنت"></label>
<div class="field-grid"><label>نوع التقرير<select id="field-frequency"><option value="daily">يومي</option><option value="weekly">أسبوعي</option><option value="monthly">شهري</option></select></label><label>العدد<input id="field-number" maxlength="100"></label></div>
<div class="field-grid"><label>من<input type="date" id="field-starts_on"></label><label>إلى<input type="date" id="field-ends_on"></label></div>
<label>تاريخ الإعداد<input type="date" id="field-prepared_on"></label>
<div class="field-grid"><label>إعداد التقرير<input id="field-author" maxlength="255"></label><label>مسؤول الشعبة / الوحدة<input id="field-manager" maxlength="255"></label></div>
<label>المرفقات المشار إليها<textarea id="field-attachments" rows="2" placeholder="وصف المرفقات وأرقامها؛ ترفق المستندات عند التسليم"></textarea></label>
</section>
<section class="control-card" data-workflow="design"><h2> التصميم والهوية</h2><div class="theme-options"><button type="button" data-theme="heritage">رسمي تراثي</button><button type="button" data-theme="modern">عصري</button><button type="button" data-theme="minimal">بسيط</button></div>
<div class="field-grid"><label>لون الهوية<input type="color" id="field-accent"></label><label>الخط<select id="field-font"><option value="cairo">القاهرة</option><option value="serif">تقليدي</option><option value="sans">بسيط</option></select></label></div>
<div class="flex gap-2 mt-3"><button type="button" id="export-template">تصدير القالب</button><label class="import-label">استيراد قالب<input type="file" id="import-template" accept=".json,application/json" hidden></label></div><p class="hint">القالب ينقل التصميم وعناوين الفقرات، دون بيانات التقرير. يقبل ملفات JSON فقط؛ لا ينفذ أكوادًا من مواقع خارجية.</p></section>
<section class="control-card" data-workflow="charts"><h2> مؤشرات ومخططات</h2><p class="hint">أدخل أرقامًا فعلية. اجمع في المخطط مؤشرات من الوحدة نفسها لتكون المقارنة صحيحة.</p><label>عرض المخطط<select id="field-chart"><option value="bar">أعمدة أفقية</option><option value="doughnut">دائري نسبي</option><option value="none">المؤشرات فقط</option></select></label><div id="metrics-editor"></div><button type="button" id="add-metric">+ إضافة مؤشر</button></section>
<section class="control-card" data-workflow="writing"><h2> فقرات التقرير</h2><p class="hint">عدّل العنوان والنص والجداول، وأخفِ الفقرات غير المطلوبة أو غيّر ترتيبها.</p><div id="sections-editor"></div><button type="button" id="add-section">+ إضافة فقرة</button></section>
<section class="control-card" data-workflow="writing"><h2>مساعد الصياغة بالذكاء الاصطناعي</h2><p class="hint">{{ $aiEnabled ? 'الاتصال مفعّل. عند الضغط على «اقتراح صياغة» يُرسل نص الفقرة وتعليماتك إلى مزود الذكاء الاصطناعي. راجع الاقتراح قبل اعتماده.' : 'غير مفعّل حاليًا. يفعّله مسؤول الخادم بإعدادات REPORT_AI_KEY و REPORT_AI_MODEL.' }}</p><label>تعليمات الصياغة<textarea id="ai-instruction" rows="2">أعد صياغة الفقرة بلغة إدارية واضحة وموجزة مع الحفاظ على الوقائع والأرقام.</textarea></label><div id="ai-result" hidden><label>الاقتراح<textarea id="ai-suggestion" rows="6" maxlength="12000"></textarea></label><button type="button" id="accept-ai">اعتماد الاقتراح</button><button type="button" id="dismiss-ai">تجاهل</button></div></section>
@include('official-reports.review')
<div class="workflow-actions"><button type="button" id="workflow-back">→ السابق</button><span id="workflow-current"></span><button type="button" id="workflow-next" class="primary">التالي ←</button></div>
</div>
<div class="preview-column"><div class="preview-label">معاينة مباشرة <button type="button" id="toggle-preview" aria-pressed="false">توسيع المعاينة</button><span>معاينة متصلة · تُقسّم إلى صفحات A4 عند الطباعة</span></div><div class="preview-pagination"><button type="button" id="preview-page-prev">الجزء السابق</button><span id="preview-page-label" aria-live="polite"></span><button type="button" id="preview-page-next">الجزء التالي</button></div><article id="report-preview" class="report-paper"></article></div>
</div></div>
@endsection
@push('scripts')
<script nonce="{{ $cspNonce }}" id="excel-import-config" type="application/json">{!! json_encode(['url' => route('official-reports.import-excel'), 'uploadUrl' => route('official-reports.uploads.start')], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script nonce="{{ $cspNonce }}" id="report-builder-config" type="application/json">{!! json_encode(['report' => $report, 'author' => \App\Support\AppAuth::user(request())?->username ?? '', 'division' => \App\Support\AppAuth::user(request())?->division ?? '', 'today' => now('Asia/Baghdad')->format('Y-m-d'), 'saveUrl' => $report ? route('official-reports.update', $report) : route('official-reports.store'), 'assistUrl' => route('official-reports.assist'), 'aiEnabled' => $aiEnabled, 'templatesUrl' => asset('report-templates/catalog.json'), 'generateTemplateUrl' => route('official-reports.generate-template')], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
<script src="{{ asset('js/report-quality.js') }}" defer></script>
<script src="{{ asset('js/official-reports.js') }}" defer></script>
@endpush
