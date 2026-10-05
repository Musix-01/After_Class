<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ], [
            'body.required' => 'Write a comment before sending.',
            'body.max'      => 'Comments can be up to 1,000 characters.',
        ]);

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'body'    => $data['body'],
        ]);

        ActivityLog::record('comment.created', "{$request->user()->name} commented on post #{$post->id}", $comment);

        return back()->with('open_post', $post->id)->withFragment('post-' . $post->id);
    }

    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);

        $postId = $comment->post_id;
        $comment->delete();

        ActivityLog::record('comment.deleted', "{$request->user()->name} deleted a comment on post #{$postId}", null, ['post_id' => $postId]);

        return back()->with('open_post', $postId)->withFragment('post-' . $postId);
    }
}
