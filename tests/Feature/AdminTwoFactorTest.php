<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Support\Totp;
use Database\Seeders\PermissionSystemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['security.admin_two_factor.enabled' => true]);
    }

    public function test_admin_without_two_factor_is_redirected_to_setup_after_login(): void
    {
        $this->seed(PermissionSystemSeeder::class);
        Role::findOrCreate('admin', 'web');

        $admin = AppUser::factory()->create([
            'role' => 'admin',
            'username' => 'admin',
            'password' => Hash::make('AdminPass1'),
        ]);
        $admin->assignRole('admin');

        $response = $this->post(route('login.submit'), [
            'username' => 'admin',
            'password' => 'AdminPass1',
        ]);

        $response->assertRedirect(route('user.two-factor.setup'));
    }

    public function test_admin_with_two_factor_must_verify_before_users_page(): void
    {
        $this->seed(PermissionSystemSeeder::class);
        Role::findOrCreate('admin', 'web');

        $secret = Totp::generateSecret();

        $admin = AppUser::factory()->create([
            'role' => 'admin',
            'username' => 'admin2',
            'password' => Hash::make('AdminPass1'),
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);
        $admin->assignRole('admin');

        $response = $this->withSession([
            'auth_app_user_id' => $admin->id,
            'auth_app_username' => $admin->username,
            'auth_app_role' => $admin->role,
        ])->get(route('users.index'));

        $response->assertRedirect(route('user.two-factor.verify'));
    }

    public function test_admin_can_access_users_after_two_factor_verification(): void
    {
        $this->seed(PermissionSystemSeeder::class);
        Role::findOrCreate('admin', 'web');

        $secret = Totp::generateSecret();

        $admin = AppUser::factory()->create([
            'role' => 'admin',
            'username' => 'admin3',
            'password' => Hash::make('AdminPass1'),
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);
        $admin->assignRole('admin');

        $verify = $this->withSession([
            'auth_app_user_id' => $admin->id,
            'auth_app_username' => $admin->username,
            'auth_app_role' => $admin->role,
        ])->post(route('user.two-factor.verify.submit'), [
            'code' => Totp::currentCode($secret),
        ]);

        $verify->assertRedirect(route('dashboard.responder'));

        $users = $this->withSession([
            'auth_app_user_id' => $admin->id,
            'auth_app_username' => $admin->username,
            'auth_app_role' => $admin->role,
            'admin_two_factor_passed' => true,
        ])->get(route('users.index'));

        $users->assertOk();
    }
}
