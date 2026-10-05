@extends('admin.layout')

@section('title', 'Memory capsules')

@section('actions')
    <a href="{{ route('admin.memories.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus" aria-hidden="true"></i> Create memory</a>
@endsection

@section('admin')

    <p class="lead">Seal a memory, choose when it unlocks, and everyone gets to open it together. You can also open a capsule early.</p>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Capsule</th><th>Unlock date</th><th>Status</th><th>Created</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($memories as $memory)
                    <tr>
                        <td class="excerpt">
                            <strong>{{ $memory->title }}</strong>
                            @if ($memory->image_path)<i class="fa-regular fa-image muted" title="Has a photo" aria-label="Has a photo"></i>@endif
                            <small>{{ \Illuminate\Support\Str::limit($memory->body, 90) }}</small>
                        </td>
                        <td>{{ $memory->unlock_at->format('M j, Y g:i A') }}</td>
                        <td>
                            @if ($memory->opened_at)
                                <span class="badge badge-green">Opened early</span>
                            @elseif ($memory->isUnlocked())
                                <span class="badge badge-green">Open</span>
                            @else
                                <span class="badge badge-yellow"><i class="fa-solid fa-lock" aria-hidden="true"></i> Sealed, {{ $memory->unlock_at->diffForHumans() }}</span>
                            @endif
                        </td>
                        <td>{{ $memory->created_at->format('M j, Y') }}</td>
                        <td class="row-actions">
                            @unless ($memory->isUnlocked())
                                <form method="POST" action="{{ route('admin.memories.open', $memory) }}" data-confirm="Open this capsule now for everyone?">
                                    @csrf
                                    <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-box-open" aria-hidden="true"></i> Open now</button>
                                </form>
                            @endunless
                            <a href="{{ route('admin.memories.edit', $memory) }}" class="btn btn-ghost btn-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.memories.destroy', $memory) }}" data-confirm="Delete this capsule for good?">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit" aria-label="Delete capsule"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-row">No capsules yet. Create the first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
