{{--
    One post on the wall.

    Needs: $post loaded through Post::forWall() (author, original, comments, counts, liked_by_me).
    Optional: $expanded (bool) – start with the comments open (used on the single post page).
--}}
@php
    $me        = auth()->id();
    $viewer    = auth()->user();
    $isOwner   = $post->isOwnedBy($me);
    $isAnon    = $post->is_anonymous;
    $name      = $post->author_name;
    $original  = $post->original;
    $authorIsAdmin = ! $isAnon && $post->user && $post->user->isAdmin();
    $showComments = ($expanded ?? false) || (int) session('open_post') === (int) $post->id;
    $categoryLabel = \App\Models\Post::CATEGORIES[$post->category] ?? 'Just sharing';
@endphp

<article class="post {{ $post->is_pinned ? 'is-pinned' : '' }}" id="post-{{ $post->id }}" data-category="{{ $post->category }}">

    @if ($post->is_pinned)
        <p class="repost-flag pin-flag">
            <i class="fa-solid fa-thumbtack" aria-hidden="true"></i> Pinned by the team
        </p>
    @endif

    @if ($post->original_post_id)
        <p class="repost-flag">
            <i class="fa-solid fa-retweet" aria-hidden="true"></i> Reposted from the wall
        </p>
    @endif

    {{-- Author, time, category and the post menu --}}
    <header class="post-head">
        <span class="avatar avatar-md {{ $isAnon ? 'avatar-anon' : '' }}" aria-hidden="true">
            @if ($isAnon)
                <i class="fa-solid fa-user-secret"></i>
            @else
                {{ mb_strtoupper(mb_substr($name, 0, 1)) }}
            @endif
        </span>

        <div class="post-who">
            <div class="post-name">
                <strong>{{ $name }}</strong>
                @if ($authorIsAdmin)
                    <span class="admin-badge"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Admin</span>
                @endif
                @if ($isAnon && $isOwner)
                    <span class="you-tag">only you can see this is you</span>
                @endif
            </div>
            <div class="post-sub">
                <a href="{{ route('posts.show', $post) }}" class="post-time">
                    <time datetime="{{ $post->created_at->toIso8601String() }}"
                          title="{{ $post->created_at->format('M j, Y g:i A') }}">
                        {{ $post->created_at->diffForHumans() }}
                    </time>
                </a>
                @if ($post->was_edited)
                    <span>edited</span>
                @endif
                <span class="cat-chip">{{ $categoryLabel }}</span>
            </div>
        </div>

        <details class="post-menu">
            <summary aria-label="Post options"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i></summary>
            <div class="menu-panel">
                @if ($isOwner)
                    <button type="button" data-edit-open="{{ $post->id }}">
                        <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i> Edit
                    </button>
                    <form method="POST" action="{{ route('posts.destroy', $post) }}" data-confirm="Delete this post? This can't be undone.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="menu-danger">
                            <i class="fa-regular fa-trash-can" aria-hidden="true"></i> Delete
                        </button>
                    </form>
                @else
                    <button type="button" data-toggle-panel="report-{{ $post->id }}">
                        <i class="fa-regular fa-flag" aria-hidden="true"></i> Report
                    </button>
                @endif

                @if ($viewer->isAdmin())
                    <a href="{{ route('admin.posts.show', $post->id) }}">
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Moderate
                    </a>
                @endif
            </div>
        </details>
    </header>

    {{-- Content --}}
    <div class="post-content" id="post-content-{{ $post->id }}">
        @if ($post->body)
            <p class="post-text">{!! nl2br(e($post->body)) !!}</p>
        @endif

        @if ($post->image_url)
            <a href="{{ $post->image_url }}" target="_blank" rel="noopener" class="post-image-link">
                <img class="post-image" src="{{ $post->image_url }}" alt="Photo shared with this post" loading="lazy">
            </a>
        @endif

        @if ($post->original_post_id)
            @if ($original && ! $original->trashed())
                <a href="{{ route('posts.show', $original) }}" class="embed">
                    <span class="embed-head">
                        <span class="avatar avatar-sm {{ $original->is_anonymous ? 'avatar-anon' : '' }}" aria-hidden="true">
                            @if ($original->is_anonymous)
                                <i class="fa-solid fa-user-secret"></i>
                            @else
                                {{ mb_strtoupper(mb_substr($original->author_name, 0, 1)) }}
                            @endif
                        </span>
                        <strong>{{ $original->author_name }}</strong>
                        <time datetime="{{ $original->created_at->toIso8601String() }}">{{ $original->created_at->diffForHumans() }}</time>
                    </span>
                    @if ($original->body)
                        <span class="embed-text">{{ \Illuminate\Support\Str::limit($original->body, 280) }}</span>
                    @endif
                    @if ($original->image_url)
                        <img class="embed-image" src="{{ $original->image_url }}" alt="Photo in the original post" loading="lazy">
                    @endif
                </a>
            @else
                <div class="embed embed-removed">
                    <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
                    The original post was removed.
                </div>
            @endif
        @endif
    </div>

    {{-- Edit form (owner only, shown by JS) --}}
    @if ($isOwner)
        <form class="edit-form" id="edit-form-{{ $post->id }}" method="POST"
              action="{{ route('posts.update', $post) }}" enctype="multipart/form-data" hidden>
            @csrf
            @method('PUT')

            <label class="sr-only" for="edit-body-{{ $post->id }}">Edit your post</label>
            <textarea id="edit-body-{{ $post->id }}" name="body" rows="4" maxlength="2000"
                      class="js-autosize">{{ $post->body }}</textarea>

            @if ($post->image_path)
                <label class="check">
                    <input type="checkbox" name="remove_image" value="1">
                    <span>Remove the current photo</span>
                </label>
            @endif

            <label class="file-line">
                <span>{{ $post->image_path ? 'Replace photo' : 'Add a photo' }}</span>
                <input type="file" name="image" accept="image/*">
            </label>

            <div class="edit-actions">
                <button type="button" class="btn btn-ghost btn-sm" data-edit-cancel="{{ $post->id }}">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save changes</button>
            </div>
        </form>
    @endif

    {{-- Like / comment / repost / share --}}
    <footer class="post-actions">

        <form method="POST" action="{{ route('posts.like', $post) }}" class="js-like-form">
            @csrf
            <button type="submit"
                    class="action-btn like-btn {{ $post->liked_by_me ? 'is-liked' : '' }}"
                    aria-pressed="{{ $post->liked_by_me ? 'true' : 'false' }}"
                    aria-label="Like this post">
                <i class="{{ $post->liked_by_me ? 'fa-solid' : 'fa-regular' }} fa-heart" aria-hidden="true"></i>
                <span class="like-count">{{ $post->likes_count }}</span>
            </button>
        </form>

        <button type="button" class="action-btn"
                data-toggle-panel="comments-{{ $post->id }}"
                aria-expanded="{{ $showComments ? 'true' : 'false' }}"
                aria-label="Comments">
            <i class="fa-regular fa-comment" aria-hidden="true"></i>
            <span>{{ $post->comments_count }}</span>
        </button>

        <button type="button" class="action-btn"
                data-toggle-panel="repost-{{ $post->id }}"
                aria-expanded="false"
                aria-label="Repost to your wall">
            <i class="fa-solid fa-retweet" aria-hidden="true"></i>
            <span>{{ $post->reposts_count }}</span>
        </button>

        <button type="button" class="action-btn action-share" data-copy-link="{{ route('posts.show', $post) }}">
            <i class="fa-solid fa-link" aria-hidden="true"></i>
            <span class="share-label">Share</span>
        </button>
    </footer>

    {{-- Report panel (other people's posts) --}}
    @unless ($isOwner)
        <section class="panel" id="report-{{ $post->id }}" aria-label="Report this post" hidden>
            <form method="POST" action="{{ route('posts.report', $post) }}">
                @csrf
                <label class="panel-label" for="report-reason-{{ $post->id }}">What's wrong with this post?</label>
                <select id="report-reason-{{ $post->id }}" name="reason" class="panel-select" required>
                    <option value="" disabled selected>Choose a reason</option>
                    @foreach (\App\Models\Report::REASONS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <label class="sr-only" for="report-details-{{ $post->id }}">More details (optional)</label>
                <textarea id="report-details-{{ $post->id }}" name="details" rows="2" maxlength="500"
                          placeholder="Anything else the moderators should know? (optional)"></textarea>
                <div class="panel-row">
                    <span class="panel-hint">Only the moderators will see this.</span>
                    <button type="submit" class="btn btn-primary btn-sm">Send report</button>
                </div>
            </form>
        </section>
    @endunless

    {{-- Repost panel --}}
    <section class="panel" id="repost-{{ $post->id }}" aria-label="Repost" hidden>
        <form method="POST" action="{{ route('posts.repost', $post) }}">
            @csrf
            <label class="sr-only" for="repost-body-{{ $post->id }}">Add a note to your repost</label>
            <textarea id="repost-body-{{ $post->id }}" name="body" rows="2" maxlength="500"
                      placeholder="Add a thought (optional)"></textarea>
            <div class="panel-row">
                <label class="check">
                    <input type="checkbox" name="is_anonymous" value="1">
                    <span>Repost anonymously</span>
                </label>
                <button type="submit" class="btn btn-primary btn-sm">Repost to the wall</button>
            </div>
        </form>
    </section>

    {{-- Comments panel --}}
    <section class="panel comments" id="comments-{{ $post->id }}" aria-label="Comments" @unless ($showComments) hidden @endunless>

        @foreach ($post->comments as $comment)
            <div class="comment" id="comment-{{ $comment->id }}">
                <span class="avatar avatar-sm" aria-hidden="true">{{ mb_strtoupper(mb_substr($comment->user->name, 0, 1)) }}</span>

                <div class="comment-body">
                    <div class="comment-meta">
                        <strong>{{ $comment->user->name }}</strong>
                        @if ($comment->user->isAdmin())
                            <span class="admin-badge"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Admin</span>
                        @endif
                        <time datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->diffForHumans() }}</time>
                    </div>
                    <p>{!! nl2br(e($comment->body)) !!}</p>
                </div>

                @if ((int) $comment->user_id === (int) $me || $isOwner)
                    <form method="POST" action="{{ route('comments.destroy', $comment) }}" data-confirm="Delete this comment?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="comment-delete" aria-label="Delete comment">
                            <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                        </button>
                    </form>
                @endif
            </div>
        @endforeach

        @if ($post->comments->isEmpty())
            <p class="comments-empty">No comments yet. Say something kind.</p>
        @endif

        <form class="comment-form" method="POST" action="{{ route('comments.store', $post) }}">
            @csrf
            <label class="sr-only" for="comment-input-{{ $post->id }}">Write a comment</label>
            <input type="text" id="comment-input-{{ $post->id }}" name="body" maxlength="1000"
                   placeholder="Write a comment..." autocomplete="off" required>
            <button type="submit" class="btn btn-primary btn-sm">Send</button>
        </form>
    </section>

</article>
