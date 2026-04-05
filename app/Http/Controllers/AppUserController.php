<?php

namespace App\Http\Controllers;

use App\Exports\AppUsersExport;
use App\Exports\AppUsersTemplateExport;
use App\Http\Requests\StoreAppUserRequest;
use App\Http\Requests\UpdateAppUserRequest;
use App\Imports\AppUsersImport;
use App\Models\AppUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AppUserController extends Controller
{
    public function index(Request $request): View
    {
        $query = AppUser::query();

        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . trim((string) $request->input('username')) . '%');
        }

        if ($request->filled('employee_number')) {
            $query->where('employee_number', 'like', '%' . trim((string) $request->input('employee_number')) . '%');
        }

        if ($request->filled('badge_number')) {
            $query->where('badge_number', 'like', '%' . trim((string) $request->input('badge_number')) . '%');
        }

        if ($request->filled('division')) {
            $query->where('division', 'like', '%' . trim((string) $request->input('division')) . '%');
        }

        if ($request->filled('unit')) {
            $query->where('unit', 'like', '%' . trim((string) $request->input('unit')) . '%');
        }

        if ($request->filled('role')) {
            $query->where('role', (string) $request->input('role'));
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

    public function excelImport(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        // Raise time limit for large files; wrap in a single transaction so SQLite
        // doesn't commit after every row (massive speed improvement).
        set_time_limit(300);

        DB::transaction(function () use ($request): void {
            Excel::import(new AppUsersImport(), $request->file('excel_file'));
        });

        return redirect()->route('users.excel')->with('success', 'تم استيراد بيانات المستخدمين بنجاح.');
    }

    public function store(StoreAppUserRequest $request): RedirectResponse
    {
        $data = $this->normalizeUserPayload($request->validated());
        $data['password'] = Hash::make($data['password']);

        AppUser::create($data);

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

        return redirect()->route('users.index')->with('success', 'تم تحديث المستخدم بنجاح.');
    }

    public function destroy(AppUser $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'تم حذف المستخدم بنجاح.');
    }

    public function destroyAll(): RedirectResponse
    {
        AppUser::query()->delete();

        return redirect()->route('users.index')->with('success', 'تم حذف جميع المستخدمين بنجاح.');
    }

    private function normalizeUserPayload(array $data): array
    {
        $data['responder_scopes'] = AppUser::sanitizeResponderScopes(
            $data['responder_scopes'] ?? [],
            (string) ($data['role'] ?? 'asker')
        );

        return $data;
    }
}
