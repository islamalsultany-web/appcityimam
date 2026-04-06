<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('auth_app_user_id')) {
            return redirect()->route($this->resolveHomeRoute((string) $request->session()->get('auth_app_role')));
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = AppUser::query()->where('username', $credentials['username'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['username' => 'بيانات تسجيل الدخول غير صحيحة.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();
        $request->session()->put('auth_app_user_id', $user->id);
        $request->session()->put('auth_app_username', $user->username);
        $request->session()->put('auth_app_role', $user->role);

        return redirect()->route($this->resolveHomeRoute($user->role));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['auth_app_user_id', 'auth_app_username', 'auth_app_role']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.form');
    }

    public function logoutHome(Request $request): RedirectResponse
    {
        $request->session()->forget(['auth_app_user_id', 'auth_app_username', 'auth_app_role']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function userInfo(Request $request): View|RedirectResponse
    {
        $authUserId = (int) $request->session()->get('auth_app_user_id');
        $user = AppUser::query()->find($authUserId);

        if (! $user) {
            return redirect()->route('login.form');
        }

        return view('auth.user-info', compact('user'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $authUserId = (int) $request->session()->get('auth_app_user_id');
        $user = AppUser::query()->find($authUserId);

        if (! $user) {
            return redirect()->route('login.form');
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user->password = Hash::make($data['password']);
        $user->password_confirmation = $data['password'];
        $user->save();

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }

    private function resolveHomeRoute(string $role): string
    {
        $preferredRoute = match ($role) {
            'asker' => 'dashboard.asker',
            'responder' => 'dashboard.responder',
            'reviewer' => 'dashboard.reviewer',
            default => 'dashboard.responder',
        };

        foreach ([$preferredRoute, 'dashboard.responder', 'dashboard.asker', 'home'] as $routeName) {
            if (Route::has($routeName)) {
                return $routeName;
            }
        }

        return 'home';
    }
}
