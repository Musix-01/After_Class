@extends('admin.layout')

@section('title', 'Dashboard')

@section('actions')
    <a href="{{ route('admin.memories.create') }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i> New memory</a>
    <a href="{{ route('admin.mysteries.create') }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> New mystery</a>
    <a href="{{ route('admin.announcements.index') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i> Announce</a>
@endsection

@section('admin')

    {{-- Numbers --}}
    <section class="stat-grid" aria-label="Overview">
        <a href="{{ route('admin.users.index') }}" class="stat-card">
            <span class="stat-label">Students</span>
            <strong>{{ number_format($stats['users']) }}</strong>
            <small>{{ $stats['new_users_week'] }} joined this week</small>
        </a>
        <a href="{{ route('admin.posts.index') }}" class="stat-card">
            <span class="stat-label">Posts</span>
            <strong>{{ number_format($stats['posts']) }}</strong>
            <small>{{ $stats['posts_today'] }} today</small>
        </a>
        <a href="{{ route('admin.reports.index') }}" class="stat-card {{ $stats['open_reports'] ? 'stat-alert' : '' }}">
            <span class="stat-label">Open reports</span>
            <strong>{{ $stats['open_reports'] }}</strong>
            <small>{{ $stats['open_reports'] ? 'Waiting for review' : 'Nothing to review' }}</small>
        </a>
        <a href="{{ route('admin.users.index', ['status' => 'suspended']) }}" class="stat-card">
            <span class="stat-label">Suspended</span>
            <strong>{{ $stats['suspended'] }}</strong>
            <small>accounts</small>
        </a>
        <a href="{{ route('admin.memories.index') }}" class="stat-card">
            <span class="stat-label">Sealed capsules</span>
            <strong>{{ $stats['locked_memories'] }}</strong>
            <small>waiting to open</small>
        </a>
        <a href="{{ route('admin.mysteries.index', ['status' => 'open']) }}" class="stat-card">
            <span class="stat-label">Open mysteries</span>
            <strong>{{ $stats['open_mysteries'] }}</strong>
            <small>under investigation</small>
        </a>
    </section>

    {{-- Manage --}}
    <h2 class="section-title">Manage</h2>
    <section class="tile-grid" aria-label="Manage">
        <a href="{{ route('admin.users.index') }}" class="tile">
            <i class="fa-solid fa-users" aria-hidden="true"></i>
            <strong>Manage users</strong>
            <span>Search accounts, suspend or change roles</span>
        </a>
        <a href="{{ route('admin.posts.index') }}" class="tile">
            <i class="fa-solid fa-note-sticky" aria-hidden="true"></i>
            <strong>Manage posts</strong>
            <span>Community posts, reposts, pin or remove</span>
        </a>
        <a href="{{ route('admin.memories.index') }}" class="tile">
            <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
            <strong>Memories</strong>
            <span>Seal capsules, set unlock dates, open them</span>
        </a>
        <a href="{{ route('admin.mysteries.index') }}" class="tile">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <strong>Mysteries</strong>
            <span>Add clues, read theories, solve or debunk</span>
        </a>
        <a href="{{ route('admin.reports.index') }}" class="tile">
            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
            <strong>Moderation</strong>
            <span>Review reports and remove content</span>
        </a>
        <a href="{{ route('admin.notifications.index') }}" class="tile">
            <i class="fa-solid fa-bell" aria-hidden="true"></i>
            <strong>Notifications</strong>
            <span>{{ $unread ? $unread . ' new' : 'All caught up' }}</span>
            @if ($unread)<em class="count">{{ $unread }}</em>@endif
        </a>
    </section>

    {{-- Recent activity --}}
    <div class="acard">
        <div class="acard-head">
            <h2>Recent activity</h2>
            <a href="{{ route('admin.logs.index') }}" class="link">Full activity log</a>
        </div>

        <ul class="feed">
            @forelse ($recent as $log)
                <li>
                    <span class="feed-dot feed-{{ \Illuminate\Support\Str::before($log->action, '.') }}" aria-hidden="true"></span>
                    <span class="feed-text">{{ $log->description }}</span>
                    <time class="feed-time" datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->diffForHumans() }}</time>
                </li>
            @empty
                <li class="muted">Nothing has happened yet.</li>
            @endforelse
        </ul>
    </div>

@endsection
