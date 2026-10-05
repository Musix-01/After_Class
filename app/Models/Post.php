<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    /** category key => label shown in the UI */
    public const CATEGORIES = [
        'general' => 'Just sharing',
        'story'   => 'Campus story',
        'memory'  => 'Memory',
        'mystery' => 'Mystery',
    ];

    protected $fillable = [
        'user_id',
        'original_post_id',
        'category',
        'body',
        'image_path',
        'is_anonymous',
        'is_pinned',
        'removed_by',
        'removal_reason',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_pinned'    => 'boolean',
    ];

    /* ---------- Relationships ---------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The moderator who removed this post (null when the author deleted it). */
    public function remover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'removed_by');
    }

    /** The post this one reposts (kept even if the original was deleted, so we can say so). */
    public function original(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'original_post_id')->withTrashed();
    }

    public function reposts(): HasMany
    {
        return $this->hasMany(Post::class, 'original_post_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->oldest();
    }

    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_likes')->withTimestamps();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /* ---------- Helpers ---------- */

    public function isOwnedBy(?int $userId): bool
    {
        return $userId !== null && (int) $this->user_id === (int) $userId;
    }

    /** Public URL of the uploaded photo (uses the current host, so it works on any dev URL). */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    /** "Anonymous" when the author chose to hide their name. */
    public function getAuthorNameAttribute(): string
    {
        return $this->is_anonymous ? 'Anonymous' : ($this->user->name ?? 'Student');
    }

    public function getWasEditedAttribute(): bool
    {
        return $this->updated_at->gt($this->created_at->copy()->addSeconds(5));
    }

    /** Hidden by a moderator (as opposed to deleted by its author). */
    public function wasRemovedByModerator(): bool
    {
        return $this->trashed() && $this->removed_by !== null;
    }

    /** Take the post down, close its open reports and write the audit log. */
    public function removeByModerator(User $admin, string $reason): void
    {
        $this->forceFill(['removed_by' => $admin->id, 'removal_reason' => $reason])->save();
        $this->delete(); // soft delete: reposts show "original removed", and it can be restored

        $this->reports()->where('status', 'open')->update([
            'status'      => 'actioned',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        ActivityLog::record(
            'post.removed',
            "Removed a post by {$this->user->name}: {$reason}",
            $this,
            ['author_id' => $this->user_id, 'reason' => $reason],
            false,
            $admin->id
        );
    }

    public function restoreByModerator(User $admin): void
    {
        $this->restore();
        $this->forceFill(['removed_by' => null, 'removal_reason' => null])->save();

        ActivityLog::record('post.restored', "Restored a post by {$this->user->name}", $this, [], false, $admin->id);
    }

    /* ---------- Scopes ---------- */

    /**
     * Everything the wall card needs, in as few queries as possible:
     * author, original (for reposts), comments, plus like/comment/repost counts
     * and whether the current user already liked the post.
     */
    public function scopeForWall(Builder $query, int $userId): Builder
    {
        return $query
            ->with(['user:id,name,role', 'original.user:id,name,role', 'comments.user:id,name,role'])
            ->withCount(['likes', 'comments', 'reposts'])
            ->withExists([
                'likes as liked_by_me' => fn ($q) => $q->where('post_likes.user_id', $userId),
            ]);
    }
}
