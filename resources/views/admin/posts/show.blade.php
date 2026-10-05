@extends('admin.layout')

@section('title', 'Post #' . $post->id)

@section('actions')
    <a href="{{ route('admin.posts.index') }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> All posts</a>
    @unless ($post->trashed())
        <a href="{{ route('posts.show', $post->id) }}" class="btn btn-ghost btn-sm" target="_blank" rel="noopener">View on the wall</a>
    @endunless
@endsection

@section('admin')

    <div class="split">

        {{-- The post --}}
        <section class="acard">
            <div class="profile-head">
                <span class="avatar avatar-md" aria-hidden="true">{{ mb_strtoupper(mb_substr($post->user->name ?? '?', 0, 1)) }}</span>
                <div>
                    <h2>
                        <a href="{{ route('admin.users.show', $post->user_id) }}">{{ $post->user->name ?? 'Deleted user' }}</a>
                    </h2>
                    <p class="muted small">
                        {{ $post->created_at->format('M j, Y g:i A') }}
                        @if ($post->is_anonymous)
                            <span class="badge badge-gray"><i class="fa-solid fa-user-secret" aria-hidden="true"></i> shown as Anonymous to students</span>
                        @endif
                    </p>
                </div>
            </div>

            <p>
                @if ($post->trashed())
                    <span class="badge badge-pink">{{ $post->removed_by ? 'Removed by ' . ($post->remover->name ?? 'a moderator') : 'Deleted by its author' }}</span>
                @else
                    <span class="badge badge-green">Live</span>
                @endif
                @if ($post->is_pinned)<span class="badge badge-yellow">Pinned</span>@endif
                @if ($post->original_post_id)<span class="badge badge-blue">Repost</span>@endif
                <span class="badge badge-gray">{{ \App\Models\Post::CATEGORIES[$post->category] ?? $post->category }}</span>
            </p>

            @if ($post->trashed() && $post->removal_reason)
                <p class="note-box"><strong>Removal reason:</strong> {{ $post->removal_reason }}</p>
            @endif

            @if ($post->body)
                <div class="post-preview">{!! nl2br(e($post->body)) !!}</div>
            @endif

            @if ($post->image_url)
                <a href="{{ $post->image_url }}" target="_blank" rel="noopener"><img class="admin-photo" src="{{ $post->image_url }}" alt="Photo attached to the post"></a>
            @endif

            @if ($post->original_post_id)
                <div class="note-box">
                    <strong>Reposted from</strong>
                    @if ($post->original)
                        <a href="{{ route('admin.posts.show', $post->original->id) }}">post #{{ $post->original->id }}</a>
                        by {{ $post->original->user->name ?? 'a student' }}@if ($post->original->is_anonymous) (anonymous)@endif
                        @if ($post->original->trashed()) <span class="badge badge-pink">removed</span>@endif
                        <br>
                        <span class="muted">{{ \Illuminate\Support\Str::limit($post->original->body ?: '(photo only)', 160) }}</span>
                    @else
                        <span class="muted">an original that no longer exists.</span>
                    @endif
                </div>
            @endif

            <dl class="kv">
                <div><dt>Likes</dt><dd>{{ $post->likes_count }}</dd></div>
                <div><dt>Comments</dt><dd>{{ $post->comments->count() }}</dd></div>
                <div><dt>Reposts</dt><dd>{{ $post->reposts_count }}</dd></div>
            </dl>
        </section>

        {{-- Actions --}}
        <section class="acard">
            <h2>Moderate</h2>

            @if ($post->trashed())
                @if ($post->removed_by)
                    <form method="POST" action="{{ route('admin.posts.restore', $post->id) }}" class="stack">
                        @csrf
                        <p class="muted">Restoring puts the post back on the wall.</p>
                        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Restore post</button>
                    </form>
                @else
                    <p class="muted">The author deleted this post themselves, so it can't be restored.</p>
                @endif
            @else
                <div class="stack">
                    <form method="POST" action="{{ route('admin.posts.pin', $post->id) }}">
                        @csrf
                        <button class="btn btn-ghost" type="submit">
                            <i class="fa-solid fa-thumbtack" aria-hidden="true"></i> {{ $post->is_pinned ? 'Unpin from the wall' : 'Pin to the top of the wall' }}
                        </button>
                    </form>

                    @include('admin.partials.remove-form', ['action' => route('admin.posts.remove', $post->id), 'label' => 'Remove this post'])
                </div>
            @endif

            <hr class="rule">
            <h2>Reports ({{ $post->reports->count() }})</h2>
            @forelse ($post->reports as $report)
                <div class="mini-report">
                    <div>
                        <span class="badge badge-{{ $report->status === 'open' ? 'pink' : 'gray' }}">{{ ucfirst($report->status) }}</span>
                        <strong>{{ $report->reason_label }}</strong>
                    </div>
                    @if ($report->details)<p>{{ $report->details }}</p>@endif
                    <small class="muted">by {{ $report->reporter->name ?? 'a student' }}, {{ $report->created_at->diffForHumans() }}</small>
                </div>
            @empty
                <p class="muted">Nobody has reported this post.</p>
            @endforelse
        </section>
    </div>

    {{-- Comments --}}
    <section class="acard">
        <div class="acard-head"><h2>Comments ({{ $post->comments->count() }})</h2></div>
        <ul class="feed feed-comments">
            @forelse ($post->comments as $comment)
                <li>
                    <span class="feed-text">
                        <strong>{{ $comment->user->name ?? 'Deleted user' }}</strong>
                        <small class="muted">{{ $comment->created_at->diffForHumans() }}</small><br>
                        {{ $comment->body }}
                    </span>
                    <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" data-confirm="Remove this comment?">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" type="submit"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Remove</button>
                    </form>
                </li>
            @empty
                <li class="muted">No comments.</li>
            @endforelse
        </ul>
    </section>

@endsection
