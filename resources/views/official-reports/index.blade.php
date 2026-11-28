@extends('official-reports.layout')
@section('title', 'التقارير الرسمية')
@push('head')
<link rel="stylesheet" href="{{ asset('css/official-reports.css') }}">
<link rel="stylesheet" href="{{ asset('css/report-workflow.css') }}">
@endpush
@section('content')
<div class="flex flex-wrap justify-between items-center gap-4 mb-8">
    <div><div class="text-sm text-gold-600 mb-2">مدينة الإمام الحسين (عليه السلام) للزائرين</div><h1 class="text-3xl font-extrabold text-navy-900">التقارير الرسمية</h1><p class="text-slate-500 mt-2">مساحة إعداد تقارير الشعب والوحدات • يومي، أسبوعي، شهري</p></div>
    <a href="{{ route('official-reports.create') }}" class="bg-navy-900 text-white rounded-xl px-6 py-3">+ إنشاء تقرير</a>
</div>
<div class="report-start-grid">
    <a href="{{ route('official-reports.create', ['start' => 'source']) }}" class="report-start-card"><span class="eyebrow">من ملفاتك إلى تقرير</span><h2>تحليل ملفات Excel</h2><p>استورد البيانات، واستخرج الجداول والمؤشرات والرسوم، وأضف تحليلًا اختياريًا بالذكاء الاصطناعي.</p><strong>اختيار الملفات ←</strong></a>
    <a href="{{ route('official-reports.create', ['start' => 'details']) }}" class="report-start-card"><span class="eyebrow">من أعمالك إلى عرض واضح</span><h2>إنشاء تقرير يدوي</h2><p>حدّد الفترة والشعبة، وأضف النتائج والتوصيات وخطة الإجراءات، ثم راجع جودة التقرير.</p><strong>بدء الكتابة ←</strong></a>
</div>
<div class="report-stat-grid"><div><strong>{{ $counts['total'] }}</strong><span>إجمالي تقاريرك</span></div><div><strong>{{ $counts['recent'] }}</strong><span>تقارير حُدّثت خلال 7 أيام</span></div><div><strong>{{ $counts['monthly'] }}</strong><span>تقارير شهرية</span></div></div>
<p class="hint">{{ \App\Support\AdminRoleGuard::isAdmin(\App\Support\AppAuth::user(request())) ? 'تظهر لك تقارير جميع المستخدمين بصفتك مديرًا.' : 'تعرض القائمة التقارير المتاحة لك فقط.' }}</p>
<form method="get" class="report-filters" action="{{ route('official-reports.index') }}">
    <label>بحث بالعنوان أو الشعبة<input type="search" name="q" maxlength="100" value="{{ request('q') }}" placeholder="ابحث عن تقرير…"></label>
    <label>دورية التقرير<select name="frequency"><option value="">كل التقارير</option>@foreach(['daily'=>'يومي', 'weekly'=>'أسبوعي', 'monthly'=>'شهري'] as $value => $label)<option value="{{ $value }}" @selected(request('frequency') === $value)>{{ $label }}</option>@endforeach</select></label>
    <button type="submit">بحث</button><a href="{{ route('official-reports.index') }}">مسح البحث</a>
</form>
<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
@forelse($reports as $report)
    <article class="bg-white border rounded-2xl p-6 shadow-sm">
        <div class="text-sm text-emerald-700">{{ ['daily'=>'يومي','weekly'=>'أسبوعي','monthly'=>'شهري'][$report->frequency] }} · {{ $report->division }}</div>
        <h2 class="text-lg font-bold my-3">{{ $report->title }}</h2>
        <p class="text-sm text-slate-500 mb-5">{{ $report->starts_on->format('Y/m/d') }} — {{ $report->ends_on->format('Y/m/d') }}</p>
        <div class="flex justify-between items-center"><a class="text-navy-700 font-bold" href="{{ route('official-reports.edit', $report) }}">فتح وتحرير ←</a>
        <form method="post" action="{{ route('official-reports.destroy', $report) }}" data-confirm-delete>@csrf @method('DELETE')<button class="text-red-600 text-sm">حذف</button></form></div>
    </article>
@empty
    <div class="col-span-full text-center bg-white border border-dashed rounded-2xl py-16"><h2 class="font-bold text-xl mb-3">{{ request()->filled('q') || request()->filled('frequency') ? 'لا توجد تقارير تطابق البحث' : 'ابدأ تقريرك الأول' }}</h2><p class="text-slate-500">{{ request()->filled('q') || request()->filled('frequency') ? 'جرّب كلمة أخرى أو امسح عوامل البحث.' : 'اختر استيراد Excel أو الإنشاء اليدوي من البطاقات أعلاه.' }}</p></div>
@endforelse
</div>
<div class="mt-6">{{ $reports->links() }}</div>
@endsection
