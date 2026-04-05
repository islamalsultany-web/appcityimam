<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InquiryRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_shows_inquiry_type_and_hides_preferred_response_method(): void
    {
        $asker = $this->createAskerUser();

        $response = $this->withSession([
            'auth_app_user_id' => $asker->id,
            'auth_app_username' => $asker->username,
        ])->get(route('asker.inquiries.create'));

        $response->assertOk();
        $response->assertSee('نوع الاستفسار');
        $response->assertDontSee('طريقة الرد المفضلة');
    }

    public function test_asker_can_create_inquiry_with_type_without_preferred_channel(): void
    {
        $asker = $this->createAskerUser();

        $response = $this->withSession([
            'auth_app_user_id' => $asker->id,
            'auth_app_username' => $asker->username,
        ])->post(route('asker.inquiries.store'), [
            'title' => 'استفسار مالي بخصوص السلفة',
            'inquiry_type' => 'financial',
            'priority' => 'urgent',
            'body' => 'أرغب بمعرفة حالة معاملة السلفة الخاصة بي.',
        ]);

        $response->assertRedirect(route('dashboard.asker'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'asker_user_id' => $asker->id,
            'title' => 'استفسار مالي بخصوص السلفة',
            'inquiry_type' => 'financial',
            'priority' => 'urgent',
            'status' => 'pending',
        ]);
    }

    public function test_responder_sees_only_inquiries_matching_his_scope(): void
    {
        $asker = $this->createAskerUser();
        $responder = $this->createResponderUser(['administrative']);

        Inquiry::create([
            'asker_user_id' => $asker->id,
            'title' => 'استفسار إداري',
            'inquiry_type' => 'administrative',
            'priority' => 'normal',
            'body' => 'تفاصيل إدارية',
            'status' => 'pending',
        ]);

        Inquiry::create([
            'asker_user_id' => $asker->id,
            'title' => 'استفسار مالي',
            'inquiry_type' => 'financial',
            'priority' => 'normal',
            'body' => 'تفاصيل مالية',
            'status' => 'pending',
        ]);

        $response = $this->withSession([
            'auth_app_user_id' => $responder->id,
            'auth_app_username' => $responder->username,
            'auth_app_role' => $responder->role,
        ])->get(route('dashboard.responder'));

        $response->assertOk();
        $response->assertSee('استفسار إداري');
        $response->assertDontSee('استفسار مالي');
    }

    public function test_responder_with_all_scope_sees_all_inquiry_types(): void
    {
        $asker = $this->createAskerUser();
        $responder = $this->createResponderUser(['all']);

        Inquiry::create([
            'asker_user_id' => $asker->id,
            'title' => 'استفسار إداري شامل',
            'inquiry_type' => 'administrative',
            'priority' => 'normal',
            'body' => 'تفاصيل إدارية',
            'status' => 'pending',
        ]);

        Inquiry::create([
            'asker_user_id' => $asker->id,
            'title' => 'استفسار مالي شامل',
            'inquiry_type' => 'financial',
            'priority' => 'normal',
            'body' => 'تفاصيل مالية',
            'status' => 'pending',
        ]);

        $response = $this->withSession([
            'auth_app_user_id' => $responder->id,
            'auth_app_username' => $responder->username,
            'auth_app_role' => $responder->role,
        ])->get(route('dashboard.responder'));

        $response->assertOk();
        $response->assertSee('استفسار إداري شامل');
        $response->assertSee('استفسار مالي شامل');
    }

    public function test_asker_does_not_see_response_before_reviewer_approval(): void
    {
        $asker = $this->createAskerUser();

        Inquiry::create([
            'asker_user_id' => $asker->id,
            'title' => 'استفسار بحاجة تدقيق',
            'inquiry_type' => 'administrative',
            'priority' => 'normal',
            'body' => 'تفاصيل الاستفسار',
            'status' => 'answered',
            'response_body' => 'هذه إجابة المجيب قبل اعتمادها',
            'review_status' => 'pending_review',
        ]);

        $response = $this->withSession([
            'auth_app_user_id' => $asker->id,
            'auth_app_username' => $asker->username,
            'auth_app_role' => $asker->role,
        ])->get(route('dashboard.asker'));

        $response->assertOk();
        $response->assertDontSee('هذه إجابة المجيب قبل اعتمادها');
        $response->assertSee('بانتظار اعتماد المدقق');
    }

    public function test_reviewer_can_approve_responder_answer(): void
    {
        $asker = $this->createAskerUser();
        $reviewer = $this->createReviewerUser();
        $inquiry = Inquiry::create([
            'asker_user_id' => $asker->id,
            'title' => 'استفسار للتدقيق',
            'inquiry_type' => 'financial',
            'priority' => 'normal',
            'body' => 'تفاصيل',
            'status' => 'answered',
            'response_body' => 'رد المجيب النهائي',
            'review_status' => 'pending_review',
        ]);

        $dashboard = $this->withSession([
            'auth_app_user_id' => $reviewer->id,
            'auth_app_username' => $reviewer->username,
            'auth_app_role' => $reviewer->role,
        ])->get(route('dashboard.reviewer'));

        $dashboard->assertOk();
        $dashboard->assertSee('استفسار للتدقيق');

        $approve = $this->withSession([
            'auth_app_user_id' => $reviewer->id,
            'auth_app_username' => $reviewer->username,
            'auth_app_role' => $reviewer->role,
        ])->patch(route('reviewer.inquiries.review', $inquiry), [
            'review_action' => 'approve',
            'review_note' => 'تم الاعتماد',
            'response_body' => 'رد المجيب النهائي بعد التدقيق',
            'status' => 'answered',
        ]);

        $approve->assertRedirect(route('dashboard.reviewer'));

        $this->assertDatabaseHas('inquiries', [
            'id' => $inquiry->id,
            'review_status' => 'approved',
            'response_body' => 'رد المجيب النهائي بعد التدقيق',
        ]);
    }

    private function createAskerUser(): AppUser
    {
        Role::findOrCreate('asker', 'web');

        $asker = AppUser::factory()->create([
            'role' => 'asker',
        ]);

        $asker->assignRole('asker');

        return $asker;
    }

    private function createResponderUser(array $scopes): AppUser
    {
        Role::findOrCreate('responder', 'web');

        $responder = AppUser::factory()->create([
            'role' => 'responder',
            'responder_scopes' => $scopes,
        ]);

        $responder->assignRole('responder');

        return $responder;
    }

    private function createReviewerUser(): AppUser
    {
        Role::findOrCreate('reviewer', 'web');

        $reviewer = AppUser::factory()->create([
            'role' => 'reviewer',
            'responder_scopes' => [],
        ]);

        $reviewer->assignRole('reviewer');

        return $reviewer;
    }
}
