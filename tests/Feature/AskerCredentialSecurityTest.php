<?php

namespace Tests\Feature;

use App\Models\AppUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AskerCredentialSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_asker_with_default_credentials_is_redirected_to_setup(): void
    {
        Role::findOrCreate('asker', 'web');

        $asker = AppUser::factory()->create([
            'role' => 'asker',
            'username' => '11914',
            'employee_number' => '11914',
            'password' => Hash::make('11914'),
        ]);
        $asker->assignRole('asker');

        $response = $this->post(route('login.submit'), [
            'username' => '11914',
            'password' => '11914',
        ]);

        $response->assertRedirect(route('user.credentials.setup'));
        $response->assertSessionHas('warning');
    }

    public function test_asker_cannot_access_dashboard_until_credentials_updated(): void
    {
        Role::findOrCreate('asker', 'web');

        $asker = AppUser::factory()->create([
            'role' => 'asker',
            'username' => '11914',
            'employee_number' => '11914',
            'password' => Hash::make('11914'),
        ]);
        $asker->assignRole('asker');

        $response = $this->withSession([
            'auth_app_user_id' => $asker->id,
            'auth_app_username' => $asker->username,
            'auth_app_role' => $asker->role,
        ])->get(route('dashboard.asker'));

        $response->assertRedirect(route('user.credentials.setup'));
    }

    public function test_asker_can_access_dashboard_after_secure_credentials_update(): void
    {
        Role::findOrCreate('asker', 'web');

        $asker = AppUser::factory()->create([
            'role' => 'asker',
            'username' => '11914',
            'employee_number' => '11914',
            'password' => Hash::make('11914'),
        ]);
        $asker->assignRole('asker');

        $update = $this->withSession([
            'auth_app_user_id' => $asker->id,
            'auth_app_username' => $asker->username,
            'auth_app_role' => $asker->role,
        ])->post(route('user.credentials.update'), [
            'username' => 'employee11914',
            'password' => 'SecurePass1',
            'password_confirmation' => 'SecurePass1',
        ]);

        $update->assertRedirect(route('dashboard.asker'));

        $dashboard = $this->withSession([
            'auth_app_user_id' => $asker->id,
            'auth_app_username' => 'employee11914',
            'auth_app_role' => $asker->role,
        ])->get(route('dashboard.asker'));

        $dashboard->assertOk();
    }
}
