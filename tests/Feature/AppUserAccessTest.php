<?php

namespace Tests\Feature;

use App\Models\AppUser;
use Database\Seeders\PermissionSystemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppUserAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_asker_cannot_access_users_index(): void
    {
        $this->seed(PermissionSystemSeeder::class);

        $asker = AppUser::factory()->create(['role' => 'asker']);
        $asker->assignRole('asker');

        $response = $this->withSession([
            'auth_app_user_id' => $asker->id,
            'auth_app_username' => $asker->username,
            'auth_app_role' => $asker->role,
        ])->get(route('users.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_access_users_index(): void
    {
        $this->seed(PermissionSystemSeeder::class);

        Role::findOrCreate('admin', 'web');

        $admin = AppUser::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $response = $this->withSession([
            'auth_app_user_id' => $admin->id,
            'auth_app_username' => $admin->username,
            'auth_app_role' => $admin->role,
        ])->get(route('users.index'));

        $response->assertOk();
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $this->seed(PermissionSystemSeeder::class);

        $admin = AppUser::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $response = $this->withSession([
            'auth_app_user_id' => $admin->id,
            'auth_app_username' => $admin->username,
            'auth_app_role' => $admin->role,
        ])->delete(route('users.destroy', $admin));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('app_users', ['id' => $admin->id]);
    }
}
