<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppUserRequest;
use App\Http\Requests\UpdateAppUserRequest;
use App\Models\AppUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AppUserController extends Controller
{
    public function index(): View
    {
        $users = AppUser::query()->latest()->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(StoreAppUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
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
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'تم تحديث المستخدم بنجاح.');
    }

    public function destroy(AppUser $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'تم حذف المستخدم بنجاح.');
    }
}
