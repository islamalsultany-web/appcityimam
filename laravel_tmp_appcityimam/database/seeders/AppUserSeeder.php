<?php

namespace Database\Seeders;

use App\Models\AppUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AppUserSeeder extends Seeder
{
    public function run(): void
    {
        AppUser::firstOrCreate(
            ['username' => 'admin'],
            [
                'password' => Hash::make('admin1234'),
                'password_confirmation' => 'admin1234',
                'employee_number' => '1001',
                'badge_number' => '9001',
                'division' => 'شعبة الإدارة',
                'unit' => 'وحدة النظام',
                'role' => 'admin',
            ]
        );

        AppUser::factory()->count(10)->create();
    }
}
