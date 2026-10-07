@extends('layout')

@section('content')
<style>
    .password-wrap { position: relative; }
    .password-wrap input { padding-right: 52px; }

    .password-wrap button.toggle-pw {
        position: absolute;
        right: 14px;
        top: 7px; /* (input height 46px - button height 32px) / 2 */
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
        color: #6b6b6b;
        cursor: pointer;
    }
    .password-wrap button.toggle-pw:hover { background: none; color: #2f2f2f; }
    .password-wrap button.toggle-pw:focus-visible { outline: 2px solid #718f4e; }

    .toggle-pw svg { width: 22px; height: 22px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .toggle-pw .eye-off { display: none; }
    .toggle-pw.is-visible .eye-on { display: none; }
    .toggle-pw.is-visible .eye-off { display: block; }
</style>

<div class="Title">
    <h1> Welcome to After Class! </h1>
    <p> Preserve memories, share your campus stories, and investigate the little mysteries and experiences that happened around the campus.</p>
</div>
<div class="auth-box">
    <h1>Login</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <input type="email" name="email" placeholder="Enter your email" required>

        <div class="password-wrap">
            <input type="password" name="password" id="password" placeholder="Enter your password" required>
            <button type="button" class="toggle-pw" id="togglePassword" aria-label="Show password" aria-pressed="false">
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

        <input type="submit" value="Login">
    </form>
    <p class="switch">Don't have an account? <a href="{{ route('register.form') }}">Register</a></p>
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
