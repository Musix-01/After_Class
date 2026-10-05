@extends('admin.layout')

@section('title', 'Users')

@section('admin')

    <form method="GET" class="toolbar" role="search">
        <label class="sr-only" for="q">Search users</label>
        <input type="search" id="q" name="q" value="{{ $q }}" class="input" placeholder="Search by name or email">

        <label class="sr-only" for="role">Role</label>
        <select id="role" name="role" class="input">
            <option value="">All roles</option>
            <option value="student" @selected($role === 'student')>Students</option>
            <option value="admin" @selected($role === 'admin')>Admins</option>
        </select>

        <label class="sr-only" for="status">Status</label>
        <select id="status" name="status" class="input">
            <option value="">Any status</option>
            <option value="active" @selected($status === 'active')>Active</option>
            <option value="suspended" @selected($status === 'suspended')>Suspended</option>
        </select>

        <button class="btn btn-primary btn-sm" type="submit">Filter</button>
        @if ($q || $role || $status)
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">Clear</a>
        @endif
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>User</th><th>Role</th><th>Status</th><th class="num">Posts</th><th class="num">Comments</th><th>Joined</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td>
                            <div class="who">
                                <span class="avatar avatar-sm" aria-hidden="true">{{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                                <span><strong>{{ $u->name }}</strong><small>{{ $u->email }}</small></span>
                            </div>
                        </td>
                        <td><span class="badge badge-{{ $u->isAdmin() ? 'yellow' : 'gray' }}">{{ ucfirst($u->role ?? 'student') }}</span></td>
                        <td>
                            @if ($u->isSuspended())
                                <span class="badge badge-pink">Suspended</span>
                            @else
                                <span class="badge badge-green">Active</span>
                            @endif
                        </td>
                        <td class="num">{{ $u->posts_count }}</td>
                        <td class="num">{{ $u->comments_count }}</td>
                        <td>{{ $u->created_at?->format('M j, Y') }}</td>
                        <td class="row-actions"><a href="{{ route('admin.users.show', $u) }}" class="btn btn-ghost btn-sm">Manage</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-row">No users match those filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('admin.partials.pager', ['items' => $users])

@endsection
