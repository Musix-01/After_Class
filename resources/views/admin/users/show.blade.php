@extends('admin.layout')

@section('title', $user->name)

@section('actions')
    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> All users</a>
@endsection

@section('admin')

    <div class="split">

        {{-- Profile --}}
        <section class="acard">
            <div class="profile-head">
                <span class="avatar avatar-lg" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <div>
                    <h2>{{ $user->name }}</h2>
                    <p class="muted">{{ $user->email }}</p>
                    <p>
                        <span class="badge badge-{{ $user->isAdmin() ? 'yellow' : 'gray' }}">{{ ucfirst($user->role ?? 'student') }}</span>
                        @if ($user->isSuspended())
                            <span class="badge badge-pink">Suspended</span>
                        @else
                            <span class="badge badge-green">Active</span>
                        @endif
                    </p>
                </div>
            </div>

            <dl class="kv">
                <div><dt>Joined</dt><dd>{{ $user->created_at?->format('M j, Y g:i A') }}</dd></div>
                <div><dt>Posts</dt><dd>{{ $user->posts_count }}</dd></div>
                <div><dt>Comments</dt><dd>{{ $user->comments_count }}</dd></div>
                <div><dt>Reports on their posts</dt><dd>{{ $reportsAbout }}</dd></div>
                @if ($user->isSuspended())
                    <div><dt>Suspended since</dt><dd>{{ $user->suspended_at->format('M j, Y g:i A') }}</dd></div>
                    <div><dt>Until</dt><dd>{{ $user->suspended_until ? $user->suspended_until->format('M j, Y g:i A') : 'Until lifted' }}</dd></div>
                    <div><dt>Reason</dt><dd>{{ $user->suspension_reason }}</dd></div>
                @endif
            </dl>
        </section>

        {{-- Actions --}}
        <section class="acard">
            <h2>Account actions</h2>

            @if ($user->isSuspended())
                <form method="POST" action="{{ route('admin.users.unsuspend', $user) }}" class="stack">
                    @csrf
                    <p class="muted">This student is locked out. Lifting the suspension lets them log in again.</p>
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-lock-open" aria-hidden="true"></i> Lift suspension</button>
                </form>
            @elseif ($user->isAdmin())
                <p class="muted">Admins cannot be suspended. Change the role to student first.</p>
            @else
                <form method="POST" action="{{ route('admin.users.suspend', $user) }}" class="stack" data-confirm="Suspend {{ $user->name }}? They will be signed out immediately.">
                    @csrf
                    <div class="field">
                        <label for="reason">Reason (the student will see this)</label>
                        <input type="text" id="reason" name="reason" class="input" maxlength="200" required value="{{ old('reason') }}">
                    </div>
                    <div class="field">
                        <label for="until">Suspend until (leave empty for no end date)</label>
                        <input type="datetime-local" id="until" name="until" class="input" value="{{ old('until') }}">
                    </div>
                    <button class="btn btn-danger" type="submit"><i class="fa-solid fa-user-slash" aria-hidden="true"></i> Suspend account</button>
                </form>
            @endif

            @unless ($user->is(auth()->user()))
                <hr class="rule">
                <form method="POST" action="{{ route('admin.users.role', $user) }}" class="stack"
                      data-confirm="Change {{ $user->name }}'s role?">
                    @csrf
                    <div class="field">
                        <label for="role">Role</label>
                        <select id="role" name="role" class="input">
                            <option value="student" @selected(! $user->isAdmin())>Student</option>
                            <option value="admin" @selected($user->isAdmin())>Admin</option>
                        </select>
                    </div>
                    <button class="btn btn-ghost" type="submit">Save role</button>
                </form>
            @endunless
        </section>
    </div>

    {{-- Their posts --}}
    <section class="acard">
        <div class="acard-head"><h2>Latest posts</h2></div>
        <ul class="feed">
            @forelse ($posts as $post)
                <li>
                    <span class="feed-dot feed-post" aria-hidden="true"></span>
                    <span class="feed-text">
                        <a href="{{ route('admin.posts.show', $post->id) }}">{{ \Illuminate\Support\Str::limit($post->body ?: ($post->image_path ? '(photo)' : '(repost)'), 90) }}</a>
                        @if ($post->is_anonymous)<span class="badge badge-gray">anonymous</span>@endif
                        @if ($post->trashed())<span class="badge badge-pink">{{ $post->removed_by ? 'removed' : 'deleted' }}</span>@endif
                    </span>
                    <time class="feed-time">{{ $post->created_at->diffForHumans() }}</time>
                </li>
            @empty
                <li class="muted">No posts yet.</li>
            @endforelse
        </ul>
    </section>

    {{-- Their log --}}
    <section class="acard">
        <div class="acard-head">
            <h2>Recent activity</h2>
            <a href="{{ route('admin.logs.index', ['q' => $user->name]) }}" class="link">More in the log</a>
        </div>
        <ul class="feed">
            @forelse ($logs as $log)
                <li>
                    <span class="feed-dot feed-{{ \Illuminate\Support\Str::before($log->action, '.') }}" aria-hidden="true"></span>
                    <span class="feed-text">{{ $log->description }}</span>
                    <time class="feed-time">{{ $log->created_at->diffForHumans() }}</time>
                </li>
            @empty
                <li class="muted">No activity recorded.</li>
            @endforelse
        </ul>
    </section>

@endsection
