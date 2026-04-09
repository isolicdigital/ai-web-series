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
                    <h1 class="mb-2 text-3xl font-bold bg-gradient-to-r from-white to-white/70 bg-clip-text text-transparent">Reset Password</h1>
                    <p class="text-white/50 text-sm">Create a new password for your account</p>
                </div>

                <form method="POST" action="{{ route('password.store') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="mb-6">
                        <label class="mb-2 block text-sm font-semibold text-white/70" for="email">Email Address</label>
                        <div class="relative">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <input id="email" class="w-full rounded-2xl border border-white/10 bg-white/5 pl-12 pr-4 py-3.5 text-white placeholder-white/30 transition-all focus:border-[#ff2d95] focus:outline-none focus:ring-4 focus:ring-[#ff2d95]/20" type="email" name="email" value="{{ old('email', $request->email) }}" placeholder="name@example.com" required autofocus autocomplete="username">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-[#ff2d95]" />
                    </div>

                    <div class="mb-6">
                        <label class="mb-2 block text-sm font-semibold text-white/70" for="password">New Password</label>
                        <div class="relative">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <input id="password" class="w-full rounded-2xl border border-white/10 bg-white/5 pl-12 pr-12 py-3.5 text-white placeholder-white/30 transition-all focus:border-[#ff2d95] focus:outline-none focus:ring-4 focus:ring-[#ff2d95]/20" type="password" name="password" placeholder="••••••••" required autocomplete="new-password">
                            <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 rounded-lg p-1 transition-all hover:bg-white/10" onclick="togglePassword('password')">
                                <svg class="h-5 w-5 text-white/40 transition-all hover:text-[#ff2d95]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-[#ff2d95]" />
                    </div>

                    <div class="mb-8">
                        <label class="mb-2 block text-sm font-semibold text-white/70" for="password_confirmation">Confirm Password</label>
                        <div class="relative">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <input id="password_confirmation" class="w-full rounded-2xl border border-white/10 bg-white/5 pl-12 pr-12 py-3.5 text-white placeholder-white/30 transition-all focus:border-[#ff2d95] focus:outline-none focus:ring-4 focus:ring-[#ff2d95]/20" type="password" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
                            <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 rounded-lg p-1 transition-all hover:bg-white/10" onclick="togglePassword('password_confirmation')">
                                <svg class="h-5 w-5 text-white/40 transition-all hover:text-[#ff2d95]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-[#ff2d95]" />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="group relative rounded-2xl bg-gradient-to-r from-[#ff2d95] to-[#8f4de0] px-8 py-3.5 font-semibold text-white transition-all hover:shadow-2xl hover:shadow-[#ff2d95]/30 active:scale-95">
                            <span class="relative z-10">Reset Password</span>
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
                    <img src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=400&h=300&fit=crop" alt="Reset password" class="rounded-2xl shadow-2xl mx-auto border border-white/10 w-64 h-48 object-cover">
                </div>
                <h2 class="mb-4 text-3xl font-bold bg-gradient-to-r from-[#ff2d95] via-[#8f4de0] to-[#ff6bb5] bg-clip-text text-transparent">Create New Password</h2>
                <p class="text-white/60 leading-relaxed max-w-sm mx-auto">Choose a strong password to keep your account secure.</p>
            </div>
        </div>
    </div>
</x-guest-layout>

<script>
    function togglePassword(fieldId) {
        const input = document.getElementById(fieldId);
        const type = input.type === 'password' ? 'text' : 'password';
        input.type = type;
    }
</script>