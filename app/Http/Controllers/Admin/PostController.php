<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostController extends Controller
{
    /** Every post, including anonymous ones (with the real author) and removed ones. */
    public function index(Request $request): View
    {
        $q        = trim((string) $request->query('q'));
        $type     = $request->query('type');      // original | repost | pinned
        $status   = $request->query('status', 'live'); // live | removed | all
        $category = $request->query('category');

        $posts = Post::withTrashed()
            ->with(['user:id,name,email', 'original' => fn ($o) => $o->with('user:id,name')])
            ->withCount([
                'likes', 'comments', 'reposts',
                'reports as open_reports_count' => fn ($r) => $r->where('status', 'open'),
            ])
            ->when($q !== '', fn ($query) => $query->where(
                fn ($w) => $w->where('body', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"))
            ))
            ->when($type === 'repost', fn ($query) => $query->whereNotNull('original_post_id'))
            ->when($type === 'original', fn ($query) => $query->whereNull('original_post_id'))
            ->when($type === 'pinned', fn ($query) => $query->where('is_pinned', true))
            ->when($status === 'live', fn ($query) => $query->whereNull('deleted_at'))
            ->when($status === 'removed', fn ($query) => $query->whereNotNull('deleted_at'))
            ->when(isset(Post::CATEGORIES[$category]), fn ($query) => $query->where('category', $category))
            ->latest()
            ->latest('id')
            ->simplePaginate(15)
            ->withQueryString();

        return view('admin.posts.index', compact('posts', 'q', 'type', 'status', 'category'));
    }

    public function show(int $post): View
    {
        $post = Post::withTrashed()
            ->with(['user', 'remover:id,name', 'original' => fn ($o) => $o->with('user:id,name'), 'comments.user:id,name', 'reports.reporter:id,name'])
            ->withCount(['likes', 'reposts'])
            ->findOrFail($post);

        return view('admin.posts.show', compact('post'));
    }

    /** Community post / discussion written by the admin team (can be pinned to the top). */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'body'      => ['nullable', 'string', 'max:2000', 'required_without:image'],
            'image'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'category'  => ['required', Rule::in(array_keys(Post::CATEGORIES))],
            'is_pinned' => ['sometimes', 'boolean'],
        ], [
            'body.required_without' => 'Write something or attach a photo first.',
        ]);

        $post = Post::create([
            'user_id'      => $request->user()->id,
            'category'     => $data['category'],
            'body'         => $data['body'] ?? null,
            'image_path'   => $request->hasFile('image') ? $request->file('image')->store('posts', 'public') : null,
            'is_anonymous' => false,
            'is_pinned'    => $request->boolean('is_pinned'),
        ]);

        ActivityLog::record('post.created', "{$request->user()->name} (admin) started a community post", $post);

        return redirect()->route('admin.posts.show', $post->id)->with('status', 'Posted to the community wall.');
    }

    public function pin(int $post): RedirectResponse
    {
        $post = Post::findOrFail($post);
        $post->forceFill(['is_pinned' => ! $post->is_pinned])->save();

        ActivityLog::record($post->is_pinned ? 'post.pinned' : 'post.unpinned', ($post->is_pinned ? 'Pinned' : 'Unpinned') . " post #{$post->id}", $post);

        return back()->with('status', $post->is_pinned ? 'Pinned to the top of the wall.' : 'Unpinned.');
    }

    public function remove(Request $request, int $post): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:200']], [
            'reason.required' => 'Write a short reason for the audit log.',
        ]);

        Post::findOrFail($post)->removeByModerator($request->user(), $data['reason']);

        return back()->with('status', 'Post removed from the wall.');
    }

    public function restore(Request $request, int $post): RedirectResponse
    {
        $post = Post::onlyTrashed()->findOrFail($post);

        if (! $post->wasRemovedByModerator()) {
            return back()->withErrors(['post' => 'The author deleted this post themselves, so it cannot be restored.']);
        }

        $post->restoreByModerator($request->user());

        return back()->with('status', 'Post restored.');
    }

    public function destroyComment(Request $request, Comment $comment): RedirectResponse
    {
        $postId = $comment->post_id;
        $comment->delete();

        ActivityLog::record('comment.removed', "Removed a comment on post #{$postId}", null, ['post_id' => $postId], false, $request->user()->id);

        return back()->with('status', 'Comment removed.');
    }
}
