@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <!-- Left Form Panel -->
    <div class="auth-form-panel">
        <div class="auth-form-content">
            <!-- Logo & Headline -->
            <div class="auth-header">
                <div class="auth-logo">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('custom/brand/frontend-logo.png') }}" alt="{{ config('app.name') }}">
                    </a>
                </div>
                <p class="auth-tagline">Sign in to continue your journey</p>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input class="form-input" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="password-field">
                        <input class="form-input" type="password" id="password" name="password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <svg viewBox="0 0 24 24" width="20" height="20">
                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Keep me signed in</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="submit-btn">
                    Sign in to account
                </button>
            </form>

            <p class="form-footer-link">
                Trouble logging in? 
                <a href="{{ env('SUPPORT_DESK') }}" target="_blank">Click here</a>
            </p>
        </div>
    </div>

    <!-- Right Graphic Panel -->
    <div class="auth-graphic-panel">
        <div class="graphic-bg"></div>
        <div class="graphic-content">
            <div class="graphic-icon">
                <svg viewBox="0 0 120 120" width="120" height="120">
                    <circle cx="60" cy="60" r="40" fill="url(#gradient)" opacity="0.2"/>
                    <circle cx="60" cy="60" r="30" fill="url(#gradient)" opacity="0.3"/>
                    <circle cx="60" cy="60" r="20" fill="url(#gradient)" opacity="0.4"/>
                    <circle cx="60" cy="60" r="10" fill="var(--primary)"/>
                    <defs>
                        <linearGradient id="gradient" x1="20" y1="20" x2="100" y2="100">
                            <stop stop-color="var(--primary)"/>
                            <stop offset="1" stop-color="#8f4de0"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <h2>Create something amazing</h2>
            <p>Access your dashboard to manage projects, collaborate with your team, and bring ideas to life.</p>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
    }
</script>
@endsection