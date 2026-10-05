@extends('admin.layout')

@section('title', 'Analytics')

@section('admin')

    <section class="stat-grid" aria-label="Totals">
        <div class="stat-card"><span class="stat-label">Students</span><strong>{{ number_format($totals['users']) }}</strong></div>
        <div class="stat-card"><span class="stat-label">Active this week</span><strong>{{ $totals['active'] }}</strong><small>logged in at least once</small></div>
        <div class="stat-card"><span class="stat-label">Posts</span><strong>{{ number_format($totals['posts']) }}</strong></div>
        <div class="stat-card"><span class="stat-label">Comments</span><strong>{{ number_format($totals['comments']) }}</strong></div>
        <div class="stat-card"><span class="stat-label">Likes</span><strong>{{ number_format($totals['likes']) }}</strong></div>
        <div class="stat-card"><span class="stat-label">Reposts</span><strong>{{ number_format($totals['reposts']) }}</strong></div>
    </section>

    <h2 class="section-title">Last {{ $days }} days</h2>
    <div class="chart-grid">
        @include('admin.partials.bars', ['title' => 'New students', 'data' => $newUsers, 'tone' => 'blue'])
        @include('admin.partials.bars', ['title' => 'New posts', 'data' => $newPosts, 'tone' => 'yellow'])
        @include('admin.partials.bars', ['title' => 'New comments', 'data' => $newComments, 'tone' => 'pink'])
    </div>

    <div class="split">
        <section class="acard">
            <h2>Posts by category</h2>
            @php $catMax = max(1, $byCategory->max() ?: 1); @endphp
            <ul class="hbars">
                @foreach ($byCategory as $label => $count)
                    <li>
                        <span class="hbar-label">{{ $label }}</span>
                        <span class="hbar-track"><span class="hbar-fill" style="width: {{ round($count / $catMax * 100) }}%"></span></span>
                        <span class="hbar-val">{{ $count }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="acard">
            <h2>Reports</h2>
            <dl class="kv">
                <div><dt>Open</dt><dd>{{ $reportStats['open'] }}</dd></div>
                <div><dt>Post removed</dt><dd>{{ $reportStats['actioned'] }}</dd></div>
                <div><dt>Dismissed</dt><dd>{{ $reportStats['dismissed'] }}</dd></div>
            </dl>
            @if ($reportsByReason->isNotEmpty())
                <hr class="rule">
                <ul class="hbars">
                    @php $rMax = max(1, $reportsByReason->max()); @endphp
                    @foreach ($reportsByReason as $reason => $total)
                        <li>
                            <span class="hbar-label">{{ \App\Models\Report::REASONS[$reason] ?? $reason }}</span>
                            <span class="hbar-track"><span class="hbar-fill hbar-pink" style="width: {{ round($total / $rMax * 100) }}%"></span></span>
                            <span class="hbar-val">{{ $total }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <div class="split">
        <section class="acard">
            <h2>Most engaging posts</h2>
            <ul class="stack-list">
                @forelse ($topPosts as $post)
                    <li class="top-row">
                        <a href="{{ route('admin.posts.show', $post->id) }}" class="excerpt">
                            {{ \Illuminate\Support\Str::limit($post->body ?: '(photo)', 70) }}
                            <small class="muted">by {{ $post->user->name ?? 'a student' }}@if ($post->is_anonymous) (anonymous)@endif</small>
                        </a>
                        <span class="top-stats">
                            <span><i class="fa-regular fa-heart" aria-hidden="true"></i> {{ $post->likes_count }}</span>
                            <span><i class="fa-regular fa-comment" aria-hidden="true"></i> {{ $post->comments_count }}</span>
                            <span><i class="fa-solid fa-retweet" aria-hidden="true"></i> {{ $post->reposts_count }}</span>
                        </span>
                    </li>
                @empty
                    <li class="muted">No likes or comments yet.</li>
                @endforelse
            </ul>
        </section>

        <section class="acard">
            <h2>Top posters (30 days)</h2>
            <ul class="stack-list">
                @forelse ($topUsers as $u)
                    <li class="top-row">
                        <a href="{{ route('admin.users.show', $u) }}">{{ $u->name }}</a>
                        <span class="badge badge-gray">{{ $u->posts_count }} {{ \Illuminate\Support\Str::plural('post', $u->posts_count) }}</span>
                    </li>
                @empty
                    <li class="muted">No posts in the last 30 days.</li>
                @endforelse
            </ul>
        </section>
    </div>

@endsection
