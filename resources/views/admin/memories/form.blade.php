@extends('admin.layout')

@php $editing = $memory->exists; @endphp

@section('title', $editing ? 'Edit memory capsule' : 'Create memory capsule')

@section('actions')
    <a href="{{ route('admin.memories.index') }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> All capsules</a>
@endsection

@section('admin')

    <form method="POST"
          action="{{ $editing ? route('admin.memories.update', $memory) : route('admin.memories.store') }}"
          enctype="multipart/form-data" class="acard form-card stack">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="field">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" class="input" maxlength="120" required
                   value="{{ old('title', $memory->title) }}" placeholder="Batch 2026 freshmen night">
        </div>

        <div class="field">
            <label for="body">The memory</label>
            <textarea id="body" name="body" rows="8" maxlength="5000" class="input js-autosize" required
                      placeholder="What happened, who was there, what should everyone remember?">{{ old('body', $memory->body) }}</textarea>
        </div>

        <div class="grid-2">
            <div class="field">
                <label for="unlock_at">Unlocks on</label>
                <input type="datetime-local" id="unlock_at" name="unlock_at" class="input" required
                       value="{{ old('unlock_at', ($memory->unlock_at ?? now()->addMonth()->startOfDay())->format('Y-m-d\TH:i')) }}">
                <small class="muted">Until then, students only see the title and a countdown.@if ($editing) Changing the date seals the capsule again.@endif</small>
            </div>

            <div class="field">
                <label for="image">Photo (optional)</label>
                <input type="file" id="image" name="image" accept="image/*" class="input input-file">
                @if ($editing && $memory->image_path)
                    <label class="check"><input type="checkbox" name="remove_image" value="1"> <span>Remove the current photo</span></label>
                @endif
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.memories.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i> {{ $editing ? 'Save changes' : 'Seal the capsule' }}
            </button>
        </div>
    </form>

@endsection
