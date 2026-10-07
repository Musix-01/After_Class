{{--
    The results list. Rendered inside the search page, and on its own for live search.
    Needs: $q (string), $users (simple paginator, or null when nothing was typed)
--}}
@php
    // Bold the part of the username that matched. Every piece is escaped, so this is safe.
    $highlight = function (string $name) use ($q) {
        $pos = $q !== '' ? mb_stripos($name, $q) : false;

        if ($pos === false) {
            return e($name);
        }

        $len = mb_strlen($q);

        return e(mb_substr($name, 0, $pos))
            . '<mark>' . e(mb_substr($name, $pos, $len)) . '</mark>'
            . e(mb_substr($name, $pos + $len));
    };
@endphp

@if ($users === null)

    <div class="empty-state">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <h3>Look someone up</h3>
        <p>Type a username above. Matches show up as you type.</p>
    </div>

@elseif ($users->isEmpty())

    <div class="empty-state">
        <i class="fa-regular fa-face-frown" aria-hidden="true"></i>
        <h3>No one found</h3>
        <p>Nobody has a username matching "{{ $q }}". Check the spelling or try fewer letters.</p>
    </div>

@else

    <p class="sr-only" role="status">{{ $users->count() }} {{ \Illuminate\Support\Str::plural('person', $users->count()) }} found on this page.</p>

    <ul class="user-list">
        @foreach ($users as $found)
            <li>
                <a href="{{ route('users.show', $found) }}" class="user-result">
                    <span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($found->username, 0, 1)) }}</span>

                    <span class="user-body">
                        <span class="user-name">{!! $highlight($found->username) !!}</span>
                        <small class="user-meta">Joined {{ $found->created_at->format('M Y') }}</small>
                    </span>

                    @if ($found->isAdmin())
                        <span class="admin-badge"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Admin</span>
                    @endif
                    @if ((int) $found->id === (int) auth()->id())
                        <span class="you-tag">You</span>
                    @endif

                    <i class="fa-solid fa-chevron-right user-go" aria-hidden="true"></i>
                </a>
            </li>
        @endforeach
    </ul>

    @if ($users->hasPages())
        <nav class="pager" aria-label="More results">
            @if ($users->previousPageUrl())
                <a class="btn btn-ghost" href="{{ $users->previousPageUrl() }}">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Previous
                </a>
            @endif
            @if ($users->hasMorePages())
                <a class="btn btn-ghost" href="{{ $users->nextPageUrl() }}">
                    More <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            @endif
        </nav>
    @endif

@endif
