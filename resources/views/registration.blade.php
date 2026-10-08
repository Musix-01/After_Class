@extends('layout')

@section('styles')
<style>
    .auth-box .pw-field {
        position: relative;
    }

    .auth-box .pw-field input[type="password"],
    .auth-box .pw-field input[type="text"] {
        padding-right: 52px;
    }

    .auth-box .pw-eye {
        position: absolute;
        top: 50%;
        right: 8px;
        transform: translateY(-50%);

        width: 36px;
        height: 36px;
        padding: 0;

        display: grid;
        place-items: center;

        background: transparent;
        border: 0;
        border-radius: 9px;
        cursor: pointer;
    }

    .auth-box .pw-eye svg {
        width: 20px;
        height: 20px;

        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .auth-box .pw-eye .eye-off { display: none; }
    .auth-box .pw-eye.is-visible .eye-on  { display: none; }
    .auth-box .pw-eye.is-visible .eye-off { display: block; }

    .comet-background {
        position: fixed;
        inset: 0;
        width: 100vw;
        height: 100vh;
        overflow: hidden;
        pointer-events: none;
        z-index: 0;
    }
</style>
@endsection

@section('content')
<<<<<<< HEAD
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
=======
<div class="comet-background">
    <div class="comets">
        <div class="comet" style="top: 20%; left: 30%; animation-delay: 0s; animation-duration: 3.5s;"></div>
        <div class="comet" style="top: 60%; left: 15%; animation-delay: 3.1s; animation-duration: 4.5s;"></div>
        <div class="comet" style="top: 10%; left: 80%; animation-delay: 1.7s; animation-duration: 3.2s;"></div>
    </div>
</div>
>>>>>>> e58595b (Describe what you changed here)

<div class="auth-box">
    <h1 class="title">Register Here</h1>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="{{ old('username') }}" maxlength="20"
               placeholder="Enter your username" autocomplete="username" required>
        @error('username')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <label for="birthday">Birthday</label>
        <input type="date" id="birthday" name="birthday" value="{{ old('birthday') }}" autocomplete="bday" required>
        @error('birthday')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}"
               placeholder="Enter your email" autocomplete="email" required>
        @error('email')
            <div class="error-message">{{ $message }}</div>
        @enderror

<<<<<<< HEAD
        <label>Password</label>
        <div class="password-wrap">
            <input type="password" name="psw" id="psw" placeholder="Enter your password" required>
            <button type="button" class="toggle-pw" aria-label="Show password" aria-pressed="false">
                {{-- eye (password hidden) --}}
=======
        {{-- Password --}}
        <label for="psw">Password</label>
        <div class="pw-field">
            <input type="password" id="psw" name="psw" placeholder="Enter your password"
                   autocomplete="new-password" required>
            <button type="button" class="pw-eye" aria-label="Show password" aria-pressed="false">
                {{-- eye: password hidden --}}
>>>>>>> e58595b (Describe what you changed here)
                <svg class="eye-on" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
<<<<<<< HEAD
                {{-- eye with a slash (password visible) --}}
=======
                {{-- eye with a slash: password visible --}}
>>>>>>> e58595b (Describe what you changed here)
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

        {{-- Confirm password --}}
        <label for="psw_confirmation">Confirm Password</label>
        <div class="pw-field">
            <input type="password" id="psw_confirmation" name="psw_confirmation" placeholder="Confirm your password"
                   autocomplete="new-password" required>
            <button type="button" class="pw-eye" aria-label="Show password" aria-pressed="false">
                <svg class="eye-on" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg class="eye-off" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20C5 20 1 12 1 12a18.5 18.5 0 0 1 5.06-5.94"/>
                    <path d="M9.9 4.24A10.9 10.9 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                    <path d="M14.12 14.12A3 3 0 1 1 9.88 9.88"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
            </button>
        </div>

        <button type="submit">Sign Up</button>
    </form>

    <p class="switch">Already have an account? <a href="{{ route('login') }}">Login</a></p>
</div>

<<<<<<< HEAD
<script>
    document.querySelectorAll('.toggle-pw').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.parentElement.querySelector('input');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.classList.toggle('is-visible', show);
            btn.setAttribute('aria-pressed', show);
=======
<script src="{{ asset('js/password-checklist.js') }}" defer></script>

<script>
    document.querySelectorAll('.pw-eye').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.closest('.pw-field').querySelector('input');
            var show = input.type === 'password';

            input.type = show ? 'text' : 'password';
            btn.classList.toggle('is-visible', show);
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
>>>>>>> e58595b (Describe what you changed here)
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });
</script>
<<<<<<< HEAD
@endsection
=======
@endsection
>>>>>>> e58595b (Describe what you changed here)
