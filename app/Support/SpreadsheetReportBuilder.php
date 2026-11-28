<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SpreadsheetReportBuilder
{
    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['files' => $message]);
    }

    public function build(array $files, string $domain = 'auto', int $headerRow = 1): array
    {
        $sections = [];
        $charts = [];
        $sources = [];
        $warnings = [];
        $records = 0;
        $sheetCount = 0;
        $detected = [];
        foreach ($files as $file) {
            $path = $file->getRealPath();
            $type = strtolower($file->getClientOriginalExtension()) === 'xlsx' ? 'Xlsx' : 'Xls';
            if (! SecureUploadValidator::isAllowed($file, 'xlsx,xls')) {
                $this->fail('اختر ملف Excel حقيقيًا بصيغة XLSX أو XLS.');
            }
            if ($type === 'Xlsx') {
                $this->checkArchive($path);
            }
            $reader = IOFactory::createReader($type);
            // Keep date formats, but never calculate formulas or load embedded charts.
            $reader->setReadEmptyCells(false);
            $reader->setIncludeCharts(false);
            try {
                $info = $reader->listWorksheetInfo($path);

            } catch (ValidationException $exception) {
                throw $exception;
            } catch (\Throwable $exception) {
                $this->fail('تعذرت قراءة ملف Excel. تأكد أنه سليم وغير محمي بكلمة مرور.');
            }
            try {
                foreach ($info as $sheetInfo) {
                    $sheetCount++;
                    $name = mb_substr($file->getClientOriginalName().' / '.$sheetInfo['worksheetName'], 0, 150);
                    $rows = [];
                    $columns = null;
                    $formulaMissing = false;
                    foreach ($this->readRows($reader, $path, $sheetInfo, $headerRow, $formulaMissing) as $row) {
                        if ($columns === null) {
                            $columns = array_map(fn ($v, $i) => mb_substr($v !== '' ? $v : 'عمود '.($i + 1), 0, 100), $row, array_keys($row));
                        } else {
                            $rows[] = $row;
                        }
                    }
                    if (! $columns || ! $rows) {
                        $warnings[] = 'لم تُستورد الورقة '.$name.' لأنها لا تحتوي سجلات بعد صف العناوين.';
                        continue;
                    }
                    if ($formulaMissing) {
                        $warnings[] = 'توجد صيغ دون نتائج محفوظة في '.$name.'. تظهر فارغة؛ أعد حساب الملف وحفظه في Excel للحصول على نتائجها.';
                    }
                    $records += count($rows);
                    $kind = $domain === 'auto' ? $this->detect(implode(' ', $columns).' '.$name) : $domain;
                    $detected[] = $kind;
                    $sources[] = ['name' => $name, 'rows' => count($rows), 'columns' => count($columns), 'domain' => $kind];
                    $analysisRows = array_values(array_filter($rows, function ($row) {
                        foreach (array_slice($row, 0, 2) as $label) {
                            if (preg_match('/^(?:الإجمالي|الاجمالي|إجمالي|اجمالي|المجموع|مجموع|grand total|total)(?:\s|:|$)/iu', trim($label))) {
                                return false;
                            }
                        }
                        return true;
                    }));
                    $numeric = $this->numericSummary($columns, $analysisRows, $name);
                    $sheetNotes = 'تعتمد الرسوم والإحصاءات على صفوف البيانات، مع استبعاد صفوف الإجمالي المعنونة في أول عمودين لتجنب العد المكرر. لا تُحسب الصيغ مجددًا؛ تُقرأ نتائجها المحفوظة في الملف فقط.';
                    if ($numeric['notes']) {
                        $sheetNotes .= "\n".implode("\n", $numeric['notes']);
                    }
                    foreach (array_chunk($rows, 100) as $page => $chunk) {
                        foreach (array_chunk($columns, 6) as $part => $headers) {
                            $sections[] = ['title' => $name.' — صفوف '.($page * 100 + 1).'–'.($page * 100 + count($chunk)).' / أعمدة '.($part * 6 + 1).'–'.($part * 6 + count($headers)),
                                'body' => $page === 0 && $part === 0 ? $sheetNotes : '', 'enabled' => true, 'columns' => $headers,
                                'rows' => array_map(fn ($row) => array_slice($row, $part * 6, count($headers)), $chunk)];
                        }
                    }
                    $charts = array_merge($charts, $this->charts($columns, $analysisRows, $name), $numeric['charts']);
                }
            } catch (ValidationException $exception) {
                throw $exception;
            } catch (\Throwable $exception) {
                $this->fail('تعذرت قراءة بيانات Excel. تحقق من سلامة الملف وموارد الخادم.');
            }
        }
        if (! $sources) {
            $this->fail('لم نجد بيانات قابلة للاستيراد. اختر رقم صف عناوين الأعمدة الصحيح وتأكد من وجود سجلات تحته.');
        }
        $labels = ['tasks' => 'المهام والمتابعة', 'hr' => 'الموارد البشرية', 'maintenance' => 'صيانة الأجهزة', 'general' => 'البيانات'];
        $kind = count(array_unique($detected)) === 1 ? $detected[0] : 'general';
        $colors = ['tasks' => '#2563a6', 'hr' => '#7c3e95', 'maintenance' => '#b45b20', 'general' => '#176354'];
        $summary = 'تم تحليل '.count($files).' ملف Excel، تتضمن '.count($sources).' ورقة بيانات و'.$records.' سجلًا. يمثل كل صف غير فارغ بعد العناوين سجلًا، وقد تشمل البيانات صفوف إجمالي من الملف الأصلي. الأرقام والمخططات مستخرجة من البيانات كما وردت دون افتراض إنجاز أو غياب أو أعطال غير مسجلة.';
        foreach ($sources as $source) {
            $summary .= "\n".$source['name'].': '.$source['rows'].' سجلًا، '.$source['columns'].' عمودًا.';
        }
        $summary .= "\n".implode("\n", $warnings);
        array_unshift($sections, ['title' => 'ملخص الملفات وملاحظات القراءة', 'body' => $summary, 'enabled' => true, 'columns' => [], 'rows' => []]);
        return ['title' => 'تقرير '.$labels[$kind].' من ملفات Excel', 'theme' => 'modern', 'accent' => $colors[$kind], 'font' => 'cairo', 'chart' => 'none',
            'metrics' => [['label' => 'الملفات', 'value' => count($files), 'unit' => 'ملف'], ['label' => 'أوراق البيانات', 'value' => count($sources), 'unit' => 'ورقة'], ['label' => 'السجلات', 'value' => $records, 'unit' => 'سجل']],
            'sections' => $sections, 'charts' => $charts, 'warnings' => $warnings,
            // Only aggregates are supplied to the optional AI, never source rows.
            'analysis' => ['records' => $records, 'sheets' => count($sources), 'domain' => $labels[$kind], 'charts' => $charts]];
    }

    private function readRows($reader, string $path, array $info, int $headerRow, bool &$formulaMissing): \Generator
    {
        $reader->setLoadSheetsOnly([$info['worksheetName']]);
        // Batch size controls working memory; it is not a limit on imported rows.
        for ($start = $headerRow; $start <= $info['totalRows']; $start += 1000) {
            $end = min($start + 999, $info['totalRows']);
            $reader->setReadFilter(new class($start, $end) implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
                public function __construct(private int $start, private int $end) {}
                public function readCell($columnAddress, $row, $worksheetName = '')
                {
                    return $row >= $this->start && $row <= $this->end;
                }
            });
            $workbook = $reader->load($path);
            try {
                $sheet = $workbook->getSheetByName($info['worksheetName']);
                for ($r = $start; $r <= $end; $r++) {
                    $row = [];
                    for ($c = 1; $c <= $info['totalColumns']; $c++) {
                        $address = Coordinate::stringFromColumnIndex($c).$r;
                        if (! $sheet->cellExists($address)) {
                            $row[] = '';
                            continue;
                        }
                        $cell = $sheet->getCell($address);
                        $value = $cell->getValue();
                        if ($cell->isFormula()) {
                            $value = $cell->getOldCalculatedValue();
                            $formulaMissing = $formulaMissing || $value === null;
                        }
                        if ($value instanceof RichText) {
                            $value = $value->getPlainText();
                        }
                        if (is_bool($value)) {
                            $value = $value ? 'TRUE' : 'FALSE';
                        }
                        if (is_numeric($value) && Date::isDateTime($cell)) {
                            $value = Date::excelToDateTimeObject((float) $value)->format('Y-m-d H:i');
                        }
                        $row[] = trim((string) $value);
                    }
                    if (count(array_filter($row, fn ($value) => $value !== '')) > 0) {
                        yield $row;
                    }
                }
            } finally {
                $workbook->disconnectWorksheets();
                unset($workbook);
            }
        }
    }

    private function detect(string $text): string
    {
        $text = mb_strtolower($text);
        foreach (['maintenance' => '/صيان|عطل|أعطال|اعطال|جهاز|أجهزة|اجهزة|maintenance|device|repair/u', 'hr' => '/موظف|موظفين|منتسب|راتب|رواتب|حضور|غياب|إجاز|اجاز|employee|salary|attendance|human resources/u', 'tasks' => '/مهم|مهام|إنجاز|انجاز|task|progress/u'] as $kind => $pattern) {
            if (preg_match($pattern, $text)) {
                return $kind;
            }
        }
        return 'general';
    }

    private function charts(array $columns, array $rows, string $name): array
    {
        if (! $rows) {
            return [];
        }
        $charts = [];
        foreach ($columns as $i => $header) {
            // Group only operational categories, avoiding employee names and IDs.
            if (! preg_match('/حالة|الحضور|القسم|الشعبة|الوحدة|النوع|التصنيف|status|department|category|type|priority|الأولوية/iu', $header)
                || preg_match('/اسم|رقم|تاريخ|name|number|date|id\b/iu', $header)) {
                continue;
            }
            $counts = [];
            foreach ($rows as $row) {
                $label = mb_substr($row[$i] !== '' ? $row[$i] : 'غير محدد', 0, 100);
                $counts[$label] = ($counts[$label] ?? 0) + 1;
            }
            arsort($counts);
            if (count($counts) > 10) {
                $other = array_sum(array_slice($counts, 9));
                $counts = array_slice($counts, 0, 9, true);
                $counts['فئات أخرى (مجمعة)'] = ($counts['فئات أخرى (مجمعة)'] ?? 0) + $other;
            }
            $charts[] = ['title' => mb_substr($name.' — التوزيع حسب '.$header, 0, 255), 'type' => count($counts) <= 5 ? 'doughnut' : 'bar',
                'unit' => 'سجل', 'labels' => array_map('strval', array_keys($counts)), 'values' => array_values($counts)];
        }
        if (! $charts) {
            foreach (array_chunk(array_keys($columns), 12) as $part => $indices) {
                $charts[] = ['title' => mb_substr($name.' — اكتمال البيانات حسب العمود / '.($part + 1), 0, 255), 'type' => 'bar', 'unit' => 'خلية غير فارغة',
                    'labels' => array_map(fn ($i) => $columns[$i], $indices), 'values' => array_map(fn ($i) => count(array_filter($rows, fn ($row) => $row[$i] !== '')), $indices)];
            }
        }
        return $charts;
    }

    private function numericSummary(array $columns, array $rows, string $name): array
    {
        $notes = [];
        $charts = [];
        foreach ($columns as $i => $header) {
            if (! preg_match('/عدد|كمية|تكلفة|كلفة|مبلغ|راتب|رواتب|ساعات|مدة|quantity|cost|amount|salary|hours|duration/iu', $header)
                || preg_match('/رقم|معرف|تاريخ|نسبة|متوسط|number|\bid\b|date|percent|average|%/iu', $header)) {
                continue;
            }
            $numbers = [];
            foreach ($rows as $row) {
                $value = strtr($row[$i], ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9', '٫' => '.', '٬' => '']);
                if (preg_match('/^[+-]?\d{1,3}(?:,\d{3})+(?:\.\d+)?$/', $value)) {
                    $value = str_replace(',', '', $value);
                }
                if (is_numeric($value) && is_finite((float) $value)) {
                    $numbers[] = (float) $value;
                }
            }
            if (! $numbers) {
                continue;
            }
            $sum = array_sum($numbers);
            $notes[] = $header.': مجموع '.round($sum, 2).'، أدنى '.min($numbers).'، أعلى '.max($numbers).' ('.count($numbers).' خلية رقمية من '.count($rows).' سجلًا). تُجمع القيم كما وردت؛ تحقق من وحدة القياس وتجانسها.';
            if (min($numbers) >= 0 && $sum <= 1e12) {
                $charts[] = ['title' => mb_substr($name.' — مجموع '.$header, 0, 255), 'type' => 'bar', 'unit' => mb_substr($header, 0, 50), 'labels' => [mb_substr($header, 0, 100)], 'values' => [$sum]];
            }
        }
        return compact('notes', 'charts');
    }

    private function checkArchive(string $path): void
    {
        $zip = new \ZipArchive;
        if ($zip->open($path) !== true) {
            $this->fail('ملف XLSX غير صالح.');
        }
        try {
            if ($zip->locateName('[Content_Types].xml') === false || $zip->locateName('xl/workbook.xml') === false) {
                $this->fail('ملف XLSX لا يحتوي بنية مصنف Excel صحيحة.');
            }
        } finally {
            $zip->close();
        }
    }
}
