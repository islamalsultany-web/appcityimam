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
            ['مهدي غازي حسن هجيرز', 'Pass11914', 'Pass11914', '11914', '', 'شعبة', 'وحدة', 'asker'],
            ['كرار خليل عبد الامير', 'Pass37364', 'Pass37364', '37364', '', '', '', 'asker'],
        ]));

        $this->assertDatabaseCount('app_users', 2);

        $first = AppUser::query()->where('employee_number', '11914')->first();
        $second = AppUser::query()->where('employee_number', '37364')->first();

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertTrue($first->hasRole('asker'));
        $this->assertTrue($second->hasRole('asker'));
        $this->assertTrue(Hash::check('Pass11914', (string) $first->password));
        $this->assertTrue(Hash::check('Pass37364', (string) $second->password));
        $this->assertFalse($first->must_change_credentials);
        $this->assertFalse($second->must_change_credentials);
    }

    public function test_import_with_employee_number_password_flags_must_change(): void
    {
        Role::findOrCreate('asker', 'web');

        Excel::import(new AppUsersImport(), $this->makeImportPath([
            ['اسم المستخدم', 'كلمة المرور', 'تأكيد كلمة المرور', 'الرقم الوظيفي', 'رقم الباج', 'الشعبة', 'الوحدة', 'الدور'],
            ['22953001', 'P2295301', 'P2295301', '22953001', '', '', '', 'asker'],
        ]));

        $user = AppUser::query()->where('employee_number', '22953001')->first();

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('P2295301', (string) $user->password));
        $this->assertTrue($user->must_change_credentials);
    }

    public function test_fresh_import_without_password_uses_random_password_and_flags_must_change(): void
    {
        Role::findOrCreate('asker', 'web');

        $import = new AppUsersImport();
        Excel::import($import, $this->makeImportPath([
            ['اسم المستخدم', 'كلمة المرور', 'تأكيد كلمة المرور', 'الرقم الوظيفي', 'رقم الباج', 'الشعبة', 'الوحدة', 'الدور'],
            ['منتسب جديد', '', '', '50001', '', 'شعبة', 'وحدة', 'asker'],
        ]));

        $user = AppUser::query()->where('employee_number', '50001')->first();

        $this->assertNotNull($user);
        $this->assertFalse(Hash::check('50001', (string) $user->password));
        $this->assertTrue($user->must_change_credentials);
        $this->assertCount(1, $import->temporaryPasswords);
        $this->assertSame('50001', $import->temporaryPasswords[0]['employee_number']);
        $this->assertTrue(Hash::check($import->temporaryPasswords[0]['temporary_password'], (string) $user->password));
    }

    public function test_fresh_import_with_strong_password_does_not_flag_must_change(): void
    {
        Role::findOrCreate('asker', 'web');

        Excel::import(new AppUsersImport(), $this->makeImportPath([
            ['اسم المستخدم', 'كلمة المرور', 'تأكيد كلمة المرور', 'الرقم الوظيفي', 'رقم الباج', 'الشعبة', 'الوحدة', 'الدور'],
            ['منتسب آمن', 'SecurePass1', 'SecurePass1', '50002', '', '', '', 'asker'],
        ]));

        $user = AppUser::query()->where('employee_number', '50002')->first();

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('SecurePass1', (string) $user->password));
        $this->assertFalse($user->must_change_credentials);
    }

    public function test_weak_import_password_is_replaced_with_random_password(): void
    {
        Role::findOrCreate('asker', 'web');

        $import = new AppUsersImport();
        Excel::import($import, $this->makeImportPath([
            ['اسم المستخدم', 'كلمة المرور', 'تأكيد كلمة المرور', 'الرقم الوظيفي', 'رقم الباج', 'الشعبة', 'الوحدة', 'الدور'],
            ['منتسب ضعيف', '123', '123', '50003', '', '', '', 'asker'],
        ]));

        $user = AppUser::query()->where('employee_number', '50003')->first();

        $this->assertNotNull($user);
        $this->assertFalse(Hash::check('123', (string) $user->password));
        $this->assertTrue($user->must_change_credentials);
        $this->assertCount(1, $import->temporaryPasswords);
    }

    public function test_import_rejects_admin_role_from_spreadsheet(): void
    {
        Role::findOrCreate('asker', 'web');

        Excel::import(new AppUsersImport(), $this->makeImportPath([
            ['اسم المستخدم', 'كلمة المرور', 'تأكيد كلمة المرور', 'الرقم الوظيفي', 'رقم الباج', 'الشعبة', 'الوحدة', 'الدور'],
            ['مستخدم تجريبي', '', '', '90001', '', '', '', 'admin'],
        ]));

        $user = AppUser::query()->where('employee_number', '90001')->first();

        $this->assertNotNull($user);
        $this->assertSame('asker', $user->role);
        $this->assertTrue($user->hasRole('asker'));
        $this->assertFalse($user->hasRole('admin'));
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
