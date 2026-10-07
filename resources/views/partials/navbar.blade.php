{{--
    resources/views/partials/navbar.blade.php
    Top bar + "Discover" sidebar.

    Route names for the sidebar links are guessed from a list of candidates
    (first one that exists wins). If a link shows "#", add your real route name
    to the matching list in $items below.
--}}
@php
    $user = auth()->user();

    // Returns [routeName, url] for the first candidate route that exists, else [null, '#'].
    $resolve = function (array $names) {
        foreach ($names as $name) {
            if (! \Illuminate\Support\Facades\Route::has($name)) {
                continue;
            }
            foreach ([[], [auth()->user()]] as $args) {
                try {
                    return [$name, route($name, $args)];
                } catch (\Throwable $e) {
                    // route needs other parameters, try the next option
                }
            }
        }
        return [null, '#'];
    };

    // label, Font Awesome icon, candidate route names
    $items = [
        ['Home',          'fa-house',             ['home']],
        ['Search',        'fa-magnifying-glass',  ['search', 'search.index']],
        ['Mysteries',     'fa-user-secret',       ['mysteries.index', 'mysteries']],
        ['Memories',      'fa-clock-rotate-left', ['capsules.index', 'memories.index', 'memories']],
        ['Notifications', 'fa-bell',              ['notifications.index', 'notifications']],
        ['Profile',       'fa-user',              ['profile', 'profile.show', 'profile.edit']],
        ['Settings',      'fa-gear',              ['settings', 'settings.index']],
    ];

    $nav = [];
    foreach ($items as [$label, $icon, $candidates]) {
        [$name, $url] = $resolve($candidates);
        $nav[$label] = [
            'label'  => $label,
            'icon'   => $icon,
            'url'    => $url,
            'active' => $name && request()->routeIs($name, \Illuminate\Support\Str::before($name, '.') . '.*'),
        ];
    }

    // Optional admin link: only shown if an admin route exists and the user is an admin.
    $isAdmin = $user && (data_get($user, 'is_admin') || data_get($user, 'role') === 'admin');
    [$adminRoute, $adminUrl] = $isAdmin ? $resolve(['admin.dashboard', 'admin.index', 'admin']) : [null, '#'];

    [, $homeUrl] = $resolve(['home']);
    $logoutUrl = \Illuminate\Support\Facades\Route::has('logout') ? route('logout') : '#';
    $username  = data_get($user, 'username') ?? data_get($user, 'name') ?? 'Account';
@endphp

<a class="skip-link" href="#main">Skip to content</a>

{{-- Checkbox drives the sidebar drawer on small screens (no JavaScript needed) --}}
<input type="checkbox" id="nav-toggle" class="nav-toggle sr-only" aria-label="Open navigation menu" aria-controls="sidebar">

<header class="site-header app-header">
    <div class="header-left">
        <label for="nav-toggle" class="menu-toggle" aria-hidden="true">
            <i class="fa-solid fa-bars"></i>
        </label>

        <a href="{{ $homeUrl }}" class="brand" aria-label="After Class, home">
            <span class="wordmark" aria-hidden="true">
                <span class="wm-script">A</span>FTER<i class="fa-solid fa-moon wm-moon"></i>LASS
            </span>
        </a>
    </div>

    <div class="nav-actions">
        <a href="{{ $nav['Notifications']['url'] }}" class="bell-link" aria-label="Notifications">
            <i class="fa-solid fa-bell" aria-hidden="true"></i>

            @if ($user && $user->notifications()->where('is_read', false)->exists())
                <span class="notification-dot" aria-label="Unread notifications"></span>
            @endif
        </a>

        <details class="profile-menu">
            <summary class="profile-button" aria-label="Account menu">
                <span class="avatar avatar-user" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
                <span class="profile-name">{{ $username }}</span>
                <i class="fa-solid fa-caret-down" aria-hidden="true"></i>
            </summary>

            <div class="menu-panel">
                <a href="{{ $nav['Profile']['url'] }}"><i class="fa-solid fa-user" aria-hidden="true"></i> Profile</a>
                <a href="{{ $nav['Settings']['url'] }}"><i class="fa-solid fa-gear" aria-hidden="true"></i> Settings</a>
                <form method="POST" action="{{ $logoutUrl }}">
                    @csrf
                    <button type="submit" class="menu-danger">
                        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Log out
                    </button>
                </form>
            </div>
        </details>
    </div>
</header>

<nav class="sidebar" id="sidebar" aria-label="Main navigation">
    <div class="sidebar-head">
        <h2 class="sidebar-title">Discover</h2>
        <label for="nav-toggle" class="sidebar-close" aria-hidden="true"><i class="fa-solid fa-xmark"></i></label>
    </div>

    <ul class="side-links">
        @foreach ($nav as $item)
            <li>
                <a href="{{ $item['url'] }}"
                   class="side-link {{ $item['active'] ? 'active' : '' }}"
                   @if ($item['active']) aria-current="page" @endif>
                    <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach

        @if ($adminRoute)
            <li>


                <a href="{{ $adminUrl }}" class="side-link side-admin {{ request()->routeIs($adminRoute, 'admin.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    <span>Admin</span>
                </a>
            </li>
        @endif
    </ul>

    <form method="POST" action="{{ $logoutUrl }}" class="side-logout">
        @csrf
        <button type="submit">
            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
            <span>Log Out</span>
        </button>
    </form>
</nav>

<label for="nav-toggle" class="nav-scrim" aria-hidden="true"></label>
