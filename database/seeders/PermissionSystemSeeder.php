<?php

namespace Database\Seeders;

use App\Models\AppUser;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSystemSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $modules = config('permissions.modules', []);
        foreach ($modules as $moduleConfig) {
            $permissions = $moduleConfig['permissions'] ?? [];
            foreach ($permissions as $permissionName => $displayName) {
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            }
        }

        $roleTemplates = config('permissions.role_templates', []);
        foreach ($roleTemplates as $roleName => $roleConfig) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if (($roleConfig['permissions'] ?? null) === '*') {
                $role->syncPermissions(Permission::all());
                continue;
            }

            $rolePermissions = $roleConfig['permissions'] ?? [];
            $role->syncPermissions($rolePermissions);
        }

        AppUser::query()->each(function (AppUser $user): void {
            if (in_array($user->role, ['admin', 'asker', 'responder', 'reviewer'], true)) {
                $user->syncRoles([$user->role]);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
