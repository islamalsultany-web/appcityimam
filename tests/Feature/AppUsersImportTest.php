<?php

namespace Tests\Feature;

use App\Imports\AppUsersImport;
use App\Models\AppUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppUsersImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reimport_updates_username_by_employee_number(): void
    {
        Role::findOrCreate('asker', 'web');

        AppUser::factory()->create([
            'username' => '20877',
            'employee_number' => '20877',
            'password' => Hash::make('SecurePass1'),
            'role' => 'asker',
        ]);

        Excel::import(new AppUsersImport(), $this->makeImportPath([
            ['اسم المستخدم', 'كلمة المرور', 'تأكيد كلمة المرور', 'الرقم الوظيفي', 'رقم الباج', 'الشعبة', 'الوحدة', 'الدور'],
            ['مهدي غازي حسن هجيرز', '', '', '20877', '', 'شعبة', 'وحدة', 'asker'],
        ]));

        $this->assertDatabaseCount('app_users', 1);
        $this->assertDatabaseHas('app_users', [
            'employee_number' => '20877',
            'username' => 'مهدي غازي حسن هجيرز',
        ]);
        $this->assertTrue(Hash::check('SecurePass1', (string) AppUser::query()->first()?->password));
    }

    public function test_fresh_import_creates_users_with_roles(): void
    {
        Excel::import(new AppUsersImport(), $this->makeImportPath([
            ['اسم المستخدم', 'كلمة المرور', 'تأكيد كلمة المرور', 'الرقم الوظيفي', 'رقم الباج', 'الشعبة', 'الوحدة', 'الدور'],
            ['مهدي غازي حسن هجيرز', '11914', '11914', '11914', '', 'شعبة', 'وحدة', 'asker'],
            ['كرار خليل عبد الامير', '37364', '37364', '37364', '', '', '', 'asker'],
        ]));

        $this->assertDatabaseCount('app_users', 2);

        $first = AppUser::query()->where('employee_number', '11914')->first();
        $second = AppUser::query()->where('employee_number', '37364')->first();

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertTrue($first->hasRole('asker'));
        $this->assertTrue($second->hasRole('asker'));
        $this->assertTrue(Hash::check('11914', (string) $first->password));
        $this->assertTrue(Hash::check('37364', (string) $second->password));
    }

    public function test_reimport_does_not_reset_password_when_password_cell_is_empty(): void
    {
        Role::findOrCreate('asker', 'web');

        AppUser::factory()->create([
            'username' => '37364',
            'employee_number' => '37364',
            'password' => Hash::make('CustomPass9'),
            'role' => 'asker',
        ]);

        Excel::import(new AppUsersImport(), $this->makeImportPath([
            ['اسم المستخدم', 'كلمة المرور', 'تأكيد كلمة المرور', 'الرقم الوظيفي', 'رقم الباج', 'الشعبة', 'الوحدة', 'الدور'],
            ['كرار خليل عبد الامير', '', '', '37364', '', '', '', 'asker'],
        ]));

        $user = AppUser::query()->where('employee_number', '37364')->first();

        $this->assertSame('كرار خليل عبد الامير', $user?->username);
        $this->assertTrue(Hash::check('CustomPass9', (string) $user?->password));
    }

    /** @param list<list<string>> $rows */
    private function makeImportPath(array $rows): string
    {
        $directory = storage_path('framework/testing');

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $path = $directory . '/users-import-' . uniqid('', true) . '.csv';
        $handle = fopen($path, 'wb');

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $path;
    }
}
