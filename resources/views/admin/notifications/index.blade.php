@extends('admin.layout')

@section('title', 'Notifications')

@section('actions')
    <form method="POST" action="{{ route('admin.notifications.read') }}">
        @csrf
        <button class="btn btn-ghost btn-sm" type="submit"><i class="fa-solid fa-check-double" aria-hidden="true"></i> Mark all as read</button>
    </form>
@endsection

@section('admin')

    <p class="lead">New sign-ups, reports and mystery theories that may need your attention.</p>

    <div class="acard flush">
        <ul class="notif-list">
            @forelse ($notifications as $n)
                @php
                    $isNew = ! $seenAt || $n->created_at->gt($seenAt);
                    $icon = match (true) {
                        str_starts_with($n->action, 'report')   => ['fa-flag', route('admin.reports.index')],
                        str_starts_with($n->action, 'mystery')  => ['fa-lightbulb', route('admin.mysteries.index')],
                        str_starts_with($n->action, 'user')     => ['fa-user-plus', $n->subject_id ? route('admin.users.show', $n->subject_id) : route('admin.users.index')],
                        default                                  => ['fa-bell', route('admin.logs.index')],
                    };
                @endphp
                <li class="{{ $isNew ? 'is-new' : '' }}">
                    <span class="notif-icon" aria-hidden="true"><i class="fa-solid {{ $icon[0] }}"></i></span>
                    <a href="{{ $icon[1] }}" class="notif-text">
                        {{ $n->description }}
                        <time datetime="{{ $n->created_at->toIso8601String() }}">{{ $n->created_at->diffForHumans() }}</time>
                    </a>
                    @if ($isNew)<span class="badge badge-yellow">New</span>@endif
                </li>
            @empty
                <li class="muted notif-empty">Nothing yet. You'll see new reports, sign-ups and theories here.</li>
            @endforelse
        </ul>
    </div>

    @include('admin.partials.pager', ['items' => $notifications])

@endsection
