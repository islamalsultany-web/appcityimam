<?php

namespace Database\Factories;

use App\Models\AppUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<AppUser>
 */
class AppUserFactory extends Factory
{
    protected $model = AppUser::class;

    public function definition(): array
    {
        $plainPassword = '12345678';

        return [
            'username' => fake()->unique()->userName(),
            'password' => Hash::make($plainPassword),
            'password_confirmation' => $plainPassword,
            'employee_number' => (string) fake()->numberBetween(1000, 99999),
            'badge_number' => (string) fake()->numberBetween(1000, 99999),
            'division' => fake()->randomElement(['شعبة الآليات', 'شعبة الفندق', 'شعبة الإدارة']),
            'unit' => fake()->randomElement(['وحدة الاتصالات', 'وحدة الزينبيات', 'وحدة النظام']),
            'role' => fake()->randomElement(['asker', 'responder', 'admin']),
        ];
    }
}
