<?php

namespace App\Support;

use App\Models\AppUser;
use App\Models\AuditLog;
use App\Models\Inquiry;
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
            'description' => $description ?? self::arabicDescription($action, $meta, $targetType, $targetId),
            'meta' => $meta === [] ? null : $meta,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    public static function displayDescription(AuditLog $log): string
    {
        if (filled($log->description)) {
            return (string) $log->description;
        }

        return self::arabicDescription(
            (string) $log->action,
            is_array($log->meta) ? $log->meta : [],
            $log->target_type,
            $log->target_id
        );
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function arabicDescription(
        string $action,
        array $meta,
        ?string $targetType = null,
        int|string|null $targetId = null
    ): string {
        $targetRef = self::targetRef($targetType, $targetId);
        $inquiryId = $meta['inquiry_id'] ?? $targetId;
        $userId = $meta['target_user_id'] ?? $meta['user_id'] ?? $targetId;

        return match ($action) {
            'auth.login.failed' => sprintf(
                'محاولة تسجيل دخول فاشلة باستخدام المعرف «%s».',
                (string) ($meta['login_id'] ?? 'غير معروف')
            ),
            'auth.login.success' => sprintf(
                'تسجيل دخول ناجح للحساب «%s» بدور %s.',
                (string) ($meta['username'] ?? 'غير معروف'),
                self::roleLabel(isset($meta['role']) ? (string) $meta['role'] : null)
            ),
            'auth.logout' => sprintf(
                'تسجيل خروج من الحساب «%s».',
                (string) ($meta['username'] ?? 'غير معروف')
            ),
            'auth.logout.home' => sprintf(
                'تسجيل خروج من البوابة للحساب «%s».',
                (string) ($meta['username'] ?? 'غير معروف')
            ),
            'auth.password.updated' => sprintf(
                'تغيير كلمة مرور الحساب «%s».',
                (string) ($meta['username'] ?? 'غير معروف')
            ),
            'auth.credentials.updated' => sprintf(
                'تحديث اسم المستخدم وكلمة المرور للحساب «%s».',
                (string) ($meta['username'] ?? 'غير معروف')
            ),
            'users.store' => sprintf(
                'إنشاء مستخدم جديد «%s» بدور %s.',
                (string) ($meta['username'] ?? 'غير معروف'),
                self::roleLabel(isset($meta['role']) ? (string) $meta['role'] : null)
            ),
            'users.update' => sprintf(
                'تحديث بيانات %s.',
                $targetRef !== '' ? $targetRef : 'المستخدم رقم ' . (string) ($userId ?? '—')
            ),
            'users.delete' => sprintf(
                'حذف المستخدم «%s» (%s).',
                (string) ($meta['username'] ?? 'غير معروف'),
                $targetRef !== '' ? $targetRef : 'رقم ' . (string) ($userId ?? '—')
            ),
            'users.bulk_delete' => sprintf(
                'حذف جماعي لعدد %d من المستخدمين.',
                (int) ($meta['deleted_count'] ?? 0)
            ),
            'users.excel.import' => 'استيراد مستخدمين من ملف إكسل.',
            'permissions.members.store' => sprintf(
                'إضافة صلاحيات للمنتسب (%s). الأدوار: %s. عدد الصلاحيات: %d.',
                $targetRef !== '' ? $targetRef : 'رقم ' . (string) ($userId ?? '—'),
                self::rolesList(isset($meta['roles']) && is_array($meta['roles']) ? $meta['roles'] : []),
                (int) ($meta['permissions_count'] ?? 0)
            ),
            'permissions.members.update' => sprintf(
                'تحديث صلاحيات المنتسب (%s). الأدوار: %s. عدد الصلاحيات: %d.',
                $targetRef !== '' ? $targetRef : 'رقم ' . (string) ($userId ?? '—'),
                self::rolesList(isset($meta['roles']) && is_array($meta['roles']) ? $meta['roles'] : []),
                (int) ($meta['permissions_count'] ?? 0)
            ),
            'inquiries.asker.store' => sprintf(
                'إرسال استفسار جديد من نوع %s.',
                self::inquiryTypeLabel(isset($meta['inquiry_type']) ? (string) $meta['inquiry_type'] : null)
            ),
            'inquiries.responder.answer' => sprintf(
                'حفظ إجابة على الاستفسار رقم %s (حالة الرد: %s).',
                (string) ($inquiryId ?? '—'),
                self::inquiryStatusLabel(isset($meta['status']) ? (string) $meta['status'] : null)
            ),
            'inquiries.responder.delete' => sprintf(
                'حذف الاستفسار رقم %s.',
                (string) ($inquiryId ?? '—')
            ),
            'inquiries.responder.restore' => sprintf(
                'استرجاع الاستفسار رقم %s من المحذوفات.',
                (string) ($inquiryId ?? '—')
            ),
            'inquiries.reviewer.review' => sprintf(
                '%s الاستفسار رقم %s (حالة التدقيق: %s).',
                self::reviewActionLabel(isset($meta['review_action']) ? (string) $meta['review_action'] : null),
                (string) ($inquiryId ?? '—'),
                self::reviewStatusLabel(isset($meta['review_status']) ? (string) $meta['review_status'] : null)
            ),
            default => $targetRef !== ''
                ? sprintf('عملية «%s» على %s.', $action, $targetRef)
                : sprintf('عملية «%s».', $action),
        };
    }

    private static function targetRef(?string $targetType, int|string|null $targetId): string
    {
        if ($targetType === null || $targetId === null || (string) $targetId === '') {
            return '';
        }

        $label = match ($targetType) {
            AppUser::class => 'المستخدم',
            Inquiry::class => 'الاستفسار',
            default => 'السجل',
        };

        return "{$label} رقم {$targetId}";
    }

    private static function roleLabel(?string $role): string
    {
        if ($role === null || $role === '') {
            return 'غير محدد';
        }

        return AppUser::ROLE_LABELS[$role] ?? $role;
    }

    /**
     * @param  list<string>  $roles
     */
    private static function rolesList(array $roles): string
    {
        if ($roles === []) {
            return 'بدون أدوار';
        }

        $labels = array_map(
            fn (string $role): string => AppUser::ROLE_LABELS[$role] ?? $role,
            $roles
        );

        return implode('، ', $labels);
    }

    private static function inquiryTypeLabel(?string $type): string
    {
        if ($type === null || $type === '') {
            return 'غير محدد';
        }

        return AppUser::RESPONDER_SCOPE_LABELS[$type] ?? $type;
    }

    private static function inquiryStatusLabel(?string $status): string
    {
        if ($status === null || $status === '') {
            return 'غير محددة';
        }

        return Inquiry::STATUS_LABELS[$status] ?? $status;
    }

    private static function reviewStatusLabel(?string $status): string
    {
        if ($status === null || $status === '') {
            return 'غير محددة';
        }

        return Inquiry::REVIEW_STATUS_LABELS[$status] ?? $status;
    }

    private static function reviewActionLabel(?string $action): string
    {
        return match ($action) {
            'approve' => 'اعتماد',
            'return' => 'إعادة للمجيب',
            default => 'تدقيق',
        };
    }
}
