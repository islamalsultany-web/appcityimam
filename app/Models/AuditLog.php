<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'actor_user_id',
        'actor_username',
        'action',
        'target_type',
        'target_id',
        'description',
        'meta',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
