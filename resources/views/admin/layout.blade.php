@extends('layout')

@section('styles')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0d1b2a">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/wall.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sections.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <script src="{{ asset('js/wall.js') }}" defer></script>
@endsection

@section('content')
    @php
        $me          = auth()->user();
        $openReports = \App\Models\Report::where('status', 'open')->count();
        $unread      = \App\Models\ActivityLog::unreadFor($me)->count();

        $nav = [
            ['Dashboard',     'fa-gauge-high',        'admin.dashboard',            'admin',                null],
            ['Users',         'fa-users',             'admin.users.index',          'admin/users*',         null],
            ['Posts',         'fa-note-sticky',       'admin.posts.index',          'admin/posts*',         null],
            ['Moderation',    'fa-shield-halved',     'admin.reports.index',        'admin/reports*',       $openReports],
            ['Memories',      'fa-hourglass-half',    'admin.memories.index',       'admin/memories*',      null],
            ['Mysteries',     'fa-magnifying-glass',  'admin.mysteries.index',      'admin/mysteries*',     null],
            ['Announcements', 'fa-bullhorn',          'admin.announcements.index',  'admin/announcements*', null],
            ['Analytics',     'fa-chart-simple',      'admin.analytics',            'admin/analytics*',     null],
            ['Activity log',  'fa-clock-rotate-left', 'admin.logs.index',           'admin/logs*',          null],
            ['Notifications', 'fa-bell',              'admin.notifications.index',  'admin/notifications*', $unread],
        ];
    @endphp

    <a href="#admin-main" class="skip-link">Skip to content</a>

    <div class="admin-shell">

        <aside class="admin-sidebar">
            <a href="{{ route('admin.dashboard') }}" class="admin-brand">
                <span class="brand-icon" aria-hidden="true"><i class="fa-solid fa-moon"></i></span>
                <span class="brand-text">
                    <strong>After Class</strong>
                    <small>Admin panel</small>
                </span>
            </a>

            <nav class="admin-nav" aria-label="Admin navigation">
                @foreach ($nav as [$label, $icon, $route, $match, $count])
                    @php $active = request()->is($match); @endphp
                    <a href="{{ route($route) }}" class="{{ $active ? 'active' : '' }}" @if ($active) aria-current="page" @endif>
                        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                        <span>{{ $label }}</span>
                        @if ($count)
                            <em class="count">{{ $count > 99 ? '99+' : $count }}</em>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="admin-side-foot">
                <a href="{{ route('home') }}" class="side-link">
                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> View the wall
                </a>
                <div class="side-user">
                    <span class="avatar avatar-sm" aria-hidden="true">{{ mb_strtoupper(mb_substr($me->name, 0, 1)) }}</span>
                    <span class="side-name">{{ $me->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="side-logout" aria-label="Log out" title="Log out">
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="admin-main" id="admin-main">

            <header class="admin-top">
                <h1>@yield('title')</h1>
                <div class="admin-actions">@yield('actions')</div>
            </header>

            @include('admin.partials.flash')

            @yield('admin')
        </main>

    </div>
@endsection
