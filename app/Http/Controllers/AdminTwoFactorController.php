<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Support\AdminTwoFactor;
use App\Support\AppAuth;
use App\Support\AppHomeRoute;
use App\Support\AuditLogger;
use App\Support\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminTwoFactorController extends Controller
{
    public function showSetup(Request $request): View|RedirectResponse
    {
        $user = $this->adminUser($request);

        if (AdminTwoFactor::isConfigured($user)) {
            return redirect()->route(AppHomeRoute::forRole($user->role));
        }

        if (! $request->session()->has('pending_two_factor_secret')) {
            $request->session()->put('pending_two_factor_secret', Totp::generateSecret());
        }

        $secret = (string) $request->session()->get('pending_two_factor_secret');
        $issuer = AdminTwoFactor::issuer();

        return view('auth.two-factor-setup', [
            'user' => $user,
            'secret' => $secret,
            'provisioningUri' => Totp::provisioningUri($secret, $user->username, $issuer),
            'issuer' => $issuer,
        ]);
    }

    public function confirmSetup(Request $request): RedirectResponse
    {
        $user = $this->adminUser($request);

        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $this->assertTwoFactorNotRateLimited($request, $user);

        $secret = (string) $request->session()->get('pending_two_factor_secret', '');

        if ($secret === '' || ! Totp::verify($secret, $data['code'])) {
            $this->recordTwoFactorFailure($request, $user);

            return back()->withErrors(['code' => 'رمز التحقق غير صحيح. تأكد من مزامنة الوقت في تطبيق المصادقة.']);
        }

        $this->clearTwoFactorRateLimit($request, $user);

        $user->two_factor_secret = $secret;
        $user->two_factor_confirmed_at = now();
        $user->save();

        $request->session()->forget('pending_two_factor_secret');
        AdminTwoFactor::markSessionPassed($request);

        AuditLogger::security($request, 'auth.two_factor.enabled', [
            'user_id' => $user->id,
            'username' => $user->username,
        ]);

        return redirect()
            ->route(AppHomeRoute::forRole($user->role))
            ->with('success', 'تم تفعيل المصادقة الثنائية بنجاح.');
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        $user = $this->adminUser($request);

        if (! AdminTwoFactor::isConfigured($user)) {
            return redirect()->route('user.two-factor.setup');
        }

        if (AdminTwoFactor::sessionPassed($request)) {
            return redirect()->route(AppHomeRoute::forRole($user->role));
        }

        return view('auth.two-factor-verify', compact('user'));
    }

    public function submitVerify(Request $request): RedirectResponse
    {
        $user = $this->adminUser($request);

        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $this->assertTwoFactorNotRateLimited($request, $user);

        $secret = (string) $user->two_factor_secret;

        if ($secret === '' || ! Totp::verify($secret, $data['code'])) {
            $this->recordTwoFactorFailure($request, $user);

            AuditLogger::security($request, 'auth.two_factor.failed', [
                'user_id' => $user->id,
                'username' => $user->username,
            ]);

            return back()->withErrors(['code' => 'رمز المصادقة الثنائية غير صحيح.']);
        }

        $this->clearTwoFactorRateLimit($request, $user);

        AdminTwoFactor::markSessionPassed($request);

        AuditLogger::security($request, 'auth.two_factor.passed', [
            'user_id' => $user->id,
            'username' => $user->username,
        ]);

        return redirect()
            ->route(AppHomeRoute::forRole($user->role))
            ->with('success', 'تم التحقق بنجاح.');
    }

    private function adminUser(Request $request): AppUser
    {
        $user = AppAuth::user($request);

        if (! $user instanceof AppUser || ! AdminTwoFactor::appliesTo($user)) {
            abort(403, 'غير مصرح لك بهذا الإجراء.');
        }

        return $user;
    }

    private function twoFactorRateLimitKey(Request $request, AppUser $user): string
    {
        return 'two-factor:' . $user->id . '|' . $request->ip();
    }

    private function assertTwoFactorNotRateLimited(Request $request, AppUser $user): void
    {
        $key = $this->twoFactorRateLimitKey($request, $user);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'code' => "محاولات تحقق كثيرة. أعد المحاولة بعد {$seconds} ثانية.",
            ]);
        }
    }

    private function recordTwoFactorFailure(Request $request, AppUser $user): void
    {
        RateLimiter::hit($this->twoFactorRateLimitKey($request, $user), 60);
    }

    private function clearTwoFactorRateLimit(Request $request, AppUser $user): void
    {
        RateLimiter::clear($this->twoFactorRateLimitKey($request, $user));
    }
}
