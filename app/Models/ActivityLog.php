<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class ActivityLog extends Model
{
    public const UPDATED_AT = null; // logs are written once and never edited

    protected $fillable = [
        'user_id', 'action', 'description', 'subject_type', 'subject_id',
        'properties', 'ip_address', 'notify',
    ];

    protected $casts = [
        'properties' => 'array',
        'notify'     => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Write one line to the audit trail. Never throws: logging must not break a request.
     *
     * @param  bool  $notify  also show it in the admin notification centre
     */
    public static function record(
        string $action,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        bool $notify = false,
        ?int $actorId = null,
    ): ?self {
        try {
            return static::create([
                'user_id'      => $actorId ?? Auth::id(),
                'action'       => $action,
                'description'  => Str::limit($description, 240),
                'subject_type' => $subject?->getMorphClass(),
                'subject_id'   => $subject?->getKey(),
                'properties'   => $properties ?: null,
                'ip_address'   => request()->ip(),
                'notify'       => $notify,
            ]);
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /** Notifications the given admin has not looked at yet. */
    public function scopeUnreadFor(Builder $query, User $admin): Builder
    {
        return $query->where('notify', true)
            ->when($admin->admin_seen_at, fn ($q, $seen) => $q->where('created_at', '>', $seen));
    }
}
