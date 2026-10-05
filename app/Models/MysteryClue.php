<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MysteryClue extends Model
{
    protected $fillable = ['mystery_id', 'body'];

    public function mystery(): BelongsTo
    {
        return $this->belongsTo(Mystery::class);
    }
}
