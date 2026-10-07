@extends('layout')

@section('styles')
    @include('partials.head')
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
                <h1>Campus mysteries</h1>
                <p>Strange things happen after class. Read the clues, share your theory, and help close the case.</p>
            </section>

            <nav class="chips" aria-label="Filter mysteries">
                <a href="{{ route('mysteries.index') }}" class="chip {{ ! $status ? 'active' : '' }}">All</a>
                @foreach (\App\Models\Mystery::STATUSES as $key => $label)
                    <a href="{{ route('mysteries.index', ['status' => $key]) }}" class="chip {{ $status === $key ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="card-list">
                @forelse ($mysteries as $mystery)
                    <a href="{{ route('mysteries.show', $mystery) }}" class="mystery-card">
                        <span class="mystery-top">
                            <span class="status status-{{ $mystery->status }}">{{ $mystery->status_label }}</span>
                            @if ($mystery->location)
                                <span class="mystery-where"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $mystery->location }}</span>
                            @endif
                        </span>
                        <strong class="mystery-title">{{ $mystery->title }}</strong>
                        <span class="capsule-excerpt">{{ \Illuminate\Support\Str::limit($mystery->summary, 150) }}</span>
                        <span class="mystery-meta">
                            <span><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> {{ $mystery->clues_count }} {{ \Illuminate\Support\Str::plural('clue', $mystery->clues_count) }}</span>
                            <span><i class="fa-regular fa-lightbulb" aria-hidden="true"></i> {{ $mystery->theories_count }} {{ \Illuminate\Support\Str::plural('theory', $mystery->theories_count) }}</span>
                        </span>
                    </a>
                @empty
                    <div class="empty-state">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <h3>No mysteries here</h3>
                        <p>When the team opens a new case, it shows up in this list.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </main>

    <footer class="site-footer">
        <p><i class="fa-solid fa-lock" aria-hidden="true"></i> A private place for students. What's shared here stays here.</p>
    </footer>

@endsection
