<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Support\AppHomeRoute;
use App\Support\EmployeeCredentialSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function index(Request $request): View
    {
        $authUser = Auth::user();
        if (! $authUser instanceof AppUser && $request->session()->has('auth_app_user_id')) {
            $authUser = AppUser::query()->find((int) $request->session()->get('auth_app_user_id'));
            if ($authUser instanceof AppUser) {
                Auth::login($authUser);
            }
        }

        $isAuthenticated = $authUser instanceof AppUser;
        $authUsername = $isAuthenticated ? (string) $authUser->username : '';
        $authRole = $isAuthenticated ? (string) $authUser->role : '';

        $inquiryRouteName = (string) config('portal.inquiry_route', 'login.form');
        if (! Route::has($inquiryRouteName)) {
            $inquiryRouteName = 'login.form';
        }

        $inquiryUrl = route($inquiryRouteName);
        $dashboardRoute = null;

        if ($isAuthenticated) {
            if (EmployeeCredentialSecurity::mustChangeCredentials($authUser)) {
                $inquiryUrl = route('user.credentials.setup');
                $dashboardRoute = 'user.credentials.setup';
            } else {
                $homeRoute = AppHomeRoute::forRole($authRole);
                $inquiryUrl = route($homeRoute);
                $dashboardRoute = $homeRoute;
            }
        }

        return view('index2', [
            'isAuthenticated' => $isAuthenticated,
            'authUsername' => $authUsername,
            'dashboardRoute' => $dashboardRoute,
            'inquiryUrl' => $inquiryUrl,
            'portalLinks' => [
                'hr' => (string) config('portal.hr_url'),
                'finance' => (string) config('portal.finance_url'),
                'assets' => (string) config('portal.assets_url'),
            ],
        ]);
    }
}
