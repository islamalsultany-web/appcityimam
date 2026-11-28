import test from 'node:test';
import assert from 'node:assert/strict';
import '../public/js/report-quality.js';

const report = () => ({title: 'تقرير الصيانة', division: 'الصيانة', starts_on: '2026-11-01', ends_on: '2026-11-28', content: {
    author: 'معد التقرير', prepared_on: '2026-11-28', chart: 'bar', metrics: [{label: 'منجزة', value: 4, unit: 'مهمة'}, {label: 'قيد التنفيذ', value: 2, unit: 'مهمة'}], charts: [],
    sections: [{title: 'ملخص النتائج', enabled: true, body: 'أعمال الفترة حسب السجلات.', columns: [], rows: []}, {title: 'خطة الإجراءات', enabled: true, body: 'مراجعة الأعمال المتبقية.', columns: ['الإجراء', 'المسؤول'], rows: [['مراجعة', 'القسم']]}],
}});

test('a complete report passes the checks without claiming factual accuracy', () => {
    const result = ReportQuality.inspect(report());
    assert.equal(result.errors.length, 0); assert.equal(result.passed, result.checks.length);
    assert.equal(result.sections, 2); assert.equal(result.tables, 1); assert.equal(result.rows, 1); assert.equal(result.charts, 1);
});
test('invalid dates and missing required metadata block saving', () => {
    const value = report(); value.ends_on = '2026-02-31'; value.division = ''; value.content.author = '';
    const result = ReportQuality.inspect(value);
    assert.deepEqual(result.errors.map(item => item.target), ['details', 'details', 'details']);
});
test('mixed units and empty cells are suggestions rather than save errors', () => {
    const value = report(); value.content.metrics[1].unit = 'دينار'; value.content.sections[1].rows[0][1] = null;
    const result = ReportQuality.inspect(value);
    assert.equal(result.errors.length, 0);
    assert.equal(result.checks.find(item => item.label === 'مؤشرات قابلة للمقارنة').pass, false);
    assert.equal(result.checks.find(item => item.label === 'مراجعة خلايا الجداول').pass, false);
});
test('hidden sections do not contribute to content review and chart mode respects units', () => {
    const value = report(); value.content.sections.forEach(item => item.enabled = false);
    value.content.chart = 'none'; value.content.metrics[1].unit = 'دينار';
    const result = ReportQuality.inspect(value);
    assert.equal(result.sections, 0); assert.equal(result.charts, 0);
    assert.equal(result.checks.find(item => item.label === 'محتوى ظاهر في التقرير').pass, false);
    assert.equal(result.checks.find(item => item.label === 'مؤشرات قابلة للمقارنة').pass, true);
});
test('zero is a valid metric and blank or negative values are errors', () => {
    const value = report(); value.content.metrics[0].value = 0;
    assert.equal(ReportQuality.inspect(value).errors.length, 0);
    for (const invalid of ['', -1, Infinity, NaN]) {
        value.content.metrics[0].value = invalid;
        assert.equal(ReportQuality.inspect(value).errors.some(item => item.target === 'charts'), true);
    }
});
