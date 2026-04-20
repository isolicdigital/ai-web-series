@extends('layouts.app')

@section('content')
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <!-- Modern Hero Section with Black Background -->
    <div class="relative min-h-[90vh] overflow-hidden bg-black">
        <!-- Background Image -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('custom/img/hero-image.png') }}" 
                 alt="Hero Background" 
                 class="w-full h-full object-cover opacity-40"
                 onerror="this.style.display='none'">
            <div class="absolute inset-0 bg-gradient-to-br via-purple-950/50"></div>
        </div>
        
        <div class="absolute inset-0 bg-gradient-to-br from-black via-purple-950/30 to-black"></div>
        
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-20 left-10 w-72 h-72 bg-purple-600/20 rounded-full blur-3xl animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-pink-600/20 rounded-full blur-3xl animate-pulse-slower"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-3xl animate-pulse"></div>
        </div>
        
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-[10%] left-[20%] w-1 h-1 bg-purple-400 rounded-full animate-float"></div>
            <div class="absolute top-[30%] left-[70%] w-2 h-2 bg-pink-400 rounded-full animate-float-delay"></div>
            <div class="absolute top-[60%] left-[10%] w-1.5 h-1.5 bg-blue-400 rounded-full animate-float"></div>
            <div class="absolute top-[80%] left-[85%] w-1 h-1 bg-purple-400 rounded-full animate-float-delay"></div>
            <div class="absolute top-[15%] left-[90%] w-2 h-2 bg-pink-400 rounded-full animate-float"></div>
            <div class="absolute top-[70%] left-[45%] w-1.5 h-1.5 bg-purple-400 rounded-full animate-float-delay"></div>
        </div>
        
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" xmlns="http://www.w3.org/2000/svg"%3E%3Cdefs%3E%3Cpattern id="grid" width="60" height="60" patternUnits="userSpaceOnUse"%3E%3Cpath d="M 60 0 L 0 0 0 60" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="1"/%3E%3C/pattern%3E%3C/defs%3E%3Crect width="100%25" height="100%25" fill="url(%23grid)"/%3E%3C/svg%3E')] opacity-30"></div>
        
        <div class="relative z-10 container mx-auto px-4 py-20 md:py-28 lg:py-32">
            <div class="max-w-5xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md px-5 py-2.5 rounded-full mb-8 border border-white/20 shadow-lg hover:shadow-purple-500/20 transition-all duration-300">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    <span class="text-white/90 text-sm font-medium tracking-wide">✨ AI POWERED PLATFORM</span>
                    <span class="w-px h-4 bg-white/20"></span>
                    <span class="text-white/60 text-xs font-mono">v1.0</span>
                </div>
                
                <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-white mb-8 tracking-tight leading-tight">
                    Create Your Own
                    <div class="relative inline-block mt-2 md:mt-0">
                        <span class="bg-gradient-to-r from-purple-400 via-pink-400 to-purple-400 bg-clip-text text-transparent bg-300% animate-gradient bg-[length:200%_200%]">
                            AI Web Series
                        </span>
                        <div class="absolute -inset-2 bg-gradient-to-r from-purple-500/30 to-pink-500/30 blur-2xl rounded-full -z-10 animate-pulse"></div>
                    </div>
                </h1>
                
                <p class="text-xl md:text-2xl lg:text-3xl text-gray-300 mb-8 font-light leading-relaxed">
                    Transform your imagination into
                    <span class="font-semibold bg-gradient-to-r from-purple-300 to-pink-300 bg-clip-text text-transparent">
                        cinematic masterpieces
                    </span>
                </p>
                
                <div class="max-w-2xl mx-auto mb-12">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20 hover:border-purple-500/30 transition-all duration-300">
                        <div class="flex flex-wrap items-center justify-center gap-4 text-gray-300 text-sm md:text-base">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-film text-purple-400"></i>
                                <span>Unlimited Episodes</span>
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
                
                <div class="flex flex-wrap gap-5 justify-center mb-16">
                    <a href="{{ route('web-series.create') }}" 
                       class="group relative px-8 py-4 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold text-lg overflow-hidden shadow-2xl hover:shadow-purple-500/30 transition-all duration-300 hover:scale-105">
                        <span class="absolute inset-0 bg-gradient-to-r from-purple-500 to-pink-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                        <span class="relative flex items-center gap-2">
                            <i class="fas fa-wand-magic text-xl group-hover:rotate-12 transition-transform duration-300"></i>
                            Start Creating
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
            </div>
        </div>
        
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
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        @keyframes scroll {
            0%, 100% { transform: translateY(0); opacity: 0.6; }
            50% { transform: translateY(8px); opacity: 1; }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
            20% { opacity: 0.5; }
            80% { opacity: 0.5; }
            100% { transform: translateY(-100px) translateX(50px); opacity: 0; }
        }
        @keyframes float-delay {
            0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
            20% { opacity: 0.5; }
            80% { opacity: 0.5; }
            100% { transform: translateY(-100px) translateX(-50px); opacity: 0; }
        }
        .animate-gradient { background-size: 200% 200%; animation: gradient 3s ease infinite; }
        .animate-scroll { animation: scroll 2s ease-in-out infinite; }
        .animate-float { animation: float 8s ease-in-out infinite; }
        .animate-float-delay { animation: float-delay 10s ease-in-out infinite; }
        .animate-pulse-slow { animation: pulse 6s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        .animate-pulse-slower { animation: pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.1); }
        }
    </style>

    <!-- Main Dashboard Content -->
    <div class="bg-black -mt-6 rounded-t-3xl relative z-20">
        <div class="container mx-auto px-4 py-12">
            
            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
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
            
            <!-- Creator Section -->
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
            
            <!-- POPULAR CATEGORIES SECTION (All Categories) -->
            @php
                use App\Models\Category;
                use App\Models\CategoryTemplate;
                
                $allCategories = Category::where('is_active', true)
                    ->orderBy('display_order', 'asc')
                    ->get();
                
                $templates = CategoryTemplate::where('is_active', true)
                    ->whereNotNull('init_image')
                    ->get()
                    ->keyBy('category_id');
            @endphp
            
            @if($allCategories->count() > 0)
            <div class="mb-12">
                <div class="mb-5">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                        <i class="fas fa-fire text-orange-400"></i>
                        All Categories
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Explore all available genres</p>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($allCategories as $category)
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
                                <div class="h-42 flex items-center justify-center">
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
            
            <!-- MY SERIES SECTION - Enhanced -->
            @if(isset($webSeries) && $webSeries->count() > 0)
            <div class="mb-12">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                            <i class="fas fa-film text-purple-400"></i>
                            My Series
                        </h2>
                        <p class="text-gray-400 text-sm mt-1">Your created web series</p>
                    </div>
                    <a href="{{ route('web-series.my-series') }}" class="text-purple-400 hover:text-purple-300 text-sm font-medium transition-all duration-300 flex items-center gap-1 group">
                        View All
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">
                    @foreach($webSeries->take(5) as $mySeries)
                    @php
                        // Get episodes for this series
                        $episodes = $mySeries->episodes;
                        $episodeCount = $episodes->count();
                        $hasMultipleEpisodes = $episodeCount > 1;
                        
                        // Get thumbnail from first episode's first scene
                        $thumbnailUrl = null;
                        $firstEpisode = $episodes->first();
                        if ($firstEpisode) {
                            $firstScene = $firstEpisode->scenes->first();
                            if ($firstScene && $firstScene->generated_image_url) {
                                $thumbnailUrl = asset($firstScene->generated_image_url);
                            }
                        }
                        
                        $progressPercent = $mySeries->total_episodes ? ($episodeCount / $mySeries->total_episodes) * 100 : 0;
                    @endphp
                    <div class="group bg-gradient-to-br from-gray-900/80 to-gray-800/40 backdrop-blur-lg rounded-2xl border border-gray-700/50 hover:border-purple-500/50 transition-all duration-500 overflow-hidden hover:shadow-2xl hover:shadow-purple-500/10 hover:-translate-y-1">
                        
                        <!-- Card Header with Thumbnail -->
                        <div class="relative h-40 overflow-hidden">
                            @if($thumbnailUrl)
                                <img src="{{ $thumbnailUrl }}" 
                                     alt="{{ $mySeries->project_name }}" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/20 to-transparent opacity-80"></div>
                            @else
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-600/30 to-pink-600/30 flex items-center justify-center">
                                    <div class="text-center transform group-hover:scale-105 transition-transform duration-300">
                                        <div class="w-16 h-16 mx-auto rounded-2xl bg-white/10 backdrop-blur-sm flex items-center justify-center mb-2">
                                            <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Status Badge -->
                            <div class="absolute top-2 right-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium backdrop-blur-sm {{ $mySeries->status === 'completed' ? 'bg-green-600/80 text-white' : ($mySeries->status === 'generating' ? 'bg-yellow-600/80 text-white' : 'bg-gray-700/80 text-gray-300') }}">
                                    @if($mySeries->status === 'completed')
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @elseif($mySeries->status === 'generating')
                                        <svg class="w-3 h-3 inline mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    @else
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    @endif
                                    {{ $mySeries->status === 'completed' ? 'Completed' : ucfirst($mySeries->status) }}
                                </span>
                            </div>
                            
                            <!-- Episode count overlay -->
                            <div class="absolute bottom-2 left-2">
                                <div class="flex items-center gap-1 bg-black/60 backdrop-blur-md px-2 py-1 rounded-lg">
                                    <svg class="w-3 h-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <span class="text-white text-xs font-medium">{{ $episodeCount }} Episode{{ $episodeCount !== 1 ? 's' : '' }}</span>
                                </div>
                            </div>
                            
                            <!-- Play icon overlay on hover -->
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <div class="w-10 h-10 rounded-full bg-purple-600/90 backdrop-blur-sm flex items-center justify-center transform scale-90 group-hover:scale-100 transition-transform duration-300">
                                    <svg class="w-5 h-5 text-white ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card Content -->
                        <div class="p-3">
                            <h4 class="text-white font-semibold text-sm mb-1 group-hover:text-purple-300 transition line-clamp-1">{{ $mySeries->project_name }}</h4>
                            
                            <!-- Category Badge -->
                            @if($mySeries->category)
                            <div class="mb-2">
                                <span class="inline-flex items-center gap-1 text-xs px-1.5 py-0.5 rounded-full bg-purple-500/20 text-purple-300">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"></path>
                                    </svg>
                                    {{ $mySeries->category->name }}
                                </span>
                            </div>
                            @endif
                            
                            <!-- Description -->
                            <p class="text-gray-400 text-xs mb-2 line-clamp-2 leading-relaxed">{{ Str::limit($mySeries->concept ?? 'No description provided', 70) }}</p>
                            
                            <!-- Progress Bar -->
                            <div class="mb-2">
                                <div class="flex justify-between text-xs text-gray-500 mb-0.5">
                                    <span class="text-[10px]">Progress</span>
                                    <span class="text-[10px] font-mono">{{ $episodeCount }}/{{ $mySeries->total_episodes ?: 1 }}</span>
                                </div>
                                <div class="w-full h-1 bg-gray-700/50 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex gap-2 mt-2 pt-2 border-t border-gray-800/50">
                                @if($hasMultipleEpisodes)
                                    <button onclick="showEpisodeSelector({{ $mySeries->id }}, '{{ addslashes($mySeries->project_name) }}', {{ json_encode($episodes->map(function($e) { return ['id' => $e->id, 'title' => $e->title, 'episode_number' => $e->episode_number]; })) }})" 
                                            class="flex-1 px-2 py-1.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-lg text-white text-xs font-medium transition-all duration-300 flex items-center justify-center gap-1 group/btn">
                                        <svg class="w-3 h-3 group-hover/btn:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Select
                                    </button>
                                @else
                                    <button onclick="viewSeries({{ $mySeries->id }})" 
                                            class="flex-1 px-2 py-1.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-lg text-white text-xs font-medium transition-all duration-300 flex items-center justify-center gap-1 group/btn">
                                        <svg class="w-3 h-3 group-hover/btn:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="text-center py-16 mb-12">
                <div class="relative w-32 h-32 mx-auto mb-6">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-600/20 to-pink-600/20 rounded-full animate-pulse"></div>
                    <div class="absolute inset-2 bg-gradient-to-br from-purple-600/30 to-pink-600/30 rounded-full backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-12 h-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">No Series Yet</h3>
                <p class="text-gray-400 mb-4 max-w-md mx-auto">Start your creative journey by creating your first AI web series</p>
                <a href="{{ route('web-series.create') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 shadow-lg hover:shadow-pink-500/25 hover:scale-105">
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
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #1a1a1a; }
        ::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #8b5cf6, #ec4899); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: linear-gradient(to bottom, #7c3aed, #db2777); }
        html { scroll-behavior: smooth; }
        .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>

    <script>
        // Episode Selector Functions
        let currentEpisodeSelectorCallback = null;
        
        function viewSeries(id) {
            window.location.href = `/web-series/${id}`;
        }
        
        function viewEpisode(seriesId, episodeId) {
            window.location.href = `/web-series/${seriesId}?episode=${episodeId}`;
        }
        
        function showEpisodeSelector(seriesId, seriesName, episodes) {
            // Create modal if it doesn't exist
            let modal = document.getElementById('episodeSelectorModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'episodeSelectorModal';
                modal.className = 'fixed inset-0 bg-black/90 backdrop-blur-md z-50 hidden items-center justify-center transition-all duration-300';
                modal.innerHTML = `
                    <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl max-w-md w-full mx-4 border border-purple-500/30 shadow-2xl transform transition-all duration-300 scale-95 opacity-0" id="episodeSelectorContent">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-white">Select Episode</h3>
                                </div>
                                <button onclick="closeEpisodeSelector()" class="text-gray-400 hover:text-white transition">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-gray-400 text-sm mb-4" id="episodeSeriesName"></p>
                            <div class="space-y-2 max-h-96 overflow-y-auto" id="episodeList"></div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            
            document.getElementById('episodeSeriesName').innerHTML = `Choose an episode from <span class="text-purple-400 font-semibold">${seriesName}</span>`;
            
            const episodeList = document.getElementById('episodeList');
            episodeList.innerHTML = '';
            
            episodes.forEach(episode => {
                const episodeCard = document.createElement('div');
                episodeCard.className = 'bg-gray-800/50 rounded-xl p-3 cursor-pointer hover:bg-gray-700/50 transition-all duration-300 border border-gray-700 hover:border-purple-500/50 group';
                episodeCard.onclick = () => {
                    closeEpisodeSelector();
                    viewEpisode(seriesId, episode.id);
                };
                episodeCard.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-600/30 to-pink-600/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span class="text-purple-400 font-bold text-sm">${episode.episode_number}</span>
                            </div>
                            <div>
                                <h4 class="text-white font-medium text-sm">${this.escapeHtml(episode.title)}</h4>
                                <p class="text-gray-500 text-xs">Episode ${episode.episode_number}</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-500 group-hover:text-purple-400 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                `;
                episodeList.appendChild(episodeCard);
            });
            
            const modalEl = document.getElementById('episodeSelectorModal');
            const content = document.getElementById('episodeSelectorContent');
            modalEl.classList.remove('hidden');
            modalEl.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
        
        function closeEpisodeSelector() {
            const modal = document.getElementById('episodeSelectorModal');
            const content = document.getElementById('episodeSelectorContent');
            if (modal && content) {
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }, 300);
            }
        }
        
        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('episodeSelectorModal');
            if (modal && !modal.classList.contains('hidden') && e.target === modal) {
                closeEpisodeSelector();
            }
        });
        
        // Function to fetch and update dashboard stats
        function fetchDashboardStats() {
            fetch('{{ route("dashboard.stats") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('totalSeriesCount').textContent = data.stats.total_series || 0;
                    document.getElementById('totalEpisodesCount').textContent = data.stats.total_episodes || 0;
                    const completionRate = data.stats.completion_rate || 0;
                    document.getElementById('completionRateValue').textContent = completionRate;
                    document.getElementById('completionProgressBar').style.width = completionRate + '%';
                }
            })
            .catch(error => console.error('Error fetching stats:', error));
        }
        
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
        
        // Initialize stats on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetchDashboardStats();
            setInterval(fetchDashboardStats, 30000);
        });
    </script>
@endsection