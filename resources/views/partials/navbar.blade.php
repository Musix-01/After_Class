@php
    $user = auth()->user();

    $tabs = [
        ['label' => 'Wall',      'icon' => 'fa-note-sticky',      'url' => route('home'),            'active' => request()->routeIs('home', 'posts.show')],
        ['label' => 'Memories',  'icon' => 'fa-hourglass-half',   'url' => route('memories.index'),  'active' => request()->routeIs('memories.*')],
        ['label' => 'Mysteries', 'icon' => 'fa-magnifying-glass', 'url' => route('mysteries.index'), 'active' => request()->routeIs('mysteries.*')],
    ];
@endphp

<a href="#main" class="skip-link">Skip to content</a>

<header class="site-header">
    <nav class="navbar" aria-label="Main navigation">

        <a href="{{ route('home') }}" class="brand">
            <span class="brand-icon" aria-hidden="true"><i class="fa-solid fa-moon"></i></span>
            <span class="brand-text">
                <strong>After Class</strong>
                <small>Campus stories &amp; memories</small>
            </span>
        </a>

        <div class="nav-links">
            @foreach ($tabs as $tab)
                <a href="{{ $tab['url'] }}"
                   class="nav-link {{ $tab['active'] ? 'active' : '' }}"
                   @if ($tab['active']) aria-current="page" @endif>
                    <i class="fa-solid {{ $tab['icon'] }}" aria-hidden="true"></i>
                    <span>{{ $tab['label'] }}</span>
                </a>
            @endforeach

            @if ($user->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="nav-link nav-admin">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    <span>Admin</span>
                </a>
            @endif
        </div>

        <div class="nav-actions">
            <details class="profile-menu">
                <summary class="profile-button" aria-label="Open account menu">
                    <span class="avatar avatar-md" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    <span class="profile-name">{{ $user->name }}</span>
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </summary>

                <div class="menu-panel">
                    <a href="{{ route('home', ['mine' => 1]) }}">
                        <i class="fa-regular fa-bookmark" aria-hidden="true"></i> My posts
                    </a>
                    @if ($user->isAdmin())
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Admin dashboard
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="menu-danger">
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Log out
                        </button>
                    </form>
                </div>
            </details>
        </div>

    </nav>
</header>
