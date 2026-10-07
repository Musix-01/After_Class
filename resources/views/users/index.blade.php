@extends('layout')

@section('content')

<link rel="stylesheet" href="{{ asset('css/wall.css') }}">
<link rel="stylesheet" href="{{ asset('css/sections.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/notifications.css') }}">

@include('partials.navbar')


<main class="notifications-page">

    <div class="notifications-container">

        <h1>Notifications</h1>

        @forelse ($notifications as $notification)

            <div class="notification-card {{ !$notification->is_read ? 'unread' : '' }}">

                <div class="notification-icon">
                    <i class="fa-solid fa-bell"></i>
                </div>

                <div class="notification-content">
                    <h3>{{ $notification->title }}</h3>

                    <p>{{ $notification->message }}</p>

                    <small>
                        {{ $notification->created_at->diffForHumans() }}
                    </small>

                    @if (!$notification->is_read)
                        <form method="POST"
                              action="{{ route('notifications.read', $notification->id) }}">
                            @csrf

                            <button type="submit">
                                Mark as read
                            </button>
                        </form>
                    @endif
                </div>

            </div>

        @empty

            <div class="empty-notifications">
                <i class="fa-regular fa-bell-slash"></i>

                <h3>No notifications</h3>
                <p>You're all caught up!</p>
            </div>

        @endforelse

        {{ $notifications->links() }}

    </div>

</main>

@endsection