@extends('admin.layout')

@section('title', 'Activity log')

@section('admin')

    <p class="lead">Everything that happens on the site: logins, posts, reports and every admin action. Entries cannot be edited.</p>

    <form method="GET" class="toolbar" role="search">
        <label class="sr-only" for="q">Search the log</label>
        <input type="search" id="q" name="q" value="{{ $q }}" class="input" placeholder="Search a name, text or IP address">

        <label class="sr-only" for="group">Type</label>
        <select id="group" name="group" class="input">
            <option value="">All activity</option>
            @foreach ($groups as $key => $label)
                <option value="{{ $key }}" @selected($group === $key)>{{ $label }}</option>
            @endforeach
        </select>

        <label class="sr-only" for="date">Date</label>
        <input type="date" id="date" name="date" value="{{ $date }}" class="input">

        <button class="btn btn-primary btn-sm" type="submit">Filter</button>
        @if ($q || $group || $date)
            <a href="{{ route('admin.logs.index') }}" class="btn btn-ghost btn-sm">Clear</a>
        @endif
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>When</th><th>Who</th><th>Type</th><th>What happened</th><th>IP</th></tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    @php $kind = \Illuminate\Support\Str::before($log->action, '.'); @endphp
                    <tr>
                        <td class="nowrap">
                            <time datetime="{{ $log->created_at->toIso8601String() }}" title="{{ $log->created_at->format('M j, Y g:i:s A') }}">
                                {{ $log->created_at->format('M j, g:i A') }}
                            </time>
                        </td>
                        <td>
                            @if ($log->user)
                                <a href="{{ route('admin.users.show', $log->user_id) }}">{{ $log->user->name }}</a>
                            @else
                                <span class="muted">Guest</span>
                            @endif
                        </td>
                        <td><span class="badge badge-log-{{ $kind }}">{{ $log->action }}</span></td>
                        <td class="excerpt">{{ $log->description }}</td>
                        <td class="nowrap muted">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-row">No log entries match.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('admin.partials.pager', ['items' => $logs])

@endsection
