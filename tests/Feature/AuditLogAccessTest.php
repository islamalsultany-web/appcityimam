<?php

namespace Tests\Feature;

use App\Models\AppUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_audit_logs_page(): void
    {
        Role::findOrCreate('admin', 'web');

        $admin = AppUser::factory()->create([
            'role' => 'admin',
        ]);
        $admin->assignRole('admin');

        $response = $this->withSession([
            'auth_app_user_id' => $admin->id,
            'auth_app_username' => $admin->username,
            'auth_app_role' => $admin->role,
        ])->get(route('security.audit-logs'));

        $response->assertOk();
        $response->assertSee('سجل التدقيق الأمني');
    }

    public function test_non_admin_cannot_open_audit_logs_page(): void
    {
        Role::findOrCreate('asker', 'web');

        $asker = AppUser::factory()->create([
            'role' => 'asker',
        ]);
        $asker->assignRole('asker');

        $response = $this->withSession([
            'auth_app_user_id' => $asker->id,
            'auth_app_username' => $asker->username,
            'auth_app_role' => $asker->role,
        ])->get(route('security.audit-logs'));

        $response->assertForbidden();
    }
}
