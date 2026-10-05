{{--
    resources/views/home.blade.php  –  The Freedom Wall
    Data comes from HomeController@index: $posts (simplePaginate), $activeCategory, $mine
--}}
@extends('layout')

@section('styles')
    @include('partials.head')
@endsection

@section('content')

    @include('partials.navbar')

    <main class="wall-page" id="main">
        <div class="column">

            {{-- Feedback --}}
            @if (session('status'))
                <div class="toast" role="status">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert" role="alert">
                    <strong>That didn't go through.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            {{-- Announcements from the admin team --}}
            @foreach ($announcements as $announcement)
                <aside class="announcement" data-announcement="{{ $announcement->id }}" role="note">
                    <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
                    <div>
                        <strong>{{ $announcement->title }}</strong>
                        <p>{{ $announcement->body }}</p>
                    </div>
                    <button type="button" class="announcement-close" data-dismiss-announcement="{{ $announcement->id }}" aria-label="Dismiss announcement">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </aside>
            @endforeach


            {{-- =========================
                 Composer
            ========================== --}}
            <section class="composer-section" aria-labelledby="composer-title">
                <h1 id="composer-title">What's on your mind after class?</h1>

                <form class="composer" method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                    @csrf

                    <label class="sr-only" for="composer-body">Write something</label>
                    <textarea id="composer-body" name="body" class="js-autosize" rows="3"
                              maxlength="2000" placeholder="Write something...">{{ old('body') }}</textarea>

                    <div class="image-preview" hidden>
                        <img src="" alt="Preview of the photo you selected">
                        <button type="button" class="remove-preview" aria-label="Remove photo">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    </div>
                    <p class="composer-note" role="status" hidden></p>

                    <div class="composer-bar">
                        <div class="composer-tools">
                            <input type="file" id="composer-image" class="sr-only" name="image" accept="image/*">
                            <label for="composer-image" class="tool-btn">
                                <i class="fa-regular fa-image" aria-hidden="true"></i>
                                <span>Photo</span>
                            </label>

                            <label class="select-wrap">
                                <span class="sr-only">Category</span>
                                <select name="category">
                                    @foreach (\App\Models\Post::CATEGORIES as $key => $label)
                                        <option value="{{ $key }}" @selected(old('category', 'general') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="toggle">
                                <input type="checkbox" name="is_anonymous" value="1" @checked(old('is_anonymous'))>
                                <span class="toggle-ui" aria-hidden="true"></span>
                                <span>Post anonymously</span>
                            </label>
                        </div>

                        <div class="composer-send">
                            <span class="char-count" aria-hidden="true">0/2000</span>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                                Post to the wall
                            </button>
                        </div>
                    </div>
                </form>
            </section>


            {{-- =========================
                 The wall
            ========================== --}}
            <section id="wall" aria-labelledby="wall-title">

                <div class="wall-heading">
                    <h2 id="wall-title">The Freedom Wall</h2>
                </div>

                <nav class="chips" aria-label="Filter the wall">
                    <a href="{{ route('home') }}" class="chip {{ ! $activeCategory && ! $mine ? 'active' : '' }}">Everything</a>
                    @foreach (\App\Models\Post::CATEGORIES as $key => $label)
                        <a href="{{ route('home', ['category' => $key]) }}"
                           class="chip {{ $activeCategory === $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                    @if ($mine)
                        <a href="{{ route('home', ['mine' => 1]) }}" class="chip active">My posts</a>
                    @endif
                </nav>

                <div class="wall-feed">
                    @forelse ($posts as $post)
                        @include('partials.post-card', ['post' => $post])
                    @empty
                        <div class="empty-state">
                            <i class="fa-regular fa-moon" aria-hidden="true"></i>
                            <h3>The wall is quiet</h3>
                            <p>Nothing here yet. Write the first post and start the conversation.</p>
                            <a href="#composer-body" class="btn btn-primary">Write something</a>
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
            </section>

        </div>
    </main>

    <footer class="site-footer">
        <p><i class="fa-solid fa-lock" aria-hidden="true"></i> A private place for students. What's shared here stays here.</p>
    </footer>

@endsection
