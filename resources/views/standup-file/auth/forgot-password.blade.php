@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <div class="auth-form-panel">
        <div class="auth-form-content">
            <div class="auth-header">
                <div class="auth-logo">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('custom/brand/frontend-logo.png') }}" alt="{{ config('app.name') }}">
                    </a>
                </div>
                <p class="auth-tagline">Enter your email address and we'll send you a link to reset your password</p>
            </div>

            @if (session('status'))
                <div class="alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input class="form-input" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <div class="alert-error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="submit-btn">
                    Send Reset Link
                </button>
            </form>

            <p class="form-footer-link">
                <a href="{{ route('login') }}">Back to login</a>
            </p>
        </div>
    </div>

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
            <h2>Don't worry</h2>
            <p>We'll help you get back into your account quickly and securely.</p>
        </div>
    </div>
@endsection