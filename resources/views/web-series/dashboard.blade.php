@extends('layouts.app')

@section('content')
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <!-- Modern Hero Section with Black Background -->
    <<div class="relative min-h-[90vh] overflow-hidden bg-black">
    <!-- Animated Gradient Background -->
    <div class="absolute inset-0 bg-gradient-to-br from-black via-purple-950/30 to-black"></div>
    
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-20 left-10 w-72 h-72 bg-purple-600/20 rounded-full blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-pink-600/20 rounded-full blur-3xl animate-pulse-slower"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-3xl animate-pulse"></div>
    </div>
    
    <!-- Floating Particles -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[10%] left-[20%] w-1 h-1 bg-purple-400 rounded-full animate-float"></div>
        <div class="absolute top-[30%] left-[70%] w-2 h-2 bg-pink-400 rounded-full animate-float-delay"></div>
        <div class="absolute top-[60%] left-[10%] w-1.5 h-1.5 bg-blue-400 rounded-full animate-float"></div>
        <div class="absolute top-[80%] left-[85%] w-1 h-1 bg-purple-400 rounded-full animate-float-delay"></div>
        <div class="absolute top-[15%] left-[90%] w-2 h-2 bg-pink-400 rounded-full animate-float"></div>
        <div class="absolute top-[70%] left-[45%] w-1.5 h-1.5 bg-purple-400 rounded-full animate-float-delay"></div>
    </div>
    
    <!-- Grid Pattern Overlay -->
    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" xmlns="http://www.w3.org/2000/svg"%3E%3Cdefs%3E%3Cpattern id="grid" width="60" height="60" patternUnits="userSpaceOnUse"%3E%3Cpath d="M 60 0 L 0 0 0 60" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="1"/%3E%3C/pattern%3E%3C/defs%3E%3Crect width="100%25" height="100%25" fill="url(%23grid)"/%3E%3C/svg%3E')] opacity-30"></div>
    
    <!-- Content -->
    <div class="relative z-10 container mx-auto px-4 py-20 md:py-28 lg:py-32">
        <div class="max-w-5xl mx-auto text-center">
            <!-- Modern Badge with Glow -->
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md px-5 py-2.5 rounded-full mb-8 border border-white/20 shadow-lg hover:shadow-purple-500/20 transition-all duration-300">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                <span class="text-white/90 text-sm font-medium tracking-wide">✨ AI POWERED PLATFORM</span>
                <span class="w-px h-4 bg-white/20"></span>
                <span class="text-white/60 text-xs font-mono">v3.0</span>
            </div>
            
            <!-- Main Title with Enhanced Gradient Animation -->
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-white mb-8 tracking-tight leading-tight">
                Create Your Own
                <div class="relative inline-block mt-2 md:mt-0">
                    <span class="bg-gradient-to-r from-purple-400 via-pink-400 to-purple-400 bg-clip-text text-transparent bg-300% animate-gradient bg-[length:200%_200%]">
                        AI Web Series
                    </span>
                    <div class="absolute -inset-2 bg-gradient-to-r from-purple-500/30 to-pink-500/30 blur-2xl rounded-full -z-10 animate-pulse"></div>
                </div>
            </h1>
            
            <!-- Enhanced Dynamic Subtitle -->
            <p class="text-xl md:text-2xl lg:text-3xl text-gray-300 mb-8 font-light leading-relaxed">
                Transform your imagination into
                <span class="font-semibold bg-gradient-to-r from-purple-300 to-pink-300 bg-clip-text text-transparent">
                    cinematic masterpieces
                </span>
            </p>
            
            <!-- Description with Glass Card -->
            <div class="max-w-2xl mx-auto mb-12">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20 hover:border-purple-500/30 transition-all duration-300">
                    <div class="flex flex-wrap items-center justify-center gap-4 text-gray-300 text-sm md:text-base">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-film text-purple-400"></i>
                            <span>Up to 10 episodes</span>
                        </span>
                        <span class="w-1 h-1 rounded-full bg-gray-600"></span>
                        <span class="flex items-center gap-2">
                            <i class="fas fa-magic text-pink-400"></i>
                            <span>AI-Generated content</span>
                        </span>
                        <span class="w-1 h-1 rounded-full bg-gray-600"></span>
                        <span class="flex items-center gap-2">
                            <i class="fas fa-bolt text-yellow-400"></i>
                            <span>Instant delivery</span>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced CTA Buttons -->
            <div class="flex flex-wrap gap-5 justify-center mb-16">
                <a href="{{ route('web-series.create') }}" 
                   class="group relative px-8 py-4 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold text-lg overflow-hidden shadow-2xl hover:shadow-purple-500/30 transition-all duration-300 hover:scale-105">
                    <span class="absolute inset-0 bg-gradient-to-r from-purple-500 to-pink-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                    <span class="relative flex items-center gap-2">
                        <i class="fas fa-wand-magic text-xl group-hover:rotate-12 transition-transform duration-300"></i>
                        Start Creating Free
                        <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform duration-300"></i>
                    </span>
                </a>
                
                <button onclick="watchDemo()" 
                        class="group px-8 py-4 rounded-xl bg-white/10 backdrop-blur-sm border border-white/30 text-white font-semibold text-lg hover:bg-white/20 hover:border-purple-500/50 transition-all duration-300 flex items-center gap-2 hover:scale-105">
                    <i class="fas fa-play-circle text-xl group-hover:scale-110 transition-transform duration-300"></i>
                    Watch Demo
                    <span class="text-xs text-gray-400 group-hover:text-white transition-colors">(2 min)</span>
                </button>
            </div>
            
            <!-- Enhanced Trust Indicators -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 pt-8 border-t border-white/10">
                <div class="group p-4 rounded-2xl bg-white/5 backdrop-blur-sm hover:bg-white/10 transition-all duration-300 cursor-default hover:scale-105">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-film text-purple-400 text-xl"></i>
                        </div>
                        <div class="text-3xl font-bold text-white">{{ number_format($stats['total_series'] ?? 0) }}+</div>
                    </div>
                    <div class="text-gray-400 text-sm font-medium">Series Created</div>
                </div>
                
                <div class="group p-4 rounded-2xl bg-white/5 backdrop-blur-sm hover:bg-white/10 transition-all duration-300 cursor-default hover:scale-105">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-users text-blue-400 text-xl"></i>
                        </div>
                        <div class="text-3xl font-bold text-white">{{ number_format($totalUsers ?? 0) }}+</div>
                    </div>
                    <div class="text-gray-400 text-sm font-medium">Active Users</div>
                </div>
                
                <div class="group p-4 rounded-2xl bg-white/5 backdrop-blur-sm hover:bg-white/10 transition-all duration-300 cursor-default hover:scale-105">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <div class="w-10 h-10 rounded-full bg-yellow-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-star text-yellow-400 text-xl"></i>
                        </div>
                        <div class="text-3xl font-bold text-white">{{ number_format($avgRating ?? 4.9, 1) }}</div>
                    </div>
                    <div class="text-gray-400 text-sm font-medium">User Rating</div>
                </div>
                
                <div class="group p-4 rounded-2xl bg-white/5 backdrop-blur-sm hover:bg-white/10 transition-all duration-300 cursor-default hover:scale-105">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-video text-green-400 text-xl"></i>
                        </div>
                        <div class="text-3xl font-bold text-white">{{ number_format($stats['total_episodes'] ?? 0) }}+</div>
                    </div>
                    <div class="text-gray-400 text-sm font-medium">Episodes Generated</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Enhanced Scroll Hint -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 cursor-pointer z-20 group" onclick="scrollToCreator()">
        <div class="flex flex-col items-center gap-2">
            <span class="text-white/40 text-xs uppercase tracking-wider group-hover:text-white/70 transition-colors">Scroll to explore</span>
            <div class="w-6 h-10 border-2 border-white/30 rounded-full flex justify-center bg-white/5 backdrop-blur-sm group-hover:border-purple-500/50 transition-all duration-300">
                <div class="w-1.5 h-2 bg-white/60 rounded-full mt-2 animate-scroll group-hover:bg-purple-400 transition-colors"></div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes gradient {
        0%, 100% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
    }
    
    @keyframes scroll {
        0%, 100% {
            transform: translateY(0);
            opacity: 0.6;
        }
        50% {
            transform: translateY(8px);
            opacity: 1;
        }
    }
    
    @keyframes float {
        0%, 100% {
            transform: translateY(0) translateX(0);
            opacity: 0;
        }
        20% {
            opacity: 0.5;
        }
        80% {
            opacity: 0.5;
        }
        100% {
            transform: translateY(-100px) translateX(50px);
            opacity: 0;
        }
    }
    
    @keyframes float-delay {
        0%, 100% {
            transform: translateY(0) translateX(0);
            opacity: 0;
        }
        20% {
            opacity: 0.5;
        }
        80% {
            opacity: 0.5;
        }
        100% {
            transform: translateY(-100px) translateX(-50px);
            opacity: 0;
        }
    }
    
    .animate-gradient {
        background-size: 200% 200%;
        animation: gradient 3s ease infinite;
    }
    
    .animate-scroll {
        animation: scroll 2s ease-in-out infinite;
    }
    
    .animate-float {
        animation: float 8s ease-in-out infinite;
    }
    
    .animate-float-delay {
        animation: float-delay 10s ease-in-out infinite;
    }
    
    .animate-pulse-slow {
        animation: pulse 6s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    .animate-pulse-slower {
        animation: pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 0.3;
            transform: scale(1);
        }
        50% {
            opacity: 0.6;
            transform: scale(1.1);
        }
    }
</style>

    <style>
        @keyframes gradient {
            0%, 100% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
        }
        
        .animate-gradient {
            background-size: 200% 200%;
            animation: gradient 3s ease infinite;
        }
        
        @keyframes scroll {
            0%, 100% {
                transform: translateY(0);
                opacity: 0.6;
            }
            50% {
                transform: translateY(6px);
                opacity: 1;
            }
        }
        
        .animate-scroll {
            animation: scroll 2s ease-in-out infinite;
        }
    </style>

    <!-- Main Dashboard Content -->
    <div class="bg-black -mt-6 rounded-t-3xl relative z-20">
        <div class="container mx-auto px-4 py-12">
            
            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <div class="bg-gradient-to-br from-purple-600/10 to-pink-600/10 rounded-2xl p-5 border border-purple-500/20 hover:border-purple-500/40 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Total Series</p>
                            <p class="text-3xl font-bold text-white">{{ $stats['total_series'] ?? 0 }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-purple-500/20 flex items-center justify-center">
                            <i class="fas fa-tv text-purple-400 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-blue-600/10 to-cyan-600/10 rounded-2xl p-5 border border-blue-500/20 hover:border-blue-500/40 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Total Episodes</p>
                            <p class="text-3xl font-bold text-white">{{ $stats['total_episodes'] ?? 0 }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center">
                            <i class="fas fa-list-ol text-blue-400 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-yellow-600/10 to-orange-600/10 rounded-2xl p-5 border border-yellow-500/20 hover:border-yellow-500/40 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Available Credits</p>
                            <p class="text-3xl font-bold text-white">{{ auth()->user()->credits ?? 0 }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-yellow-500/20 flex items-center justify-center">
                            <i class="fas fa-gem text-yellow-400 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-green-600/10 to-teal-600/10 rounded-2xl p-5 border border-green-500/20 hover:border-green-500/40 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Completion Rate</p>
                            <p class="text-3xl font-bold text-white">{{ $completionRate ?? 0 }}%</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-green-500/20 flex items-center justify-center">
                            <i class="fas fa-chart-line text-green-400 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Creator Section - Card Style -->
            <div class="max-w-5xl mx-auto mb-16" id="creator-section">
                <div class="bg-gradient-to-r from-gray-900/50 to-gray-800/50 backdrop-blur-lg rounded-2xl border border-gray-800 overflow-hidden shadow-xl hover:border-purple-500/30 transition-all duration-300">
                    <div class="p-6 md:p-8">
                        <div class="flex flex-col md:flex-row gap-6 items-center">
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center flex-shrink-0 shadow-lg">
                                <i class="fas fa-robot text-white text-3xl"></i>
                            </div>
                            <div class="flex-1 text-center md:text-left">
                                <h3 class="text-2xl font-bold text-white mb-1">AI Series Generator</h3>
                                <p class="text-gray-400 text-sm mb-3">Create a new web series with AI in minutes</p>
                                <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                                    <span class="text-xs bg-purple-500/20 text-purple-300 px-2 py-1 rounded-full">Up to 10 episodes</span>
                                    <span class="text-xs bg-purple-500/20 text-purple-300 px-2 py-1 rounded-full">HD Quality</span>
                                    <span class="text-xs bg-purple-500/20 text-purple-300 px-2 py-1 rounded-full">Instant Generation</span>
                                </div>
                            </div>
                            <a href="{{ route('web-series.create') }}" 
                               class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-pink-500/25 hover:scale-105">
                                <i class="fas fa-plus-circle"></i>
                                New Series
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- POPULAR CATEGORIES - SHOW FIRST -->
            @php
                use App\Models\Category;
                use App\Models\CategoryTemplate;
                
                $categories = Category::where('is_active', true)
                    ->orderBy('display_order', 'asc')
                    ->get();
                
                // Get templates with images
                $templates = CategoryTemplate::where('is_active', true)
                    ->whereNotNull('init_image')
                    ->get()
                    ->keyBy('category_id');
            @endphp
            
            @if($categories->count() > 0)
            <div class="mb-12">
                <div class="mb-5">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                        <i class="fas fa-fire text-orange-400"></i>
                        Popular Categories
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Explore trending genres</p>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($categories as $category)
                    @php
                        $template = $templates[$category->id] ?? null;
                        $imageUrl = $template && $template->init_image ? asset($template->init_image) : null;
                    @endphp
                    <div class="group cursor-pointer" onclick="window.location.href='{{ route('web-series.create') }}?category_id={{ $category->id }}'">
                        <div class="relative rounded-xl overflow-hidden bg-gradient-to-br from-purple-600/20 to-pink-600/20 border border-purple-500/20 group-hover:border-purple-500/50 transition-all duration-300 group-hover:scale-9 hover:shadow-xl">
                            @if($imageUrl)
                                <div class="relative h-42 overflow-hidden">
                                    <img src="{{ $imageUrl }}" 
                                         alt="{{ $category->name }}"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                                </div>
                            @else
                                <div class="h-32 flex items-center justify-center">
                                    <div class="text-5xl group-hover:scale-110 transition-transform">
                                        <i class="fas {{ $category->icon ?? 'fa-tag' }} text-purple-400"></i>
                                    </div>
                                </div>
                            @endif
                            <div class="p-3 text-center">
                                <h3 class="text-white font-semibold text-sm">{{ $category->name }}</h3>
                                @if($category->description)
                                    <p class="text-gray-400 text-xs mt-1 line-clamp-2">{{ Str::limit($category->description, 50) }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            <!-- MY SERIES SECTION - SHOW AFTER CATEGORIES -->
            @if(isset($webSeries) && $webSeries->count() > 0)
            <div class="mb-12">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                            <i class="fas fa-film text-purple-400"></i>
                            My Series
                        </h2>
                        <p class="text-gray-400 text-sm mt-1">Your created web series</p>
                    </div>
                    <a href="{{ route('web-series.my-series') }}" class="text-purple-400 hover:text-purple-300 text-sm font-medium transition-colors">
                        View All →
                    </a>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($webSeries->take(5) as $series)
                    <div class="group cursor-pointer" onclick="window.location.href='{{ route('web-series.show', $series->id) }}'">
                        <div class="relative rounded-xl overflow-hidden bg-gray-900 hover:shadow-xl transition-all duration-300">
                            <div class="w-full aspect-video bg-gradient-to-br from-purple-600/30 to-pink-600/30 flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                                <i class="fas fa-film text-white/30 text-4xl"></i>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="absolute top-2 left-2">
                                <span class="bg-purple-600 text-white text-xs px-2 py-0.5 rounded-full">
                                    {{ $series->status === 'completed' ? 'Completed' : ucfirst($series->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h4 class="text-white font-medium text-sm truncate">{{ $series->project_name }}</h4>
                            <p class="text-gray-400 text-xs">{{ $series->episodes_count ?? $series->episodes->count() }} episodes</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <!-- Empty State for My Series -->
            <div class="text-center py-16 mb-12">
                <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-purple-500/20 flex items-center justify-center">
                    <i class="fas fa-film text-4xl text-purple-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">No Series Yet</h3>
                <p class="text-gray-400 mb-4">Start your creative journey by creating your first AI web series</p>
                <a href="{{ route('web-series.create') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 shadow-lg hover:shadow-pink-500/25 hover:scale-105">
                    <i class="fas fa-plus-circle"></i>
                    Create Your First Series
                </a>
            </div>
            @endif
            
            <!-- Call to Action Banner -->
            <div class="mt-8 bg-gradient-to-r from-purple-600/10 to-pink-600/10 rounded-2xl border border-purple-500/30 p-6 md:p-8 text-center hover:border-purple-500/50 transition-all duration-300">
                <h3 class="text-2xl font-bold text-white mb-2">Ready to Create Your Own Series?</h3>
                <p class="text-gray-400 mb-4">Start your creative journey with our AI-powered platform today.</p>
                <a href="{{ route('web-series.create') }}" 
                   class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold transition-all duration-300 shadow-lg hover:shadow-pink-500/25 hover:scale-105">
                    Get Started Now
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>

    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #1a1a1a;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #8b5cf6, #ec4899);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #7c3aed, #db2777);
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    <script>
        function scrollToCreator() {
            const section = document.getElementById('creator-section');
            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
        
        function watchDemo() {
            Swal.fire({
                title: 'Demo Video',
                text: 'Watch how AI creates amazing web series in minutes!',
                icon: 'info',
                confirmButtonColor: '#7c3aed',
                background: '#1e1b4b',
                color: '#fff'
            });
        }
    </script>
@endsection