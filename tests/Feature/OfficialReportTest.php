<?php

namespace Tests\Feature;

use App\Models\OfficialReport;
use App\Models\AppUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OfficialReportTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $email = 'head@example.com', string $role = 'asker'): AppUser
    {
        return AppUser::factory()->create(['username' => $email, 'password' => bcrypt('SecurePass#2026'), 'role' => $role, 'must_change_credentials' => false]);
    }

    public function test_ai_can_generate_a_compatible_template_without_saving_a_report(): void
    {
        config(['reporting.ai.key' => 'test-key', 'reporting.ai.model' => 'test-model', 'reporting.ai.endpoint' => 'https://example.test/chat']);
        $template = json_decode(file_get_contents(public_path('report-templates/catalog.json')), true)['templates'][0]['template'];
        Http::fake(['example.test/*' => Http::response(['choices' => [['message' => ['content' => json_encode($template)]]]])]);
        $this->actingAs($this->user())->postJson(route('official-reports.generate-template'), ['prompt' => 'تقرير صيانة'])
            ->assertOk()->assertJsonPath('template.version', 1)->assertJsonPath('template.sections.0.enabled', true);
        $this->assertDatabaseCount('official_reports', 0);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer test-key') && $request['model'] === 'test-model');
    }

    public function test_ai_rejects_invalid_templates_and_handles_provider_failure(): void
    {
        config(['reporting.ai.key' => 'test-key', 'reporting.ai.model' => 'test-model', 'reporting.ai.endpoint' => 'https://example.test/chat']);
        $this->actingAs($this->user());
        Http::fake(['example.test/*' => Http::response(['choices' => [['message' => ['content' => '{"version":1,"theme":"external"}']]]])]);
        $this->postJson(route('official-reports.generate-template'), ['prompt' => 'تقرير'])->assertStatus(502);
        Http::fake(['example.test/*' => Http::response([], 401)]);
        $this->postJson(route('official-reports.generate-template'), ['prompt' => 'تقرير'])->assertStatus(502);
        config(['reporting.ai.key' => null]);
        $this->postJson(route('official-reports.generate-template'), ['prompt' => 'تقرير'])->assertStatus(503);
    }

    private function payload(): array
    {
        return ['title' => 'التقرير الأسبوعي', 'division' => 'الاتصالات', 'frequency' => 'weekly',
            'starts_on' => '2026-11-20', 'ends_on' => '2026-11-26', 'content' => [
                'theme' => 'heritage', 'accent' => '#176354', 'font' => 'cairo', 'chart' => 'bar',
                'number' => '42', 'prepared_on' => '2026-11-26', 'author' => 'مسؤول الوحدة', 'manager' => '', 'attachments' => '',
                'metrics' => [['label' => 'أعطال معالجة', 'value' => 12, 'unit' => 'عطل']],
                'sections' => [['title' => 'الأعمال المنجزة', 'body' => 'تمت معالجة 12 عطلًا.', 'enabled' => true,
                    'columns' => ['العمل', 'العدد'], 'rows' => [['صيانة', '12']]]],
            ]];
    }

    public function test_guests_can_manage_their_session_reports_without_exposing_other_reports(): void
    {
        $owner = $this->user();
        $private = new OfficialReport($this->payload());
        $private->user_id = $owner->id;
        $private->title = 'PRIVATE_ACCOUNT_REPORT';
        $private->save();
        $this->get(route('official-reports.index'))->assertOk()->assertDontSee('PRIVATE_ACCOUNT_REPORT');
        $this->get(route('official-reports.create'))->assertOk()->assertSee('الدخول المباشر متاح')->assertSee('excel-files');
        $this->postJson(route('official-reports.store'), $this->payload() + ['user_id' => $owner->id, 'guest_key' => 'forged'])->assertCreated();
        $report = OfficialReport::whereNull('user_id')->firstOrFail();
        $token = session('official_reports_guest_token');
        $this->assertNotEmpty($token);
        $this->assertSame(hash('sha256', $token), $report->guest_key);
        $this->assertArrayNotHasKey('guest_key', $report->toArray());
        $this->get(route('official-reports.edit', $report))->assertOk();
        $this->get(route('official-reports.index'))->assertSee($report->title)->assertDontSee('PRIVATE_ACCOUNT_REPORT');
        $this->get(route('official-reports.edit', $private))->assertForbidden();
        $this->delete(route('official-reports.destroy', $private))->assertForbidden();
        $this->withSession(['official_reports_guest_token' => 'another-visitor']);
        $this->get(route('official-reports.index'))->assertOk()->assertDontSee($report->title);
        $this->get(route('official-reports.edit', $report))->assertForbidden();
        $this->putJson(route('official-reports.update', $report), $this->payload())->assertForbidden();
        $this->delete(route('official-reports.destroy', $report))->assertForbidden();
        $this->withSession(['official_reports_guest_token' => $token]);
        $payload = $this->payload();
        $payload['title'] = 'UPDATED_GUEST_REPORT';
        $this->putJson(route('official-reports.update', $report), $payload)->assertOk();
        $this->assertSame('UPDATED_GUEST_REPORT', $report->fresh()->title);
        $this->delete(route('official-reports.destroy', $report))->assertRedirect(route('official-reports.index'));
        $this->assertDatabaseHas('official_reports', ['id' => $private->id]);
    }

    public function test_portal_contains_program_and_legacy_session_can_create_report(): void
    {
        $user = $this->user();
        $this->get(route('home'))->assertOk()->assertSee('برنامج التقارير الرسمية');
        $this->withSession(['auth_app_user_id' => $user->id])
            ->get(route('official-reports.create'))->assertOk()->assertSee($user->username);
        $this->postJson(route('official-reports.store'), $this->payload())->assertCreated();
        $this->assertSame($user->id, OfficialReport::firstOrFail()->user_id);
    }

    public function test_report_routes_keep_portal_security_requirements(): void
    {
        $user = $this->user();
        $user->update(['must_change_credentials' => true]);
        $this->actingAs($user)->get(route('official-reports.create'))
            ->assertRedirect(route('user.credentials.setup'));
        $this->postJson(route('official-reports.store'), $this->payload())
            ->assertRedirect(route('user.credentials.setup'));
        $this->assertDatabaseCount('official_reports', 0);

        config(['security.admin_two_factor.enabled' => true]);
        $admin = $this->user('admin-security@example.com', 'admin');
        $this->actingAs($admin)->get(route('official-reports.index'))
            ->assertRedirect(route('user.two-factor.setup'));
    }

    public function test_report_library_filters_only_accessible_reports_and_editor_has_review_tools(): void
    {
        $user = $this->user();
        foreach ([['تقرير الصيانة', 'monthly'], ['تقرير المتابعة', 'daily']] as [$title, $frequency]) {
            $report = new OfficialReport($this->payload());
            $report->title = $title;
            $report->frequency = $frequency;
            $report->user_id = $user->id;
            $report->save();
        }
        $other = new OfficialReport($this->payload());
        $other->title = 'تقرير الصيانة الخاص';
        $other->user_id = $this->user('private@example.com')->id;
        $other->save();
        $this->actingAs($user)->get(route('official-reports.index', ['q' => 'الصيانة', 'frequency' => 'monthly']))
            ->assertOk()->assertSee('تقرير الصيانة')->assertDontSee('تقرير المتابعة')->assertDontSee('تقرير الصيانة الخاص')
            ->assertViewHas('counts', fn ($counts) => $counts['total'] === 2 && $counts['monthly'] === 1)
            ->assertViewHas('reports', fn ($reports) => $reports->total() === 1);
        $this->get(route('official-reports.create'))->assertOk()->assertSee('data-workflow-tab="review"', false)
            ->assertSee('quality-checklist')->assertSee('generate-executive-summary')->assertSee('download-report-text')
            ->assertSee('js/report-quality.js')->assertSee('css/report-workflow.css');
    }

    public function test_report_can_be_created_edited_and_deleted_by_owner(): void
    {
        $user = $this->user();
        $this->get(route('official-reports.index'))->assertOk();
        $this->actingAs($user)->get(route('official-reports.create'))->assertOk()->assertSee('صمّم تقريرًا');
        $payload = $this->payload();
        $this->postJson(route('official-reports.store'), $payload + ['user_id' => 999])->assertCreated();
        $report = OfficialReport::firstOrFail();
        $this->assertSame($user->id, $report->user_id);
        $this->assertSame('12', $report->content['sections'][0]['rows'][0][1]);
        $this->get(route('official-reports.edit', $report))->assertOk();
        $payload['content']['theme'] = 'modern';
        $this->putJson(route('official-reports.update', $report), $payload)->assertOk();
        $this->assertSame('modern', $report->fresh()->content['theme']);
        $this->delete(route('official-reports.destroy', $report))->assertRedirect(route('official-reports.index'));
        $this->assertDatabaseCount('official_reports', 0);
    }

    public function test_other_users_cannot_read_change_or_delete_reports_and_admin_can_access(): void
    {
        $owner = $this->user();
        $this->actingAs($owner)->postJson(route('official-reports.store'), $this->payload())->assertCreated();
        $report = OfficialReport::firstOrFail();
        $this->actingAs($this->user('other@example.com'));
        $this->get(route('official-reports.index'))->assertDontSee('التقرير الأسبوعي');
        $this->get(route('official-reports.edit', $report))->assertForbidden();
        $this->putJson(route('official-reports.update', $report), $this->payload())->assertForbidden();
        $this->delete(route('official-reports.destroy', $report))->assertForbidden();
        $this->actingAs($this->user('admin@example.com', 'admin'))->get(route('official-reports.edit', $report))->assertOk();
        $this->get(route('official-reports.index'))->assertSee('التقرير الأسبوعي');
    }

    public function test_invalid_dates_design_and_metrics_are_rejected(): void
    {
        $payload = $this->payload();
        $payload['ends_on'] = '2026-11-01';
        $payload['content']['accent'] = 'url(javascript:alert(1))';
        $payload['content']['theme'] = 'external-html';
        $payload['content']['metrics'][0]['value'] = -1;
        $this->actingAs($this->user())->postJson(route('official-reports.store'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors(['ends_on', 'content.accent', 'content.theme', 'content.metrics.0.value']);
        $this->assertDatabaseCount('official_reports', 0);
    }

    public function test_ai_is_optional_and_returns_suggestion_without_saving_report(): void
    {
        Http::preventStrayRequests();
        $this->actingAs($this->user());
        config(['reporting.ai.key' => null, 'reporting.ai.model' => null]);
        $data = ['text' => 'أنجزنا 12 معاملة', 'instruction' => 'صياغة رسمية'];
        $this->postJson(route('official-reports.assist'), $data)->assertStatus(503);
        Http::assertNothingSent();
        config(['reporting.ai.key' => 'test-key', 'reporting.ai.model' => 'configured-model']);
        Http::fake(['*' => Http::sequence()
            ->push(['choices' => [['message' => ['content' => 'أُنجزت 12 معاملة.']]]])
            ->push(['error' => 'secret provider detail'], 401)]);
        $this->postJson(route('official-reports.assist'), $data)->assertOk()->assertJsonPath('text', 'أُنجزت 12 معاملة.');
        $this->assertDatabaseCount('official_reports', 0);
        $this->postJson(route('official-reports.assist'), $data)->assertStatus(502)->assertDontSee('secret provider detail');
    }
}
