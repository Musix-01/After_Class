@extends('layout')

@section('content')

<div class="Title">
    <h1> Welcome to After Class! </h1>
    <p> Preserve memories, share your campus stories, and investigate the little mysteries and experiences that happened around the campus.  </p>
</div>
<div class="auth-box">
    <h1>Login</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <input type="email" name="email" placeholder="Enter your email" required>
        <input type="password" name="password" placeholder="Enter your password" required>
        <input type="submit" value="Login">
    </form>
    <p class="switch">Don't have an account? <a href="{{ route('register.form') }}">Register</a></p>
</div>
@endsection
