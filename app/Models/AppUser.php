<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class AppUser extends Authenticatable
{
    use HasFactory;
    use HasRoles;

    public const ROLE_OPTIONS = ['asker', 'responder', 'reviewer', 'admin'];

    /** Roles allowed when bulk-importing users from Excel (admin is excluded). */
    public const IMPORTABLE_ROLES = ['asker', 'responder', 'reviewer'];

    public const ROLE_LABELS = [
        'asker' => 'مستفسر',
        'responder' => 'مجيب',
        'reviewer' => 'مدقق',
        'admin' => 'مدير النظام',
    ];

    public const RESPONDER_SCOPE_OPTIONS = ['all', 'financial', 'administrative', 'technical', 'warehouse', 'other'];

    public const RESPONDER_SCOPE_LABELS = [
        'all' => 'كل الأنواع',
        'financial' => 'مالي',
        'administrative' => 'إداري',
        'technical' => 'تقني',
        'warehouse' => 'مخزني',
        'other' => 'أخرى',
    ];

    protected string $guard_name = 'web';

    protected $table = 'app_users';

    protected $fillable = [
        'username',
        'password',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'employee_number',
        'badge_number',
        'division',
        'unit',
        'role',
        'responder_scopes',
    ];

    protected $casts = [
        'responder_scopes' => 'array',
        'two_factor_secret' => 'encrypted',
        'two_factor_confirmed_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
    ];

    public static function sanitizeResponderScopes(?array $scopes, string $role): array
    {
        if (! in_array($role, ['responder', 'reviewer', 'admin'], true)) {
            return [];
        }

        $validScopes = array_values(array_intersect(array_unique($scopes ?? []), self::RESPONDER_SCOPE_OPTIONS));

        if ($validScopes === [] || in_array('all', $validScopes, true)) {
            return ['all'];
        }

        return $validScopes;
    }

    public function normalizedResponderScopes(): array
    {
        return self::sanitizeResponderScopes($this->responder_scopes, (string) $this->role);
    }

    public function canHandleInquiryType(?string $inquiryType): bool
    {
        if ($this->role === 'admin' || $this->hasRole('admin')) {
            return true;
        }

        $scopes = $this->normalizedResponderScopes();

        return in_array('all', $scopes, true)
            || ($inquiryType !== null && in_array($inquiryType, $scopes, true));
    }
}
