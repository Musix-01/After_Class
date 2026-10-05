<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mystery extends Model
{
    public const STATUSES = [
        'open'     => 'Under investigation',
        'solved'   => 'Solved',
        'debunked' => 'Debunked',
    ];

    protected $fillable = ['title', 'summary', 'location', 'status', 'resolution', 'resolved_at', 'created_by'];

    protected $casts = ['resolved_at' => 'datetime'];

    public function clues(): HasMany
    {
        return $this->hasMany(MysteryClue::class)->oldest();
    }

    /** Accepted theory first, then newest. */
    public function theories(): HasMany
    {
        return $this->hasMany(MysteryTheory::class)->orderByDesc('is_accepted')->latest();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
