@extends('admin.layout')

@section('title', 'Announcements')

@section('admin')

    <div class="split">

        <form method="POST" action="{{ route('admin.announcements.store') }}" class="acard stack">
            @csrf
            <h2>New announcement</h2>
            <p class="muted small">It appears at the top of the Freedom Wall for every student until you hide it or it expires.</p>

            <div class="field">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="input" maxlength="120" required value="{{ old('title') }}"
                       placeholder="Foundation week schedule">
            </div>

            <div class="field">
                <label for="body">Message</label>
                <textarea id="body" name="body" rows="4" maxlength="1000" class="input js-autosize" required>{{ old('body') }}</textarea>
            </div>

            <div class="field">
                <label for="expires_at">Hide automatically on (optional)</label>
                <input type="datetime-local" id="expires_at" name="expires_at" class="input" value="{{ old('expires_at') }}">
            </div>

            <div><button class="btn btn-primary" type="submit"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i> Publish</button></div>
        </form>

        <section class="acard">
            <h2>All announcements</h2>

            <ul class="stack-list">
                @forelse ($announcements as $announcement)
                    <li class="announce-row">
                        <div>
                            <strong>{{ $announcement->title }}</strong>
                            @if ($announcement->isLive())
                                <span class="badge badge-green">Live</span>
                            @elseif (! $announcement->is_active)
                                <span class="badge badge-gray">Hidden</span>
                            @else
                                <span class="badge badge-gray">Expired</span>
                            @endif
                            <p>{{ $announcement->body }}</p>
                            <small class="muted">
                                {{ $announcement->created_at->format('M j, Y') }}
                                @if ($announcement->expires_at) &middot; ends {{ $announcement->expires_at->format('M j, g:i A') }} @endif
                            </small>
                        </div>
                        <div class="row-actions">
                            <form method="POST" action="{{ route('admin.announcements.toggle', $announcement) }}">
                                @csrf
                                <button class="btn btn-ghost btn-sm" type="submit">{{ $announcement->is_active ? 'Hide' : 'Show' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" data-confirm="Delete this announcement?">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit" aria-label="Delete announcement"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li class="muted">No announcements yet.</li>
                @endforelse
            </ul>
        </section>
    </div>

@endsection
