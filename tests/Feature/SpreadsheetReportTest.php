<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\OfficialReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class SpreadsheetReportTest extends TestCase
{
    use RefreshDatabase;

    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            @unlink($path);
        }
        parent::tearDown();
    }

    private function excel(array $sheets, string $type = 'Xlsx'): UploadedFile
    {
        $book = new Spreadsheet;
        foreach ($sheets as $i => $data) {
            $sheet = $i === 0 ? $book->getActiveSheet() : $book->createSheet();
            $sheet->setTitle('Sheet '.($i + 1));
            $sheet->fromArray($data, null, 'A1', true);
        }
        $path = tempnam(sys_get_temp_dir(), 'report-excel-');
        $this->tempFiles[] = $path;
        $writer = IOFactory::createWriter($book, $type);
        $writer->setPreCalculateFormulas(false);
        $writer->save($path);
        $book->disconnectWorksheets();

        return new UploadedFile($path, 'report.'.strtolower($type), null, null, true);
    }

    private function login(): AppUser
    {
        $user = AppUser::factory()->create(['role' => 'asker', 'must_change_credentials' => false]);
        $this->actingAs($user);

        return $user;
    }

    private function analyze(array $files, array $extra = [])
    {
        return $this->post(route('official-reports.import-excel'), array_merge([
            'files' => $files, 'domain' => 'auto', 'header_row' => 1, 'use_ai' => '0',
        ], $extra), ['Accept' => 'application/json']);
    }

    public function test_multiple_excel_files_are_analyzed_and_result_can_be_saved_and_reopened(): void
    {
        Http::preventStrayRequests();
        $user = $this->login();
        $file = $this->excel([[['المهمة', 'الحالة', 'المسؤول'], ['مهمة أ', 'منجزة', 'أحمد'], ['مهمة ب', 'قيد التنفيذ', 'سارة']], [['المهمة', 'الحالة'], ['مهمة ج', 'منجزة']]]);
        $other = $this->excel([[['المهمة', 'الحالة'], ['مهمة د', 'مؤجلة']]], 'Xls');
        $response = $this->analyze([$file, $other])->assertOk()->assertJsonPath('ai_used', false)
            ->assertJsonPath('metrics.0.value', 2)->assertJsonPath('metrics.1.value', 3)->assertJsonPath('metrics.2.value', 4)
            ->assertJsonPath('accent', '#2563a6')->assertJsonPath('charts.0.values', [1, 1]);
        $data = $response->json();
        $content = array_intersect_key($data, array_flip(['theme', 'accent', 'font', 'chart', 'sections', 'charts', 'metrics']));
        $content += ['number' => '', 'prepared_on' => '2026-11-28', 'author' => $user->username, 'manager' => '', 'attachments' => ''];
        $this->postJson(route('official-reports.store'), ['title' => $data['title'], 'division' => 'الوحدة', 'frequency' => 'daily', 'starts_on' => '2026-11-28', 'ends_on' => '2026-11-28', 'content' => $content])->assertCreated();
        $report = OfficialReport::firstOrFail();
        $this->assertSame('أحمد', $report->content['sections'][1]['rows'][0][2]);
        $this->assertSame($data['charts'], $report->content['charts']);
        $this->get(route('official-reports.edit', $report))->assertOk()->assertSee('excel-files')->assertSee('excel-import-config');
        Http::assertNothingSent();
    }

    public function test_large_tables_are_split_without_losing_rows_or_columns(): void
    {
        $this->login();
        $rows = [['المهمة', 'الحالة', 'أ', 'ب', 'ج', 'د', 'هـ']];
        for ($i = 1; $i <= 101; $i++) {
            $rows[] = ['مهمة '.$i, 'مفتوحة', 'a', 'b', 'c', 'd', 'last-'.$i];
        }
        $data = $this->analyze([$this->excel([$rows])])->assertOk()->assertJsonPath('metrics.2.value', 101)->json();
        $this->assertCount(5, $data['sections']);
        $this->assertSame('last-101', $data['sections'][4]['rows'][0][0]);
        $this->assertSame(101, array_sum($data['charts'][0]['values']));
    }

    public function test_header_row_domain_override_and_fallback_chart(): void
    {
        $this->login();
        $file = $this->excel([[['عنوان الملف'], [], ['اسم الجهاز', 'الملاحظة'], ['حاسوب', 'جيد']]]);
        $this->analyze([$file], ['header_row' => 3, 'domain' => 'maintenance'])->assertOk()
            ->assertJsonPath('accent', '#b45b20')->assertJsonPath('charts.0.unit', 'خلية غير فارغة')
            ->assertJsonPath('sections.1.columns', ['اسم الجهاز', 'الملاحظة'])->assertJsonPath('metrics.2.value', 1);
    }

    public function test_ai_receives_aggregates_only_and_failure_keeps_local_report(): void
    {
        $this->login();
        config(['reporting.ai.key' => 'key', 'reporting.ai.model' => 'model', 'reporting.ai.endpoint' => 'https://ai.example.test/chat']);
        Http::fakeSequence('ai.example.test/*')->push([
            'choices' => [['message' => ['content' => 'تحليل مقترح وفق الإحصاءات.']]],
        ])->push([], 500);
        $rows = [[['اسم الموظف', 'الحالة'], ['PRIVATE_EMPLOYEE', 'حاضر']]];
        $this->analyze([$this->excel($rows)], ['use_ai' => '1'])->assertOk()->assertJsonPath('ai_used', true)
            ->assertJsonPath('sections.2.body', 'تحليل مقترح وفق الإحصاءات.');
        Http::assertSent(fn ($request) => ! str_contains($request->body(), 'PRIVATE_EMPLOYEE') && $request['model'] === 'model');
        $response = $this->analyze([$this->excel($rows)], ['use_ai' => '1'])->assertOk()->assertJsonPath('ai_used', false);
        $this->assertNotEmpty($response->json('warnings'));
        $this->assertDatabaseCount('official_reports', 0);
    }

    public function test_invalid_empty_and_over_limit_workbooks_are_rejected(): void
    {
        $this->login();
        $this->analyze([UploadedFile::fake()->createWithContent('fake.xlsx', '<html>not excel</html>')])->assertUnprocessable();
        $this->analyze([$this->excel([[['المهمة']]])])->assertUnprocessable();
        $this->analyze([$this->excel([array_fill(0, 1000, array_fill(0, 24, 'value'))])])->assertUnprocessable();
        $this->analyze([$this->excel([[['المهمة'], ['أ']]])], ['header_row' => 31])->assertUnprocessable();
        $this->assertDatabaseCount('official_reports', 0);
    }

    public function test_import_allows_guests_and_checks_signed_in_user_credentials(): void
    {
        $file = $this->excel([[['الحالة'], ['مفتوحة']]]);
        $this->analyze([$file])->assertOk();
        $user = $this->login();
        $user->update(['must_change_credentials' => true]);
        $this->analyze([$file])->assertRedirect(route('user.credentials.setup'));
    }

    public function test_numeric_summary_excludes_totals_and_does_not_sum_identifiers(): void
    {
        $this->login();
        $data = $this->analyze([$this->excel([[['الجهاز', 'الحالة', 'تكلفة الصيانة', 'رقم الجهاز'],
            ['أ', 'يعمل', 100, 1001], ['ب', 'عاطل', 200, 1002], ['الإجمالي', '', 300, ''],
        ]])])->assertOk()->json();
        $this->assertSame([300], $data['charts'][1]['values']);
        $this->assertSame(2, array_sum($data['charts'][0]['values']));
        $this->assertStringContainsString('تكلفة الصيانة: مجموع 300', $data['sections'][1]['body']);
        $this->assertStringNotContainsString('رقم الجهاز: مجموع', $data['sections'][1]['body']);
        $this->assertCount(3, $data['sections'][1]['rows']);
    }

    public function test_formulas_are_not_executed_and_missing_results_are_reported(): void
    {
        $this->login();
        $file = $this->excel([[['الجهاز', 'الحالة', 'التكلفة'], ['أ', 'عاطل', '=1+2']]]);
        $this->analyze([$file])->assertOk()->assertJsonPath('sections.1.rows.0.2', '0');
        $zip = new \ZipArchive;
        $zip->open($file->getRealPath());
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $xml = preg_replace('/(<f[^>]*>.*?<\/f>)<v>.*?<\/v>/s', '$1', $xml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $xml);
        $zip->close();
        $response = $this->analyze([$file])->assertOk();
        $this->assertNotEmpty($response->json('warnings'));
        $this->assertSame('', $response->json('sections.1.rows.0.2'));
    }
}
