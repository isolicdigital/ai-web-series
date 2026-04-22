{{-- resources/views/web-series/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black py-[120px] px-4">
    <div class="container mx-auto max-w-7xl">
        
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('web-series.my-series') }}" class="text-gray-400 hover:text-white transition-colors inline-flex items-center gap-2 group">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to My Series
            </a>
        </div>
        
        <!-- Series Title -->
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">{{ $series->project_name }}</h1>
            <div class="flex gap-3 mt-2">
                <span class="text-purple-400 text-xs px-2 py-0.5 rounded-full bg-purple-500/20">{{ $series->category ? $series->category->name : 'Uncategorized' }}</span>
                <span class="text-gray-500 text-xs">{{ $series->scenes->count() }} Segments</span>
            </div>
        </div>
        
        <!-- Stats -->
        @php
            $totalScenes = $series->scenes->count();
            $isDemoUser = auth()->id() == 141;
            $completedVideos = $series->scenes->whereNotNull('video_url')->count();
            $displayCompletedCount = $isDemoUser ? 0 : $completedVideos;
            $allVideosCompleted = $totalScenes > 0 && $displayCompletedCount == $totalScenes;
        @endphp
        
        <div class="mb-8 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full {{ $allVideosCompleted ? 'bg-blue-500' : 'bg-green-500' }} animate-pulse"></div>
                <span class="text-xs text-gray-400" id="completedCount">{{ $displayCompletedCount }}</span>
                <span class="text-xs text-gray-400">of {{ $totalScenes }} segments completed</span>
            </div>
            <div class="flex gap-1" id="progressDots">
                @for($i = 1; $i <= $totalScenes; $i++)
                    <div class="w-2 h-2 rounded-full progress-dot-{{ $i }} bg-gray-700"></div>
                @endfor
            </div>
        </div>
        
        <!-- Segments Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($series->scenes as $index => $scene)
            @php
                $isVideoCompleted = !$isDemoUser && !is_null($scene->video_url);
                $isImageCompleted = !is_null($scene->generated_image_url);
                $imageUrl = $scene->generated_image_url ? asset($scene->generated_image_url) : '';
                $videoUrl = $scene->video_url ? asset($scene->video_url) : '';
                $isFirstSegment = ($index == 0);
                
                $showLockOverlay = false;
                if($isDemoUser) {
                    $showLockOverlay = !$isFirstSegment;
                } else {
                    if($index > 0) {
                        $previousScene = $series->scenes[$index - 1];
                        if(!$previousScene->video_url) {
                            $showLockOverlay = true;
                        }
                    }
                }
            @endphp
            
            <div class="relative group segment-card" id="scene-{{ $scene->id }}" 
                 data-scene-id="{{ $scene->id }}" 
                 data-scene-number="{{ $scene->scene_number }}" 
                 data-scene-index="{{ $index }}"
                 data-image-url="{{ $imageUrl }}"
                 data-video-url="{{ $videoUrl }}"
                 data-prompt="{{ $scene->image_prompt }}"
                 data-is-demo="{{ $isDemoUser ? 'true' : 'false' }}">
                
                @if($showLockOverlay)
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm rounded-xl z-20 flex flex-col items-center justify-center border border-gray-700 locked-overlay">
                    <div class="text-center p-4">
                        <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-800 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <p class="text-gray-400 text-sm font-medium">Segment Locked</p>
                        <p class="text-gray-600 text-xs mt-1">Complete Segment {{ $scene->scene_number - 1 }} first</p>
                    </div>
                </div>
                @endif
                
                <div class="bg-gradient-to-br from-gray-900/80 to-gray-800/40 rounded-xl border border-gray-700/50 overflow-hidden transition-all duration-500">
                    
                    <div class="px-4 py-3 border-b border-gray-700/50 bg-gradient-to-r from-gray-800/50 to-gray-900/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold segment-icon bg-gray-700 text-gray-500">
                                    {{ $scene->scene_number }}
                                </div>
                                <div>
                                    <h3 class="text-white font-semibold text-sm">{{ $scene->title }}</h3>
                                    <p class="text-gray-500 text-[10px]">Segment {{ $scene->scene_number }}</p>
                                </div>
                            </div>
                            <div>
                                <span class="text-[10px] px-2 py-1 rounded-full bg-gray-700 text-gray-500 flex items-center gap-1 segment-status">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Locked
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-3 segment-content">
                        @if(!$isDemoUser && $isVideoCompleted)
                            <!-- Video Player -->
                            <div class="relative group/video bg-black rounded-lg overflow-hidden cursor-pointer" style="aspect-ratio: 4 / 3;">
                                <div class="absolute inset-0 w-full h-full overflow-hidden">
                                    <img src="{{ $imageUrl }}" class="w-full h-full object-cover blur-2xl scale-110 opacity-60">
                                </div>
                                <div class="absolute inset-0 bg-black/30"></div>
                                <div class="absolute inset-0 flex flex-col items-center justify-center transition-all duration-300 z-10" id="playOverlay-{{ $scene->id }}">
                                    <div class="relative">
                                        <div class="absolute inset-0 rounded-full bg-purple-500/20 animate-ping" style="animation-duration: 1.5s;"></div>
                                        <svg class="w-16 h-16 text-purple-400 cursor-pointer hover:text-purple-300 transition-all duration-300 hover:scale-110 relative z-10" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </div>
                                    <p class="text-white text-sm font-semibold drop-shadow-lg mt-5">Ready to View</p>
                                </div>
                                <video controls class="w-full h-full object-cover hidden relative z-20" id="video-{{ $scene->id }}">
                                    <source src="{{ asset($scene->video_url) }}" type="video/mp4">
                                </video>
                                <div class="absolute bottom-2 right-2 flex gap-1 opacity-0 group-hover/video:opacity-100 transition z-30">
                                    <button onclick="downloadClick('{{ $scene->video_url }}', {{ $scene->id }})" class="bg-black/60 hover:bg-green-600 p-1.5 rounded-lg backdrop-blur-sm transition">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <script>
                                (function() {
                                    const overlay = document.getElementById('playOverlay-{{ $scene->id }}');
                                    const video = document.getElementById('video-{{ $scene->id }}');
                                    if (overlay && video) {
                                        overlay.addEventListener('click', function() {
                                            overlay.style.display = 'none';
                                            video.classList.remove('hidden');
                                            video.play();
                                        });
                                    }
                                })();
                            </script>
                            
                        @elseif($isDemoUser && !$isVideoCompleted)
                            <!-- Demo User - Generate Button -->
                            <div class="relative rounded-lg overflow-hidden border border-purple-500/30 demo-content" style="aspect-ratio: 4 / 3; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);" data-demo-scene-id="{{ $scene->id }}">
                                @if($imageUrl && $imageUrl != '')
                                <div class="absolute inset-0 w-full h-full overflow-hidden">
                                    <img src="{{ $imageUrl }}" class="w-full h-full object-cover blur-2xl scale-110 opacity-50">
                                </div>
                                @endif
                                <div class="absolute inset-0 bg-black/40"></div>
                                <div class="absolute inset-0 flex flex-col items-center justify-center z-10">
                                    <div class="flex flex-col items-center justify-center mb-4">
                                        <svg class="w-10 h-10 text-purple-400 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                        </svg>
                                    </div>
                                    <p class="text-white text-sm font-semibold mb-2">Ready to Generate</p>
                                    <p class="text-gray-300 text-xs mb-4">Click to create your clip</p>
                                    <button onclick="startVideoGeneration({{ $scene->id }})" 
                                            class="px-6 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-lg text-white text-sm font-medium transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-pink-500/25 transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        Generate Clip
                                    </button>
                                </div>
                            </div>
                            
                        @elseif(!$isDemoUser && $isImageCompleted && !$isVideoCompleted)
                            <!-- Normal User - Generate Button -->
                            <div class="relative rounded-lg overflow-hidden border border-purple-500/30" style="aspect-ratio: 4 / 3; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);">
                                @if($imageUrl && $imageUrl != '')
                                <div class="absolute inset-0 w-full h-full overflow-hidden">
                                    <img src="{{ $imageUrl }}" class="w-full h-full object-cover blur-2xl scale-110 opacity-50">
                                </div>
                                @endif
                                <div class="absolute inset-0 bg-black/40"></div>
                                <div class="absolute inset-0 flex flex-col items-center justify-center z-10">
                                    <div class="flex flex-col items-center justify-center mb-4">
                                        <svg class="w-10 h-10 text-purple-400 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                        </svg>
                                    </div>
                                    <p class="text-white text-sm font-semibold mb-2">Ready to Generate</p>
                                    <p class="text-gray-300 text-xs mb-4">Click to create your clip</p>
                                    <button onclick="startVideoGeneration({{ $scene->id }})" 
                                            class="px-6 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-lg text-white text-sm font-medium transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-pink-500/25 transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        Generate Clip
                                    </button>
                                </div>
                            </div>
                            
                        @elseif(!$isDemoUser && !$isImageCompleted && $isFirstSegment)
                            <div class="relative bg-gradient-to-br from-gray-800/80 to-gray-900/80 rounded-lg overflow-hidden border border-purple-500/30" style="aspect-ratio: 4 / 3;">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-900/30 to-pink-900/30 animate-pulse"></div>
                                <div class="absolute inset-0 flex flex-col items-center justify-center z-10">
                                    <div class="relative">
                                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 animate-pulse shadow-xl shadow-purple-500/30 flex items-center justify-center">
                                            <svg class="w-10 h-10 text-white animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="absolute inset-0 w-20 h-20 rounded-full border-2 border-purple-400/30 animate-ping"></div>
                                    </div>
                                    <p class="text-purple-400 text-sm font-semibold mt-6">Generating Image...</p>
                                    <p class="text-gray-400 text-xs mt-1">AI is creating your scene</p>
                                </div>
                            </div>
                            <button onclick="generateImage({{ $scene->id }})" id="btn-{{ $scene->id }}" style="display: none;"></button>
                            
                        @elseif($showLockOverlay)
                            <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-lg border border-gray-700 flex flex-col items-center justify-center" style="aspect-ratio: 4 / 3;">
                                <div class="text-center p-4">
                                    <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-gray-800 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 text-xs">Locked</p>
                                </div>
                            </div>
                        @else
                            <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-lg border border-yellow-500/30 flex flex-col items-center justify-center" style="aspect-ratio: 4 / 3;">
                                <div class="text-center p-4">
                                    <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-yellow-500/20 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-yellow-400 text-xs font-medium">Pending</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
            
            <!-- Sixth Card: Episode Preview -->
            <!-- Sixth Card: Episode Preview - With Font Awesome Icons -->
<div class="relative group">
    <div class="bg-gradient-to-br from-gray-900/80 to-gray-800/40 rounded-xl border border-gray-700/50 overflow-hidden transition-all duration-500 hover:border-blue-500/50 hover:shadow-xl hover:shadow-blue-500/10 transform hover:-translate-y-1">
        
        <div class="px-4 py-3 border-b border-gray-700/50 bg-gradient-to-r from-gray-800/50 to-gray-900/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold episode-preview-icon bg-gray-700 text-gray-500 transition-all duration-300">
                        <i class="fas fa-film text-white text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold text-sm group-hover:text-purple-400 transition-colors">Episode Preview</h3>
                        <p class="text-gray-500 text-[10px]">Full Episode</p>
                    </div>
                </div>
                <div>
                    <span class="text-[10px] px-2 py-1 rounded-full bg-gray-700 text-gray-500 flex items-center gap-1 episode-preview-status transition-all duration-300">
                        <i class="fas fa-lock text-xs"></i>
                        <span class="episode-preview-status-text">Locked</span>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="p-3">
            <div class="relative bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-lg border border-gray-700 overflow-hidden episode-preview-content transition-all duration-300 group-hover:border-blue-500/30" style="aspect-ratio: 4 / 3;">
                
                <!-- Background blur effect -->
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-purple-500/10"></div>
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-500/20 to-purple-500/20 blur-xl"></div>
                </div>
                
                <div class="relative z-10 flex flex-col items-center justify-center h-full text-center p-4">
                    <!-- Icon container with animation -->
                    <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-800 flex items-center justify-center transition-all duration-500 episode-icon-container group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-purple-500/20">
                        <i class="fas fa-lock text-3xl text-gray-600 episode-icon"></i>
                    </div>
                    
                    <!-- Status text -->
                    <p class="text-gray-400 text-sm font-medium episode-preview-locked-text transition-all duration-300 group-hover:text-gray-300">Episode Preview Locked</p>
                    
                    <!-- Progress message -->
                    <p class="text-gray-500 text-xs mt-2 transition-all duration-300" id="episodeProgressMessage">
                        Complete all {{ $totalScenes }} segments to unlock
                    </p>
                    
                    <!-- Progress bar -->
                    <div class="mt-3 w-full max-w-[180px] mx-auto">
                        <div class="w-full bg-gray-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-500 to-cyan-500 h-1.5 rounded-full transition-all duration-500 transform origin-left" style="width: 0%" id="episodeProgress"></div>
                        </div>
                    </div>
                    
                    <!-- Counter -->
                    <p class="text-gray-600 text-[10px] mt-2 font-mono">
                        <i class="fas fa-check-circle text-[8px] mr-1"></i>
                        <span id="episodeCompletedCount">0</span>/<span id="episodeTotalCount">{{ $totalScenes }}</span> segments completed
                    </p>
                    
                    <!-- Animated pulse ring when almost complete -->
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none" id="pulseRing" style="display: none;">
                        <div class="w-32 h-32 rounded-full border-2 border-green-500/30 animate-ping"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</div>

<!-- Professional Episode Preview Modal with Segment Thumbnails -->
<div id="episodeModal" class="fixed inset-0 bg-black/95 backdrop-blur-xl z-50 hidden items-center justify-center p-2 sm:p-4" style="display: none;">
    <div class="relative w-full max-w-6xl h-full max-h-[95vh] sm:max-h-[90vh] bg-gradient-to-br from-gray-900 to-black rounded-xl sm:rounded-2xl border border-gray-700 overflow-hidden shadow-2xl shadow-purple-500/20 flex flex-col">
        
        <!-- Modal Header -->
        <div class="flex-shrink-0 flex justify-between items-center px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-700 bg-gradient-to-r from-gray-800/50 to-gray-900/50">
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center shadow-lg">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">Episode Preview</h2>
                    <p class="text-gray-400 text-xs sm:text-sm">Create your final episode with narration & music</p>
                </div>
            </div>
            <div class="flex items-center gap-1 sm:gap-2">
                <button onclick="toggleFullscreen()" class="p-1.5 sm:p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-300">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                    </svg>
                </button>
                <button onclick="closeEpisodeModal()" class="p-1.5 sm:p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-300">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-6 space-y-4 sm:space-y-6">
            
            <!-- Main Video Player -->
            <div class="relative bg-black rounded-xl overflow-hidden shadow-2xl mx-auto w-full" style="max-width: 800px; aspect-ratio: 16 / 9;">
                <video id="episodePlayer" class="w-full h-full object-contain">
                    <source src="" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                
                <div class="absolute bottom-2 sm:bottom-4 left-2 sm:left-4 right-2 sm:right-4 flex justify-between items-center opacity-0 hover:opacity-100 transition-opacity duration-300">
                    <div class="flex items-center gap-1 sm:gap-2">
                        <button onclick="playPrevious()" class="bg-black/60 hover:bg-purple-600 p-1.5 sm:p-2 rounded-full backdrop-blur-sm transition-all duration-300">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button onclick="playNext()" class="bg-black/60 hover:bg-purple-600 p-1.5 sm:p-2 rounded-full backdrop-blur-sm transition-all duration-300">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="text-white text-xs sm:text-sm bg-black/60 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full backdrop-blur-sm font-mono" id="videoTimeInfo">00:00 / 00:00</div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="max-w-2xl mx-auto w-full px-2">
                <div class="flex justify-between text-xs text-gray-400 mb-1">
                    <span>Episode Progress</span>
                    <span id="episodeProgressPercent">0%</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-1.5 sm:h-2 overflow-hidden cursor-pointer">
                    <div id="episodeProgressBar" class="bg-gradient-to-r from-blue-500 to-purple-500 h-1.5 sm:h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
            
            <!-- Segment Thumbnails Row - Small cards in one line -->
            <div>
                <div class="flex items-center justify-between mb-2 sm:mb-3 px-2">
                    <h3 class="text-white font-semibold flex items-center gap-2 text-sm sm:text-base">
                        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        Segments
                    </h3>
                    <span class="text-xs text-gray-500" id="playlistCount">0 segments</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-5 gap-2 sm:gap-3 max-h-48 overflow-y-auto custom-scrollbar p-2" id="thumbnailContainer">
                    <!-- Thumbnails will be populated here -->
                </div>
            </div>
            
            <!-- Toggle Buttons Row - Narration & Music -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- Left: Background Narration Toggle -->
                <div class="bg-gradient-to-br from-purple-900/20 to-purple-800/10 rounded-xl border border-purple-500/30 p-4 hover:border-purple-500/50 transition-all duration-300">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m-4 0h8m-4-8V4m-2 4l-2-2 2-2m2 4l2-2-2-2"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-white font-semibold text-sm">Background Narration</h3>
                <p class="text-gray-400 text-xs">Add voiceover to your episode</p>
            </div>
        </div>
        <!-- Toggle Switch -->
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" id="narrationToggle" class="sr-only peer" onchange="toggleNarration()">
            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
            <span class="ms-3 text-xs font-medium text-gray-400 peer-checked:text-purple-400" id="narrationToggleLabel">OFF</span>
        </label>
    </div>
    
    <!-- Narration Content (Hidden by default) -->
    <div id="narrationContent" class="hidden mt-3">
        <!-- Voice Settings Panel -->
        <div id="voiceSettings" class="mb-4 p-3 bg-gradient-to-r from-gray-800/50 to-gray-900/50 rounded-xl border border-gray-700">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m-4 0h8m-4-8V4m-2 4l-2-2 2-2m2 4l2-2-2-2"></path>
                </svg>
                <span class="text-white text-sm font-semibold">Voice Settings</span>
            </div>
            
            <!-- Language Dropdown -->
            <div class="mb-3">
    <label class="block text-gray-400 text-xs mb-1.5 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
        </svg>
        Select Language
    </label>
    <div class="relative">
        <select id="languageSelect" onchange="filterVoicesByGender()" 
                class="w-full px-4 py-2.5 bg-gray-800/80 border border-gray-600 rounded-xl text-white text-sm appearance-none cursor-pointer focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/30 focus:bg-gray-800 transition-all duration-300 hover:border-purple-400/50">
            <option value="English" class="bg-gray-900">🌍 English</option>
            <option value="Chinese" class="bg-gray-900">🇨🇳 Chinese</option>
            <option value="Japanese" class="bg-gray-900">🇯🇵 Japanese</option>
            <option value="Korean" class="bg-gray-900">🇰🇷 Korean</option>
            <option value="French" class="bg-gray-900">🇫🇷 French</option>
            <option value="German" class="bg-gray-900">🇩🇪 German</option>
            <option value="Italian" class="bg-gray-900">🇮🇹 Italian</option>
            <option value="Spanish" class="bg-gray-900">🇪🇸 Spanish</option>
            <option value="Portuguese" class="bg-gray-900">🇵🇹 Portuguese</option>
            <option value="Russian" class="bg-gray-900">🇷🇺 Russian</option>
        </select>
        <!-- Custom dropdown arrow -->
        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
            <svg class="w-4 h-4 text-purple-400 transition-transform duration-300 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>
</div>
            
            <!-- Gender Switch (Male/Female) -->
            <div class="mb-3">
                <div class="flex items-center justify-between">
                    <label class="text-gray-400 text-xs flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Voice Gender
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400" id="genderFemaleLabel">Female</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="genderSwitch" class="sr-only peer bg-pink-700" onchange="filterVoicesByGender()">
                            <div class="w-10 h-5 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-pink-600"></div>
                        </label>
                        <span class="text-xs text-gray-400" id="genderMaleLabel">Male</span>
                    </div>
                </div>
            </div>
            
            <!-- Voice Dropdown (Filtered by gender) -->
            <div>
                <label class="block text-gray-400 text-xs mb-1 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m-4 0h8m-4-8V4m-2 4l-2-2 2-2m2 4l2-2-2-2"></path>
                    </svg>
                    Voice
                </label>
                <select id="voiceSelect" 
                class="voice-select w-full px-4 py-2.5 bg-gray-800/80 border border-gray-600 rounded-xl text-white text-sm appearance-none cursor-pointer transition-all duration-300 focus:outline-none">
            <option value="" class="bg-gray-900 text-gray-400">🎤 Choose a voice</option>
            <option value="Aiden" class="bg-gray-900">Aiden</option>
            <option value="Dylan" class="bg-gray-900">Dylan</option>
            <option value="Eric" class="bg-gray-900">Eric</option>
            <option value="Ono_anna" class="bg-gray-900">Ono anna</option>
            <option value="Ryan" class="bg-gray-900">Ryan</option>
            <option value="Serena" class="bg-gray-900">Serena</option>
            <option value="Sohee" class="bg-gray-900">Sohee</option>
            <option value="Uncle_fu" class="bg-gray-900">Uncle fu</option>
            <option value="Vivian" class="bg-gray-900">Vivian</option>
        </select>
            </div>
        </div>
        
        <!-- Generate Button -->
        <!-- <button id="generateNarrationBtn" onclick="generateBackgroundNarration()" 
                class="w-full py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-lg text-white text-sm font-medium transition-all duration-300 flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m-4 0h8m-4-8V4m-2 4l-2-2 2-2m2 4l2-2-2-2"></path>
            </svg>
            Generate Narration
        </button> -->
        
        <!-- Narration Progress Bar -->
        <div id="narrationProgressContainer" class="hidden mt-3">
            <div class="flex justify-between text-xs text-gray-400 mb-1">
                <span>Generating...</span>
                <span id="narrationPercent">0%</span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-1.5 overflow-hidden">
                <div id="narrationProgressFill" class="bg-gradient-to-r from-purple-500 to-pink-500 h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p id="narrationStatus" class="text-gray-500 text-xs mt-1">🎤 Preparing narration...</p>
        </div>
        
        <!-- Narration Success -->
        <div id="narrationSuccess" class="hidden mt-3 p-2 bg-green-500/20 rounded-lg border border-green-500/30">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-green-400 text-xs">Narration ready! ✓</span>
            </div>
        </div>
        
        <!-- Audio Preview (Demo Only) -->
        <div id="audioPreviewContainer" class="hidden mt-4 p-3 bg-gradient-to-r from-purple-900/30 to-pink-900/30 rounded-xl border border-purple-500/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m-4 0h8m-4-8V4m-2 4l-2-2 2-2m2 4l2-2-2-2"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-white text-sm font-semibold">Background Narration</span>
                        <span class="text-green-400 text-xs flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Ready
                        </span>
                    </div>
                    <audio id="narrationAudioPlayer" controls class="w-full h-8 rounded-lg" style="height: 32px;">
                        <source id="narrationAudioSource" src="" type="audio/mpeg">
                    </audio>
                </div>
                <button onclick="downloadNarration()" class="p-2 rounded-lg bg-purple-500/20 hover:bg-purple-500/30 transition-all duration-300">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
                
                <!-- Right: Background Music Toggle -->
                <div class="bg-gradient-to-br from-pink-900/20 to-pink-800/10 rounded-xl border border-pink-500/30 p-4 hover:border-pink-500/50 transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-pink-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold text-sm">Background Music</h3>
                                <p class="text-gray-400 text-xs">Enhance your episode with music</p>
                            </div>
                        </div>
                        <!-- Toggle Switch -->
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="musicToggle" class="sr-only peer" onchange="toggleMusic()">
                            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                            <span class="ms-3 text-xs font-medium text-gray-400 peer-checked:text-pink-400" id="musicToggleLabel">OFF</span>
                        </label>
                    </div>
                    
                    <!-- Music Content (Hidden by default) -->
                    <div id="musicContent" class="hidden mt-3">
                        <label class="w-full cursor-pointer">
                            <div class="w-full py-2.5 bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 rounded-lg text-white text-sm font-medium transition-all duration-300 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Choose Music File
                            </div>
                            <input type="file" id="musicFileInput" accept="audio/*" class="hidden" onchange="uploadMusicFile(this.files[0])">
                        </label>
                        
                        <!-- Music Upload Progress -->
                        <div id="musicProgressContainer" class="hidden mt-3">
                            <div class="flex justify-between text-xs text-gray-400 mb-1">
                                <span>Uploading...</span>
                                <span id="musicPercent">0%</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                <div id="musicProgressFill" class="bg-gradient-to-r from-pink-500 to-rose-500 h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>
                        
                        <!-- Uploaded Music Preview -->
                        <div id="musicPreview" class="hidden mt-3 p-2 bg-pink-500/20 rounded-lg border border-pink-500/30">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"></path>
                                    </svg>
                                    <span class="text-pink-400 text-xs truncate max-w-[150px] sm:max-w-[200px]" id="musicFileName">music.mp3</span>
                                </div>
                                <button onclick="removeMusicFile()" class="text-red-400 hover:text-red-300 transition-colors p-1 rounded-lg hover:bg-red-500/10">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="mt-2 flex items-center gap-2 text-xs text-gray-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Music uploaded successfully</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Create Episode Button -->
            <div class="p-4 bg-gradient-to-r from-purple-900/30 to-pink-900/30 rounded-xl border border-purple-500/30">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h3 class="text-white font-semibold text-sm">Create Final Episode</h3>
                        <p class="text-gray-400 text-xs">Merge all segments with selected enhancements</p>
                    </div>
                    <button id="mergeEpisodeBtn" onclick="createFullEpisode()" 
                            class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-lg text-white text-sm font-medium transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-pink-500/25 transform hover:scale-105">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Create Episode
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="flex-shrink-0 px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-700 bg-gradient-to-r from-gray-800/50 to-gray-900/50 flex justify-between items-center">
            <div class="flex items-center gap-2 sm:gap-4">
                <div class="flex items-center gap-2 bg-gray-800/50 px-2 sm:px-3 py-1 rounded-full">
                    <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-green-500 animate-pulse"></div>
                    <span class="text-xs sm:text-sm text-gray-400" id="currentSegmentInfo">Segment 1 of 0</span>
                </div>
            </div>
            <button onclick="downloadEpisode()" class="text-xs text-gray-400 hover:text-white transition-colors flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download
            </button>
        </div>
    </div>
</div>

<!-- Full Page Loader -->
<div id="fullPageLoader" class="fixed inset-0 bg-black/95 backdrop-blur-xl z-[200] hidden items-center justify-center flex-col" style="display: none;">
    <div class="text-center">
        <div class="relative w-24 h-24 mb-6 mx-auto">
            <div class="absolute inset-0 border-4 border-purple-500/20 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-t-purple-500 border-r-pink-500 rounded-full animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <svg class="w-10 h-10 text-purple-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
        <p class="text-white text-xl font-semibold mb-2">Creating Your Episode</p>
        <p class="text-gray-400 text-sm" id="loaderMessage">Merging videos and adding music...</p>
        <div class="mt-6 w-64 h-1.5 bg-gray-700 rounded-full overflow-hidden mx-auto">
            <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full transition-all duration-500" style="width: 0%" id="loaderProgress"></div>
        </div>
        <p class="text-gray-500 text-xs mt-3" id="loaderPercent">0%</p>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-50 hidden"></div>

<script>
const intervals = {};
let isProcessingVideo = false;
const isDemoUser = {{ auth()->check() && auth()->user()->demo_mode ? 'true' : 'false' }};
let demoSegmentsProgress = {};
let episodeVideoUrls = [];
let currentMusic = null;
let autoPlayEnabled = true;
let loopEnabled = false;
let currentPlaylistIndex = 0;
let uploadedAudioUrl = null;
let currentMusicSource = null;
let uploadedAudioFileUrl = null;
let mergedVideoUrl = null;
let isNarrationGenerated = false;
let isGeneratingNarration = false;
let uploadedMusicUrl = null;

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const colors = { success: 'bg-green-600', error: 'bg-red-600', info: 'bg-blue-600', warning: 'bg-yellow-600' };
    toast.className = `fixed bottom-8 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded-lg text-white text-sm font-medium z-50 transition-all duration-300 ${colors[type]} opacity-0 translate-y-4 shadow-lg`;
    toast.textContent = message;
    toast.style.display = 'block';
    setTimeout(() => toast.classList.remove('opacity-0', 'translate-y-4'), 10);
    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-4');
        setTimeout(() => toast.style.display = 'none', 300);
    }, 3000);
}

// ==================== TOGGLE FUNCTIONS ====================

function toggleNarration() {
    const toggle = document.getElementById('narrationToggle');
    const label = document.getElementById('narrationToggleLabel');
    const content = document.getElementById('narrationContent');
    
    if (toggle.checked) {
        label.textContent = 'ON';
        content.classList.remove('hidden');
        content.classList.add('animate-fadeIn');
    } else {
        label.textContent = 'OFF';
        content.classList.add('hidden');
        isNarrationGenerated = false;
        const successMsg = document.getElementById('narrationSuccess');
        if (successMsg) successMsg.classList.add('hidden');
    }
}

function toggleMusic() {
    const toggle = document.getElementById('musicToggle');
    const label = document.getElementById('musicToggleLabel');
    const content = document.getElementById('musicContent');
    
    if (toggle.checked) {
        label.textContent = 'ON';
        content.classList.remove('hidden');
        content.classList.add('animate-fadeIn');
    } else {
        label.textContent = 'OFF';
        content.classList.add('hidden');
        if (uploadedAudioUrl) {
            removeUploadedAudio();
        }
    }
}

function uploadMusicFile(file) {
    if (!file) return;
    
    if (file.size > 10 * 1024 * 1024) {
        showToast('File size must be less than 10MB', 'error');
        return;
    }
    
    const validTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp3'];
    if (!validTypes.includes(file.type)) {
        showToast('Please upload MP3, WAV, or OGG files only', 'error');
        return;
    }
    
    const progressContainer = document.getElementById('musicProgressContainer');
    const progressFill = document.getElementById('musicProgressFill');
    const progressPercent = document.getElementById('musicPercent');
    const preview = document.getElementById('musicPreview');
    const fileName = document.getElementById('musicFileName');
    
    progressContainer.classList.remove('hidden');
    
    let progress = 0;
    const interval = setInterval(() => {
        progress += 10;
        progressFill.style.width = `${progress}%`;
        progressPercent.textContent = `${progress}%`;
        if (progress >= 100) {
            clearInterval(interval);
            
            const audioUrl = URL.createObjectURL(file);
            uploadedMusicUrl = audioUrl;
            uploadedAudioUrl = audioUrl;
            uploadedAudioFileUrl = audioUrl;
            
            fileName.textContent = file.name.length > 30 ? file.name.substring(0, 27) + '...' : file.name;
            
            progressContainer.classList.add('hidden');
            preview.classList.remove('hidden');
            
            showToast('✅ Music uploaded successfully!', 'success');
        }
    }, 200);
}

function removeMusicFile() {
    if (uploadedMusicUrl) {
        URL.revokeObjectURL(uploadedMusicUrl);
        uploadedMusicUrl = null;
        uploadedAudioUrl = null;
        uploadedAudioFileUrl = null;
    }
    
    document.getElementById('musicPreview').classList.add('hidden');
    document.getElementById('musicFileInput').value = '';
    showToast('Music removed', 'info');
}

function removeUploadedAudio() {
    if (uploadedAudioUrl) {
        URL.revokeObjectURL(uploadedAudioUrl);
        uploadedAudioUrl = null;
        uploadedAudioFileUrl = null;
    }
    currentMusicSource = null;
    showToast('Audio removed', 'info');
}

// ==================== NARRATION GENERATION ====================

// Update generateRealNarration
async function generateRealNarration(voiceId, language, gender) {
    const seriesId = {{ $series->id }};
    const generateBtn = document.getElementById('generateNarrationBtn');
    const progressContainer = document.getElementById('narrationProgressContainer');
    const successMsg = document.getElementById('narrationSuccess');
    
    try {
        const response = await fetch('/api/generate-narration', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                series_id: seriesId,
                voice_id: voiceId,
                language: language,
                gender: gender
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            successMsg.classList.remove('hidden');
            progressContainer.classList.add('hidden');
            generateBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Narration Ready';
            generateBtn.classList.add('bg-green-600', 'hover:bg-green-700');
            isNarrationGenerated = true;
            isGeneratingNarration = false;
            showToast(`✅ ${gender === 'male' ? 'Male' : 'Female'} ${language} narration generated!`, 'success');
        } else {
            throw new Error(result.message || 'Failed to generate narration');
        }
    } catch (error) {
        console.error('Narration generation error:', error);
        showToast('❌ Failed to generate narration: ' + error.message, 'error');
        generateBtn.disabled = false;
        generateBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m-4 0h8m-4-8V4m-2 4l-2-2 2-2m2 4l2-2-2-2"></path></svg> Try Again';
        generateBtn.classList.remove('opacity-50');
        progressContainer.classList.add('hidden');
        isGeneratingNarration = false;
    }
}

// Download narration function
function downloadNarration() {
    const audioPlayer = document.getElementById('narrationAudioPlayer');
    if (audioPlayer && audioPlayer.src) {
        const a = document.createElement('a');
        a.href = audioPlayer.src;
        a.download = 'background_narration.mp3';
        a.click();
        showToast('📥 Narration downloading...', 'success');
    } else {
        showToast('No audio available to download', 'error');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initVoiceSettings();
});

function generateImage(sceneId) {
    console.log('Generating image for scene:', sceneId);
    setTimeout(() => {
        location.reload();
    }, 3000);
}

function startClipPolling(sceneId) {
    const interval = setInterval(async () => {
        try {
            const response = await fetch(`/api/scene/${sceneId}/status`, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.video_url && data.video_url !== '') {
                clearInterval(interval);
                location.reload();
            }
        } catch (error) {
            console.error('Polling error:', error);
        }
    }, 3000);
}

// ==================== EPISODE CREATION FUNCTIONS ====================

async function createFullEpisode() {
    const seriesId = {{ $series->id }};
    const isDemo = {{ auth()->id() == 141 ? 'true' : 'false' }};
    const musicToggle = document.getElementById('musicToggle');
    const addMusic = musicToggle ? musicToggle.checked : false;
    const narrationToggle = document.getElementById('narrationToggle');
    const addNarration = narrationToggle ? narrationToggle.checked : false;
    
    if (addMusic && !uploadedAudioUrl && !uploadedMusicUrl) {
        showToast('Please upload music first or turn off music toggle', 'warning');
        return;
    }
    
    const loader = document.getElementById('fullPageLoader');
    const loaderMessage = document.getElementById('loaderMessage');
    const loaderProgress = document.getElementById('loaderProgress');
    const loaderPercent = document.getElementById('loaderPercent');
    
    loader.style.display = 'flex';
    loaderProgress.style.width = '0%';
    loaderPercent.textContent = '0%';
    
    let progress = 0;
    const totalDuration = isDemo ? 5000 : 30000;
    const startTime = Date.now();
    
    const progressInterval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        progress = Math.min((elapsed / totalDuration) * 100, 100);
        loaderProgress.style.width = `${progress}%`;
        loaderPercent.textContent = `${Math.floor(progress)}%`;
        
        const messages = [
            "🎬 Merging video segments...",
            "🎤 Adding background narration...",
            "🎵 Syncing audio tracks...",
            "✨ Rendering final episode..."
        ];
        const messageIndex = Math.floor(progress / 25);
        if (messageIndex < messages.length) {
            loaderMessage.textContent = messages[messageIndex];
        }
        
        if (progress >= 100) {
            clearInterval(progressInterval);
            setTimeout(() => {
                loader.style.display = 'none';
                showToast('✅ Episode created successfully!', 'success');
                if (isDemo) {
                    window.location.href = `/web-series/${seriesId}/episodes`;
                } else {
                    window.location.href = '{{ route("web-series.my-series") }}';
                }
            }, 1000);
        }
    }, 100);
}

// ==================== EPISODE MODAL FUNCTIONS ====================

function collectEpisodeVideos() {
    const videoUrls = [];
    document.querySelectorAll('.segment-card').forEach(card => {
        const videoUrl = card.getAttribute('data-video-url');
        const sceneNumber = card.getAttribute('data-scene-number');
        const title = card.querySelector('.segment-icon')?.parentElement?.querySelector('h3')?.innerText || `Segment ${sceneNumber}`;
        const imageUrl = card.getAttribute('data-image-url');
        const isCompleted = isDemoUser ? demoSegmentsProgress[card.getAttribute('data-scene-id')] : (videoUrl && videoUrl !== '');
        
        if (isCompleted && videoUrl) {
            videoUrls.push({
                url: videoUrl,
                sceneNumber: sceneNumber,
                title: title,
                imageUrl: imageUrl
            });
        }
    });
    return videoUrls;
}

function loadThumbnails() {
    const thumbnailContainer = document.getElementById('thumbnailContainer');
    if (!thumbnailContainer) return;
    
    thumbnailContainer.innerHTML = '';
    
    document.querySelectorAll('.segment-card').forEach((card, index) => {
        const sceneNumber = card.getAttribute('data-scene-number');
        const title = card.querySelector('.segment-icon')?.parentElement?.querySelector('h3')?.innerText || `Segment ${sceneNumber}`;
        const imageUrl = card.getAttribute('data-image-url');
        const videoUrl = card.getAttribute('data-video-url');
        const isCompleted = isDemoUser ? demoSegmentsProgress[card.getAttribute('data-scene-id')] : (videoUrl && videoUrl !== '');
        
        const thumbnailCard = document.createElement('div');
        thumbnailCard.className = `flex-shrink-0 w-full sm:w-full rounded-lg overflow-hidden border transition-all duration-300 cursor-pointer ${isCompleted ? 'border-purple-500/50 hover:border-purple-500' : 'border-gray-700 opacity-50'}`;
        thumbnailCard.onclick = () => {
            if (isCompleted && episodeVideoUrls[index]) {
                playVideoAtIndex(index);
            }
        };
        
        thumbnailCard.innerHTML = `
            <div class="relative aspect-video bg-gray-800">
                ${imageUrl && imageUrl !== '' ? 
                    `<img src="${imageUrl}" class="w-full h-full object-cover blur-sm" alt="Segment ${sceneNumber}">` :
                    `<div class="w-full h-full flex items-center justify-center bg-gray-800">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>`
                }
                ${isCompleted ? 
                    '<div class="absolute inset-0 bg-black/40 flex items-center justify-center"><svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></div>' : 
                    '<div class="absolute inset-0 bg-black/60 flex items-center justify-center"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></div>'
                }
            </div>
            <div class="p-1 text-center bg-gray-800/50">
                <span class="text-xs text-gray-400">${sceneNumber}</span>
            </div>
        `;
        
        thumbnailContainer.appendChild(thumbnailCard);
    });
}

function toggleFullscreen() {
    const modal = document.getElementById('episodeModal');
    if (!document.fullscreenElement) {
        modal.requestFullscreen().catch(err => console.log(err));
    } else {
        document.exitFullscreen();
    }
}

function playPrevious() {
    if (currentPlaylistIndex > 0) {
        playVideoAtIndex(currentPlaylistIndex - 1);
    }
}

function playNext() {
    if (currentPlaylistIndex < episodeVideoUrls.length - 1) {
        playVideoAtIndex(currentPlaylistIndex + 1);
    } else if (loopEnabled) {
        playVideoAtIndex(0);
    }
}

function updateVideoTime() {
    const video = document.getElementById('episodePlayer');
    if (video && video.duration) {
        const currentMin = Math.floor(video.currentTime / 60);
        const currentSec = Math.floor(video.currentTime % 60);
        const totalMin = Math.floor(video.duration / 60);
        const totalSec = Math.floor(video.duration % 60);
        document.getElementById('videoTimeInfo').innerHTML = `${currentMin.toString().padStart(2, '0')}:${currentSec.toString().padStart(2, '0')} / ${totalMin.toString().padStart(2, '0')}:${totalSec.toString().padStart(2, '0')}`;
        
        const progress = (video.currentTime / video.duration) * 100;
        document.getElementById('episodeProgressBar').style.width = `${progress}%`;
        document.getElementById('episodeProgressPercent').innerHTML = `${Math.round(progress)}%`;
    }
}

function playVideoAtIndex(index) {
    if (index >= episodeVideoUrls.length) return;
    
    currentPlaylistIndex = index;
    const player = document.getElementById('episodePlayer');
    player.src = episodeVideoUrls[index].url;
    player.play();
    
    document.getElementById('currentSegmentInfo').innerHTML = `Segment ${index + 1} of ${episodeVideoUrls.length}`;
    document.getElementById('playlistCount').innerHTML = `${episodeVideoUrls.length} segments`;
    
    player.ontimeupdate = updateVideoTime;
    player.onloadedmetadata = updateVideoTime;
}

function openEpisodeModal() {
    episodeVideoUrls = collectEpisodeVideos();
    
    if (episodeVideoUrls.length === 0) {
        showToast('No videos available. Please generate clips first.', 'warning');
        return;
    }
    
    const totalSegments = document.querySelectorAll('.segment-card').length;
    const completedCount = isDemoUser ? Object.keys(demoSegmentsProgress).length : episodeVideoUrls.length;
    
    if (completedCount < totalSegments) {
        showToast(`Complete all ${totalSegments} segments first! (${completedCount}/${totalSegments} completed)`, 'warning');
        return;
    }
    
    loadThumbnails();
    playVideoAtIndex(0);
    
    const modal = document.getElementById('episodeModal');
    modal.style.display = 'flex';
    modal.classList.add('items-center', 'justify-center');
    
    const player = document.getElementById('episodePlayer');
    player.onended = () => {
        if (autoPlayEnabled && currentPlaylistIndex < episodeVideoUrls.length - 1) {
            playVideoAtIndex(currentPlaylistIndex + 1);
        } else if (loopEnabled) {
            playVideoAtIndex(0);
        }
    };
}

function closeEpisodeModal() {
    const modal = document.getElementById('episodeModal');
    const player = document.getElementById('episodePlayer');
    player.pause();
    modal.style.display = 'none';
}

function downloadEpisode() {
    if (episodeVideoUrls.length === 0) {
        showToast('No videos to download', 'warning');
        return;
    }
    showToast('🎬 Download started!', 'success');
    const a = document.createElement('a');
    a.href = episodeVideoUrls[0].url;
    a.download = 'episode_preview.mp4';
    a.click();
}

function updateEpisodePreviewCard() {
    const totalSegments = document.querySelectorAll('.segment-card').length;
    const completedCount = isDemoUser ? Object.keys(demoSegmentsProgress).length : document.querySelectorAll('[data-video-url][data-video-url!=""]').length;
    const allCompleted = completedCount === totalSegments && totalSegments > 0;
    const progressPercent = (completedCount / totalSegments) * 100;
    
    // Re-query all elements fresh each time
    const previewIcon = document.querySelector('.episode-preview-icon');
    const previewStatus = document.querySelector('.episode-preview-status');
    const previewStatusText = document.querySelector('.episode-preview-status-text');
    const previewContent = document.querySelector('.episode-preview-content');
    const previewLockedText = document.querySelector('.episode-preview-locked-text');
    const episodeProgress = document.getElementById('episodeProgress');
    const episodeCompletedCount = document.getElementById('episodeCompletedCount');
    const episodeProgressMessage = document.getElementById('episodeProgressMessage');
    const pulseRing = document.getElementById('pulseRing');
    const iconContainer = document.querySelector('.episode-icon-container');
    const episodeIcon = document.querySelector('.episode-icon');
    const episodeTotalCount = document.getElementById('episodeTotalCount');
    
    // Update total count if exists
    if (episodeTotalCount) episodeTotalCount.textContent = totalSegments;
    if (episodeCompletedCount) episodeCompletedCount.textContent = completedCount;
    if (episodeProgress) episodeProgress.style.width = progressPercent + '%';
    
    if (allCompleted) {
        // ========== ALL SEGMENTS COMPLETED - WATCH NOW STATE ==========
        
        // Update preview icon (the small icon in header)
        if (previewIcon) {
            previewIcon.className = 'w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-lg shadow-green-500/20';
            previewIcon.innerHTML = '<i class="fas fa-play text-white text-sm"></i>';
        }
        
        // Update status badge
        if (previewStatus) {
            previewStatus.className = 'text-[10px] px-2 py-1 rounded-full bg-green-500/20 text-green-400 flex items-center gap-1 animate-pulse';
        }
        if (previewStatusText) {
            previewStatusText.innerHTML = 'Watch Now';
            previewStatusText.className = '';
        }
        if (previewStatus) {
            const existingIcon = previewStatus.querySelector('i');
            if (existingIcon) {
                existingIcon.className = 'fas fa-play text-[8px]';
            }
        }
        
        // Update locked text
        if (previewLockedText) {
            previewLockedText.innerHTML = '🎬 Episode Ready! Click to Watch';
            previewLockedText.className = 'text-green-400 text-sm font-semibold';
        }
        
        // Update progress message
        if (episodeProgressMessage) {
            episodeProgressMessage.innerHTML = 'All segments completed! Ready to watch.';
            episodeProgressMessage.className = 'text-green-400 text-xs mt-2';
        }
        
        // Make content clickable
        if (previewContent) {
            previewContent.onclick = () => openEpisodeModal();
            previewContent.style.cursor = 'pointer';
            previewContent.classList.add('cursor-pointer', 'hover:bg-gradient-to-br', 'hover:from-green-900/20', 'hover:to-emerald-900/20');
        }
        
        // Update the large icon container (center icon)
        if (iconContainer) {
            iconContainer.className = 'w-16 h-16 mx-auto mb-3 rounded-full bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center transition-all duration-500 group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-green-500/20';
        }
        
        // Update the icon itself
        if (episodeIcon) {
            episodeIcon.className = 'fas fa-play text-3xl text-white episode-icon';
        }
        
        // Show pulse ring animation
        if (pulseRing) {
            pulseRing.style.display = 'flex';
            setTimeout(() => {
                if (pulseRing) pulseRing.style.display = 'none';
            }, 3000);
        }
        
        // Show success toast (only once)
        if (completedCount === totalSegments && totalSegments > 0 && !window.episodeReadyToastShown) {
            window.episodeReadyToastShown = true;
            showToast('🎉 All segments completed! Episode is ready to watch!', 'success');
        }
        
    } else if (completedCount > 0) {
        // ========== PARTIALLY COMPLETED - IN PROGRESS STATE ==========
        
        if (previewIcon) {
            previewIcon.className = 'w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold bg-gradient-to-r from-yellow-500 to-orange-500 text-white shadow-lg shadow-yellow-500/20';
            previewIcon.innerHTML = '<i class="fas fa-spinner fa-pulse text-white text-sm"></i>';
        }
        
        if (previewStatus) {
            previewStatus.className = 'text-[10px] px-2 py-1 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center gap-1';
        }
        if (previewStatusText) {
            previewStatusText.innerHTML = 'In Progress';
        }
        if (previewStatus) {
            const existingIcon = previewStatus.querySelector('i');
            if (existingIcon) {
                existingIcon.className = 'fas fa-spinner fa-pulse text-[8px]';
            }
        }
        
        if (previewLockedText) {
            previewLockedText.innerHTML = '🎬 Generating your episode...';
            previewLockedText.className = 'text-yellow-400 text-sm font-semibold';
        }
        
        if (episodeProgressMessage) {
            episodeProgressMessage.innerHTML = `${completedCount} of ${totalSegments} segments completed`;
            episodeProgressMessage.className = 'text-yellow-400 text-xs mt-2';
        }
        
        if (previewContent) {
            previewContent.onclick = null;
            previewContent.style.cursor = 'default';
        }
        
        if (iconContainer) {
            iconContainer.className = 'w-16 h-16 mx-auto mb-3 rounded-full bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center transition-all duration-500';
        }
        
        if (episodeIcon) {
            episodeIcon.className = 'fas fa-spinner fa-pulse text-3xl text-white episode-icon';
        }
        
        // Reset the toast flag when not completed
        window.episodeReadyToastShown = false;
        
    } else {
        // ========== LOCKED STATE - NO SEGMENTS COMPLETED ==========
        
        if (previewIcon) {
            previewIcon.className = 'w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold bg-gray-700 text-gray-500';
            previewIcon.innerHTML = '<i class="fas fa-lock text-gray-500 text-sm"></i>';
        }
        
        if (previewStatus) {
            previewStatus.className = 'text-[10px] px-2 py-1 rounded-full bg-gray-700 text-gray-500 flex items-center gap-1';
        }
        if (previewStatusText) {
            previewStatusText.innerHTML = 'Locked';
        }
        if (previewStatus) {
            const existingIcon = previewStatus.querySelector('i');
            if (existingIcon) {
                existingIcon.className = 'fas fa-lock text-[8px]';
            }
        }
        
        if (previewLockedText) {
            previewLockedText.innerHTML = 'Episode Preview Locked';
            previewLockedText.className = 'text-gray-400 text-sm font-medium';
        }
        
        if (episodeProgressMessage) {
            episodeProgressMessage.innerHTML = `Complete all ${totalSegments} segments to unlock`;
            episodeProgressMessage.className = 'text-gray-500 text-xs mt-2';
        }
        
        if (previewContent) {
            previewContent.onclick = null;
            previewContent.style.cursor = 'default';
        }
        
        if (iconContainer) {
            iconContainer.className = 'w-16 h-16 mx-auto mb-3 rounded-full bg-gray-800 flex items-center justify-center transition-all duration-500';
        }
        
        if (episodeIcon) {
            episodeIcon.className = 'fas fa-lock text-3xl text-gray-600 episode-icon';
        }
        
        // Reset the toast flag
        window.episodeReadyToastShown = false;
    }
    
    // Force a repaint to ensure the icon updates visually
    if (episodeIcon && episodeIcon.parentElement) {
        const parent = episodeIcon.parentElement;
        parent.style.transform = 'scale(0.99)';
        setTimeout(() => {
            parent.style.transform = '';
        }, 50);
    }
}
// ==================== DEMO USER PROGRESS FUNCTIONS ====================

function initDemoProgress() {
    if (!isDemoUser) return;
    
    const saved = localStorage.getItem('demo_segments_progress_' + window.location.pathname);
    if (saved) {
        demoSegmentsProgress = JSON.parse(saved);
    }
    
    const segments = document.querySelectorAll('.segment-card');
    
    segments.forEach(card => {
        const lockOverlay = card.querySelector('.locked-overlay');
        if (lockOverlay) lockOverlay.style.display = 'none';
    });
    
    segments.forEach((card, idx) => {
        const sceneId = card.getAttribute('data-scene-id');
        const isCompleted = demoSegmentsProgress[sceneId];
        
        if (isCompleted) {
            markSegmentCompletedForDemo(card, sceneId, false);
        } else {
            let shouldBeUnlocked = false;
            if (idx === 0) {
                shouldBeUnlocked = true;
            } else {
                const prevCard = segments[idx - 1];
                const prevSceneId = prevCard.getAttribute('data-scene-id');
                if (demoSegmentsProgress[prevSceneId]) shouldBeUnlocked = true;
            }
            
            if (!shouldBeUnlocked) {
                const lockOverlay = card.querySelector('.locked-overlay');
                if (lockOverlay) lockOverlay.style.display = 'flex';
            }
        }
    });
    
    updateDemoProgressDisplay();
    updateEpisodePreviewCard();
}

function updateDemoProgressDisplay() {
    if (!isDemoUser) return;
    
    const completedCount = Object.keys(demoSegmentsProgress).length;
    const totalScenes = document.querySelectorAll('.segment-card').length;
    
    const completedCountEl = document.getElementById('completedCount');
    if (completedCountEl) completedCountEl.textContent = completedCount;
    
    const episodeCompletedCountEl = document.getElementById('episodeCompletedCount');
    if (episodeCompletedCountEl) episodeCompletedCountEl.textContent = completedCount;
    
    const progressPercent = (completedCount / totalScenes) * 100;
    const episodeProgressEl = document.getElementById('episodeProgress');
    if (episodeProgressEl) episodeProgressEl.style.width = progressPercent + '%';
    
    for (let i = 1; i <= totalScenes; i++) {
        const dot = document.querySelector(`.progress-dot-${i}`);
        if (dot) {
            const segment = document.querySelector(`[data-scene-number="${i}"]`);
            const sceneId = segment ? segment.getAttribute('data-scene-id') : null;
            if (sceneId && demoSegmentsProgress[sceneId]) {
                dot.className = `w-2 h-2 rounded-full bg-green-500 progress-dot-${i}`;
            } else {
                dot.className = `w-2 h-2 rounded-full bg-gray-700 progress-dot-${i}`;
            }
        }
    }
    
    updateEpisodePreviewCard();
}

function saveDemoProgress() {
    if (!isDemoUser) return;
    localStorage.setItem('demo_segments_progress_' + window.location.pathname, JSON.stringify(demoSegmentsProgress));
    updateDemoProgressDisplay();
}

function markSegmentCompletedForDemo(sceneCard, sceneId, save = true) {
    if (!isDemoUser) return;
    
    if (save) {
        demoSegmentsProgress[sceneId] = true;
        saveDemoProgress();
    }
    
    const imageUrl = sceneCard.getAttribute('data-image-url');
    const videoUrl = sceneCard.getAttribute('data-video-url');
    const contentContainer = sceneCard.querySelector('.segment-content');
    
    if (!contentContainer) return;
    
    contentContainer.innerHTML = `
        <div class="relative group/video bg-black rounded-lg overflow-hidden cursor-pointer" style="aspect-ratio: 4 / 3;">
            <div class="absolute inset-0 w-full h-full overflow-hidden">
                <img src="${imageUrl}" class="w-full h-full object-cover blur-2xl scale-110 opacity-60">
            </div>
            <div class="absolute inset-0 bg-black/30"></div>
            <div class="absolute inset-0 flex flex-col items-center justify-center transition-all duration-300 z-10" id="playOverlay-${sceneId}">
                <div class="relative">
                    <div class="absolute inset-0 rounded-full bg-purple-500/20 animate-ping" style="animation-duration: 1.5s;"></div>
                    <svg class="w-16 h-16 text-purple-400 cursor-pointer hover:text-purple-300 transition-all duration-300 hover:scale-110 relative z-10" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </div>
                <p class="text-white text-sm font-semibold drop-shadow-lg mt-5">Ready to View</p>
            </div>
            <video controls class="w-full h-full object-cover hidden relative z-20" id="video-${sceneId}">
                <source src="${videoUrl}" type="video/mp4">
            </video>
            <div class="absolute bottom-2 right-2 flex gap-1 opacity-0 group-hover/video:opacity-100 transition z-30">
                <button onclick="downloadClick('${videoUrl}', ${sceneId})" class="bg-black/60 hover:bg-green-600 p-1.5 rounded-lg backdrop-blur-sm transition">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                </button>
            </div>
        </div>
    `;
    
    setTimeout(() => {
        const overlay = document.getElementById(`playOverlay-${sceneId}`);
        const video = document.getElementById(`video-${sceneId}`);
        if (overlay && video) {
            const newOverlay = overlay.cloneNode(true);
            overlay.parentNode.replaceChild(newOverlay, overlay);
            newOverlay.addEventListener('click', function() {
                newOverlay.style.display = 'none';
                video.classList.remove('hidden');
                video.play();
            });
        }
    }, 100);
    
    const iconDiv = sceneCard.querySelector('.segment-icon');
    const statusSpan = sceneCard.querySelector('.segment-status');
    
    if (iconDiv) {
        iconDiv.className = 'w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold bg-gradient-to-r from-blue-500 to-cyan-500 text-white shadow-lg shadow-blue-500/20';
        iconDiv.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
    }
    
    if (statusSpan) {
        statusSpan.className = 'text-[10px] px-2 py-1 rounded-full bg-blue-500/20 text-blue-400 flex items-center gap-1';
        statusSpan.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Completed';
    }
    
    const sceneNumber = sceneCard.getAttribute('data-scene-number');
    const dot = document.querySelector(`.progress-dot-${sceneNumber}`);
    if (dot) dot.className = `w-2 h-2 rounded-full bg-green-500 progress-dot-${sceneNumber}`;
    
    sceneCard.setAttribute('data-video-url', videoUrl);
    
    const nextSegment = sceneCard.nextElementSibling;
    if (nextSegment && nextSegment.classList.contains('segment-card')) {
        const nextLockOverlay = nextSegment.querySelector('.locked-overlay');
        if (nextLockOverlay) nextLockOverlay.style.display = 'none';
    }
    
    updateDemoProgressDisplay();
    
    const completedCount = Object.keys(demoSegmentsProgress).length;
    const totalScenes = document.querySelectorAll('.segment-card').length;
    if (completedCount === totalScenes) {
        showToast('🎉 Congratulations! You have completed all segments!', 'success');
        updateEpisodePreviewCard();
    }
    updateEpisodePreviewCard();
}

function startVideoGeneration(sceneId) {
    if (isProcessingVideo) {
        showToast('Please wait, another video is being generated...', 'warning');
        return;
    }
    
    const sceneCard = document.getElementById(`scene-${sceneId}`);
    if (!sceneCard) return;
    
    const lockOverlay = sceneCard.querySelector('.locked-overlay');
    if (lockOverlay && lockOverlay.style.display !== 'none') {
        showToast('This segment is locked. Complete previous segment first.', 'warning');
        return;
    }
    
    const imageUrl = sceneCard.getAttribute('data-image-url');
    const contentContainer = sceneCard.querySelector('.segment-content');
    
    if (!imageUrl) {
        showToast('No image found for this segment', 'error');
        return;
    }
    
    isProcessingVideo = true;
    
    const loadingMessages = [
        "🖼️ Analyzing image and preparing scene...",
        "🎬 Generating video frames...",
        "✨ Adding effects and enhancements...",
        "🎥 Finalizing your clip..."
    ];
    
    let messageIndex = 0;
    const totalDuration = 5000;
    const startTime = Date.now();
    
    contentContainer.innerHTML = `
        <div class="relative rounded-lg overflow-hidden border border-purple-500/30" style="aspect-ratio: 4 / 3;">
            <div class="absolute inset-0 w-full h-full overflow-hidden">
                <img src="${imageUrl}" class="w-full h-full object-cover blur-2xl scale-110 opacity-60">
            </div>
            <div class="absolute inset-0 bg-black/40"></div>
            <div class="absolute inset-0 flex flex-col items-center justify-center z-10">
                <div class="relative w-20 h-20 mb-4">
                    <div class="absolute inset-0 border-4 border-purple-500/20 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-t-purple-500 border-r-pink-500 rounded-full animate-spin"></div>
                </div>
                <p class="text-purple-400 text-sm font-semibold text-center" id="loadingMsg-${sceneId}">${loadingMessages[0]}</p>
                <div class="mt-6 w-64 h-1.5 bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full transition-all duration-300" style="width: 0%" id="progressBar-${sceneId}"></div>
                </div>
            </div>
        </div>
    `;
    
    const progressInterval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        const percent = Math.min((elapsed / totalDuration) * 100, 100);
        const progressBar = document.getElementById(`progressBar-${sceneId}`);
        if (progressBar) progressBar.style.width = `${percent}%`;
        if (percent >= 100) clearInterval(progressInterval);
    }, 500);
    
    const messageIntervalDuration = totalDuration / loadingMessages.length;
    const messageInterval = setInterval(() => {
        messageIndex++;
        if (messageIndex < loadingMessages.length) {
            const msgElement = document.getElementById(`loadingMsg-${sceneId}`);
            if (msgElement) msgElement.innerHTML = loadingMessages[messageIndex];
        } else {
            clearInterval(messageInterval);
        }
    }, messageIntervalDuration);
    
    if (isDemoUser) {
        setTimeout(() => {
            clearInterval(messageInterval);
            clearInterval(progressInterval);
            markSegmentCompletedForDemo(sceneCard, sceneId, true);
            isProcessingVideo = false;
            showToast('✅ Clip generated successfully!', 'success');
        }, totalDuration);
    } else {
        setTimeout(() => {
            clearInterval(messageInterval);
            clearInterval(progressInterval);
            markSegmentCompletedForDemo(sceneCard, sceneId, true);
            isProcessingVideo = false;
            showToast('✅ Clip generated successfully!', 'success');
        }, totalDuration);
    }
}

function downloadClick(videoUrl, sceneId) {
    fetch(videoUrl).then(r => r.blob()).then(blob => {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `segment_${sceneId}_clip.mp4`;
        a.click();
        URL.revokeObjectURL(url);
        showToast('📥 Clip downloaded!', 'success');
    }).catch(() => { window.open(videoUrl, '_blank'); showToast('📥 Clip opened', 'info'); });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (isDemoUser) {
        console.log('Demo user mode');
        initDemoProgress();
        
        // Auto-update check for demo user
        const checkInterval = setInterval(() => {
            const totalSegments = document.querySelectorAll('.segment-card').length;
            const completedCount = Object.keys(demoSegmentsProgress).length;
            const currentDisplayText = document.getElementById('completedCount')?.textContent || '0';
            const currentDisplayCount = parseInt(currentDisplayText);
            
            if (completedCount !== currentDisplayCount) {
                updateEpisodePreviewCard();
            }
            
            if (completedCount === totalSegments && totalSegments > 0) {
                clearInterval(checkInterval);
            }
        }, 2000);
        
    } else {
        console.log('Normal user mode - real API calls');
        updateEpisodePreviewCard();
        
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-video-url') {
                    updateEpisodePreviewCard();
                }
            });
        });
        
        document.querySelectorAll('.segment-card').forEach(card => {
            observer.observe(card, { attributes: true });
        });
        
        const checkInterval = setInterval(() => {
            const totalSegments = document.querySelectorAll('.segment-card').length;
            const completedCount = document.querySelectorAll('[data-video-url][data-video-url!=""]').length;
            const currentDisplayText = document.getElementById('completedCount')?.textContent || '0';
            const currentDisplayCount = parseInt(currentDisplayText);
            
            if (completedCount !== currentDisplayCount) {
                updateEpisodePreviewCard();
            }
            
            if (completedCount === totalSegments && totalSegments > 0) {
                clearInterval(checkInterval);
            }
        }, 3000);
        
        const firstScene = document.querySelector('[data-scene-index="0"]');
        if (firstScene) {
            const btn = firstScene.querySelector('#btn-0');
            if (btn) setTimeout(() => btn.click(), 1500);
        }
    }
});

// Voice database with language and gender mapping
const voiceDatabase = {
    'English': {
        female: [
            { id: 'Serena', name: 'Serena - Female English Voice' },
            { id: 'Vivian', name: 'Vivian - Female English Voice' }
        ],
        male: [
            { id: 'Aiden', name: 'Aiden - Male English Voice' },
            { id: 'Dylan', name: 'Dylan - Male English Voice' },
            { id: 'Eric', name: 'Eric - Male English Voice' },
            { id: 'Ryan', name: 'Ryan - Male English Voice' }
        ]
    },
    'Korean': {
        female: [
            { id: 'Sohee', name: 'Sohee - Female Korean Voice' }
        ],
        male: [
            { id: 'Ono_anna', name: 'Ono_anna - Male Korean Voice' }
        ]
    },
    'Chinese': {
        female: [],
        male: [
            { id: 'Uncle_fu', name: 'Uncle_fu - Male Chinese Voice' }
        ]
    },
    'Japanese': {
        female: [],
        male: []
    },
    'French': {
        female: [],
        male: []
    },
    'German': {
        female: [],
        male: []
    },
    'Italian': {
        female: [],
        male: []
    },
    'Spanish': {
        female: [],
        male: []
    },
    'Portuguese': {
        female: [],
        male: []
    },
    'Russian': {
        female: [],
        male: []
    }
};

// Get current gender based on switch
function getCurrentGender() {
    const genderSwitch = document.getElementById('genderSwitch');
    return genderSwitch && genderSwitch.checked ? 'male' : 'female';
}

// Get selected language
function getSelectedLanguage() {
    const languageSelect = document.getElementById('languageSelect');
    return languageSelect ? languageSelect.value : 'English';
}

// Filter voices by gender only (language selection doesn't filter)
function filterVoicesByGender() {
    const currentGender = getCurrentGender();
    const voiceSelect = document.getElementById('voiceSelect');
    
    // Update gender label colors for visual feedback
    const femaleLabel = document.getElementById('genderFemaleLabel');
    const maleLabel = document.getElementById('genderMaleLabel');
    
    if (femaleLabel && maleLabel) {
        if (currentGender === 'male') {
            femaleLabel.classList.add('text-gray-500');
            femaleLabel.classList.remove('text-purple-400');
            maleLabel.classList.add('text-purple-400');
            maleLabel.classList.remove('text-gray-500');
        } else {
            maleLabel.classList.add('text-gray-500');
            maleLabel.classList.remove('text-purple-400');
            femaleLabel.classList.add('text-purple-400');
            femaleLabel.classList.remove('text-gray-500');
        }
    }
    
    if (!voiceSelect) return;
    
    // Get all voice options
    const allOptions = voiceSelect.querySelectorAll('option');
    
    // Filter options based on gender (using naming convention)
    allOptions.forEach(option => {
        const voiceName = option.textContent.toLowerCase();
        
        if (currentGender === 'female') {
            // Show female voices (Serena, Vivian, Sohee)
            if (voiceName.includes('serena') || voiceName.includes('vivian') || voiceName.includes('sohee')) {
                option.style.display = '';
            } else if (option.value === '') {
                option.style.display = ''; // Keep placeholder
            } else {
                option.style.display = 'none';
            }
        } else {
            // Show male voices (Aiden, Dylan, Eric, Ryan, Ono_anna, Uncle_fu)
            if (voiceName.includes('aiden') || voiceName.includes('dylan') || voiceName.includes('eric') || 
                voiceName.includes('ryan') || voiceName.includes('ono_anna') || voiceName.includes('uncle_fu')) {
                option.style.display = '';
            } else if (option.value === '') {
                option.style.display = ''; // Keep placeholder
            } else {
                option.style.display = 'none';
            }
        }
    });
    
    // Reset selection if current selected is hidden
    if (voiceSelect.selectedOptions[0] && voiceSelect.selectedOptions[0].style.display === 'none') {
        voiceSelect.value = '';
    }
}

// Get selected voice ID
function getSelectedVoice() {
    const voiceSelect = document.getElementById('voiceSelect');
    return voiceSelect ? voiceSelect.value : null;
}

// Initialize voice settings when narration toggle is turned ON
function initVoiceSettings() {
    const narrationToggle = document.getElementById('narrationToggle');
    const voiceSettings = document.getElementById('voiceSettings');
    
    if (narrationToggle && voiceSettings) {
        if (narrationToggle.checked) {
            voiceSettings.classList.remove('hidden');
            // Initialize gender filtering
            filterVoicesByGender();
        } else {
            voiceSettings.classList.add('hidden');
        }
    }
}

// Update toggleNarration function
window.toggleNarration = function() {
    const toggle = document.getElementById('narrationToggle');
    const label = document.getElementById('narrationToggleLabel');
    const content = document.getElementById('narrationContent');
    
    if (toggle && toggle.checked) {
        if (label) label.textContent = 'ON';
        if (content) {
            content.classList.remove('hidden');
            content.classList.add('animate-fadeIn');
        }
    } else {
        if (label) label.textContent = 'OFF';
        if (content) content.classList.add('hidden');
    }
    initVoiceSettings();
};
// Update generateBackgroundNarration to include voice validation
async function generateBackgroundNarration() {
    if (isGeneratingNarration) {
        showToast('Narration is already being generated...', 'info');
        return;
    }
    
    const totalSegments = document.querySelectorAll('.segment-card').length;
    const completedCount = isDemoUser ? Object.keys(demoSegmentsProgress).length : document.querySelectorAll('[data-video-url][data-video-url!=""]').length;
    
    if (completedCount < totalSegments) {
        showToast(`Please complete all ${totalSegments} segments first! (${completedCount}/${totalSegments} completed)`, 'warning');
        return;
    }
    
    // Validate voice selection
    const selectedVoice = getSelectedVoice();
    if (!selectedVoice) {
        showToast('Please select a voice first', 'warning');
        return;
    }
    
    isGeneratingNarration = true;
    
    const generateBtn = document.getElementById('generateNarrationBtn');
    const progressContainer = document.getElementById('narrationProgressContainer');
    const progressFill = document.getElementById('narrationProgressFill');
    const progressPercent = document.getElementById('narrationPercent');
    const narrationStatus = document.getElementById('narrationStatus');
    const successMsg = document.getElementById('narrationSuccess');
    
    const selectedLanguage = getSelectedLanguage();
    const gender = getCurrentGender();
    
    generateBtn.disabled = true;
    generateBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Generating...';
    generateBtn.classList.add('opacity-50');
    
    progressContainer.classList.remove('hidden');
    
    let progress = 0;
    const totalDuration = isDemoUser ? 5000 : 15000;
    const startTime = Date.now();
    
    const messages = [
        "🎤 Analyzing episode content...", 
        "📝 Writing voiceover script...", 
        `🎙️ Generating ${gender} ${selectedLanguage} voiceover...`, 
        "🔊 Syncing audio with episode..."
    ];
    let messageIndex = 0;
    
    const interval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        progress = Math.min((elapsed / totalDuration) * 100, 100);
        
        progressFill.style.width = `${progress}%`;
        progressPercent.textContent = `${Math.floor(progress)}%`;
        
        const newIndex = Math.floor(progress / 25);
        if (newIndex < messages.length && newIndex !== messageIndex) {
            messageIndex = newIndex;
            narrationStatus.innerHTML = messages[messageIndex];
        }
        
        if (progress >= 100) {
            clearInterval(interval);
            
            if (isDemoUser) {
                setTimeout(() => {
                    successMsg.classList.remove('hidden');
                    progressContainer.classList.add('hidden');
                    generateBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Narration Ready';
                    generateBtn.disabled = false;
                    generateBtn.classList.remove('opacity-50');
                    generateBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                    isNarrationGenerated = true;
                    isGeneratingNarration = false;
                    showToast(`✅ ${gender === 'male' ? 'Male' : 'Female'} ${selectedLanguage} voiceover generated!`, 'success');
                }, 500);
            } else {
                generateRealNarration(selectedVoice, selectedLanguage, gender);
            }
        }
    }, 100);
}
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes ping { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(2); opacity: 0; } }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-spin { animation: spin 1s linear infinite; }
.animate-ping { animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite; }
.animate-bounce { animation: bounce 1s ease-in-out infinite; }
.animate-fadeIn { animation: fadeIn 0.3s ease-out; }
@keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
.blur-2xl { filter: blur(10px); }
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #1f2937; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #8b5cf6, #ec4899); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: linear-gradient(to bottom, #7c3aed, #db2777); }
.custom-scrollbar { scrollbar-width: thin; scrollbar-color: #8b5cf6 #1f2937; }
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: #1a1a1a; border-radius: 3px; }
::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #8b5cf6, #ec4899); border-radius: 3px; }
button { transition: all 0.2s ease; }
</style>
@endsection