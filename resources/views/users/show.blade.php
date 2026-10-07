{{--
    resources/views/users/show.blade.php  –  A student's public page
    Data comes from UserProfileController@show: $profile, $isMe, $postCount, $posts
--}}
@extends('layout')

@section('styles')
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('css/search.css') }}">
@endsection

@section('content')

    @include('partials.navbar')

    
    <main class="wall-page" id="main">
        <div class="column">

            <a href="{{ route('search') }}" class="back-link">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to search
            </a>

            <section class="profile-card" aria-labelledby="profile-name">
                <span class="avatar profile-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($profile->username, 0, 1)) }}</span>

                <div class="profile-info">
                    <h1 id="profile-name">
                        {{ $profile->username }}
                        @if ($profile->isAdmin())
                            <span class="admin-badge"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Admin</span>
                        @endif
                        @if ($isMe)
                            <span class="you-tag">That's you</span>
                        @endif
                    </h1>

                    <p class="profile-meta">
                        <span>Joined {{ $profile->created_at->format('F Y') }}</span>
                        <span>{{ $postCount }} public {{ \Illuminate\Support\Str::plural('post', $postCount) }}</span>
                    </p>

                    <div class="profile-actions">
                        @if ($isMe)
                            <a class="btn btn-ghost btn-sm" href="{{ route('home', ['mine' => 1]) }}">
                                <i class="fa-regular fa-bookmark" aria-hidden="true"></i> See all my posts
                            </a>
                        @endif
                        @if (auth()->user()->isAdmin())
                            <a class="btn btn-ghost btn-sm" href="{{ route('admin.users.show', $profile) }}">
                                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Manage in admin
                            </a>
                        @endif
                    </div>

                    @if ($isMe)
                        <p class="profile-note">Posts you make anonymously never appear on this page.</p>
                    @endif
                </div>
            </section>

            <div class="wall-heading">
                <h2>Posts</h2>
            </div>

            <div class="wall-feed">
                @forelse ($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @empty
                    <div class="empty-state">
                        <i class="fa-regular fa-moon" aria-hidden="true"></i>
                        <h3>No public posts yet</h3>
                        <p>{{ $isMe ? "You haven't posted under your own name yet." : "This student hasn't posted under their own name yet." }}</p>
                    </div>
                @endforelse
            </div>

            @if ($posts->hasPages())
                <nav class="pager" aria-label="More posts">
                    @if ($posts->previousPageUrl())
                        <a class="btn btn-ghost" href="{{ $posts->previousPageUrl() }}">
                            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Newer posts
                        </a>
                    @endif
                    @if ($posts->hasMorePages())
                        <a class="btn btn-ghost" href="{{ $posts->nextPageUrl() }}">
                            Older posts <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    @endif
                </nav>
            @endif

        </div>
    </main>

    <footer class="site-footer">
        <p><i class="fa-solid fa-lock" aria-hidden="true"></i> A private place for students. What's shared here stays here.</p>
    </footer>

@endsection
