<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /** Only the person who created a post can edit it (even if it is anonymous). */
    public function update(User $user, Post $post): bool
    {
        return (int) $user->id === (int) $post->user_id;
    }

    public function delete(User $user, Post $post): bool
    {
        return (int) $user->id === (int) $post->user_id;
    }
}
