<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppUser extends Model
{
    use HasFactory;

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
