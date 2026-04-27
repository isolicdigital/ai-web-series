<nav class="fixed top-0 w-full z-50 bg-gradient-to-b from-black/95 via-black/80 to-transparent px-4 md:px-12 py-3 transition-all duration-300 backdrop-blur-sm" id="navbar">
    <div class="flex items-center justify-between">
        <!-- Logo Section -->
        <div class="flex items-center gap-8">
            <div class="flex items-center gap-2 group cursor-pointer" onclick="window.location.href='{{ route('dashboard') }}'">
                <div class="relative">
                    <img src="{{ asset('custom/brand/frontend-logo.png') }}" 
                         alt="AI Series Logo" 
                         class="relative w-42 h-10 rounded-lg object-cover shadow-lg">
                </div>
            </div>
            
            <!-- Navigation Links -->
            <div class="hidden lg:flex items-center gap-1">
                <a href="{{ route('dashboard') }}" class="nav-link px-4 py-2 text-gray-300 hover:text-white transition-all text-sm font-medium rounded-lg hover:bg-white/10">
                    <i class="fas fa-home mr-2 text-xs"></i>Home
                </a>
                <a href="{{ route('web-series.my-series') }}" class="nav-link px-4 py-2 text-gray-300 hover:text-white transition-all text-sm font-medium rounded-lg hover:bg-white/10">
                    <i class="fas fa-tv mr-2 text-xs"></i>My Series
                </a>

                
                <!-- DFY Dropdown (Admin or Level 3+) -->
                @php 
                    $planLevel = Auth::user()->plan_level ?? 0;
                    $isAdmin = Auth::user()->role === 'admin';
                @endphp
                
                @if($isAdmin || $planLevel >= 3)
                <div class="relative group">
                    <a href="#" class="nav-link px-4 py-2 text-gray-300 hover:text-white transition-all text-sm font-medium rounded-lg hover:bg-white/10 inline-flex items-center gap-1">
                        <i class="fas fa-box-open mr-1 text-xs"></i>DFY
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </a>
                    <div class="absolute top-full left-0 mt-1 w-52 bg-black/95 backdrop-blur-xl rounded-xl shadow-2xl border border-gray-800 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="p-2">
                            <a href="{{ route('dfy.images') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
                                <i class="fas fa-image w-4 text-pink-400"></i>
                                DFY Visuals
                            </a>
                            <a href="{{ route('dfy.videos') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
                                <i class="fas fa-video w-4 text-blue-400"></i>
                                DFY Footages
                            </a>
                            <a href="{{ route('page-builder.index') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
                                <i class="fas fa-globe w-4 text-green-400"></i>
                                DFY Sites
                            </a>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Reel Maker (Admin or Level 4+) -->
                @if($isAdmin || $planLevel >= 4)
                <a href="#" class="nav-link px-4 py-2 text-gray-300 hover:text-white transition-all text-sm font-medium rounded-lg hover:bg-white/10" onclick="showComingSoon(event)">
                    <i class="fas fa-film mr-2 text-xs"></i>Reel Maker
                </a>
                @endif
                
                <!-- Whitelabel (Admin or Level 5+) -->
                @if($isAdmin || $planLevel >= 5)
                <a href="{{ route('whitelabel') }}" class="nav-link px-4 py-2 text-gray-300 hover:text-white transition-all text-sm font-medium rounded-lg hover:bg-white/10">
                    <i class="fas fa-tags mr-2 text-xs"></i>Whitelabel
                </a>
                @endif
                
            </div>
        </div>
        
        <!-- Right Section -->
        <div class="flex items-center gap-3">    
            <!-- Credits Display -->
            @php
                $userCredits = Auth::user()->credits ?? 0;
                $freeCreditsRemaining = Auth::user()->videoCredit ? (Auth::user()->videoCredit->free_credits - Auth::user()->videoCredit->free_credits_used) : 0;
                $paidCreditsRemaining = Auth::user()->videoCredit ? (Auth::user()->videoCredit->paid_credits - Auth::user()->videoCredit->paid_credits_used) : 0;
                $totalCredits = $freeCreditsRemaining + $paidCreditsRemaining;
            @endphp
            
            <div class="hidden md:flex items-center gap-2 bg-gradient-to-r from-yellow-500/20 to-orange-500/20 px-3 py-1.5 rounded-full border border-yellow-500/40 shadow-lg">
    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center">
        <i class="fas fa-coins text-white text-xs"></i>
    </div>
    <div class="flex flex-col">
        <span class="text-white text-sm font-bold leading-tight">{{ number_format($totalCredits) }}</span>
        <span class="text-[9px] text-yellow-400 leading-tight">credits</span>
    </div>
    
    <!-- Divider -->
    <div class="w-px h-8 bg-yellow-500/30 mx-1"></div>
    
    <!-- Buy Credit Button -->
    <a href="{{ route('buycredits') }}" class="flex items-center gap-1.5 text-yellow-400 hover:text-yellow-300 transition-all group">
        <i class="fas fa-plus-circle text-xs group-hover:scale-110 transition-transform"></i>
        <span class="text-xs font-medium">Buy</span>
    </a>
</div>
            
            <!-- Admin Dropdown (Admin only) -->
            @if($isAdmin)
            <div class="relative group hidden md:block">
                <button class="flex items-center gap-2 text-white p-1.5 rounded-lg hover:bg-white/10 transition-all">
                    <i class="fas fa-crown text-yellow-400 text-lg"></i>
                    <span class="text-sm font-medium">Admin</span>
                    <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                </button>
                <div class="absolute right-0 mt-2 w-48 bg-black/95 backdrop-blur-xl rounded-xl shadow-2xl border border-gray-800 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                    <div class="p-2">
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
                            <i class="fas fa-users w-4"></i> Users
                        </a>
                        <a href="{{ route('admin.plans.index') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
                            <i class="fas fa-layer-group w-4"></i> Plans
                        </a>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- User Menu -->
            <div class="relative group">
                <button class="flex items-center gap-2 text-white p-1 rounded-full hover:bg-white/10 transition-all">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full blur-sm opacity-0 group-hover:opacity-50 transition-opacity"></div>
                        <i class="fas fa-user-circle text-3xl text-purple-400"></i>
                    </div>
                    <div class="hidden md:flex flex-col items-start">
                        <span class="text-white text-sm font-medium leading-tight">{{ Auth::user()->name ?? 'User' }}</span>
                        <span class="text-[10px] text-gray-400 leading-tight">
                            @if(Auth::user()->plan)
                                {{ Auth::user()->plan }} Plan
                            @else
                                FE Version
                            @endif
                        </span>
                    </div>
                    <i class="fas fa-chevron-down text-xs text-gray-400 hidden md:inline group-hover:text-white transition-colors"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div class="absolute right-0 mt-3 w-64 bg-black/95 backdrop-blur-xl rounded-xl shadow-2xl border border-gray-800 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                    <div class="p-2">
    <!-- User Info -->
    <div class="px-3 py-3 border-b border-gray-800">
        <div class="flex items-center gap-3">
            <i class="fas fa-user-circle text-4xl text-purple-400"></i>
            <div>
                <p class="text-white font-semibold text-sm">{{ Auth::user()->name ?? 'User' }}</p>
                <p class="text-gray-400 text-xs">{{ Auth::user()->email ?? 'user@example.com' }}</p>
            </div>
        </div>
    </div>
    
    <!-- Menu Items -->
    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
        <i class="fas fa-user-circle w-4 text-purple-400"></i>
        <span>My Profile</span>
    </a>
    
    <!-- Buy Credit Button (Prominent) -->
    <a href="{{ route('buycredits') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
        <i class="fas fa-coins w-4 text-yellow-300 group-hover:scale-110 transition-transform"></i>
        <span>Buy Credits</span>
    </a>
    
    <a href="{{ route('web-series.my-series') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
        <i class="fas fa-tv w-4 text-blue-400"></i>
        <span>My Series</span>
    </a>
    
    <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
        <i class="fas fa-cog w-4 text-gray-400"></i>
        <span>Settings</span>
    </a>
    
    <a href="{{ env('TRAINING_URL', '#') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
        <i class="fas fa-graduation-cap w-4 text-purple-400"></i>
        <span>Training</span>
    </a>
    
    <a href="{{ env('SUPPORT_EXT', '#') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm" target="_blank">
        <i class="fas fa-headset w-4 text-yellow-400"></i>
        <span>Support</span>
    </a>
    
    <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm" target="_blank">
        <i class="fas fa-rocket w-4 text-green-400"></i>
        <span>Upgrades</span>
    </a>
    
    <hr class="border-gray-800 my-2">
    
    <a href="{{ route('logout') }}" 
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
       class="flex items-center gap-3 px-3 py-2.5 text-red-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all text-sm">
        <i class="fas fa-sign-out-alt w-4"></i>
        <span>Logout</span>
    </a>
    
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</div>
                </div>
            </div>
            
            <!-- Mobile Menu Button -->
            <button class="lg:hidden text-white p-2 rounded-lg hover:bg-white/10 transition-colors" id="mobileMenuBtn">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
    </div>
    
    <!-- Mobile Menu -->
    <div class="lg:hidden hidden mt-4 pb-4" id="mobileMenu">
        <div class="flex flex-col gap-2 bg-black/50 backdrop-blur-md rounded-xl p-4 border border-gray-800 max-h-[70vh] overflow-y-auto">
            <div class="flex items-center gap-3 px-3 py-2 border-b border-gray-700 mb-2">
                <i class="fas fa-user-circle text-3xl text-purple-400"></i>
                <div>
                    <p class="text-white font-semibold">{{ Auth::user()->name ?? 'User' }}</p>
                    <p class="text-gray-400 text-xs">{{ Auth::user()->email ?? 'user@example.com' }}</p>
                </div>
            </div>
            
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-home w-4"></i> Home
            </a>
            <a href="{{ route('web-series.my-series') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-tv w-4"></i> My Series
            </a>
            
            <!-- Comedy Section -->
            <div class="px-3 py-1 text-xs text-gray-500 font-semibold mt-1">COMEDY STUDIO</div>
            <a href="#" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-microphone-alt w-4 text-purple-400"></i> Comedy Studio
            </a>
            <a href="#" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-book-open w-4 text-blue-400"></i> My Jokes
            </a>
            <a href="#" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-video w-4 text-green-400"></i> My Videos
            </a>
            
            @if($isAdmin || $planLevel >= 3)
            <div class="px-3 py-1 text-xs text-gray-500 font-semibold mt-1">DFY CONTENT</div>
            <a href="{{ route('dfy.images') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-image w-4 text-pink-400"></i> DFY Visuals
            </a>
            <a href="{{ route('dfy.videos') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-video w-4 text-blue-400"></i> DFY Footages
            </a>
            <a href="{{ route('page-builder.index') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-globe w-4 text-green-400"></i> DFY Sites
            </a>
            @endif
            
            @if($isAdmin || $planLevel >= 4)
            <a href="#" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition" onclick="showComingSoon(event)">
                <i class="fas fa-film w-4"></i> Reel Maker
            </a>
            @endif
            
            @if($isAdmin || $planLevel >= 5)
            <a href="{{ route('whitelabel') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-tags w-4"></i> Whitelabel
            </a>
            @endif
            
            <!-- Resources Section -->
            <div class="px-3 py-1 text-xs text-gray-500 font-semibold mt-1">RESOURCES</div>
            <a href="{{ env('TRAINING_URL', '#') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-graduation-cap w-4 text-purple-400"></i> Training
            </a>
            <a href="{{ env('SUPPORT_EXT', '#') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition" target="_blank">
                <i class="fas fa-headset w-4 text-blue-400"></i> Support
            </a>
            <a href="https://aistandup.live/upgrades" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition" target="_blank">
                <i class="fas fa-rocket w-4 text-yellow-400"></i> Upgrades
            </a>
            
            <a href="{{ route('web-series.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-fire w-4 text-orange-400"></i> Dashboard
            </a>
            
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-user w-4"></i> Profile
            </a>
            
            <hr class="border-gray-800 my-1">
            
            <a href="{{ route('logout') }}" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="flex items-center gap-3 px-3 py-2 text-red-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition mt-2">
                <i class="fas fa-sign-out-alt w-4"></i> Logout
            </a>
        </div>
    </div>
</nav>

<!-- Logout Form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>

<style>
    /* Navbar scroll effect */
    .navbar-scrolled {
        background: linear-gradient(135deg, rgba(0,0,0,0.98) 0%, rgba(0,0,0,0.95) 100%);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(255,255,255,0.05);
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }
    
    /* Nav link hover effect */
    .nav-link {
        position: relative;
    }
    
    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 2px;
        background: linear-gradient(90deg, #a855f7, #ec4899);
        transition: all 0.3s ease;
        transform: translateX(-50%);
        border-radius: 2px;
    }
    
    .nav-link:hover::after {
        width: 70%;
    }
    
    /* Dropdown animation */
    .group:hover .group-hover\:visible {
        visibility: visible;
    }
    
    /* Custom scrollbar for mobile menu */
    .max-h-\[70vh\]::-webkit-scrollbar {
        width: 4px;
    }
    
    .max-h-\[70vh\]::-webkit-scrollbar-track {
        background: #1a1a1a;
        border-radius: 4px;
    }
    
    .max-h-\[70vh\]::-webkit-scrollbar-thumb {
        background: linear-gradient(to bottom, #a855f7, #ec4899);
        border-radius: 4px;
    }
</style>

<script>
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    });
    
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            const icon = mobileMenuBtn.querySelector('i');
            if (icon.classList.contains('fa-bars')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
    
    // Coming Soon function
    function showComingSoon(event) {
        event.preventDefault();
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Coming Soon!',
                text: 'This feature is under development and will be available soon.',
                icon: 'info',
                confirmButtonColor: '#a855f7',
                background: '#1a1a1a',
                color: '#fff'
            });
        } else {
            alert('This feature is coming soon!');
        }
    }
    
    function scrollToCreator() {
        const creatorSection = document.getElementById('creator-section');
        if (creatorSection) {
            creatorSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
</script>