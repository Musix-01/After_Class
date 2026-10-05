@extends('admin.layout')

@section('title', 'Posts')

@section('admin')

    {{-- Community post / discussion by the team --}}
    <details class="acard compose-card" @if ($errors->has('body') || $errors->has('image')) open @endif>
        <summary class="compose-summary">
            <i class="fa-solid fa-pen" aria-hidden="true"></i> Start a community post or discussion
        </summary>

        <form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data" class="stack">
            @csrf
            <div class="field">
                <label for="new-body">What do you want the community to talk about?</label>
                <textarea id="new-body" name="body" rows="4" maxlength="2000" class="input js-autosize"
                          placeholder="Write a post, a question or a discussion topic...">{{ old('body') }}</textarea>
            </div>

            <div class="grid-3">
                <div class="field">
                    <label for="new-category">Category</label>
                    <select id="new-category" name="category" class="input">
                        @foreach (\App\Models\Post::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" @selected(old('category', 'general') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="new-image">Photo (optional)</label>
                    <input type="file" id="new-image" name="image" accept="image/*" class="input input-file">
                </div>
                <label class="check check-box">
                    <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned'))>
                    <span>Pin to the top of the wall</span>
                </label>
            </div>

            <p class="muted small">It appears on the wall with an Admin badge. Students can like, comment, repost and share it like any other post.</p>
            <div><button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Publish to the wall</button></div>
        </form>
    </details>

    {{-- Filters --}}
    <form method="GET" class="toolbar" role="search">
        <label class="sr-only" for="q">Search posts</label>
        <input type="search" id="q" name="q" value="{{ $q }}" class="input" placeholder="Search text or author">

        <label class="sr-only" for="type">Type</label>
        <select id="type" name="type" class="input">
            <option value="">All types</option>
            <option value="original" @selected($type === 'original')>Original posts</option>
            <option value="repost" @selected($type === 'repost')>Reposts</option>
            <option value="pinned" @selected($type === 'pinned')>Pinned</option>
        </select>

        <label class="sr-only" for="category">Category</label>
        <select id="category" name="category" class="input">
            <option value="">All categories</option>
            @foreach (\App\Models\Post::CATEGORIES as $key => $label)
                <option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>
            @endforeach
        </select>

        <label class="sr-only" for="status">Status</label>
        <select id="status" name="status" class="input">
            <option value="live" @selected($status === 'live')>On the wall</option>
            <option value="removed" @selected($status === 'removed')>Removed or deleted</option>
            <option value="all" @selected($status === 'all')>Everything</option>
        </select>

        <button class="btn btn-primary btn-sm" type="submit">Filter</button>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Author</th><th>Post</th><th>Category</th><th class="num">Likes</th><th class="num">Comments</th><th class="num">Reposts</th><th>Status</th><th>Posted</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr>
                        <td>
                            <strong>{{ $post->user->name ?? 'Deleted user' }}</strong>
                            @if ($post->is_anonymous)<small class="anon-note"><i class="fa-solid fa-user-secret" aria-hidden="true"></i> posted anonymously</small>@endif
                        </td>
                        <td class="excerpt">
                            @if ($post->original_post_id)<span class="badge badge-blue">Repost</span>@endif
                            @if ($post->is_pinned)<span class="badge badge-yellow">Pinned</span>@endif
                            {{ \Illuminate\Support\Str::limit($post->body ?: ($post->image_path ? '(photo only)' : '(no text)'), 80) }}
                            @if ($post->open_reports_count)<span class="badge badge-pink">{{ $post->open_reports_count }} open {{ \Illuminate\Support\Str::plural('report', $post->open_reports_count) }}</span>@endif
                        </td>
                        <td>{{ \App\Models\Post::CATEGORIES[$post->category] ?? $post->category }}</td>
                        <td class="num">{{ $post->likes_count }}</td>
                        <td class="num">{{ $post->comments_count }}</td>
                        <td class="num">{{ $post->reposts_count }}</td>
                        <td>
                            @if ($post->trashed())
                                <span class="badge badge-pink">{{ $post->removed_by ? 'Removed' : 'Deleted by author' }}</span>
                            @else
                                <span class="badge badge-green">Live</span>
                            @endif
                        </td>
                        <td>{{ $post->created_at->format('M j, g:i A') }}</td>
                        <td class="row-actions"><a href="{{ route('admin.posts.show', $post->id) }}" class="btn btn-ghost btn-sm">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="empty-row">No posts match those filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('admin.partials.pager', ['items' => $posts])

@endsection
