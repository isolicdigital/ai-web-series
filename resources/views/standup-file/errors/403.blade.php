@extends('layouts.auth')

@section('title', 'Forbidden')

@section('content')
    <div class="auth-form-panel">
        <div class="auth-form-content" style="text-align: center;">
            <div class="auth-logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('custom/brand/frontend-logo.png') }}" alt="{{ config('app.name') }}">
                </a>
            </div>

            <div style="margin: 2rem 0;">
                <div style="font-size: 6rem; font-weight: 700; color: var(--primary);">403</div>
                <h1 style="font-size: 1.5rem; margin: 1rem 0 0.5rem;">Access Forbidden</h1>
                <p style="color: var(--text-muted);">You don't have permission to access this page.</p>
            </div>

            <a href="{{ url('/') }}" class="submit-btn" style="display: inline-block; text-decoration: none; text-align: center;">
                Go to Homepage
            </a>
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
            <h2>Restricted Area</h2>
            <p>This area is off-limits to your account.</p>
        </div>
    </div>
@endsection