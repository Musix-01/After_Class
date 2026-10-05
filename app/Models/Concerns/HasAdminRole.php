<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Add `use HasAdminRole;` to App\Models\User.
 * Gives the user a role, suspension support and the relations the admin panel needs.
 */
trait HasAdminRole
{
    public static function bootHasAdminRole(): void
    {
        // Every new account lands in the audit log (and the admin notifications).
        static::created(function ($user) {
            ActivityLog::record('user.registered', "{$user->name} created an account", $user, [], true, $user->id);
        });
    }

    public function initializeHasAdminRole(): void
    {
        $this->mergeCasts([
            'suspended_at'    => 'datetime',
            'suspended_until' => 'datetime',
            'admin_seen_at'   => 'datetime',
        ]);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null
            && ($this->suspended_until === null || $this->suspended_until->isFuture());
    }

    public function scopeSuspended(Builder $query): Builder
    {
        return $query->whereNotNull('suspended_at')
            ->where(fn ($q) => $q->whereNull('suspended_until')->orWhere('suspended_until', '>', now()));
    }
}
