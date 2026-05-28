<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function security(
        Request $request,
        string $action,
        array $meta = [],
        ?string $description = null,
        ?string $targetType = null,
        int|string|null $targetId = null
    ): void {
        $actor = Auth::user();

        if (! $actor instanceof AppUser) {
            $actor = AppAuth::user($request);
        }

        AuditLog::query()->create([
            'actor_user_id' => $actor?->id,
            'actor_username' => $actor?->username,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId !== null ? (string) $targetId : null,
            'description' => $description,
            'meta' => $meta === [] ? null : $meta,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
