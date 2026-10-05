@extends('layout')

@section('styles')
    @include('partials.head')
@endsection

@section('content')

    @include('partials.navbar')

    <main class="wall-page" id="main">
        <div class="column">

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

            <a href="{{ route('home') }}" class="back-link">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to the wall
            </a>

            <div class="wall-feed">
                @include('partials.post-card', ['post' => $post, 'expanded' => true])
            </div>

        </div>
    </main>

    <footer class="site-footer">
        <p><i class="fa-solid fa-lock" aria-hidden="true"></i> A private place for students. What's shared here stays here.</p>
    </footer>

@endsection
