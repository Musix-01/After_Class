<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MysteryTheory extends Model
{
    protected $fillable = ['mystery_id', 'user_id', 'body', 'is_accepted'];

    protected $casts = ['is_accepted' => 'boolean'];

    public function mystery(): BelongsTo
    {
        return $this->belongsTo(Mystery::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
