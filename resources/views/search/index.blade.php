{{--
    resources/views/search/index.blade.php  –  Search for students by username
    Data comes from SearchController@index: $q (string), $users (paginator or null)
--}}
@extends('layout')

@section('styles')
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('css/search.css') }}">
    <script src="{{ asset('js/search.js') }}" defer></script>
@endsection

@section('content')

    @include('partials.navbar')


        {{-- Cute background stars --}}
        <div class="star-background" aria-hidden="true">
            <span class="star star-1">✦</span>
            <span class="star star-2">✧</span>
            <span class="star star-3">⋆</span>
            <span class="star star-4">✦</span>
            <span class="star star-5">✧</span>
            <span class="star star-6">⋆</span>
            <span class="star star-7">✦</span>
            <span class="star star-8">✧</span>
            <span class="star star-9">⋆</span>
            <span class="star star-10">✦</span>
            <span class="star star-11">✧</span>
            <span class="star star-12">⋆</span>
        </div>

    <main class="wall-page" id="main">
        <div class="column">

            <section class="page-intro">
                <h1>Search</h1>
                <p>Find classmates by their username.</p>
            </section>

            <form class="search-form" method="GET" action="{{ route('search') }}" role="search" data-search-form>
                <div class="search-field">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <label class="sr-only" for="search-input">Search by username</label>
                    <input id="search-input" type="search" name="q" value="{{ $q }}"
                           maxlength="30" placeholder="Search by username"
                           autocomplete="off" autocapitalize="off" spellcheck="false"
                           @if ($q === '') autofocus @endif>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <div id="search-results" aria-live="polite">
                @include('search._results')
            </div>

        </div>
    </main>

    <footer class="site-footer">
        <p><i class="fa-solid fa-lock" aria-hidden="true"></i> A private place for students. What's shared here stays here.</p>
    </footer>

@endsection
