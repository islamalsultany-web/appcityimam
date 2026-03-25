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

    protected $fillable = [
        'asker_user_id',
        'title',
        'priority',
        'preferred_channel',
        'body',
        'attachment_path',
        'status',
        'response_type',
        'follow_up_date',
        'response_body',
        'internal_note',
        'response_attachment_path',
        'responder_user_id',
        'responded_at',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
        'responded_at' => 'datetime',
    ];

    public function asker(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'asker_user_id');
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'responder_user_id');
    }
}
