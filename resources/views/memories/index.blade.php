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
                <h1>Memory capsules</h1>
                <p>Moments sealed by the team and opened later. Some are waiting for their date.</p>
            </section>

            {{-- Open --}}
            <section aria-labelledby="open-title">
                <div class="wall-heading"><h2 id="open-title">Opened</h2></div>

                <div class="card-list">
                    @forelse ($opened as $memory)
                        <a href="{{ route('memories.show', $memory) }}" class="capsule capsule-open">
                            <span class="capsule-icon" aria-hidden="true"><i class="fa-solid fa-box-open"></i></span>
                            <span class="capsule-body">
                                <strong>{{ $memory->title }}</strong>
                                <small>Opened {{ ($memory->opened_at ?? $memory->unlock_at)->format('M j, Y') }}</small>
                                <span class="capsule-excerpt">{{ \Illuminate\Support\Str::limit($memory->body, 140) }}</span>
                            </span>
                            <i class="fa-solid fa-chevron-right capsule-go" aria-hidden="true"></i>
                        </a>
                    @empty
                        <div class="empty-state">
                            <i class="fa-regular fa-box-open" aria-hidden="true"></i>
                            <h3>Nothing opened yet</h3>
                            <p>The first capsule will appear here when its date arrives.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Locked --}}
            @if ($locked->isNotEmpty())
                <section aria-labelledby="locked-title" class="section-gap">
                    <div class="wall-heading"><h2 id="locked-title">Still sealed</h2></div>

                    <div class="card-list">
                        @foreach ($locked as $memory)
                            <a href="{{ route('memories.show', $memory) }}" class="capsule capsule-locked">
                                <span class="capsule-icon" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
                                <span class="capsule-body">
                                    <strong>{{ $memory->title }}</strong>
                                    <small>Opens {{ $memory->unlock_at->format('M j, Y') }} ({{ $memory->unlock_at->diffForHumans() }})</small>
                                </span>
                                <i class="fa-solid fa-chevron-right capsule-go" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

        </div>
    </main>

    <footer class="site-footer">
        <p><i class="fa-solid fa-lock" aria-hidden="true"></i> A private place for students. What's shared here stays here.</p>
    </footer>

@endsection
