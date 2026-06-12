<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Support\AdminRoleGuard;
use Database\Seeders\PermissionSystemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminRoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_assign_admin_role_in_user_forms(): void
    {
        $this->seed(PermissionSystemSeeder::class);

        $staff = AppUser::factory()->create(['role' => 'reviewer']);
        $staff->assignRole('reviewer');

        $this->assertNotContains('admin', AdminRoleGuard::assignableRoleNames($staff));
    }

    public function test_non_admin_cannot_grant_admin_via_member_permissions(): void
    {
        $this->seed(PermissionSystemSeeder::class);

        Role::findOrCreate('reviewer', 'web');
        Role::findOrCreate('asker', 'web');

        $staff = AppUser::factory()->create(['role' => 'reviewer']);
        $staff->assignRole('reviewer');
        $staff->givePermissionTo('permissions.members.edit');

        $target = AppUser::factory()->create(['role' => 'asker']);
        $target->assignRole('asker');

        $response = $this->withSession([
            'auth_app_user_id' => $staff->id,
            'auth_app_username' => $staff->username,
            'auth_app_role' => $staff->role,
        ])->put(route('permissions.members.update', $target), [
            'legacy_role' => 'admin',
            'roles' => ['admin'],
        ]);

        $response->assertForbidden();
        $target->refresh();
        $this->assertSame('asker', $target->role);
        $this->assertFalse($target->hasRole('admin'));
    }

    public function test_admin_can_grant_admin_role_via_member_permissions(): void
    {
        $this->seed(PermissionSystemSeeder::class);

        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('asker', 'web');

        $admin = AppUser::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $target = AppUser::factory()->create(['role' => 'asker']);
        $target->assignRole('asker');

        $response = $this->withSession([
            'auth_app_user_id' => $admin->id,
            'auth_app_username' => $admin->username,
            'auth_app_role' => $admin->role,
            'admin_two_factor_passed' => true,
        ])->put(route('permissions.members.update', $target), [
            'legacy_role' => 'admin',
            'roles' => ['admin'],
        ]);

        $response->assertRedirect(route('permissions.members.index'));
        $target->refresh();
        $this->assertSame('admin', $target->role);
        $this->assertTrue($target->hasRole('admin'));
    }
}
