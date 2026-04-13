@extends('layouts.app')

@section('content')
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <!-- Modern Hero Section with Clean Design -->
    <div class="relative min-h-[90vh] overflow-hidden">
        <!-- Animated Gradient Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-indigo-950 to-purple-950"></div>
        
        <!-- Modern Abstract Shapes -->
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-gradient-to-tr from-blue-500/10 to-cyan-500/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-r from-purple-500/5 to-transparent rounded-full blur-3xl"></div>
        
        <!-- Grid Pattern Overlay -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" xmlns="http://www.w3.org/2000/svg"%3E%3Cdefs%3E%3Cpattern id="grid" width="60" height="60" patternUnits="userSpaceOnUse"%3E%3Cpath d="M 60 0 L 0 0 0 60" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="1"/%3E%3C/pattern%3E%3C/defs%3E%3Crect width="100%25" height="100%25" fill="url(%23grid)"/%3E%3C/svg%3E')] opacity-50"></div>
        
        <div class="relative z-10 container mx-auto px-4 py-20 md:py-28 lg:py-32">
            <div class="max-w-5xl mx-auto text-center">
                <!-- Modern Badge -->
                <div class="inline-flex items-center gap-2 bg-white/5 backdrop-blur-md px-4 py-2 rounded-full mb-6 border border-white/10 shadow-lg">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    <span class="text-white/90 text-sm font-medium tracking-wide">AI POWERED PLATFORM</span>
                    <span class="w-px h-4 bg-white/20"></span>
                    <span class="text-white/60 text-xs">v2.0</span>
                </div>
                
                <!-- Main Title with Gradient Animation -->
                <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-white mb-6 tracking-tight">
                    Create Your Own
                    <div class="relative inline-block mt-2 md:mt-0">
                        <span class="bg-gradient-to-r from-purple-400 via-pink-400 to-purple-400 bg-clip-text text-transparent bg-300% animate-gradient">
                            AI Web Series
                        </span>
                        <div class="absolute -inset-1 bg-gradient-to-r from-purple-500/20 to-pink-500/20 blur-xl rounded-full -z-10"></div>
                    </div>
                </h1>
                
                <!-- Dynamic Subtitle -->
                <p class="text-xl md:text-2xl lg:text-3xl text-gray-200 mb-6 font-light">
                    Transform your imagination into
                    <span class="font-semibold text-purple-300">cinematic masterpieces</span>
                </p>
                
                <!-- Description with Glass Card -->
                <div class="max-w-2xl mx-auto mb-10">
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-4 border border-white/10">
                        <p class="text-gray-300 text-base leading-relaxed">
                            🎬 Up to 10 episodes per series • ✨ AI-Generated content • 🚀 Instant delivery
                        </p>
                    </div>
                </div>
                
                <!-- CTA Buttons - Modern Design -->
                <div class="flex flex-wrap gap-5 justify-center mb-12">
                    <a href="{{ route('web-series.create') }}" 
                       class="group relative px-8 py-3.5 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold text-lg overflow-hidden shadow-2xl hover:shadow-purple-500/25 transition-all duration-300 hover:scale-105">
                        <span class="absolute inset-0 bg-gradient-to-r from-purple-500 to-pink-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                        <span class="relative flex items-center gap-2">
                            <i class="fas fa-wand-magic text-lg group-hover:rotate-12 transition-transform"></i>
                            Start Creating Free
                            <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                        </span>
                    </a>
                    
                    <button onclick="watchDemo()" 
                            class="group px-8 py-3.5 rounded-xl bg-white/5 backdrop-blur-sm border border-white/20 text-white font-semibold text-lg hover:bg-white/10 hover:border-white/30 transition-all duration-300 flex items-center gap-2">
                        <i class="fas fa-play-circle group-hover:scale-110 transition-transform"></i>
                        Watch Demo
                        <span class="text-xs text-gray-400 group-hover:text-white transition-colors">(2 min)</span>
                    </button>
                </div>
                
                <!-- Trust Indicators - Modern Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-8 border-t border-white/10">
                    <div class="group p-4 rounded-2xl bg-white/5 backdrop-blur-sm hover:bg-white/10 transition-all duration-300 cursor-default">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <i class="fas fa-film text-purple-400 text-xl group-hover:scale-110 transition-transform"></i>
                            <div class="text-2xl font-bold text-white">{{ number_format($totalSeries ?? 0) }}+</div>
                        </div>
                        <div class="text-gray-400 text-sm">Series Created</div>
                    </div>
                    
                    <div class="group p-4 rounded-2xl bg-white/5 backdrop-blur-sm hover:bg-white/10 transition-all duration-300 cursor-default">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <i class="fas fa-users text-blue-400 text-xl group-hover:scale-110 transition-transform"></i>
                            <div class="text-2xl font-bold text-white">{{ number_format($totalUsers ?? 0) }}+</div>
                        </div>
                        <div class="text-gray-400 text-sm">Active Users</div>
                    </div>
                    
                    <div class="group p-4 rounded-2xl bg-white/5 backdrop-blur-sm hover:bg-white/10 transition-all duration-300 cursor-default">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <i class="fas fa-star text-yellow-400 text-xl group-hover:scale-110 transition-transform"></i>
                            <div class="text-2xl font-bold text-white">{{ number_format($avgRating ?? 4.9, 1) }}</div>
                        </div>
                        <div class="text-gray-400 text-sm">User Rating</div>
                    </div>
                    
                    <div class="group p-4 rounded-2xl bg-white/5 backdrop-blur-sm hover:bg-white/10 transition-all duration-300 cursor-default">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <i class="fas fa-video text-green-400 text-xl group-hover:scale-110 transition-transform"></i>
                            <div class="text-2xl font-bold text-white">{{ number_format($totalEpisodesGenerated ?? 0) }}+</div>
                        </div>
                        <div class="text-gray-400 text-sm">Episodes Generated</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modern Scroll Hint -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 cursor-pointer z-20" onclick="scrollToCreator()">
            <div class="flex flex-col items-center gap-2">
                <span class="text-white/50 text-xs uppercase tracking-wider">Scroll</span>
                <div class="w-6 h-10 border-2 border-white/30 rounded-full flex justify-center bg-white/5 backdrop-blur-sm">
                    <div class="w-1.5 h-2 bg-white/60 rounded-full mt-2 animate-scroll"></div>
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
        
        /* Smooth transitions */
        .group-hover\:rotate-12 {
            transition: transform 0.3s ease;
        }
        
        .group-hover\:translate-x-1 {
            transition: transform 0.3s ease;
        }
    </style>

    <!-- Main Dashboard Content -->
    <div class="bg-slate-900 -mt-6 rounded-t-3xl relative z-20">
        <div class="container mx-auto px-4 py-12">
            
            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                <div class="bg-gradient-to-br from-purple-500/10 to-pink-500/10 rounded-2xl p-5 border border-purple-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Total Series</p>
                            <p class="text-3xl font-bold text-white">{{ $mySeriesCount ?? 0 }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-purple-500/20 flex items-center justify-center">
                            <i class="fas fa-tv text-purple-400 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-blue-500/10 to-cyan-500/10 rounded-2xl p-5 border border-blue-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Total Episodes</p>
                            <p class="text-3xl font-bold text-white">{{ $myEpisodesCount ?? 0 }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center">
                            <i class="fas fa-list-ol text-blue-400 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-yellow-500/10 to-orange-500/10 rounded-2xl p-5 border border-yellow-500/20">
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
                
                <div class="bg-gradient-to-br from-green-500/10 to-teal-500/10 rounded-2xl p-5 border border-green-500/20">
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
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl border border-slate-700 overflow-hidden shadow-xl">
                    <div class="p-6 md:p-8">
                        <div class="flex flex-col md:flex-row gap-6 items-center">
                            <!-- Icon -->
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-robot text-white text-3xl"></i>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 text-center md:text-left">
                                <h3 class="text-2xl font-bold text-white mb-1">AI Series Generator</h3>
                                <p class="text-gray-400 text-sm mb-3">Create a new web series with AI in minutes</p>
                                <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                                    <span class="text-xs bg-purple-500/20 text-purple-300 px-2 py-1 rounded-full">Up to 10 episodes</span>
                                    <span class="text-xs bg-purple-500/20 text-purple-300 px-2 py-1 rounded-full">HD Quality</span>
                                    <span class="text-xs bg-purple-500/20 text-purple-300 px-2 py-1 rounded-full">Instant Generation</span>
                                </div>
                            </div>
                            
                            <!-- Button -->
                            <a href="{{ route('web-series.create') }}" 
                               class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold transition-colors flex items-center gap-2">
                                <i class="fas fa-plus-circle"></i>
                                New Series
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- My Series Section -->
            @if(isset($webSeries) && count($webSeries) > 0)
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
                        <div class="relative rounded-xl overflow-hidden bg-slate-800">
                            @if($series->cover_image)
                                <img src="{{ Storage::url($series->cover_image) }}" 
                                     alt="{{ $series->name }}"
                                     class="w-full aspect-video object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full aspect-video bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                                    <i class="fas fa-film text-white text-4xl opacity-50"></i>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="absolute top-2 left-2">
                                <span class="bg-purple-600 text-white text-xs px-2 py-0.5 rounded-full">
                                    {{ $series->status === 'completed' ? 'Completed' : ucfirst($series->status) }}
                                </span>
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 p-3 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                <div class="flex gap-2">
                                    <button class="bg-white/20 backdrop-blur-sm rounded-full p-1.5 hover:bg-white/30">
                                        <i class="fas fa-play text-white text-xs"></i>
                                    </button>
                                    <button class="bg-white/20 backdrop-blur-sm rounded-full p-1.5 hover:bg-white/30">
                                        <i class="fas fa-info text-white text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h4 class="text-white font-medium text-sm truncate">{{ $series->name }}</h4>
                            <p class="text-gray-400 text-xs">{{ $series->scenes_count ?? $series->scenes->count() }} scenes</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <!-- Empty State -->
            <div class="text-center py-16 mb-12">
                <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-purple-500/20 flex items-center justify-center">
                    <i class="fas fa-film text-4xl text-purple-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">No Series Yet</h3>
                <p class="text-gray-400 mb-4">Start your creative journey by creating your first AI web series</p>
                <a href="{{ route('web-series.create') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 rounded-xl text-white font-semibold transition-colors">
                    <i class="fas fa-plus-circle"></i>
                    Create Your First Series
                </a>
            </div>
            @endif
            
            <!-- Featured Categories - Grid Layout -->
            <div class="mb-12">
                <div class="mb-5">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                        <i class="fas fa-fire text-orange-400"></i>
                        Trending Categories
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Popular genres right now</p>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="relative rounded-xl overflow-hidden cursor-pointer group" onclick="window.location.href='{{ route('web-series.create') }}?genre=Action'">
                        <img src="https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=1925&auto=format&fit=crop" 
                             class="w-full h-32 object-cover group-hover:scale-110 transition-transform duration-300">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                        <div class="absolute bottom-3 left-3">
                            <h3 class="text-white font-bold text-lg">Action</h3>
                            <p class="text-gray-300 text-xs">24 series</p>
                        </div>
                    </div>
                    <div class="relative rounded-xl overflow-hidden cursor-pointer group" onclick="window.location.href='{{ route('web-series.create') }}?genre=Drama'">
                        <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=2070&auto=format&fit=crop" 
                             class="w-full h-32 object-cover group-hover:scale-110 transition-transform duration-300">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                        <div class="absolute bottom-3 left-3">
                            <h3 class="text-white font-bold text-lg">Drama</h3>
                            <p class="text-gray-300 text-xs">18 series</p>
                        </div>
                    </div>
                    <div class="relative rounded-xl overflow-hidden cursor-pointer group" onclick="window.location.href='{{ route('web-series.create') }}?genre=Comedy'">
                        <img src="https://images.unsplash.com/photo-1500462918059-b1a0cb512f1d?q=80&w=1974&auto=format&fit=crop" 
                             class="w-full h-32 object-cover group-hover:scale-110 transition-transform duration-300">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                        <div class="absolute bottom-3 left-3">
                            <h3 class="text-white font-bold text-lg">Comedy</h3>
                            <p class="text-gray-300 text-xs">31 series</p>
                        </div>
                    </div>
                    <div class="relative rounded-xl overflow-hidden cursor-pointer group" onclick="window.location.href='{{ route('web-series.create') }}?genre=Sci-Fi'">
                        <img src="https://images.unsplash.com/photo-1531259683007-016a7b628fc3?q=80&w=1887&auto=format&fit=crop" 
                             class="w-full h-32 object-cover group-hover:scale-110 transition-transform duration-300">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                        <div class="absolute bottom-3 left-3">
                            <h3 class="text-white font-bold text-lg">Sci-Fi</h3>
                            <p class="text-gray-300 text-xs">15 series</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Call to Action Banner -->
            <div class="mt-8 bg-gradient-to-r from-purple-600/20 to-pink-600/20 rounded-2xl border border-purple-500/30 p-6 md:p-8 text-center">
                <h3 class="text-2xl font-bold text-white mb-2">Ready to Create Your Own Series?</h3>
                <p class="text-gray-400 mb-4">Start your creative journey with our AI-powered platform today.</p>
                <a href="{{ route('web-series.create') }}" 
                   class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-semibold transition-all">
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
            background: #1e293b;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #7c3aed;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #8b5cf6;
        }
        
        html {
            scroll-behavior: smooth;
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
            // You can replace this with an actual video modal
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