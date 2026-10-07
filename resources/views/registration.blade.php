@extends('layout')

@section('content')
<div class="auth-box">
    <h1 class="title">Register Here</h1>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <label>Username</label>
        <input type="text" name="username" value="{{ old('username') }}" maxlength="20" placeholder="Enter your username" required>
        @error('username')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <label>Birthday</label>
        <input type="date" name="birthday" value="{{ old('birthday') }}" required>
        @error('birthday')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required>
        @error('email')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <label>Password</label>
        <input type="password" name="psw" placeholder="Enter your password" required>
        @error('psw')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <label>Confirm Password</label>
        <input type="password" name="psw_confirmation" placeholder="Confirm your password" required>

        <button type="submit">Sign Up</button>
    </form>
    <p class="switch">Already have an account? <a href="{{ route('login') }}">Login</a></p>
</div>
@endsection
