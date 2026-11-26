@extends('official-reports.layout')
@section('title', 'التقارير الرسمية')
@section('content')
<div class="flex flex-wrap justify-between items-center gap-4 mb-8">
    <div><div class="text-sm text-gold-600 mb-2">مدينة الإمام الحسين (عليه السلام) للزائرين</div><h1 class="text-3xl font-extrabold text-navy-900">التقارير الرسمية</h1><p class="text-slate-500 mt-2">مساحة إعداد تقارير الشعب والوحدات • يومي، أسبوعي، شهري</p></div>
    <a href="{{ route('official-reports.create') }}" class="bg-navy-900 text-white rounded-xl px-6 py-3">+ إنشاء تقرير</a>
</div>
<div class="bg-navy-900 text-white p-7 rounded-2xl mb-8"><h2 class="text-xl font-bold mb-2">هيكل رسمي واحد، وهوية خاصة لكل تقرير</h2><p class="text-slate-300">فقرات مرنة، جداول متابعة، مؤشرات ومخططات، وقوالب تصميم قابلة للتبادل. {{ \App\Support\AdminRoleGuard::isAdmin(\App\Support\AppAuth::user(request())) ? 'تظهر لك تقارير جميع المستخدمين بصفتك مديرًا.' : 'تظهر هنا تقاريرك الخاصة.' }}</p></div>
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
    <div class="col-span-full text-center bg-white border border-dashed rounded-2xl py-16"><h2 class="font-bold text-xl mb-3">ابدأ تقريرك الأول</h2><p class="text-slate-500">اختر الفترة، وأدخل أعمال الوحدة، ثم خصص التصميم وعاين التقرير.</p></div>
@endforelse
</div>
<div class="mt-6">{{ $reports->links() }}</div>
@endsection
