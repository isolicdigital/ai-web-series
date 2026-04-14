<nav class="fixed top-0 w-full z-50 bg-gradient-to-b from-black/95 via-black/80 to-transparent px-4 md:px-12 py-3 transition-all duration-300 backdrop-blur-sm" id="navbar">
    <div class="flex items-center justify-between">
        <!-- Logo Section -->
        <div class="flex items-center gap-8">
            <div class="flex items-center gap-2 group cursor-pointer" onclick="window.location.href='{{ route('dashboard') }}'">
                <!-- Logo Image -->
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
                <a href="{{ route('web-series.create') }}" class="nav-link px-4 py-2 text-gray-300 hover:text-white transition-all text-sm font-medium rounded-lg hover:bg-white/10">
                    <i class="fas fa-wand-magic mr-2 text-xs"></i>Create
                </a>
                <a href="{{ route('web-series.my-series') }}" class="nav-link px-4 py-2 text-gray-300 hover:text-white transition-all text-sm font-medium rounded-lg hover:bg-white/10">
                    <i class="fas fa-tv mr-2 text-xs"></i>My Series
                </a>
                <a href="{{ route('web-series.dashboard') }}" class="nav-link px-4 py-2 text-gray-300 hover:text-white transition-all text-sm font-medium rounded-lg hover:bg-white/10">
                    <i class="fas fa-fire mr-2 text-xs text-orange-400"></i>Dashboard
                </a>
                <a href="{{ route('buycredits') }}" class="nav-link px-4 py-2 text-gray-300 hover:text-white transition-all text-sm font-medium rounded-lg hover:bg-white/10">
                    <i class="fas fa-gem mr-2 text-xs text-yellow-400"></i>Buy Credits
                </a>
            </div>
        </div>
        
        <!-- Right Section -->
        <div class="flex items-center gap-3">    
            
            <!-- Credits Display -->
            <div class="hidden md:flex items-center gap-2 bg-gradient-to-r from-yellow-500/20 to-orange-500/20 px-3 py-1.5 rounded-full border border-yellow-500/40 shadow-lg">
                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center">
                    <i class="fas fa-gem text-white text-xs"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-white text-sm font-bold leading-tight">{{ auth()->user()->credits ?? 100 }}</span>
                    <span class="text-[9px] text-yellow-400 leading-tight">credits</span>
                </div>
                <a href="{{ route('buycredits') }}" class="ml-1 w-5 h-5 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                    <i class="fas fa-plus text-white text-[8px]"></i>
                </a>
            </div>
            
            <!-- User Menu -->
            <div class="relative group">
                <button class="flex items-center gap-2 text-white p-1 rounded-full hover:bg-white/10 transition-all">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full blur-sm opacity-0 group-hover:opacity-50 transition-opacity"></div>
                        <i class="fas fa-user-circle text-3xl text-purple-400"></i>
                    </div>
                    <div class="hidden md:flex flex-col items-start">
                        <span class="text-white text-sm font-medium leading-tight">{{ auth()->user()->name ?? 'User' }}</span>
                        <span class="text-[10px] text-gray-400 leading-tight">
                            @if(auth()->user()->plan)
                                {{ auth()->user()->plan }} Plan
                            @else
                                Free Plan
                            @endif
                        </span>
                    </div>
                    <i class="fas fa-chevron-down text-xs text-gray-400 hidden md:inline group-hover:text-white transition-colors"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div class="absolute right-0 mt-3 w-64 bg-black/95 backdrop-blur-xl rounded-xl shadow-2xl border border-gray-800 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                    <div class="p-2">
                        <!-- User Info -->
                        <div class="px-3 py-3 border-b border-gray-800">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-user-circle text-4xl text-purple-400"></i>
                                <div>
                                    <p class="text-white font-semibold text-sm">{{ auth()->user()->name ?? 'User' }}</p>
                                    <p class="text-gray-400 text-xs">{{ auth()->user()->email ?? 'user@example.com' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Menu Items -->
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
                            <i class="fas fa-user-circle w-4 text-purple-400"></i>
                            <span>My Profile</span>
                        </a>
                        <a href="{{ route('web-series.my-series') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
                            <i class="fas fa-tv w-4 text-blue-400"></i>
                            <span>My Series</span>
                        </a>
                        <a href="{{ route('buycredits') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
                            <i class="fas fa-gem w-4 text-yellow-400"></i>
                            <span>Buy Credits</span>
                            <span class="ml-auto text-[10px] bg-purple-500/20 text-purple-300 px-1.5 py-0.5 rounded">{{ auth()->user()->credits ?? 0 }} credits</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
                            <i class="fas fa-history w-4 text-blue-400"></i>
                            <span>Generation History</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
                            <i class="fas fa-cog w-4 text-gray-400"></i>
                            <span>Settings</span>
                        </a>
                        <a href="{{ route('support') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-all text-sm">
                            <i class="fas fa-question-circle w-4 text-yellow-400"></i>
                            <span>Help & Support</span>
                        </a>
                        
                        <hr class="border-gray-800 my-2">
                        
                        <a href="{{ route('logout') }}" 
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="flex items-center gap-3 px-3 py-2.5 text-red-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all text-sm">
                            <i class="fas fa-sign-out-alt w-4"></i>
                            <span>Logout</span>
                        </a>
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
        <div class="flex flex-col gap-2 bg-black/50 backdrop-blur-md rounded-xl p-4 border border-gray-800">
            <div class="flex items-center gap-3 px-3 py-2 border-b border-gray-700 mb-2">
                <i class="fas fa-user-circle text-3xl text-purple-400"></i>
                <div>
                    <p class="text-white font-semibold">{{ auth()->user()->name ?? 'User' }}</p>
                    <p class="text-gray-400 text-xs">{{ auth()->user()->email ?? 'user@example.com' }}</p>
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-home w-4"></i> Home
            </a>
            <a href="{{ route('web-series.create') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-wand-magic w-4"></i> Create Series
            </a>
            <a href="{{ route('web-series.my-series') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-tv w-4"></i> My Series
            </a>
            <a href="{{ route('web-series.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-fire w-4 text-orange-400"></i> Dashboard
            </a>
            <a href="{{ route('buycredits') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-gem w-4 text-yellow-400"></i> Buy Credits
            </a>
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-user w-4"></i> Profile
            </a>
            <a href="{{ route('support') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-question-circle w-4"></i> Support
            </a>
            <hr class="border-gray-800 my-1">
            <div class="flex items-center justify-between gap-2 bg-yellow-500/10 px-3 py-2 rounded-lg">
                <div class="flex items-center gap-2">
                    <i class="fas fa-gem text-yellow-500 text-sm"></i>
                    <span class="text-white text-sm">Credits Available</span>
                </div>
                <span class="text-yellow-400 font-bold">{{ auth()->user()->credits ?? 100 }}</span>
            </div>
            <a href="{{ route('logout') }}" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="flex items-center gap-3 px-3 py-2 text-red-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition mt-2">
                <i class="fas fa-sign-out-alt w-4"></i> Logout
            </a>
        </div>
    </div>
</nav>

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
        background: linear-gradient(90deg, #ef4444, #a855f7);
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
    
    function scrollToCreator() {
        const creatorSection = document.getElementById('creator-section');
        if (creatorSection) {
            creatorSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
</script>