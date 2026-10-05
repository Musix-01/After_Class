@extends('layout')

@section('styles')
    @include('partials.head')
@endsection

@section('content')

    @include('partials.navbar')

    @php $me = auth()->user(); @endphp

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
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <a href="{{ route('mysteries.index') }}" class="back-link">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> All mysteries
            </a>

            <article class="case">
                <div class="mystery-top">
                    <span class="status status-{{ $mystery->status }}">{{ $mystery->status_label }}</span>
                    @if ($mystery->location)
                        <span class="mystery-where"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $mystery->location }}</span>
                    @endif
                </div>
                <h1>{{ $mystery->title }}</h1>
                <div class="capsule-text">{!! nl2br(e($mystery->summary)) !!}</div>
            </article>

            {{-- Verdict --}}
            @unless ($mystery->isOpen())
                <section class="verdict verdict-{{ $mystery->status }}" aria-label="Verdict">
                    <p class="eyebrow">
                        <i class="fa-solid {{ $mystery->status === 'solved' ? 'fa-circle-check' : 'fa-ban' }}" aria-hidden="true"></i>
                        {{ $mystery->status === 'solved' ? 'Case solved' : 'Case debunked' }}
                        @if ($mystery->resolved_at) on {{ $mystery->resolved_at->format('M j, Y') }} @endif
                    </p>
                    <div class="capsule-text">{!! nl2br(e($mystery->resolution)) !!}</div>
                </section>
            @endunless

            {{-- Clues --}}
            <section class="section-gap" aria-labelledby="clues-title">
                <div class="wall-heading"><h2 id="clues-title">Clues</h2></div>

                <ol class="clue-list">
                    @forelse ($mystery->clues as $clue)
                        <li>
                            <span class="clue-no">{{ $loop->iteration }}</span>
                            <p>{!! nl2br(e($clue->body)) !!}</p>
                        </li>
                    @empty
                        <li class="clue-empty">No clues yet. Check back soon.</li>
                    @endforelse
                </ol>
            </section>

            {{-- Theories --}}
            <section class="section-gap" id="theories" aria-labelledby="theories-title">
                <div class="wall-heading"><h2 id="theories-title">Theories</h2></div>

                @if ($mystery->isOpen())
                    <form class="composer theory-form" method="POST" action="{{ route('theories.store', $mystery) }}">
                        @csrf
                        <label class="sr-only" for="theory-body">Your theory</label>
                        <textarea id="theory-body" name="body" rows="3" maxlength="1000" class="js-autosize"
                                  placeholder="What do you think really happened?" required>{{ old('body') }}</textarea>
                        <div class="composer-bar">
                            <span class="char-count">Your name is shown with your theory.</span>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa-regular fa-lightbulb" aria-hidden="true"></i> Share theory
                            </button>
                        </div>
                    </form>
                @endif

                <div class="theory-list">
                    @forelse ($mystery->theories as $theory)
                        <div class="theory {{ $theory->is_accepted ? 'theory-accepted' : '' }}">
                            <span class="avatar avatar-sm" aria-hidden="true">{{ mb_strtoupper(mb_substr($theory->user->name, 0, 1)) }}</span>
                            <div class="comment-body">
                                <div class="comment-meta">
                                    <strong>{{ $theory->user->name }}</strong>
                                    @if ($theory->user->isAdmin())
                                        <span class="admin-badge"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Admin</span>
                                    @endif
                                    @if ($theory->is_accepted)
                                        <span class="status status-solved"><i class="fa-solid fa-trophy" aria-hidden="true"></i> Closest to the truth</span>
                                    @endif
                                    <time datetime="{{ $theory->created_at->toIso8601String() }}">{{ $theory->created_at->diffForHumans() }}</time>
                                </div>
                                <p>{!! nl2br(e($theory->body)) !!}</p>
                            </div>

                            @if ($me->isAdmin() || (int) $theory->user_id === (int) $me->id)
                                <form method="POST" action="{{ route('theories.destroy', $theory) }}" data-confirm="Remove this theory?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="comment-delete" aria-label="Remove theory">
                                        <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="comments-empty">No theories yet. Be the first detective.</p>
                    @endforelse
                </div>
            </section>

        </div>
    </main>

    <footer class="site-footer">
        <p><i class="fa-solid fa-lock" aria-hidden="true"></i> A private place for students. What's shared here stays here.</p>
    </footer>

@endsection
