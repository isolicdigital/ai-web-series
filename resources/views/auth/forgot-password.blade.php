@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <div class="flex w-full lg:w-1/2 items-center justify-center bg-[#0a0a1a] p-8 lg:p-12">
        <div class="w-full max-w-md">
            <div class="mb-10 text-center">
                <div class="mb-5 flex justify-center">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('custom/brand/frontend-logo.png') }}" alt="{{ config('app.name') }}" class="h-14 transition-all duration-300 hover:scale-105" style="filter: drop-shadow(0 0 25px rgba(255,45,149,0.6));">
                    </a>
                </div>
                <h1 class="mb-2 text-3xl font-bold bg-gradient-to-r from-white to-white/70 bg-clip-text text-transparent">Forgot password?</h1>
                <p class="text-white/50 text-sm">Enter your email address and we'll send you a link to reset your password</p>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-2xl bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 text-sm text-center">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-8">
                    <label class="mb-2 block text-sm font-semibold text-white/70" for="email">Email Address</label>
                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <input class="w-full rounded-2xl border border-white/10 bg-white/5 pl-12 pr-4 py-3.5 text-white placeholder-white/30 transition-all focus:border-[#ff2d95] focus:outline-none focus:ring-4 focus:ring-[#ff2d95]/20" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
                    </div>
                    @error('email')
                        <div class="mt-2 text-sm text-[#ff2d95]">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="group relative w-full overflow-hidden rounded-2xl bg-gradient-to-r from-[#ff2d95] to-[#8f4de0] py-3.5 font-semibold text-white transition-all hover:shadow-2xl hover:shadow-[#ff2d95]/30 active:scale-95">
                    <span class="relative z-10">Send Reset Link</span>
                    <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-500 group-hover:translate-x-full"></div>
                </button>
            </form>

            <p class="mt-8 text-center text-sm text-white/40">
                <a href="{{ route('login') }}" class="font-medium text-[#8f4de0] transition-all hover:text-[#ff2d95] hover:underline">Back to login</a>
            </p>
        </div>
    </div>

    <div class="relative hidden w-1/2 overflow-hidden lg:flex lg:items-center lg:justify-center">
        <video class="absolute inset-0 w-full h-full object-cover" autoplay loop muted playsinline>
            <source src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a1a] via-[#0a0a1a]/60 to-[#0a0a1a]/30"></div>
        
        <div class="relative z-10 text-center px-12">
            <div class="mb-8">
                <img src="https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=400&h=300&fit=crop" alt="Reset password" class="rounded-2xl shadow-2xl mx-auto border border-white/10 w-64 h-48 object-cover">
            </div>
            <h2 class="mb-4 text-3xl font-bold bg-gradient-to-r from-[#ff2d95] via-[#8f4de0] to-[#ff6bb5] bg-clip-text text-transparent">Don't worry</h2>
            <p class="text-white/60 leading-relaxed max-w-sm mx-auto">We'll help you get back into your account quickly and securely.</p>
        </div>
    </div>
@endsection