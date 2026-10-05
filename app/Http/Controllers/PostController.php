<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PostController extends Controller
{
    private const MESSAGES = [
        'body.required_without' => 'Write something or attach a photo first.',
        'body.max'              => 'Posts can be up to 2,000 characters.',
        'image.image'           => 'The file must be an image (JPG, PNG, WEBP or GIF).',
        'image.mimes'           => 'The file must be a JPG, PNG, WEBP or GIF image.',
        'image.max'             => 'Photos can be up to 4 MB.',
        'image.uploaded'        => 'The photo could not be uploaded. It may be larger than the server allows.',
    ];

    /** A single post on its own page (used by the "Share" link). */
    public function show(Request $request, int $post): View
    {
        $post = Post::query()->forWall($request->user()->id)->findOrFail($post);

        return view('posts.show', ['post' => $post]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'body'         => ['nullable', 'string', 'max:2000', 'required_without:image'],
            'image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'category'     => ['required', Rule::in(array_keys(Post::CATEGORIES))],
            'is_anonymous' => ['sometimes', 'boolean'],
        ], self::MESSAGES);

        $post = Post::create([
            'user_id'      => $request->user()->id,
            'category'     => $data['category'],
            'body'         => $data['body'] ?? null,
            'image_path'   => $request->hasFile('image')
                ? $request->file('image')->store('posts', 'public')
                : null,
            'is_anonymous' => $request->boolean('is_anonymous'),
        ]);

        ActivityLog::record(
            'post.created',
            "{$request->user()->name} posted on the wall" . ($post->is_anonymous ? ' (anonymously)' : ''),
            $post
        );

        return redirect()->route('home')->with('status', 'Your post is on the wall.');
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $data = $request->validate([
            'body'         => ['nullable', 'string', 'max:2000'],
            'image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'remove_image' => ['sometimes', 'boolean'],
        ], self::MESSAGES);

        $body        = trim($data['body'] ?? '');
        $replacing   = $request->hasFile('image');
        $removing    = $request->boolean('remove_image');
        $stillHasImg = $replacing || ($post->image_path && ! $removing);

        // A post (other than a repost) must keep some text or a photo.
        if ($body === '' && ! $stillHasImg && ! $post->original_post_id) {
            throw ValidationException::withMessages([
                'body' => 'A post needs some text or a photo.',
            ]);
        }

        if ($replacing || $removing) {
            $this->deleteImage($post);
            $post->image_path = null;
        }

        if ($replacing) {
            $post->image_path = $request->file('image')->store('posts', 'public');
        }

        $post->body = $body !== '' ? $body : null;
        $post->save();

        ActivityLog::record('post.edited', "{$request->user()->name} edited a post", $post);

        return back()->with('status', 'Post updated.')->withFragment('post-' . $post->id);
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $this->deleteImage($post);
        $post->forceFill(['image_path' => null])->save();
        $post->delete(); // soft delete: reposts will show "original removed"

        ActivityLog::record('post.deleted', "{$request->user()->name} deleted their own post", $post);

        return redirect()->route('home')->with('status', 'Post deleted.');
    }

    /** Share someone's post onto your own wall, with an optional note. */
    public function repost(Request $request, Post $post): RedirectResponse
    {
        // Always repost the root post, never a chain of reposts.
        $original = $post->original_post_id ? $post->original : $post;
        abort_if(! $original || $original->trashed(), 404);

        $data = $request->validate([
            'body'         => ['nullable', 'string', 'max:500'],
            'is_anonymous' => ['sometimes', 'boolean'],
        ], self::MESSAGES);

        $repost = Post::create([
            'user_id'          => $request->user()->id,
            'original_post_id' => $original->id,
            'category'         => $original->category,
            'body'             => $data['body'] ?? null,
            'is_anonymous'     => $request->boolean('is_anonymous'),
        ]);

        ActivityLog::record(
            'post.reposted',
            "{$request->user()->name} reposted post #{$original->id}" . ($repost->is_anonymous ? ' (anonymously)' : ''),
            $repost
        );

        return redirect()->route('home')->with('status', 'Reposted to the wall.');
    }

    private function deleteImage(Post $post): void
    {
        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }
    }
}
