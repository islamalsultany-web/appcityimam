(() => {
    'use strict';
    const text = value => String(value ?? '').trim();
    const validDate = value => /^\d{4}-\d{2}-\d{2}$/.test(value || '') && !Number.isNaN(Date.parse(value)) && new Date(value).toISOString().slice(0, 10) === value;
    function inspect(report) {
        const c = report.content, visible = c.sections.filter(s => s.enabled);
        const populated = visible.filter(s => text(s.body) || s.rows.some(row => row.some(cell => text(cell))));
        const dates = validDate(report.starts_on) && validDate(report.ends_on) && report.starts_on <= report.ends_on && validDate(c.prepared_on);
        const metricsValid = c.metrics.every(m => text(m.label) && m.value !== '' && m.value !== null && Number.isFinite(Number(m.value)) && Number(m.value) >= 0 && Number(m.value) <= 1e12);
        const units = new Set(c.metrics.map(m => text(m.unit).toLowerCase()));
        const checks = [
            {label: 'عنوان واضح وشعبة محددة', pass: !!text(report.title) && !!text(report.division), severity: 'error', target: 'details', detail: 'أكمل عنوان التقرير واسم الشعبة / الوحدة.'},
            {label: 'الفترة وتاريخ الإعداد', pass: dates, severity: 'error', target: 'details', detail: 'حدد تواريخ صحيحة؛ يجب ألا تسبق نهاية الفترة بدايتها.'},
            {label: 'اسم مُعدّ التقرير', pass: !!text(c.author), severity: 'error', target: 'details', detail: 'أدخل اسم مُعدّ التقرير قبل الحفظ.'},
            {label: 'عناوين الفقرات والجداول', pass: c.sections.length > 0 && c.sections.every(s => text(s.title) && s.columns.every(col => text(col))), severity: 'error', target: 'writing', detail: 'أكمل عناوين الفقرات وعناوين أعمدة الجداول.'},
            {label: 'قيم المؤشرات صالحة', pass: metricsValid, severity: 'error', target: 'charts', detail: 'لكل مؤشر اسم وقيمة رقمية بين صفر وتريليون.'},
            {label: 'محتوى ظاهر في التقرير', pass: populated.length > 0, severity: 'warning', target: 'writing', detail: 'أضف نصًا أو سجلات فعلية؛ التقرير الحالي لا يحتوي فقرات ظاهرة ببيانات.'},
            {label: 'الفقرات الظاهرة مكتملة', pass: visible.length === populated.length && visible.length > 0, severity: 'warning', target: 'writing', detail: 'املأ الفقرات الظاهرة الفارغة أو أخفِ الفقرات غير المطلوبة.'},
            {label: 'ملخص وتوصيات للإدارة', pass: populated.some(s => /ملخص|موجز|خلاصة/u.test(s.title)) && populated.some(s => /توصي|إجراء|اجراء|المطلوب|خطة/u.test(s.title)), severity: 'warning', target: 'writing', detail: 'أضف ملخصًا يوضح النتائج، وتوصيات أو إجراءات بمسؤول وموعد متابعة.'},
            {label: 'مؤشرات قابلة للمقارنة', pass: c.chart === 'none' || c.metrics.length < 2 || (units.size === 1 && !units.has('')), severity: 'warning', target: 'charts', detail: 'حدد وحدة قياس متجانسة لمؤشرات الرسم المشترك، أو اختر عرض المؤشرات فقط.'},
            {label: 'مراجعة خلايا الجداول', pass: visible.every(s => s.rows.every(row => s.columns.every((_, i) => text(row[i])))), severity: 'warning', target: 'writing', detail: 'توجد خلايا فارغة. استكملها أو وضّح سبب عدم توفر بياناتها.'},
        ];
        return {checks, passed: checks.filter(item => item.pass).length, errors: checks.filter(item => !item.pass && item.severity === 'error'),
            sections: visible.length, tables: visible.filter(s => s.columns.length && s.rows.length).length,
            rows: visible.reduce((sum, s) => sum + s.rows.length, 0), charts: (c.charts || []).length + (c.chart !== 'none' && c.metrics.length ? 1 : 0)};
    }
    globalThis.ReportQuality = {inspect};
})();
