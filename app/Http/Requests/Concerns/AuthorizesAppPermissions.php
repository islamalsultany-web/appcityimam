<?php

namespace App\Http\Requests\Concerns;

use App\Support\AppAuth;

trait AuthorizesAppPermissions
{
    protected function appCan(string $permission): bool
    {
        return AppAuth::can($this, $permission);
    }
}
