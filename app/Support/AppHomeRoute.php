<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class AppHomeRoute
{
    public static function forRole(string $role): string
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
