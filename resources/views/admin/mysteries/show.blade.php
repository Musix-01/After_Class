@extends('admin.layout')

@section('title', $mystery->title)

@section('actions')
    <a href="{{ route('admin.mysteries.index') }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> All mysteries</a>
    <a href="{{ route('admin.mysteries.edit', $mystery) }}" class="btn btn-ghost btn-sm">Edit</a>
    <a href="{{ route('mysteries.show', $mystery) }}" class="btn btn-ghost btn-sm" target="_blank" rel="noopener">View as student</a>
@endsection

@section('admin')

    <section class="acard">
        <p>
            <span class="status status-{{ $mystery->status }}">{{ $mystery->status_label }}</span>
            @if ($mystery->location)<span class="muted small"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $mystery->location }}</span>@endif
        </p>
        <div class="post-preview">{!! nl2br(e($mystery->summary)) !!}</div>

        @unless ($mystery->isOpen())
            <div class="note-box verdict-{{ $mystery->status }}">
                <strong>{{ $mystery->status === 'solved' ? 'Solved' : 'Debunked' }}
                    {{ $mystery->resolved_at ? 'on ' . $mystery->resolved_at->format('M j, Y') : '' }}:</strong>
                {!! nl2br(e($mystery->resolution)) !!}
            </div>
        @endunless
    </section>

    <div class="split">

        {{-- Clues --}}
        <section class="acard" id="clues">
            <h2>Clues ({{ $mystery->clues->count() }})</h2>

            <ol class="clue-list">
                @forelse ($mystery->clues as $clue)
                    <li>
                        <span class="clue-no">{{ $loop->iteration }}</span>
                        <p>{!! nl2br(e($clue->body)) !!}</p>
                        <form method="POST" action="{{ route('admin.clues.destroy', $clue) }}" data-confirm="Remove this clue?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="comment-delete" aria-label="Remove clue"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                        </form>
                    </li>
                @empty
                    <li class="clue-empty">No clues yet.</li>
                @endforelse
            </ol>

            <form method="POST" action="{{ route('admin.mysteries.clues.store', $mystery) }}" class="stack">
                @csrf
                <div class="field">
                    <label for="clue">Add a clue</label>
                    <textarea id="clue" name="body" rows="3" maxlength="1000" class="input js-autosize" required
                              placeholder="A new detail for students to investigate"></textarea>
                </div>
                <div><button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add clue</button></div>
            </form>
        </section>

        {{-- Verdict --}}
        <section class="acard">
            <h2>Verdict</h2>

            @if ($mystery->isOpen())
                <form method="POST" action="{{ route('admin.mysteries.resolve', $mystery) }}" class="stack"
                      data-confirm="Close this case? Students will no longer be able to add theories.">
                    @csrf

                    <fieldset class="field radios">
                        <legend>Outcome</legend>
                        <label class="check"><input type="radio" name="outcome" value="solved" @checked(old('outcome') === 'solved') required> <span>Solved, we know what happened</span></label>
                        <label class="check"><input type="radio" name="outcome" value="debunked" @checked(old('outcome') === 'debunked')> <span>Debunked, it was a rumour</span></label>
                    </fieldset>

                    <div class="field">
                        <label for="resolution">What really happened?</label>
                        <textarea id="resolution" name="resolution" rows="4" maxlength="3000" class="input js-autosize" required>{{ old('resolution') }}</textarea>
                    </div>

                    @if ($mystery->theories->isNotEmpty())
                        <div class="field">
                            <label for="accepted">Credit the closest theory (solved only)</label>
                            <select id="accepted" name="accepted_theory_id" class="input">
                                <option value="">None</option>
                                @foreach ($mystery->theories as $theory)
                                    <option value="{{ $theory->id }}" @selected((int) old('accepted_theory_id') === $theory->id)>
                                        {{ $theory->user->name }}: {{ \Illuminate\Support\Str::limit($theory->body, 60) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-gavel" aria-hidden="true"></i> Close the case</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.mysteries.reopen', $mystery) }}" class="stack">
                    @csrf
                    <p class="muted">Reopening clears the verdict and lets students add theories again.</p>
                    <button class="btn btn-ghost" type="submit"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Reopen the mystery</button>
                </form>
            @endif

            <hr class="rule">
            <form method="POST" action="{{ route('admin.mysteries.destroy', $mystery) }}" data-confirm="Delete this mystery with all its clues and theories?">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm" type="submit"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Delete mystery</button>
            </form>
        </section>
    </div>

    {{-- Theories --}}
    <section class="acard">
        <div class="acard-head"><h2>Theories ({{ $mystery->theories->count() }})</h2></div>
        <ul class="feed feed-comments">
            @forelse ($mystery->theories as $theory)
                <li>
                    <span class="feed-text">
                        <strong>{{ $theory->user->name }}</strong>
                        @if ($theory->is_accepted)<span class="status status-solved">Closest to the truth</span>@endif
                        <small class="muted">{{ $theory->created_at->diffForHumans() }}</small><br>
                        {!! nl2br(e($theory->body)) !!}
                    </span>
                    <form method="POST" action="{{ route('theories.destroy', $theory) }}" data-confirm="Remove this theory?">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" type="submit"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Remove</button>
                    </form>
                </li>
            @empty
                <li class="muted">No theories yet. Students add theirs on the mystery page.</li>
            @endforelse
        </ul>
    </section>

@endsection
