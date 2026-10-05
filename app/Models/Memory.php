<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Memory extends Model
{
    protected $fillable = ['title', 'body', 'image_path', 'unlock_at', 'opened_at', 'created_by'];

    protected $casts = [
        'unlock_at' => 'datetime',
        'opened_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** A capsule is open once its date has passed, or when an admin opens it early. */
    public function isUnlocked(): bool
    {
        return $this->opened_at !== null || $this->unlock_at->lte(now());
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }
}
