@extends('admin.layout')

@php $editing = $mystery->exists; @endphp

@section('title', $editing ? 'Edit mystery' : 'Create mystery')

@section('actions')
    <a href="{{ $editing ? route('admin.mysteries.show', $mystery) : route('admin.mysteries.index') }}" class="btn btn-ghost btn-sm">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back
    </a>
@endsection

@section('admin')

    <form method="POST"
          action="{{ $editing ? route('admin.mysteries.update', $mystery) : route('admin.mysteries.store') }}"
          class="acard form-card stack">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="field">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" class="input" maxlength="140" required
                   value="{{ old('title', $mystery->title) }}" placeholder="The piano that plays at midnight">
        </div>

        <div class="field">
            <label for="location">Where did it happen? (optional)</label>
            <input type="text" id="location" name="location" class="input" maxlength="120"
                   value="{{ old('location', $mystery->location) }}" placeholder="Old music room, 3rd floor">
        </div>

        <div class="field">
            <label for="summary">What do we know so far?</label>
            <textarea id="summary" name="summary" rows="7" maxlength="3000" class="input js-autosize" required
                      placeholder="Describe the story, the rumours and who noticed it first.">{{ old('summary', $mystery->summary) }}</textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> {{ $editing ? 'Save changes' : 'Open the case' }}
            </button>
        </div>
    </form>

@endsection
