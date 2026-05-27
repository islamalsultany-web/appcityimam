<?php

namespace App\Http\Controllers;

use App\Exports\AppUsersExport;
use App\Exports\AppUsersTemplateExport;
use App\Http\Requests\ImportAppUsersRequest;
use App\Http\Requests\IndexAppUsersRequest;
use App\Http\Requests\StoreAppUserRequest;
use App\Http\Requests\UpdateAppUserRequest;
use App\Imports\AppUsersImport;
use App\Models\AppUser;
use App\Support\AppAuth;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AppUserController extends Controller
{
    public function index(IndexAppUsersRequest $request): View
    {
        $filters = $request->validated();
        $query = AppUser::query();

        if (! empty($filters['username'])) {
            $query->where('username', 'like', '%' . trim((string) $filters['username']) . '%');
        }

        if (! empty($filters['employee_number'])) {
            $query->where('employee_number', 'like', '%' . trim((string) $filters['employee_number']) . '%');
        }

        if (! empty($filters['badge_number'])) {
            $query->where('badge_number', 'like', '%' . trim((string) $filters['badge_number']) . '%');
        }

        if (! empty($filters['division'])) {
            $query->where('division', 'like', '%' . trim((string) $filters['division']) . '%');
        }

        if (! empty($filters['unit'])) {
            $query->where('unit', 'like', '%' . trim((string) $filters['unit']) . '%');
        }

        if (! empty($filters['role'])) {
            $query->where('role', (string) $filters['role']);
        }

        $users = $query->latest()->paginate(15)->appends($request->query());

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function excelPage(): View
    {
        return view('users.excel');
    }

    public function excelTemplate()
    {
        return Excel::download(new AppUsersTemplateExport(), 'users-template.xlsx');
    }

    public function excelExport()
    {
        return Excel::download(new AppUsersExport(), 'users-export.xlsx');
    }

    public function excelImport(ImportAppUsersRequest $request): RedirectResponse
    {
        set_time_limit(300);

        DB::transaction(function () use ($request): void {
            Excel::import(new AppUsersImport(), $request->file('excel_file'));
        });
        AuditLogger::security($request, 'users.excel.import');

        return redirect()->route('users.excel')->with('success', 'تم استيراد بيانات المستخدمين بنجاح.');
    }

    public function store(StoreAppUserRequest $request): RedirectResponse
    {
        $data = $this->normalizeUserPayload($request->validated());
        $data['password'] = Hash::make($data['password']);

        AppUser::create($data);
        AuditLogger::security($request, 'users.store', [
            'username' => $data['username'] ?? null,
            'role' => $data['role'] ?? null,
        ]);

        return redirect()->route('users.index')->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    public function show(AppUser $user): View
    {
        return view('users.show', compact('user'));
    }

    public function edit(AppUser $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateAppUserRequest $request, AppUser $user): RedirectResponse
    {
        $data = $this->normalizeUserPayload($request->validated());
        $data['password'] = Hash::make($data['password']);

        $user->update($data);
        AuditLogger::security($request, 'users.update', [
            'target_user_id' => $user->id,
            'username' => $user->username,
        ], targetType: AppUser::class, targetId: $user->id);

        return redirect()->route('users.index')->with('success', 'تم تحديث المستخدم بنجاح.');
    }

    public function destroy(Request $request, AppUser $user): RedirectResponse
    {
        $authUserId = AppAuth::id($request) ?? 0;

        if ($user->id === $authUserId) {
            return redirect()
                ->route('users.index')
                ->withErrors(['delete' => 'لا يمكنك حذف حسابك الحالي.']);
        }

        if ($this->isAdminUser($user) && $this->adminUsersCount() <= 1) {
            return redirect()
                ->route('users.index')
                ->withErrors(['delete' => 'لا يمكن حذف آخر مدير نظام في المنصة.']);
        }

        $user->delete();
        AuditLogger::security($request, 'users.delete', [
            'target_user_id' => $user->id,
            'username' => $user->username,
        ], targetType: AppUser::class, targetId: $user->id);

        return redirect()->route('users.index')->with('success', 'تم حذف المستخدم بنجاح.');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $authUserId = AppAuth::id($request) ?? 0;
        $protectedIds = collect([$authUserId]);

        $primaryAdmin = AppUser::query()
            ->where(function ($query): void {
                $query->where('role', 'admin')
                    ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'admin'));
            })
            ->orderBy('id')
            ->first();

        if ($primaryAdmin) {
            $protectedIds->push($primaryAdmin->id);
        }

        $deletedCount = AppUser::query()
            ->whereNotIn('id', $protectedIds->unique()->values()->all())
            ->delete();
        AuditLogger::security($request, 'users.bulk_delete', [
            'deleted_count' => $deletedCount,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', "تم حذف {$deletedCount} مستخدم. تم الإبقاء على حسابك ومدير النظام الأساسي.");
    }

    private function normalizeUserPayload(array $data): array
    {
        unset($data['password_confirmation']);

        $data['responder_scopes'] = AppUser::sanitizeResponderScopes(
            $data['responder_scopes'] ?? [],
            (string) ($data['role'] ?? 'asker')
        );

        return $data;
    }

    private function isAdminUser(AppUser $user): bool
    {
        return $user->role === 'admin' || $user->hasRole('admin');
    }

    private function adminUsersCount(): int
    {
        return AppUser::query()
            ->where(function ($query): void {
                $query->where('role', 'admin')
                    ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'admin'));
            })
            ->count();
    }
}
