(() => {
    'use strict';
    const config = JSON.parse(document.getElementById('report-builder-config').textContent);
    const $ = id => document.getElementById(id);
    const section = (title, columns = []) => ({title, body: '', enabled: true, columns, rows: []});
    const state = config.report ? {
        title: config.report.title, division: config.report.division, frequency: config.report.frequency,
        starts_on: config.report.starts_on.slice(0, 10), ends_on: config.report.ends_on.slice(0, 10), content: config.report.content,
    } : {
        title: 'التقرير اليومي لأعمال الشعبة / الوحدة', division: config.division || '', frequency: 'daily', starts_on: config.today, ends_on: config.today,
        content: {theme: 'heritage', accent: '#176354', font: 'cairo', chart: 'bar', number: '', prepared_on: config.today,
            author: config.author, manager: '', attachments: '', metrics: [], sections: [
                section('أولًا ـ موجز التقرير'),
                section('ثانيًا ـ الأعمال المنجزة', ['العمل المنجز', 'تاريخ الإنجاز', 'العدد أو الكمية', 'الملاحظات أو رقم المستند']),
                section('ثالثًا ـ الأعمال قيد التنفيذ والمتابعة', ['العمل أو المعاملة', 'الإجراء المتخذ', 'الجهة المسؤولة عن المتابعة', 'الموعد المتوقع للإنجاز']),
                section('رابعًا ـ المعوقات والإجراءات المتخذة'), section('خامسًا ـ خطة الفترة المقبلة'), section('سادسًا ـ المطلوب من الإدارة'),
            ]},
    };
    const names = {daily: 'اليومي', weekly: 'الأسبوعي', monthly: 'الشهري'};
    let chart, dirty = false, aiTarget = null, aiBusy = false;
    const status = (message, error = false) => { $('builder-status').textContent = message; $('builder-status').classList.toggle('error', error); };
    const node = (tag, text, className) => { const el = document.createElement(tag); if (text !== undefined) el.textContent = text; if (className) el.className = className; return el; };
    const button = (text, action, parent) => { const el = node('button', text); el.type = 'button'; el.addEventListener('click', action); parent.append(el); return el; };
    const changed = () => { dirty = true; preview(); };
    const input = (parent, label, value, update, type = 'text') => {
        const wrapper = node('label', label), el = node(type === 'textarea' ? 'textarea' : 'input');
        if (type !== 'textarea') el.type = type; else el.rows = 3;
        el.value = value ?? ''; el.setAttribute('aria-label', label);
        el.addEventListener('input', () => { update(el.value); changed(); }); wrapper.append(el); parent.append(wrapper); return el;
    };
    const contentFields = ['number', 'prepared_on', 'author', 'manager', 'attachments', 'accent', 'font', 'chart'];
    function syncFields() {
        ['title', 'division', 'frequency', 'starts_on', 'ends_on'].forEach(key => $('field-' + key).value = state[key]);
        contentFields.forEach(key => $('field-' + key).value = state.content[key] ?? '');
        document.querySelectorAll('[data-theme]').forEach(el => el.setAttribute('aria-pressed', String(el.dataset.theme === state.content.theme)));
    }
    ['title', 'division', 'frequency', 'starts_on', 'ends_on', ...contentFields].forEach(key => {
        $('field-' + key).addEventListener('input', event => {
            const oldTitle = `التقرير ${names[state.frequency]} لأعمال الشعبة / الوحدة`;
            (contentFields.includes(key) ? state.content : state)[key] = event.target.value;
            if (key === 'frequency' && state.title === oldTitle) { state.title = `التقرير ${names[state.frequency]} لأعمال الشعبة / الوحدة`; $('field-title').value = state.title; }
            changed();
        });
    });
    document.querySelectorAll('[data-theme]').forEach(el => el.addEventListener('click', () => { state.content.theme = el.dataset.theme; syncFields(); changed(); }));
    function renderSections() {
        const container = $('sections-editor'); container.replaceChildren();
        state.content.sections.forEach((s, index) => {
            const card = node('div', undefined, 'section-item');
            const toggle = node('label'), checkbox = node('input'); checkbox.type = 'checkbox'; checkbox.checked = s.enabled;
            checkbox.addEventListener('change', () => { s.enabled = checkbox.checked; changed(); }); toggle.append(checkbox, document.createTextNode('إظهار الفقرة')); card.append(toggle);
            input(card, 'عنوان الفقرة', s.title, value => s.title = value).maxLength = 255;
            input(card, 'نص الفقرة', s.body, value => s.body = value, 'textarea').maxLength = 12000;
            const actions = node('div', undefined, 'section-actions');
            button('↑', () => moveSection(index, -1), actions).setAttribute('aria-label', 'نقل الفقرة للأعلى');
            button('↓', () => moveSection(index, 1), actions).setAttribute('aria-label', 'نقل الفقرة للأسفل');
            button('حذف الفقرة', () => { if (state.content.sections.length === 1) return status('يجب إبقاء فقرة واحدة على الأقل.', true); state.content.sections.splice(index, 1); renderSections(); changed(); }, actions);
            const ai = button('اقتراح صياغة', () => assist(s), actions); ai.disabled = !config.aiEnabled || aiBusy;
            card.append(actions);
            if (s.columns.length) {
                const scroll = node('div', undefined, 'table-editor'), table = node('table'), head = node('tr');
                s.columns.forEach((column, i) => { const cell = node('th'); input(cell, `عنوان العمود ${i + 1}`, column, v => s.columns[i] = v).maxLength = 100; head.append(cell); }); table.append(head);
                s.rows.forEach((row, rowIndex) => {
                    const tr = node('tr'); s.columns.forEach((_, colIndex) => { const td = node('td'); input(td, `صف ${rowIndex + 1}، عمود ${colIndex + 1}`, row[colIndex], v => row[colIndex] = v).maxLength = 1000; tr.append(td); });
                    const td = node('td'); button('حذف', () => { s.rows.splice(rowIndex, 1); renderSections(); changed(); }, td); tr.append(td); table.append(tr);
                }); scroll.append(table); card.append(scroll);
                button('+ صف', () => { if (s.rows.length >= 100) return status('الحد الأقصى 100 صف لكل جدول.', true); s.rows.push(s.columns.map(() => '')); renderSections(); changed(); }, card);
                button('إزالة الجدول', () => { if (s.rows.length && !confirm('إزالة الجدول وبياناته؟')) return; s.columns = []; s.rows = []; renderSections(); changed(); }, card);
            } else button('+ جدول', () => { s.columns = ['العمل', 'المسؤول', 'الموعد', 'الملاحظات']; renderSections(); changed(); }, card);
            container.append(card);
        });
    }
    function moveSection(index, delta) {
        const next = index + delta; if (next < 0 || next >= state.content.sections.length) return;
        [state.content.sections[index], state.content.sections[next]] = [state.content.sections[next], state.content.sections[index]]; renderSections(); changed();
    }
    $('add-section').addEventListener('click', () => { if (state.content.sections.length >= 20) return status('الحد الأقصى 20 فقرة.', true); state.content.sections.push(section('فقرة إضافية')); renderSections(); changed(); });
    function renderMetrics() {
        $('metrics-editor').replaceChildren();
        state.content.metrics.forEach((metric, index) => {
            const card = node('div', undefined, 'metric-item');
            input(card, 'اسم المؤشر', metric.label, value => metric.label = value).maxLength = 100;
            const value = input(card, 'القيمة', metric.value, v => metric.value = v === '' ? '' : Number(v), 'number'); value.min = 0; value.max = 1e12; value.step = 'any';
            input(card, 'وحدة القياس', metric.unit, v => metric.unit = v).maxLength = 50;
            button('حذف المؤشر', () => { state.content.metrics.splice(index, 1); renderMetrics(); changed(); }, card); $('metrics-editor').append(card);
        });
    }
    $('add-metric').addEventListener('click', () => { if (state.content.metrics.length >= 12) return status('الحد الأقصى 12 مؤشرًا.', true); state.content.metrics.push({label: 'مؤشر جديد', value: 0, unit: ''}); renderMetrics(); changed(); });
    function preview() {
        const paper = $('report-preview'), c = state.content;
        if (chart) { chart.destroy(); chart = null; }
        paper.replaceChildren(); paper.className = `report-paper theme-${c.theme} font-${c.font}`; paper.style.setProperty('--accent', c.accent);
        const header = node('div', undefined, 'paper-header'); header.append(node('p', 'العتبة الحسينية المقدسة'), node('h2', 'مدينة الإمام الحسين (عليه السلام) للزائرين'), node('p', `الشعبة / الوحدة: ${state.division || '……………………'}`)); paper.append(header);
        const meta = node('div', undefined, 'paper-meta'); meta.append(node('span', `العدد: ${c.number || '…………'}`), node('span', `تاريخ الإعداد: ${c.prepared_on}`)); paper.append(meta);
        paper.append(node('p', 'إلى / السيد مدير مدينة الإمام الحسين (عليه السلام) للزائرين المحترم'), node('h1', `م / ${state.title}`, 'paper-title'), node('p', `الفترة المشمولة بالتقرير: من ${state.starts_on} إلى ${state.ends_on}`), node('p', 'تحية طيبة…'), node('p', `نرفع إلى جنابكم تقرير أعمال ${state.division || '[اسم الشعبة أو الوحدة]'} للفترة أعلاه، متضمنًا الأعمال المنجزة والأعمال قيد المتابعة والمعوقات والإجراءات المتخذة بشأنها، وكما يأتي.`));
        if (c.metrics.length) {
            const metrics = node('div', undefined, 'paper-metrics');
            c.metrics.forEach(m => { const box = node('div', undefined, 'paper-metric'); box.append(node('strong', Number(m.value || 0).toLocaleString('ar-IQ')), node('span', `${m.label} ${m.unit ? `(${m.unit})` : ''}`)); metrics.append(box); }); paper.append(metrics);
            if (c.chart !== 'none' && typeof Chart !== 'undefined') {
                const wrap = node('div', undefined, 'report-chart'), canvas = node('canvas'); canvas.setAttribute('role', 'img'); canvas.setAttribute('aria-label', 'مخطط المؤشرات، القيم مبيّنة في بطاقات المؤشرات أعلاه'); wrap.append(canvas); paper.append(wrap);
                chart = new Chart(canvas, {type: c.chart, data: {labels: c.metrics.map(m => m.label), datasets: [{label: 'القيمة', data: c.metrics.map(m => Number(m.value) || 0), backgroundColor: c.chart === 'bar' ? c.accent : [c.accent, '#b79a57', '#436b8a', '#8b5c79', '#718657', '#c88358']}]}, options: {animation: false, responsive: true, maintainAspectRatio: false, ...(c.chart === 'bar' ? {indexAxis: 'y', scales: {x: {beginAtZero: true}}} : {}), plugins: {legend: {display: c.chart !== 'bar', rtl: true}}}});
            }
        }
        c.sections.filter(s => s.enabled).forEach(s => {
            const block = node('section', undefined, 'paper-section'); block.append(node('h3', s.title)); if (s.body) block.append(node('p', s.body));
            if (s.columns.length) {
                const table = node('table'), head = node('thead'), tr = node('tr'); tr.append(node('th', 'ت')); s.columns.forEach(col => tr.append(node('th', col))); head.append(tr); table.append(head);
                const body = node('tbody'); s.rows.forEach((row, index) => { const line = node('tr'); line.append(node('td', String(index + 1))); s.columns.forEach((_, i) => line.append(node('td', row[i] || '—'))); body.append(line); }); table.append(body); block.append(table);
            } paper.append(block);
        });
        if (c.attachments) paper.append(node('p', `المرفقات: ${c.attachments}`));
        paper.append(node('p', 'مع فائق الاحترام والتقدير.'));
        const signatures = node('div', undefined, 'paper-signatures'); signatures.append(node('p', `إعداد التقرير: ${c.author}\nالتوقيع: ……………………`), node('p', `مسؤول الشعبة / الوحدة: ${c.manager || '……………………'}\nالتوقيع: ……………………`)); paper.append(signatures, node('div', 'مدينة الإمام الحسين (عليه السلام) للزائرين • ' + names[state.frequency], 'paper-footer'));
    }
    async function request(url, method, body) {
        const response = await fetch(url, {method, headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}, body: JSON.stringify(body)});
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.errors ? Object.values(data.errors).flat().join('\n') : data.message || 'تعذر تنفيذ الطلب. تحقق من الاتصال وصلاحية تسجيل الدخول.');
        return data;
    }
    $('save-report').addEventListener('click', async () => {
        const el = $('save-report'); el.disabled = true; status('جارٍ حفظ التقرير…');
        try { const result = await request(config.saveUrl, config.report ? 'PUT' : 'POST', state); dirty = false; status(result.message); if (result.url) window.location.assign(result.url); } catch (error) { status(error.message, true); } finally { el.disabled = false; }
    });
    $('print-report').addEventListener('click', () => { if (dirty) status('تُطبع المعاينة الحالية. احفظ التقرير للاحتفاظ بالتعديلات.'); window.print(); });
    window.addEventListener('beforeunload', event => { if (dirty) { event.preventDefault(); event.returnValue = ''; } });
    window.addEventListener('beforeprint', () => chart?.resize(600, 230));
    window.addEventListener('afterprint', () => chart?.resize());
    function validateTemplate(t) {
        if (!t || t.version !== 1 || !['heritage', 'modern', 'minimal'].includes(t.theme) || !/^#[0-9a-f]{6}$/i.test(t.accent) || !['cairo', 'serif', 'sans'].includes(t.font) || !['bar', 'doughnut', 'none'].includes(t.chart) || !Array.isArray(t.sections) || !t.sections.length || t.sections.length > 20) throw new Error('ملف القالب غير صالح.');
        t.sections.forEach(s => { if (!s || typeof s.title !== 'string' || !s.title.trim() || s.title.length > 255 || typeof s.enabled !== 'boolean' || !Array.isArray(s.columns) || s.columns.length > 6 || s.columns.some(v => typeof v !== 'string' || !v.trim() || v.length > 100)) throw new Error('بنية فقرات القالب غير صالحة.'); });
        return t;
    }
    function downloadTemplate(template, name = 'official-report-template') {
        validateTemplate(template);
        const url = URL.createObjectURL(new Blob([JSON.stringify(template, null, 2)], {type: 'application/json'}));
        const link = node('a'); link.href = url; link.download = name + '.json'; link.click(); setTimeout(() => URL.revokeObjectURL(url), 1000);
    }
    function applyLibraryTemplate(template) {
        validateTemplate(template);
        if ($('template-mode').value === 'full') {
            const hasData = state.content.sections.some(s => s.body.trim() || s.rows.length);
            if (hasData && !confirm('سيتم استبدال نصوص الفقرات وصفوف الجداول الحالية. هل تريد المتابعة؟')) return;
            state.content.sections = template.sections.map(s => section(s.title, [...s.columns]));
            state.content.sections.forEach((s, i) => s.enabled = template.sections[i].enabled);
        }
        ['theme', 'accent', 'font', 'chart'].forEach(k => state.content[k] = template[k]);
        aiTarget = null; $('ai-result').hidden = true;
        syncFields(); renderSections(); changed(); status('تم تطبيق القالب. احفظ التقرير للاحتفاظ به.');
    }
    function templateCard(item, parent) {
        const t = validateTemplate(item.template), card = node('div', undefined, 'template-card');
        card.append(node('h3', item.name), node('p', item.description || '', 'hint'));
        const sample = node('div', undefined, 'template-sample');
        sample.classList.add('sample-' + t.theme, 'font-' + t.font); sample.style.setProperty('--accent', t.accent);
        sample.append(node('strong', item.name));
        t.sections.forEach(s => sample.append(node('p', s.title + (s.columns.length ? ' ?' : ''))));
        card.append(sample);
        button('استخدام القالب', () => applyLibraryTemplate(t), card);
        button('تنزيل JSON', () => downloadTemplate(t), card);
        parent.append(card);
    }
    async function loadTemplates() {
        const el = $('refresh-templates'); el.disabled = true;
        $('template-status').textContent = 'جارٍ تحميل أحدث القوالب المنشورة…';
        try {
            const response = await fetch(config.templatesUrl, {cache: 'no-store', headers: {'Accept': 'application/json'}});
            if (!response.ok) throw new Error('تعذّر تحميل مكتبة القوالب. حاول تحديثها مجددًا.');
            const catalog = await response.json();
            if (!Array.isArray(catalog.templates) || catalog.templates.length > 100) throw new Error('مكتبة القوالب غير صالحة.');
            const cards = document.createDocumentFragment(); catalog.templates.forEach(item => templateCard(item, cards));
            $('template-library').replaceChildren(cards);
            $('template-status').textContent = 'آخر إصدار منشور: ' + catalog.updated_at;
        } catch (error) { $('template-status').textContent = error.message; }
        finally { el.disabled = false; }
    }
    $('refresh-templates').addEventListener('click', loadTemplates);
    $('generate-template').addEventListener('click', async () => {
        const prompt = $('template-prompt').value.trim();
        if (!prompt) return status('اكتب وصف القالب المطلوب أولًا.', true);
        const el = $('generate-template'); el.disabled = true; status('جارٍ إنشاء القالب…');
        try {
            const result = await request(config.generateTemplateUrl, 'POST', {prompt});
            const cards = document.createDocumentFragment();
            templateCard({name: 'قالب مقترح بالذكاء الاصطناعي', description: 'راجع العناوين والجداول قبل التطبيق.', template: result.template}, cards);
            $('generated-template').replaceChildren(cards); status('القالب جاهز للمراجعة. لم تتغير بيانات تقريرك.');
        } catch (error) { status(error.message, true); }
        finally { el.disabled = false; }
    });
    loadTemplates();
    $('export-template').addEventListener('click', () => {
        const c = state.content, template = {version: 1, theme: c.theme, accent: c.accent, font: c.font, chart: c.chart, sections: c.sections.map(s => ({title: s.title, enabled: s.enabled, columns: s.columns}))};
        const url = URL.createObjectURL(new Blob([JSON.stringify(template, null, 2)], {type: 'application/json'})), link = node('a'); link.href = url; link.download = 'official-report-template.json'; link.click(); setTimeout(() => URL.revokeObjectURL(url), 1000);
    });
    $('import-template').addEventListener('change', async event => {
        try {
            const file = event.target.files[0]; if (!file) return; if (file.size > 100000) throw new Error('حجم القالب يجب ألا يتجاوز 100 كيلوبايت.');
            const t = JSON.parse(await file.text());
            if (t.version !== 1 || !['heritage', 'modern', 'minimal'].includes(t.theme) || !/^#[0-9a-f]{6}$/i.test(t.accent) || !['cairo', 'serif', 'sans'].includes(t.font) || !['bar', 'doughnut', 'none'].includes(t.chart) || !Array.isArray(t.sections) || !t.sections.length || t.sections.length > 20) throw new Error('ملف القالب غير صالح.');
            t.sections.forEach(s => { if (typeof s.title !== 'string' || !s.title.trim() || s.title.length > 255 || typeof s.enabled !== 'boolean' || !Array.isArray(s.columns) || s.columns.length > 6 || s.columns.some(v => typeof v !== 'string' || !v.trim() || v.length > 100)) throw new Error('بنية فقرات القالب غير صالحة.'); });
            if (!confirm('تطبيق التصميم فقط؟ اضغط موافق للاحتفاظ بالفقرات والبيانات الحالية.')) {
                if (!confirm('هل تريد استبدال الفقرات والجداول بالقالب؟ ستُحذف نصوص الفقرات وصفوفها الحالية.')) return;
                state.content.sections = t.sections.map(s => ({title: s.title, enabled: s.enabled, columns: [...s.columns], body: '', rows: []}));
            }
            ['theme', 'accent', 'font', 'chart'].forEach(k => state.content[k] = t[k]); syncFields(); renderSections(); changed(); status('تم تطبيق القالب. احفظ التقرير للاحتفاظ به.');
        } catch (error) { status(error instanceof SyntaxError ? 'تعذر قراءة JSON. اختر قالبًا صالحًا.' : error.message, true); } finally { event.target.value = ''; }
    });
    async function assist(s) {
        if (aiBusy) return;
        if (!s.body.trim()) return status('أدخل نص الفقرة أولًا ليعيد المساعد صياغته.', true);
        if (!$('ai-instruction').value.trim()) return status('أدخل تعليمات الصياغة.', true);
        aiBusy = true; renderSections(); status('جارٍ طلب اقتراح الصياغة…');
        try { const result = await request(config.assistUrl, 'POST', {text: s.body, instruction: $('ai-instruction').value}); aiTarget = s; $('ai-suggestion').value = result.text; $('ai-result').hidden = false; $('ai-result').scrollIntoView({behavior: 'smooth', block: 'center'}); status('الاقتراح جاهز للمراجعة. لم تتغير فقرتك بعد.'); } catch (error) { status(error.message, true); } finally { aiBusy = false; renderSections(); }
    }
    $('accept-ai').addEventListener('click', () => { if (aiTarget && state.content.sections.includes(aiTarget)) { aiTarget.body = $('ai-suggestion').value; renderSections(); changed(); } else status('الفقرة الأصلية حُذفت. يمكنك نسخ الاقتراح إلى فقرة أخرى.', true); $('ai-result').hidden = true; aiTarget = null; });
    $('dismiss-ai').addEventListener('click', () => { $('ai-result').hidden = true; aiTarget = null; });
    syncFields(); renderSections(); renderMetrics(); preview();
})();
