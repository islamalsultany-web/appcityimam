<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Support\AppAuth;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * @return array<string, string>
     */
    private function actionLabels(): array
    {
        return [
            'auth.login.success' => 'تسجيل دخول ناجح',
            'auth.login.failed' => 'فشل تسجيل الدخول',
            'auth.logout' => 'تسجيل خروج',
            'auth.logout.home' => 'تسجيل خروج من البوابة',
            'auth.password.updated' => 'تحديث كلمة المرور',
            'auth.credentials.updated' => 'تحديث بيانات الدخول',
            'users.store' => 'إنشاء مستخدم',
            'users.update' => 'تحديث مستخدم',
            'users.delete' => 'حذف مستخدم',
            'users.bulk_delete' => 'حذف جماعي للمستخدمين',
            'users.excel.import' => 'استيراد مستخدمين من إكسل',
            'permissions.members.store' => 'إضافة صلاحيات منتسب',
            'permissions.members.update' => 'تحديث صلاحيات منتسب',
            'inquiries.asker.store' => 'إرسال استفسار',
            'inquiries.responder.answer' => 'إجابة استفسار',
            'inquiries.responder.delete' => 'حذف استفسار',
            'inquiries.responder.restore' => 'استرجاع استفسار',
            'inquiries.reviewer.review' => 'تدقيق استفسار',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function targetTypeLabels(): array
    {
        return [
            'App\\Models\\AppUser' => 'المستخدم',
            'App\\Models\\Inquiry' => 'الاستفسار',
        ];
    }

    public function index(Request $request): View
    {
        $user = AppAuth::user($request);

        if (! $user || ! ($user->role === 'admin' || $user->hasRole('admin'))) {
            abort(403);
        }

        $validated = $request->validate([
            'actor' => ['nullable', 'string', 'max:120'],
            'action' => ['nullable', 'string', 'max:120'],
            'target' => ['nullable', 'string', 'max:120'],
            'ip' => ['nullable', 'string', 'max:45'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $actionTerm = trim((string) ($validated['action'] ?? ''));
        $targetTerm = trim((string) ($validated['target'] ?? ''));
        $actionLabels = $this->actionLabels();
        $targetTypeLabels = $this->targetTypeLabels();

        $matchingActionKeys = [];
        if ($actionTerm !== '') {
            foreach ($actionLabels as $key => $label) {
                if (mb_stripos($label, $actionTerm) !== false || mb_stripos($key, $actionTerm) !== false) {
                    $matchingActionKeys[] = $key;
                }
            }
        }

        $matchingTargetTypes = [];
        if ($targetTerm !== '') {
            foreach ($targetTypeLabels as $type => $label) {
                if (mb_stripos($label, $targetTerm) !== false || mb_stripos($type, $targetTerm) !== false) {
                    $matchingTargetTypes[] = $type;
                }
            }
        }

        $logs = AuditLog::query()
            ->when(! empty($validated['actor']), fn ($q) => $q->where('actor_username', 'like', '%' . trim((string) $validated['actor']) . '%'))
            ->when($actionTerm !== '', function ($q) use ($actionTerm, $matchingActionKeys) {
                $q->where(function ($subQ) use ($actionTerm, $matchingActionKeys): void {
                    $subQ->where('action', 'like', '%' . $actionTerm . '%');

                    if ($matchingActionKeys !== []) {
                        $subQ->orWhereIn('action', $matchingActionKeys);
                    }
                });
            })
            ->when($targetTerm !== '', function ($q) use ($targetTerm, $matchingTargetTypes) {
                $q->where(function ($subQ) use ($targetTerm, $matchingTargetTypes): void {
                    $subQ->where('target_type', 'like', '%' . $targetTerm . '%');

                    if ($matchingTargetTypes !== []) {
                        $subQ->orWhereIn('target_type', $matchingTargetTypes);
                    }
                });
            })
            ->when(! empty($validated['ip']), fn ($q) => $q->where('ip_address', 'like', '%' . trim((string) $validated['ip']) . '%'))
            ->when(! empty($validated['date_from']), fn ($q) => $q->whereDate('created_at', '>=', $validated['date_from']))
            ->when(! empty($validated['date_to']), fn ($q) => $q->whereDate('created_at', '<=', $validated['date_to']))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('security.audit-logs', [
            'logs' => $logs,
            'filters' => $validated,
            'actionLabels' => $actionLabels,
            'targetTypeLabels' => $targetTypeLabels,
        ]);
    }
}
