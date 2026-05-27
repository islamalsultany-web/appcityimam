<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAskerCredentialsRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\AppUser;
use App\Support\AppHomeRoute;
use App\Support\AppUserLogin;
use App\Support\EmployeeCredentialSecurity;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check() || $request->session()->has('auth_app_user_id')) {
            $user = $this->resolveAuthenticatedUser($request);

            if ($user && EmployeeCredentialSecurity::mustChangeCredentials($user)) {
                return redirect()->route('user.credentials.setup');
            }

            return redirect()->route(AppHomeRoute::forRole((string) $request->session()->get('auth_app_role')));
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $loginId = trim($credentials['username']);
        $rateLimitKey = 'login:' . Str::lower($loginId) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            throw ValidationException::withMessages([
                'username' => "محاولات تسجيل دخول كثيرة. أعد المحاولة بعد {$seconds} ثانية.",
            ]);
        }

        $user = AppUserLogin::findByLoginId($loginId);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($rateLimitKey, 60);
            AuditLogger::security($request, 'auth.login.failed', [
                'login_id' => $loginId,
            ]);

            return back()
                ->withErrors(['username' => 'بيانات تسجيل الدخول غير صحيحة.'])
                ->onlyInput('username');
        }

        RateLimiter::clear($rateLimitKey);

        $request->session()->regenerate();
        Auth::login($user);
        $request->session()->put('auth_app_user_id', $user->id);
        $request->session()->put('auth_app_username', $user->username);
        $request->session()->put('auth_app_role', $user->role);
        AuditLogger::security($request, 'auth.login.success', [
            'user_id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
        ]);

        if (EmployeeCredentialSecurity::mustChangeCredentials($user)) {
            return redirect()
                ->route('user.credentials.setup')
                ->with('warning', EmployeeCredentialSecurity::warningMessage());
        }

        return redirect()->route(AppHomeRoute::forRole($user->role));
    }

    public function showCredentialsSetup(Request $request): View|RedirectResponse
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (! $user) {
            return redirect()->route('login.form');
        }

        if (! EmployeeCredentialSecurity::mustChangeCredentials($user)) {
            return redirect()->route(AppHomeRoute::forRole($user->role));
        }

        return view('auth.credentials-setup', compact('user'));
    }

    public function updateCredentials(UpdateAskerCredentialsRequest $request): RedirectResponse
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (! $user) {
            return redirect()->route('login.form');
        }

        $data = $request->validated();

        $user->username = trim($data['username']);
        $user->password = Hash::make($data['password']);
        $user->save();

        Auth::login($user);
        $request->session()->put('auth_app_username', $user->username);
        $request->session()->put('auth_app_role', $user->role);
        AuditLogger::security($request, 'auth.credentials.updated', [
            'user_id' => $user->id,
            'username' => $user->username,
        ]);

        return redirect()
            ->route(AppHomeRoute::forRole($user->role))
            ->with('success', 'تم تحديث بيانات الدخول بنجاح. يمكنك الآن استخدام النظام بأمان.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $authUser = $this->resolveAuthenticatedUser($request);
        if ($authUser) {
            AuditLogger::security($request, 'auth.logout', [
                'user_id' => $authUser->id,
                'username' => $authUser->username,
            ]);
        }

        Auth::logout();
        $request->session()->forget(['auth_app_user_id', 'auth_app_username', 'auth_app_role']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.form');
    }

    public function logoutHome(Request $request): RedirectResponse
    {
        $authUser = $this->resolveAuthenticatedUser($request);
        if ($authUser) {
            AuditLogger::security($request, 'auth.logout.home', [
                'user_id' => $authUser->id,
                'username' => $authUser->username,
            ]);
        }

        Auth::logout();
        $request->session()->forget(['auth_app_user_id', 'auth_app_username', 'auth_app_role']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function userInfo(Request $request): View|RedirectResponse
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (! $user) {
            return redirect()->route('login.form');
        }

        return view('auth.user-info', compact('user'));
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (! $user) {
            return redirect()->route('login.form');
        }

        $data = $request->validated();

        $user->password = Hash::make($data['password']);
        $user->save();
        AuditLogger::security($request, 'auth.password.updated', [
            'user_id' => $user->id,
            'username' => $user->username,
        ]);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }

    private function resolveAuthenticatedUser(Request $request): ?AppUser
    {
        $authUser = Auth::user();

        if ($authUser instanceof AppUser) {
            if (! $request->session()->has('auth_app_user_id')) {
                $request->session()->put('auth_app_user_id', $authUser->id);
                $request->session()->put('auth_app_username', $authUser->username);
                $request->session()->put('auth_app_role', $authUser->role);
            }

            return $authUser;
        }

        $authUserId = (int) $request->session()->get('auth_app_user_id');

        if ($authUserId <= 0) {
            return null;
        }

        $user = AppUser::query()->find($authUserId);

        if ($user) {
            Auth::login($user);
        }

        return $user;
    }

}
