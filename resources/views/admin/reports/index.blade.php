@extends('admin.layout')

@section('title', 'Moderation')

@section('actions')
    <a href="{{ route('admin.posts.index', ['type' => 'repost']) }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-retweet" aria-hidden="true"></i> Review reposts</a>
@endsection

@section('admin')

    <nav class="chips" aria-label="Report status">
        @foreach (['open' => 'Needs review', 'actioned' => 'Post removed', 'dismissed' => 'Dismissed', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.reports.index', ['status' => $key]) }}" class="chip {{ $status === $key ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </nav>

    <div class="stack-lg">
        @forelse ($reports as $report)
            @php $post = $report->post; @endphp
            <article class="acard report">
                <header class="report-head">
                    <div>
                        <span class="badge badge-{{ $report->status === 'open' ? 'pink' : ($report->status === 'actioned' ? 'yellow' : 'gray') }}">
                            {{ $report->status === 'actioned' ? 'Post removed' : ucfirst($report->status) }}
                        </span>
                        <strong>{{ $report->reason_label }}</strong>
                    </div>
                    <small class="muted">Reported by {{ $report->reporter->name ?? 'a student' }}, {{ $report->created_at->diffForHumans() }}</small>
                </header>

                @if ($report->details)
                    <p class="note-box">"{{ $report->details }}"</p>
                @endif

                <div class="reported-post">
                    <div class="small muted">
                        Post #{{ $post->id }} by <a href="{{ route('admin.users.show', $post->user_id) }}">{{ $post->user->name ?? 'Deleted user' }}</a>
                        @if ($post->is_anonymous) (posted anonymously)@endif
                        @if ($post->original_post_id) <span class="badge badge-blue">Repost</span>@endif
                        @if ($post->trashed()) <span class="badge badge-pink">already removed</span>@endif
                    </div>
                    <p>{{ \Illuminate\Support\Str::limit($post->body ?: ($post->image_path ? '(photo only)' : '(repost with no note)'), 300) }}</p>
                    @if ($post->original_post_id && $post->original)
                        <p class="muted small">Original: {{ \Illuminate\Support\Str::limit($post->original->body ?: '(photo)', 160) }}</p>
                    @endif
                </div>

                <footer class="report-actions">
                    <a href="{{ route('admin.posts.show', $post->id) }}" class="btn btn-ghost btn-sm">Open post</a>

                    @if ($report->status === 'open')
                        <form method="POST" action="{{ route('admin.reports.dismiss', $report) }}">
                            @csrf
                            <button class="btn btn-ghost btn-sm" type="submit"><i class="fa-solid fa-check" aria-hidden="true"></i> Dismiss, post is fine</button>
                        </form>

                        @unless ($post->trashed())
                            @include('admin.partials.remove-form', ['action' => route('admin.reports.remove', $report), 'label' => 'Remove post'])
                        @endunless
                    @elseif ($report->reviewer)
                        <small class="muted">Reviewed by {{ $report->reviewer->name }} {{ $report->reviewed_at?->diffForHumans() }}</small>
                    @endif
                </footer>
            </article>
        @empty
            <div class="empty-state">
                <i class="fa-regular fa-circle-check" aria-hidden="true"></i>
                <h3>{{ $status === 'open' ? 'Queue is clear' : 'Nothing here' }}</h3>
                <p>{{ $status === 'open' ? 'No reports are waiting for review.' : 'No reports with this status.' }}</p>
            </div>
        @endforelse
    </div>

    @include('admin.partials.pager', ['items' => $reports])

@endsection
