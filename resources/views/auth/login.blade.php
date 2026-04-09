{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="flex w-full lg:w-1/2 items-center justify-center bg-[#0a0a1a] p-8 lg:p-12">
        <div class="w-full max-w-md">
            <div class="mb-10 text-center">
                <div class="mb-5 flex justify-center">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('custom/brand/frontend-logo.png') }}" alt="{{ config('app.name') }}" class="h-14 transition-all duration-300 hover:scale-105" style="filter: drop-shadow(0 0 25px rgba(255,45,149,0.6));">
                    </a>
                </div>
                <h1 class="mb-2 text-3xl font-bold bg-gradient-to-r from-white to-white/70 bg-clip-text text-transparent">Welcome back</h1>
                <p class="bg-gradient-to-r from-[#ff2d95] via-[#8f4de0] to-[#ff6bb5] bg-[length:200%_auto] bg-clip-text text-transparent animate-[shimmer_3s_linear_infinite]">Sign in to continue your journey</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-6">
                    <label class="mb-2 block text-sm font-semibold text-white/70" for="email">Email Address</label>
                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <input class="w-full rounded-2xl border border-white/10 bg-white/5 pl-12 pr-4 py-3.5 text-white placeholder-white/30 transition-all focus:border-[#ff2d95] focus:outline-none focus:ring-4 focus:ring-[#ff2d95]/20" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="mb-2 block text-sm font-semibold text-white/70" for="password">Password</label>
                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <input class="w-full rounded-2xl border border-white/10 bg-white/5 pl-12 pr-12 py-3.5 text-white placeholder-white/30 transition-all focus:border-[#ff2d95] focus:outline-none focus:ring-4 focus:ring-[#ff2d95]/20" type="password" id="password" name="password" placeholder="••••••••" required>
                        <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 rounded-lg p-1 transition-all hover:bg-white/10" onclick="togglePassword()">
                            <svg class="h-5 w-5 text-white/40 transition-all hover:text-[#ff2d95]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="mb-8 flex items-center justify-between">
                    <label class="flex cursor-pointer items-center gap-2 group">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} class="h-4 w-4 rounded border-white/20 bg-white/5 text-[#ff2d95] focus:ring-[#ff2d95]/20 focus:ring-offset-0">
                        <span class="text-sm text-white/50 transition-colors group-hover:text-white/80">Keep me signed in</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-[#8f4de0] transition-all hover:text-[#ff2d95] hover:underline">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="group relative w-full overflow-hidden rounded-2xl bg-gradient-to-r from-[#ff2d95] to-[#8f4de0] py-3.5 font-semibold text-white transition-all hover:shadow-2xl hover:shadow-[#ff2d95]/30 active:scale-95">
                    <span class="relative z-10">Sign in to account</span>
                    <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-500 group-hover:translate-x-full"></div>
                </button>
            </form>

            <div class="mt-8 pt-6 text-center border-t border-white/10">
                <p class="text-sm text-white/40">
                    Trouble logging in? 
                    <a href="{{ env('SUPPORT_DESK') }}" target="_blank" class="font-medium text-[#ff2d95] transition-all hover:text-[#ff6bb5] hover:underline">Click here</a>
                </p>
            </div>
        </div>
    </div>

    <div class="relative hidden w-1/2 overflow-hidden lg:flex lg:items-center lg:justify-center">
        <video class="absolute inset-0 w-full h-full object-cover" autoplay loop muted playsinline poster="https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=1600">
            <source src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a1a] via-[#0a0a1a]/60 to-[#0a0a1a]/30"></div>
        
        <div class="relative z-10 text-center px-12">
            <div class="mb-8">
                <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=400&h=300&fit=crop" alt="Streaming platform" class="rounded-2xl shadow-2xl mx-auto border border-white/10 w-64 h-48 object-cover">
            </div>
            <h2 class="mb-4 text-3xl font-bold bg-gradient-to-r from-[#ff2d95] via-[#8f4de0] to-[#ff6bb5] bg-clip-text text-transparent">Watch Anywhere, Anytime</h2>
            <p class="text-white/60 leading-relaxed max-w-sm mx-auto">Stream thousands of movies, series, and exclusive content in stunning quality.</p>
            
            <div class="mt-8 flex justify-center gap-4">
                <div class="flex items-center gap-2 text-white/40 text-sm">
                    <svg class="w-4 h-4 text-[#ff2d95]" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/></svg>
                    <span>4K HDR</span>
                </div>
                <div class="flex items-center gap-2 text-white/40 text-sm">
                    <svg class="w-4 h-4 text-[#ff2d95]" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/></svg>
                    <span>Dolby Audio</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        
        const svg = document.querySelector('.password-toggle svg');
        if (type === 'text') {
            svg.innerHTML = '<path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/><path d="M3 3l18 18"/><path d="M15 12a3 3 0 01-3 3"/>';
        } else {
            svg.innerHTML = '<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
        }
    }
</script>
@endsection