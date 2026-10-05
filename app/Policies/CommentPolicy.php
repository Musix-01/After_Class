<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /** A comment can be removed by whoever wrote it, or by the owner of the post it is on. */
    public function delete(User $user, Comment $comment): bool
    {
        return (int) $user->id === (int) $comment->user_id
            || (int) $user->id === (int) $comment->post?->user_id;
    }
}
