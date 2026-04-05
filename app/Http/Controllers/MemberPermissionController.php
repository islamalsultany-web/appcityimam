<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MemberPermissionController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureCanViewMembersPermissions($request);

        $users = AppUser::query()->with('roles')->latest()->paginate(20);

        return view('permissions.members-index', compact('users'));
    }

    public function create(Request $request): View
    {
        $this->ensureCanEditMembersPermissions($request);

        $users = AppUser::query()->orderBy('username')->get();
        $roles = Role::query()->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();
        $modulePermissions = $this->modulePermissions();

        return view('permissions.member-create', compact('users', 'roles', 'permissions', 'modulePermissions'));
    }

    public function edit(Request $request, AppUser $user): View
    {
        $this->ensureCanEditMembersPermissions($request);

        $roles = Role::query()->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();
        $modulePermissions = $this->modulePermissions();

        return view('permissions.member-edit', compact('user', 'roles', 'permissions', 'modulePermissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureCanEditMembersPermissions($request);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:app_users,id'],
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
            'legacy_role' => ['nullable', Rule::in(AppUser::ROLE_OPTIONS)],
            'responder_scopes' => ['nullable', 'array'],
            'responder_scopes.*' => ['string', Rule::in(AppUser::RESPONDER_SCOPE_OPTIONS)],
        ]);

        $user = AppUser::query()->findOrFail((int) $data['user_id']);
        $roles = $data['roles'] ?? [];
        $permissions = $data['permissions'] ?? [];

        $user->syncRoles($roles);
        $user->syncPermissions($permissions);

        if (! empty($data['legacy_role'])) {
            $user->role = $data['legacy_role'];
        }

        $user->responder_scopes = AppUser::sanitizeResponderScopes(
            $data['responder_scopes'] ?? [],
            (string) $user->role
        );
        $user->save();

        return redirect()->route('permissions.members.index')->with('success', 'تمت إضافة صلاحيات المنتسب بنجاح.');
    }

    public function update(Request $request, AppUser $user): RedirectResponse
    {
        $this->ensureCanEditMembersPermissions($request);

        $data = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
            'legacy_role' => ['nullable', Rule::in(AppUser::ROLE_OPTIONS)],
            'responder_scopes' => ['nullable', 'array'],
            'responder_scopes.*' => ['string', Rule::in(AppUser::RESPONDER_SCOPE_OPTIONS)],
        ]);

        $roles = $data['roles'] ?? [];
        $permissions = $data['permissions'] ?? [];

        $user->syncRoles($roles);
        $user->syncPermissions($permissions);

        if (! empty($data['legacy_role'])) {
            $user->role = $data['legacy_role'];
        }

        $user->responder_scopes = AppUser::sanitizeResponderScopes(
            $data['responder_scopes'] ?? [],
            (string) $user->role
        );
        $user->save();

        return redirect()->route('permissions.members.index')->with('success', 'تم تحديث صلاحيات المنتسب بنجاح.');
    }

    private function resolveAuthUser(Request $request): AppUser
    {
        $authUserId = (int) $request->session()->get('auth_app_user_id');
        return AppUser::query()->findOrFail($authUserId);
    }

    private function ensureCanViewMembersPermissions(Request $request): void
    {
        $authUser = $this->resolveAuthUser($request);

        if (! $authUser->hasRole('admin') && ! $authUser->can('permissions.members.view') && ! $authUser->can('permissions.members.edit')) {
            abort(403);
        }
    }

    private function ensureCanEditMembersPermissions(Request $request): void
    {
        $authUser = $this->resolveAuthUser($request);

        if (! $authUser->hasRole('admin') && ! $authUser->can('permissions.members.edit')) {
            abort(403);
        }
    }

    private function modulePermissions(): array
    {
        $result = [];

        foreach (config('permissions.modules', []) as $moduleConfig) {
            $moduleName = $moduleConfig['display_name'] ?? 'بدون تصنيف';
            $modulePermissions = $moduleConfig['permissions'] ?? [];

            if (! empty($modulePermissions)) {
                $result[$moduleName] = $modulePermissions;
            }
        }

        return $result;
    }
}
