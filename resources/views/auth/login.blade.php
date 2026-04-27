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
                        <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-white/30"></i>
                        <input class="w-full rounded-2xl border border-white/10 bg-white/5 pl-12 pr-4 py-3.5 text-white placeholder-white/30 transition-all focus:border-[#ff2d95] focus:outline-none focus:ring-4 focus:ring-[#ff2d95]/20" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
                        @error('email')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label class="mb-2 block text-sm font-semibold text-white/70" for="password">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-white/30"></i>
                        <input class="w-full rounded-2xl border border-white/10 bg-white/5 pl-12 pr-12 py-3.5 text-white placeholder-white/30 transition-all focus:border-[#ff2d95] focus:outline-none focus:ring-4 focus:ring-[#ff2d95]/20" type="password" id="password" name="password" placeholder="••••••••" required>
                        <button type="button" id="togglePasswordBtn" class="absolute right-4 top-1/2 -translate-y-1/2 rounded-lg p-1 transition-all hover:bg-white/10" onclick="togglePasswordVisibility()">
                            <i class="fas fa-eye text-white/40 text-lg"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
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
                    <a href="{{ env('SUPPORT_DESK', '#') }}" target="_blank" class="font-medium text-[#ff2d95] transition-all hover:text-[#ff6bb5] hover:underline">Click here</a>
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
                    <i class="fas fa-check-circle text-[#ff2d95]"></i>
                    <span>4K HDR</span>
                </div>
                <div class="flex items-center gap-2 text-white/40 text-sm">
                    <i class="fas fa-music text-[#ff2d95]"></i>
                    <span>Dolby Audio</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
{{-- No additional scripts needed since function is in head --}}
@endsection