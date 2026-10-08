@extends('layout')

@section('content')
<style>
    .password-wrap { position: relative; }
    .password-wrap input { padding-right: 52px; }

    .password-wrap button.toggle-pw {
        position: absolute;
        right: 14px;
        top: 7px; 
        width: 32px;
        height: 32px;
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: none;
        border: 0;
        border-radius: 50%;
        box-shadow: none;
        color: #9aa4b5;
        cursor: pointer;
    }
    .password-wrap button.toggle-pw:hover { background: none; color: #fff; }
    .password-wrap button.toggle-pw:focus-visible { outline: 2px solid #f9d71c; }

    .toggle-pw svg { width: 22px; height: 22px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .toggle-pw .eye-off { display: none; }
    .toggle-pw.is-visible .eye-on { display: none; }
    .toggle-pw.is-visible .eye-off { display: block; }
</style>

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
        <div class="password-wrap">
            <input type="password" name="psw" id="psw" placeholder="Enter your password" required>
            <button type="button" class="toggle-pw" aria-label="Show password" aria-pressed="false">
                {{-- eye (password hidden) --}}
                <svg class="eye-on" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                {{-- eye with a slash (password visible) --}}
                <svg class="eye-off" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20C5 20 1 12 1 12a18.5 18.5 0 0 1 5.06-5.94"/>
                    <path d="M9.9 4.24A10.9 10.9 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                    <path d="M14.12 14.12A3 3 0 1 1 9.88 9.88"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
            </button>
        </div>
        @error('psw')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <label>Confirm Password</label>
        <input type="password" name="psw_confirmation" placeholder="Confirm your password" required>

        <button type="submit">Sign Up</button>
    </form>
    <p class="switch">Already have an account? <a href="{{ route('login') }}">Login</a></p>
</div>

<script>
    document.querySelectorAll('.toggle-pw').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.parentElement.querySelector('input');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.classList.toggle('is-visible', show);
            btn.setAttribute('aria-pressed', show);
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });
</script>
@endsection
