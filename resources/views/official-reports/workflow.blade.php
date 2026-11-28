<section class="builder-intro">
    <div><span class="eyebrow">من البيانات إلى قرار واضح</span><h2>تقرير مرتب، خطوة بخطوة</h2><p>ابدأ بملف Excel أو قالب، حدّد الفترة، ثم راجع النتائج والتوصيات قبل إخراج النسخة النهائية.</p></div>
    <div class="builder-save-state" id="report-save-state" role="status">{{ $report ? 'نسخة محفوظة' : 'تقرير جديد' }}</div>
</section>
<nav class="workflow-nav" aria-label="خطوات إعداد التقرير">
    @foreach(['source' => 'مصدر التقرير', 'details' => 'البيانات والفترة', 'writing' => 'المحتوى والتحليل', 'charts' => 'المؤشرات والرسوم', 'design' => 'التصميم', 'review' => 'المراجعة والإخراج'] as $step => $label)
        <button type="button" data-workflow-tab="{{ $step }}" aria-controls="workflow-{{ $step }}"><span>{{ $loop->iteration }}</span>{{ $label }}</button>
    @endforeach
</nav>
