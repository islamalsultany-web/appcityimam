<section class="control-card excel-import-card" data-workflow="source">
    <h2>تقرير تلقائي من ملفات Excel</h2>
    <p class="hint">ارفع ملفات المهام أو الموارد البشرية أو الصيانة، وسنقرأ جميع أوراقها وننشئ ملخصًا وجداول ورسومًا وألوانًا مناسبة. يمكنك مراجعة النتيجة وتعديلها قبل الحفظ أو الطباعة.</p>
    <label>ملفات Excel
        <input type="file" id="excel-files" multiple accept=".xlsx,.xls">
    </label>
    <p class="hint">لا يفرض البرنامج حدًا ثابتًا لعدد الملفات أو حجمها أو عدد الأوراق والصفوف والأعمدة. تُرفع الملفات على أجزاء، وتُنظّم الجداول على أجزاء للطباعة دون حذف بيانات. تعتمد السعة الفعلية على موارد الخادم والمتصفح.</p>
    <div class="field-grid">
        <label>نوع التقرير<select id="excel-domain">
            <option value="auto">تحديد تلقائي</option><option value="tasks">مهام ومتابعة</option><option value="hr">موارد بشرية</option><option value="maintenance">صيانة أجهزة</option><option value="general">بيانات عامة</option>
        </select></label>
        <label>رقم صف عناوين الأعمدة<input id="excel-header-row" type="number" min="1" value="1"></label>
    </div>
    <label class="excel-ai-option"><input id="excel-use-ai" type="checkbox" {{ $aiEnabled ? '' : 'disabled' }}> إضافة تحليل وتوصيات بالذكاء الاصطناعي</label>
    <p class="hint">{{ $aiEnabled ? 'عند تفعيل هذا الخيار تُرسل الإحصاءات وعناوين الرسوم وقيم الفئات إلى مزود الذكاء الاصطناعي المهيأ، دون صفوف الجداول الأصلية. راجع النتائج قبل اعتمادها.' : 'التحليل المحلي متاح الآن. لتفعيل الذكاء الاصطناعي اضبط إعدادات REPORT_AI على الخادم.' }}</p>
    <label>توجيهات التحليل الاختيارية<textarea id="excel-instruction" rows="2" maxlength="1000" placeholder="مثال: ركّز على توزيع حالات المهام ونقاط المتابعة"></textarea></label>
    <button type="button" id="analyze-excel" class="primary">قراءة الملفات وإنشاء التقرير</button>
    <button type="button" id="cancel-excel" hidden>إلغاء العملية</button>
    <progress id="excel-progress" max="100" value="0" aria-label="تقدم رفع ملفات Excel" hidden></progress>
    <p id="excel-status" role="status" aria-live="polite"></p>
    <div id="excel-result" hidden>
        <p class="hint">النتيجة جاهزة. تطبيقها يستبدل فقرات التقرير ومؤشراته ورسومه وتصميمه، مع الاحتفاظ ببيانات المُعدّ والوحدة والتواريخ.</p>
        <button type="button" id="apply-excel">تطبيق النتيجة على التقرير</button>
    </div>
</section>
<section class="control-card" data-workflow="charts"><h2>رسوم بيانات Excel</h2><div id="excel-chart-editor"></div></section>
