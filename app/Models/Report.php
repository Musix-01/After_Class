<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    public const REASONS = [
        'spam'          => 'Spam or advertising',
        'harassment'    => 'Bullying or harassment',
        'inappropriate' => 'Inappropriate content',
        'privacy'       => 'Shares private information',
        'misinformation' => 'False or misleading',
        'other'         => 'Something else',
    ];

    protected $fillable = ['post_id', 'user_id', 'reason', 'details', 'status', 'reviewed_by', 'reviewed_at'];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class)->withTrashed();
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getReasonLabelAttribute(): string
    {
        return self::REASONS[$this->reason] ?? 'Something else';
    }
}
