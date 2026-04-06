<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inquiry extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_LABELS = [
        'pending' => 'بانتظار الرد',
        'in_progress' => 'قيد المعالجة',
        'answered' => 'تمت الإجابة',
        'needs_info' => 'بحاجة معلومات إضافية',
        'closed' => 'مغلق',
    ];

    public const REVIEW_STATUS_LABELS = [
        'pending_review' => 'بانتظار التدقيق',
        'approved' => 'معتمد',
        'returned' => 'معاد للمجيب',
    ];

    protected $fillable = [
        'asker_user_id',
        'title',
        'inquiry_type',
        'priority',
        'body',
        'attachment_path',
        'status',
        'response_type',
        'follow_up_date',
        'response_body',
        'review_status',
        'review_note',
        'internal_note',
        'response_attachment_path',
        'responder_user_id',
        'reviewed_by_user_id',
        'responded_at',
        'reviewed_at',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
        'responded_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function asker(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'asker_user_id');
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'responder_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'reviewed_by_user_id');
    }

    public function isResponseApproved(): bool
    {
        return $this->review_status === 'approved'
            && $this->response_body !== null
            && trim($this->response_body) !== '';
    }

    public function publicResponseBody(): ?string
    {
        return $this->isResponseApproved() ? $this->response_body : null;
    }

    public function publicResponsePlaceholder(): string
    {
        return match ($this->review_status) {
            'pending_review' => 'بانتظار اعتماد المدقق.',
            'returned' => 'تمت إعادة الإجابة للمجيب للتعديل.',
            default => 'لا توجد إجابة حتى الآن.',
        };
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? (string) $this->status;
    }

    public function displayStatusLabel(): string
    {
        return $this->review_status === 'pending_review'
            ? 'قيد التدقيق'
            : $this->statusLabel();
    }

    public function statusBadgeClass(bool $preferReviewState = false): string
    {
        if ($preferReviewState && $this->review_status === 'pending_review') {
            return 'status-reviewing';
        }

        return match ($this->status) {
            'pending' => 'status-pending',
            'in_progress' => 'status-progress',
            'answered' => 'status-answered',
            'needs_info' => 'status-needs-info',
            'closed' => 'status-closed',
            default => 'status-neutral',
        };
    }

    public function reviewStatusLabel(): string
    {
        return self::REVIEW_STATUS_LABELS[$this->review_status] ?? 'لم تُرسل للتدقيق';
    }

    public function reviewStatusBadgeClass(): string
    {
        return match ($this->review_status) {
            'pending_review' => 'status-reviewing',
            'approved' => 'status-approved',
            'returned' => 'status-returned',
            default => 'status-neutral',
        };
    }
}
