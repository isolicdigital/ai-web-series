<x-guest-layout>
    <div class="flex min-h-screen">
        <div class="flex w-full lg:w-1/2 items-center justify-center bg-[#0a0a1a] p-8 lg:p-12">
            <div class="w-full max-w-md">
                <div class="mb-10 text-center">
                    <div class="mb-5 flex justify-center">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('custom/brand/frontend-logo.png') }}" alt="{{ config('app.name') }}" class="h-14 transition-all duration-300 hover:scale-105" style="filter: drop-shadow(0 0 25px rgba(255,45,149,0.6));">
                        </a>
                    </div>
                    <h1 class="mb-2 text-3xl font-bold bg-gradient-to-r from-white to-white/70 bg-clip-text text-transparent">Verify Email</h1>
                    <p class="text-white/50 text-sm">Thanks for signing up! Before getting started, please verify your email address.</p>
                </div>

                <div class="mb-6 rounded-2xl bg-white/5 border border-white/10 p-4 text-center">
                    <p class="text-white/60 text-sm">
                        We sent a verification link to your email address. Click the link to activate your account.
                    </p>
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-6 rounded-2xl bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 text-sm text-center">
                        A new verification link has been sent to your email address.
                    </div>
                @endif

                <div class="flex items-center justify-between gap-4">
                    <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="group relative w-full rounded-2xl bg-gradient-to-r from-[#ff2d95] to-[#8f4de0] py-3.5 font-semibold text-white transition-all hover:shadow-2xl hover:shadow-[#ff2d95]/30 active:scale-95">
                            <span class="relative z-10">Resend Verification Email</span>
                            <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-500 group-hover:translate-x-full rounded-2xl"></div>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-2xl border border-white/20 bg-white/5 px-6 py-3.5 text-sm font-medium text-white/60 transition-all hover:bg-white/10 hover:text-white/80">
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="relative hidden w-1/2 overflow-hidden lg:flex lg:items-center lg:justify-center">
            <video class="absolute inset-0 w-full h-full object-cover" autoplay loop muted playsinline>
                <source src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4" type="video/mp4">
            </video>
            <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a1a] via-[#0a0a1a]/60 to-[#0a0a1a]/30"></div>
            
            <div class="relative z-10 text-center px-12">
                <div class="mb-8">
                    <img src="https://images.unsplash.com/photo-1596526131083-a240c0b94b36?w=400&h=300&fit=crop" alt="Email verification" class="rounded-2xl shadow-2xl mx-auto border border-white/10 w-64 h-48 object-cover">
                </div>
                <h2 class="mb-4 text-3xl font-bold bg-gradient-to-r from-[#ff2d95] via-[#8f4de0] to-[#ff6bb5] bg-clip-text text-transparent">Check Your Inbox</h2>
                <p class="text-white/60 leading-relaxed max-w-sm mx-auto">Verify your email to unlock full access to your account.</p>
            </div>
        </div>
    </div>
</x-guest-layout>