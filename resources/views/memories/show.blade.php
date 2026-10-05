@extends('layout')

@section('styles')
    @include('partials.head')
@endsection

@section('content')

    @include('partials.navbar')

    <main class="wall-page" id="main">
        <div class="column">

            <a href="{{ route('memories.index') }}" class="back-link">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> All capsules
            </a>

            @if ($memory->isUnlocked())

                <article class="capsule-page">
                    <p class="eyebrow"><i class="fa-solid fa-box-open" aria-hidden="true"></i> Memory capsule, opened {{ ($memory->opened_at ?? $memory->unlock_at)->format('M j, Y') }}</p>
                    <h1>{{ $memory->title }}</h1>

                    @if ($memory->image_url)
                        <img class="capsule-photo" src="{{ $memory->image_url }}" alt="Photo from the memory capsule">
                    @endif

                    <div class="capsule-text">{!! nl2br(e($memory->body)) !!}</div>
                </article>

            @else

                <div class="sealed">
                    <span class="sealed-icon" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
                    <h1>{{ $memory->title }}</h1>
                    <p>This capsule is sealed. It opens on</p>
                    <p class="sealed-date">{{ $memory->unlock_at->format('F j, Y') }}</p>
                    <p class="sealed-in">{{ $memory->unlock_at->diffForHumans() }}</p>
                </div>

            @endif

        </div>
    </main>

    <footer class="site-footer">
        <p><i class="fa-solid fa-lock" aria-hidden="true"></i> A private place for students. What's shared here stays here.</p>
    </footer>

@endsection
