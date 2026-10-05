@extends('admin.layout')

@section('title', 'Mysteries')

@section('actions')
    <a href="{{ route('admin.mysteries.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus" aria-hidden="true"></i> Create mystery</a>
@endsection

@section('admin')

    <nav class="chips" aria-label="Filter mysteries">
        <a href="{{ route('admin.mysteries.index') }}" class="chip {{ ! $status ? 'active' : '' }}">All</a>
        @foreach (\App\Models\Mystery::STATUSES as $key => $label)
            <a href="{{ route('admin.mysteries.index', ['status' => $key]) }}" class="chip {{ $status === $key ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </nav>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Mystery</th><th>Status</th><th class="num">Clues</th><th class="num">Theories</th><th>Opened</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($mysteries as $mystery)
                    <tr>
                        <td class="excerpt">
                            <strong>{{ $mystery->title }}</strong>
                            @if ($mystery->location)<small><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $mystery->location }}</small>@endif
                        </td>
                        <td><span class="status status-{{ $mystery->status }}">{{ $mystery->status_label }}</span></td>
                        <td class="num">{{ $mystery->clues_count }}</td>
                        <td class="num">{{ $mystery->theories_count }}</td>
                        <td>{{ $mystery->created_at->format('M j, Y') }}</td>
                        <td class="row-actions"><a href="{{ route('admin.mysteries.show', $mystery) }}" class="btn btn-ghost btn-sm">Investigate</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-row">No mysteries yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
