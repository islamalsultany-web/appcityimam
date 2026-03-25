<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class AppUser extends Authenticatable
{
    use HasFactory;
    use HasRoles;

    protected string $guard_name = 'web';

    protected $table = 'app_users';

    protected $fillable = [
        'username',
        'password',
        'password_confirmation',
        'employee_number',
        'badge_number',
        'division',
        'unit',
        'role',
    ];

    protected $hidden = [
        'password',
        'password_confirmation',
    ];
}
