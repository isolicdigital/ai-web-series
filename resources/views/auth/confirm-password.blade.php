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
                    <h1 class="mb-2 text-3xl font-bold bg-gradient-to-r from-white to-white/70 bg-clip-text text-transparent">Confirm Password</h1>
                    <p class="text-white/50 text-sm">This is a secure area. Please confirm your password before continuing.</p>
                </div>

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf

                    <div class="mb-8">
                        <label class="mb-2 block text-sm font-semibold text-white/70" for="password">Password</label>
                        <div class="relative">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <input id="password" class="w-full rounded-2xl border border-white/10 bg-white/5 pl-12 pr-4 py-3.5 text-white placeholder-white/30 transition-all focus:border-[#ff2d95] focus:outline-none focus:ring-4 focus:ring-[#ff2d95]/20" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-[#ff2d95]" />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="group relative rounded-2xl bg-gradient-to-r from-[#ff2d95] to-[#8f4de0] px-8 py-3.5 font-semibold text-white transition-all hover:shadow-2xl hover:shadow-[#ff2d95]/30 active:scale-95">
                            <span class="relative z-10">Confirm</span>
                            <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-500 group-hover:translate-x-full rounded-2xl"></div>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="relative hidden w-1/2 overflow-hidden lg:flex lg:items-center lg:justify-center">
            <video class="absolute inset-0 w-full h-full object-cover" autoplay loop muted playsinline>
                <source src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4" type="video/mp4">
            </video>
            <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a1a] via-[#0a0a1a]/60 to-[#0a0a1a]/30"></div>
            
            <div class="relative z-10 text-center px-12">
                <div class="mb-8">
                    <img src="https://images.unsplash.com/photo-1550439062-609e1531270e?w=400&h=300&fit=crop" alt="Secure area" class="rounded-2xl shadow-2xl mx-auto border border-white/10 w-64 h-48 object-cover">
                </div>
                <h2 class="mb-4 text-3xl font-bold bg-gradient-to-r from-[#ff2d95] via-[#8f4de0] to-[#ff6bb5] bg-clip-text text-transparent">Secure Verification</h2>
                <p class="text-white/60 leading-relaxed max-w-sm mx-auto">Protecting your account with an extra layer of security.</p>
            </div>
        </div>
    </div>
</x-guest-layout>