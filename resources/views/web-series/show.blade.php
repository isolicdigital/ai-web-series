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
                @if(auth()->id() == 141)
                    <span class="text-amber-400 text-xs px-2 py-0.5 rounded-full bg-amber-500/20">Demo Account</span>
                @endif
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
                         @if(!$isDemoUser && $isVideoCompleted)
<div class="relative group/video bg-black rounded-lg overflow-hidden cursor-pointer" style="aspect-ratio: 4 / 3;">
    <!-- Blurred Background Image -->
    <div class="absolute inset-0 w-full h-full overflow-hidden">
        <img src="{{ $imageUrl }}" class="w-full h-full object-cover blur-2xl scale-110 opacity-60">
    </div>
    <div class="absolute inset-0 bg-black/30"></div>
    
    <!-- Play Overlay -->
    <div class="absolute inset-0 flex flex-col items-center justify-center transition-all duration-300 z-10" id="playOverlay-{{ $scene->id }}">
        <div class="relative">
            <div class="absolute inset-0 rounded-full bg-purple-500/20 animate-ping" style="animation-duration: 1.5s;"></div>
            <svg class="w-16 h-16 text-purple-400 cursor-pointer hover:text-purple-300 transition-all duration-300 hover:scale-110 relative z-10" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8 5v14l11-7z"/>
            </svg>
        </div>
        <p class="text-white text-sm font-semibold drop-shadow-lg mt-5">Ready to View</p>
    </div>
    
    <!-- Video Element -->
    <video controls class="w-full h-full object-cover hidden relative z-20" id="video-{{ $scene->id }}">
        <source src="{{ asset($scene->video_url) }}" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    
    <!-- Download Button -->
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
@endif
                            
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
            <div class="relative group">
                <div class="bg-gradient-to-br from-gray-900/80 to-gray-800/40 rounded-xl border border-gray-700/50 overflow-hidden transition-all duration-500 hover:border-blue-500/50 hover:shadow-xl hover:shadow-blue-500/10">
                    
                    <div class="px-4 py-3 border-b border-gray-700/50 bg-gradient-to-r from-gray-800/50 to-gray-900/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold episode-preview-icon bg-gray-700 text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-white font-semibold text-sm">Episode Preview</h3>
                                    <p class="text-gray-500 text-[10px]">Full Episode</p>
                                </div>
                            </div>
                            <div>
                                <span class="text-[10px] px-2 py-1 rounded-full bg-gray-700 text-gray-500 flex items-center gap-1 episode-preview-status">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Locked
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-3">
                        <div class="relative bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-lg border border-gray-700 flex flex-col items-center justify-center episode-preview-content" style="aspect-ratio: 4 / 3; cursor: pointer;" onclick="openEpisodeModal()">
                            <div class="text-center p-4">
                                <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-800 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-400 text-sm font-medium episode-preview-locked-text">Episode Preview Locked</p>
                                <p class="text-gray-500 text-xs mt-2">Complete all {{ $totalScenes }} segments to unlock</p>
                                <div class="mt-3 w-full bg-gray-700 rounded-full h-1.5">
                                    <div class="bg-gradient-to-r from-blue-500 to-cyan-500 h-1.5 rounded-full transition-all duration-500" style="width: 0%" id="episodeProgress"></div>
                                </div>
                                <p class="text-gray-600 text-[10px] mt-2"><span id="episodeCompletedCount">0</span>/{{ $totalScenes }} segments completed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Episode Preview Modal -->
<div id="episodeModal" class="fixed inset-0 bg-black/95 backdrop-blur-xl z-50 hidden items-center justify-center p-2 sm:p-4" style="display: none;">
    <!-- Modal content (same as before) -->
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
                    <p class="text-gray-400 text-xs sm:text-sm">Watch all segments in sequence</p>
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
                <video id="episodePlayer" controls class="w-full h-full object-contain">
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
            
            <!-- Playlist -->
            <div>
                <div class="flex items-center justify-between mb-2 sm:mb-3 px-2">
                    <h3 class="text-white font-semibold flex items-center gap-2 text-sm sm:text-base">
                        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        Segments Playlist
                    </h3>
                    <span class="text-xs text-gray-500" id="playlistCount">0 segments</span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3 max-h-36 sm:max-h-48 overflow-y-auto custom-scrollbar p-2 bg-gray-800/20 rounded-lg" id="playlistContainer"></div>
            </div>
            
            <!-- Audio Upload Section -->
            <div class="p-4 bg-gray-800/50 rounded-xl border border-gray-700">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="text-white font-medium">Background Music</span>
                        <p class="text-gray-500 text-xs">Upload your own audio file</p>
                    </div>
                </div>
                
                <div class="border-2 border-dashed border-gray-600 rounded-lg p-6 text-center hover:border-purple-500 hover:bg-purple-500/5 transition-all duration-300 cursor-pointer" id="uploadArea">
                    <input type="file" id="audioUpload" accept="audio/*" class="hidden" />
                    <svg class="w-12 h-12 mx-auto text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                    </svg>
                    <p class="text-gray-400 text-sm">Click or drag to upload your audio file</p>
                    <p class="text-gray-500 text-xs mt-1">MP3, WAV, OGG (Max 10MB)</p>
                    <div id="uploadProgress" class="hidden mt-3">
                        <div class="w-full bg-gray-700 rounded-full h-2 overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full transition-all duration-300" style="width: 0%" id="uploadProgressBar"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1" id="uploadStatus">Uploading...</p>
                    </div>
                </div>
                
                <div id="uploadedAudioPreview" class="hidden mt-3 p-3 bg-gray-700/50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white text-sm font-medium" id="uploadedFileName">audio_file.mp3</p>
                                <p class="text-gray-400 text-xs" id="uploadedFileSize">0 MB</p>
                            </div>
                        </div>
                        <button onclick="removeUploadedAudio()" class="text-red-400 hover:text-red-300 transition-colors p-1 rounded-lg hover:bg-red-500/10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                    <audio controls class="w-full mt-2 h-8 rounded-lg">
                        <source src="" id="uploadedAudioSource" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                </div>
                
                <div id="musicStatus" class="mt-3 text-sm text-gray-400 hidden">
                    <span class="text-green-400">✓</span> Audio applied to episode
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
                <div class="flex items-center gap-1 sm:gap-2">
                    <button onclick="toggleAutoPlay()" id="autoPlayBtn" class="text-xs px-2 sm:px-3 py-1 sm:py-1.5 rounded-full bg-purple-500/20 text-purple-400 hover:bg-purple-500/30 transition-all duration-300 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        </svg>
                        Auto-play ON
                    </button>
                    <button onclick="toggleLoop()" id="loopBtn" class="text-xs px-2 sm:px-3 py-1 sm:py-1.5 rounded-full bg-gray-700 text-gray-400 hover:bg-gray-600 transition-all duration-300 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Loop OFF
                    </button>
                </div>
            </div>
            <div class="flex gap-2 sm:gap-3">
                <button onclick="downloadEpisode()" class="px-3 sm:px-4 py-1.5 sm:py-2 bg-green-600 hover:bg-green-700 rounded-lg text-white text-xs sm:text-sm font-medium transition-all duration-300 flex items-center gap-1 sm:gap-2 transform hover:scale-105">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download
                </button>
                <button onclick="shareEpisode()" class="px-3 sm:px-4 py-1.5 sm:py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-white text-xs sm:text-sm font-medium transition-all duration-300 flex items-center gap-1 sm:gap-2 transform hover:scale-105">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                    </svg>
                    Share
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-50 hidden"></div>

<script>
const intervals = {};
let isProcessingVideo = false;
const isDemoUser = {{ auth()->id() == 141 ? 'true' : 'false' }};
let demoSegmentsProgress = {};
let episodeVideoUrls = [];
let currentMusic = null;
let autoPlayEnabled = true;
let loopEnabled = false;
let currentPlaylistIndex = 0;
let uploadedAudioUrl = null;
let currentMusicSource = null;

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

// ==================== UNIFIED VIDEO GENERATION FUNCTION (Same for both user types) ====================

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
    const videoUrl = sceneCard.getAttribute('data-video-url');
    const contentContainer = sceneCard.querySelector('.segment-content');
    
    if (!imageUrl) {
        showToast('No image found for this segment', 'error');
        return;
    }
    
    isProcessingVideo = true;
    
    // Loading messages
    const loadingMessages = [
        "🖼️ Analyzing image and preparing scene...",
        "🎬 Generating video frames...",
        "✨ Adding effects and enhancements...",
        "🎥 Finalizing your clip..."
    ];
    
    let messageIndex = 0;
    const totalDuration = 2100; // 3.5 minutes
    const startTime = Date.now();
    
    // Show loader with animation
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
                
                <p class="text-purple-400 text-sm font-semibold text-center" id="loadingMsg-${sceneId}">
                    ${loadingMessages[0]}
                </p>
                
                <div class="mt-6 w-64 h-1.5 bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full transition-all duration-300" style="width: 0%" id="progressBar-${sceneId}"></div>
                </div>
            </div>
        </div>
    `;
    
    // Update progress bar smoothly
    const progressInterval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        const percent = Math.min((elapsed / totalDuration) * 100, 100);
        const progressBar = document.getElementById(`progressBar-${sceneId}`);
        if (progressBar) {
            progressBar.style.width = `${percent}%`;
        }
        if (percent >= 100) {
            clearInterval(progressInterval);
        }
    }, 50);
    
    // Rotate messages every few seconds
    const messageIntervalDuration = totalDuration / loadingMessages.length;
    const messageInterval = setInterval(() => {
        messageIndex++;
        if (messageIndex < loadingMessages.length) {
            const msgElement = document.getElementById(`loadingMsg-${sceneId}`);
            if (msgElement) {
                msgElement.innerHTML = loadingMessages[messageIndex];
            }
        } else {
            clearInterval(messageInterval);
        }
    }, messageIntervalDuration);
    
    // Store intervals for cleanup
    window[`progressInterval_${sceneId}`] = progressInterval;
    window[`messageInterval_${sceneId}`] = messageInterval;
    
    if (isDemoUser) {
        // Demo user: simulated generation
        setTimeout(() => {
            clearInterval(messageInterval);
            clearInterval(progressInterval);
            markSegmentCompletedForDemo(sceneCard, sceneId, true);
            isProcessingVideo = false;
            showToast('✅ Clip generated successfully!', 'success');
        }, totalDuration);
    } else {
        // Normal user: real API call
        (async () => {
            try {
                const response = await fetch('{{ route("generate.scene.video") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ scene_id: sceneId, image_url: imageUrl })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    startClipPolling(sceneId);
                    showToast('🎬 Clip generation started...', 'info');
                } else {
                    throw new Error(result.message || 'Failed to generate clip');
                }
            } catch (error) {
                console.error('Clip generation error:', error);
                showToast('❌ Error generating clip: ' + error.message, 'error');
                clearInterval(messageInterval);
                clearInterval(progressInterval);
                location.reload();
            }
        })();
    }
}

function startClipPolling(sceneId) {
    if (intervals[sceneId]) clearInterval(intervals[sceneId]);
    
    let attempts = 0;
    intervals[sceneId] = setInterval(async () => {
        attempts++;
        
        try {
            const response = await fetch(`/check-video-status/${sceneId}`, {
                headers: { 'Accept': 'application/json' }
            });
            
            const result = await response.json();
            
            if (result.success && result.status === 'completed' && result.video_url) {
                clearInterval(intervals[sceneId]);
                delete intervals[sceneId];
                
                // Clear loader intervals
                const messageInterval = window[`messageInterval_${sceneId}`];
                const progressInterval = window[`progressInterval_${sceneId}`];
                if (messageInterval) clearInterval(messageInterval);
                if (progressInterval) clearInterval(progressInterval);
                
                // Update the UI without reload
                updateVideoInCard(sceneId, result.video_url);
                showToast('✅ Clip generated successfully!', 'success');
            } else if (result.success && result.status === 'failed') {
                clearInterval(intervals[sceneId]);
                delete intervals[sceneId];
                showToast('❌ Clip generation failed. Please try again.', 'error');
            } else if (attempts >= 60) {
                clearInterval(intervals[sceneId]);
                delete intervals[sceneId];
                showToast('⚠️ Timeout. Please refresh the page.', 'warning');
            }
        } catch (error) {
            console.error('Clip polling error:', error);
        }
    }, 3000);
}

// Add this function to update video in card without reload
function updateVideoInCard(sceneId, videoUrl) {
    const sceneCard = document.getElementById(`scene-${sceneId}`);
    if (!sceneCard) return;
    
    const imageUrl = sceneCard.getAttribute('data-image-url');
    const contentContainer = sceneCard.querySelector('.segment-content');
    
    // Update video URL attribute
    sceneCard.setAttribute('data-video-url', videoUrl);
    
    // Update content to show video player
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
                Your browser does not support the video tag.
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
    
    // Setup play overlay
    setTimeout(() => {
        const overlay = document.getElementById(`playOverlay-${sceneId}`);
        const video = document.getElementById(`video-${sceneId}`);
        if (overlay && video) {
            overlay.addEventListener('click', function() {
                overlay.style.display = 'none';
                video.classList.remove('hidden');
                video.play();
            });
        }
    }, 100);
    
    // Update header icon and status
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
    
    // Update progress dot
    const sceneNumber = sceneCard.getAttribute('data-scene-number');
    const dot = document.querySelector(`.progress-dot-${sceneNumber}`);
    if (dot) dot.className = `w-2 h-2 rounded-full bg-green-500 progress-dot-${sceneNumber}`;
    
    // Update completed count
    const completedCount = document.querySelectorAll('[data-video-url][data-video-url!=""]').length + 1;
    document.getElementById('completedCount').textContent = completedCount;
    document.getElementById('episodeCompletedCount').textContent = completedCount;
    
    // Update episode progress
    const totalScenes = document.querySelectorAll('.segment-card').length;
    const progressPercent = (completedCount / totalScenes) * 100;
    const episodeProgress = document.getElementById('episodeProgress');
    if (episodeProgress) episodeProgress.style.width = progressPercent + '%';
    
    // Unlock next segment
    const nextSegment = sceneCard.nextElementSibling;
    if (nextSegment && nextSegment.classList.contains('segment-card')) {
        const nextLockOverlay = nextSegment.querySelector('.locked-overlay');
        if (nextLockOverlay) {
            nextLockOverlay.style.display = 'none';
        }
        
        // Update next segment header to show "Ready to Generate"
        const nextIconDiv = nextSegment.querySelector('.segment-icon');
        const nextStatusSpan = nextSegment.querySelector('.segment-status');
        
        if (nextIconDiv) {
            nextIconDiv.className = 'w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg shadow-purple-500/20';
            nextIconDiv.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>';
        }
        
        if (nextStatusSpan) {
            nextStatusSpan.className = 'text-[10px] px-2 py-1 rounded-full bg-purple-500/20 text-purple-400 flex items-center gap-1';
            nextStatusSpan.innerHTML = '<div class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-pulse"></div> Ready to Generate';
        }
    }
    
    // Update episode preview card if all completed
    updateEpisodePreviewCard();
    
    isProcessingVideo = false;
}

function updateSixthCardProgress() {
    const totalSegments = document.querySelectorAll('.segment-card').length;
    const completedCount = document.querySelectorAll('[data-video-url][data-video-url!=""]').length;
    const allCompleted = completedCount === totalSegments && totalSegments > 0;
    
    const episodeProgress = document.getElementById('episodeProgress');
    const episodeCompletedCount = document.getElementById('episodeCompletedCount');
    const previewContent = document.querySelector('.episode-preview-content');
    const previewLockedText = document.querySelector('.episode-preview-locked-text');
    const previewIcon = document.querySelector('.episode-preview-icon');
    const previewStatus = document.querySelector('.episode-preview-status');
    
    if (episodeCompletedCount) episodeCompletedCount.textContent = completedCount;
    if (episodeProgress) episodeProgress.style.width = (completedCount / totalSegments) * 100 + '%';
    
    if (allCompleted) {
        if (previewIcon) {
            previewIcon.className = 'w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-lg shadow-green-500/20';
            previewIcon.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path></svg>';
        }
        if (previewStatus) {
            previewStatus.className = 'text-[10px] px-2 py-1 rounded-full bg-green-500/20 text-green-400 flex items-center gap-1';
            previewStatus.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Watch Now';
        }
        if (previewLockedText) {
            previewLockedText.innerHTML = '🎬 Episode Ready! Click to Watch';
            previewLockedText.className = 'text-green-400 text-sm font-semibold';
        }
        if (previewContent) {
            previewContent.onclick = () => openEpisodeModal();
            previewContent.style.cursor = 'pointer';
        }
    }
}

// ==================== DEMO USER COMPLETION FUNCTION ====================

function markSegmentCompletedForDemo(sceneCard, sceneId, save = true) {
    if (!isDemoUser) return;
    
    console.log('Marking segment as completed:', sceneId);
    
    if (save) {
        demoSegmentsProgress[sceneId] = true;
        saveDemoProgress();
    }
    
    const imageUrl = sceneCard.getAttribute('data-image-url');
    const videoUrl = sceneCard.getAttribute('data-video-url');
    const contentContainer = sceneCard.querySelector('.segment-content');
    
    if (!contentContainer) {
        console.error('Content container not found');
        return;
    }
    
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
                Your browser does not support the video tag.
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
    
    const completedCount = Object.keys(demoSegmentsProgress).length;
    const completedCountEl = document.getElementById('completedCount');
    if (completedCountEl) completedCountEl.textContent = completedCount;
    
    const episodeCompletedCountEl = document.getElementById('episodeCompletedCount');
    if (episodeCompletedCountEl) episodeCompletedCountEl.textContent = completedCount;
    
    const totalScenes = document.querySelectorAll('.segment-card').length;
    const progressPercent = (completedCount / totalScenes) * 100;
    const episodeProgressEl = document.getElementById('episodeProgress');
    if (episodeProgressEl) episodeProgressEl.style.width = progressPercent + '%';
    
    const nextSegment = sceneCard.nextElementSibling;
    if (nextSegment && nextSegment.classList.contains('segment-card')) {
        const nextLockOverlay = nextSegment.querySelector('.locked-overlay');
        if (nextLockOverlay) {
            nextLockOverlay.style.display = 'none';
        }
    }
    
    if (completedCount === totalScenes) {
        showToast('🎉 Congratulations! You have completed all segments!', 'success');
        updateEpisodePreviewCard();
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
    
    const completedCount = Object.keys(demoSegmentsProgress).length;
    const completedCountEl = document.getElementById('completedCount');
    if (completedCountEl) completedCountEl.textContent = completedCount;
    
    const episodeCompletedCountEl = document.getElementById('episodeCompletedCount');
    if (episodeCompletedCountEl) episodeCompletedCountEl.textContent = completedCount;
    
    const totalScenes = document.querySelectorAll('.segment-card').length;
    const progressPercent = (completedCount / totalScenes) * 100;
    const episodeProgressEl = document.getElementById('episodeProgress');
    if (episodeProgressEl) episodeProgressEl.style.width = progressPercent + '%';
    
    updateEpisodePreviewCard();
}

// ==================== EPISODE MODAL FUNCTIONS ====================

function collectEpisodeVideos() {
    const videoUrls = [];
    document.querySelectorAll('.segment-card').forEach(card => {
        const videoUrl = card.getAttribute('data-video-url');
        const sceneNumber = card.getAttribute('data-scene-number');
        const title = card.querySelector('.segment-icon')?.parentElement?.querySelector('h3')?.innerText || `Segment ${sceneNumber}`;
        const isCompleted = isDemoUser ? demoSegmentsProgress[card.getAttribute('data-scene-id')] : (videoUrl && videoUrl !== '');
        
        if (isCompleted && videoUrl) {
            videoUrls.push({
                url: videoUrl,
                sceneNumber: sceneNumber,
                title: title
            });
        }
    });
    return videoUrls;
}

function toggleFullscreen() {
    const modal = document.getElementById('episodeModal');
    if (!document.fullscreenElement) {
        modal.requestFullscreen().catch(err => {
            console.log(`Error: ${err.message}`);
        });
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

function toggleAutoPlay() {
    autoPlayEnabled = !autoPlayEnabled;
    const btn = document.getElementById('autoPlayBtn');
    if (autoPlayEnabled) {
        btn.className = 'text-xs px-3 py-1.5 rounded-full bg-purple-500/20 text-purple-400 hover:bg-purple-500/30 transition-all duration-300 flex items-center gap-1';
        btn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path></svg> Auto-play ON';
    } else {
        btn.className = 'text-xs px-3 py-1.5 rounded-full bg-gray-700 text-gray-400 hover:bg-gray-600 transition-all duration-300 flex items-center gap-1';
        btn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Auto-play OFF';
    }
}

function toggleLoop() {
    loopEnabled = !loopEnabled;
    const btn = document.getElementById('loopBtn');
    if (loopEnabled) {
        btn.className = 'text-xs px-3 py-1.5 rounded-full bg-purple-500/20 text-purple-400 hover:bg-purple-500/30 transition-all duration-300 flex items-center gap-1';
        btn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Loop ON';
    } else {
        btn.className = 'text-xs px-3 py-1.5 rounded-full bg-gray-700 text-gray-400 hover:bg-gray-600 transition-all duration-300 flex items-center gap-1';
        btn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Loop OFF';
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
    
    document.querySelectorAll('.playlist-item').forEach((item, i) => {
        if (i === index) {
            item.classList.add('ring-2', 'ring-purple-500', 'bg-gray-700');
            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            item.classList.remove('ring-2', 'ring-purple-500', 'bg-gray-700');
        }
    });
    
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
    
    const playlistContainer = document.getElementById('playlistContainer');
    playlistContainer.innerHTML = '';
    
    episodeVideoUrls.forEach((video, index) => {
        const playlistItem = document.createElement('div');
        playlistItem.className = `p-3 bg-gray-800 rounded-lg cursor-pointer transition-all duration-300 playlist-item ${index === 0 ? 'ring-2 ring-purple-500 bg-gray-700' : 'hover:bg-gray-700'}`;
        playlistItem.setAttribute('data-index', index);
        playlistItem.innerHTML = `
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center text-xs font-bold text-white">${video.sceneNumber}</div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate">${video.title}</p>
                    <p class="text-gray-400 text-xs">Segment ${video.sceneNumber}</p>
                </div>
                ${index === currentPlaylistIndex ? '<svg class="w-4 h-4 text-purple-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>' : ''}
            </div>
        `;
        playlistItem.onclick = () => playVideoAtIndex(index);
        playlistContainer.appendChild(playlistItem);
    });
    
    document.getElementById('playlistCount').innerHTML = `${episodeVideoUrls.length} segments`;
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
    
    setTimeout(initAudioUpload, 100);
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
    showToast('🎬 Episode download started!', 'success');
    const a = document.createElement('a');
    a.href = episodeVideoUrls[0].url;
    a.download = 'episode_preview.mp4';
    a.click();
}

function shareEpisode() {
    if (episodeVideoUrls.length === 0) {
        showToast('No videos to share', 'warning');
        return;
    }
    showToast('📤 Share link copied to clipboard!', 'success');
    navigator.clipboard.writeText(window.location.href);
}

function updateEpisodePreviewCard() {
    const totalSegments = document.querySelectorAll('.segment-card').length;
    const completedCount = isDemoUser ? Object.keys(demoSegmentsProgress).length : document.querySelectorAll('[data-video-url][data-video-url!=""]').length;
    const allCompleted = completedCount === totalSegments && totalSegments > 0;
    
    const previewIcon = document.querySelector('.episode-preview-icon');
    const previewStatus = document.querySelector('.episode-preview-status');
    const previewContent = document.querySelector('.episode-preview-content');
    const previewLockedText = document.querySelector('.episode-preview-locked-text');
    const episodeProgress = document.getElementById('episodeProgress');
    const episodeCompletedCount = document.getElementById('episodeCompletedCount');
    
    if (episodeCompletedCount) episodeCompletedCount.textContent = completedCount;
    if (episodeProgress) episodeProgress.style.width = (completedCount / totalSegments) * 100 + '%';
    
    if (allCompleted) {
        if (previewIcon) {
            previewIcon.className = 'w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-lg shadow-green-500/20';
            previewIcon.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path></svg>';
        }
        if (previewStatus) {
            previewStatus.className = 'text-[10px] px-2 py-1 rounded-full bg-green-500/20 text-green-400 flex items-center gap-1';
            previewStatus.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Watch Now';
        }
        if (previewLockedText) {
            previewLockedText.innerHTML = '🎬 Episode Ready! Click to Watch';
            previewLockedText.className = 'text-green-400 text-sm font-semibold';
        }
        if (previewContent) {
            previewContent.onclick = () => openEpisodeModal();
            previewContent.style.cursor = 'pointer';
        }
    } else {
        if (previewIcon) {
            previewIcon.className = 'w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold bg-gray-700 text-gray-500';
            previewIcon.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>';
        }
        if (previewStatus) {
            previewStatus.className = 'text-[10px] px-2 py-1 rounded-full bg-gray-700 text-gray-500 flex items-center gap-1';
            previewStatus.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg> Locked';
        }
        if (previewLockedText) previewLockedText.innerHTML = 'Episode Preview Locked';
    }
}

// ==================== NORMAL USER IMAGE FUNCTIONS ====================

async function generateImage(sceneId) {
    if (isDemoUser) return;
    
    const sceneCard = document.getElementById(`scene-${sceneId}`);
    const prompt = sceneCard.getAttribute('data-prompt');
    
    if (!prompt) {
        showToast('No prompt found for this segment', 'error');
        return;
    }
    
    try {
        const response = await fetch('/generate-image', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ prompt: prompt, scene_id: sceneId })
        });
        
        const result = await response.json();
        
        if (result.success && result.image_url) {
            sceneCard.setAttribute('data-image-url', result.image_url);
            location.reload();
        } else if (result.processing) {
            showToast('🖼️ Image generation started...', 'info');
            startImagePolling(sceneId);
        } else {
            showToast('❌ Error: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Image generation error:', error);
        showToast('❌ Network error', 'error');
    }
}

function startImagePolling(sceneId) {
    if (intervals[sceneId]) clearInterval(intervals[sceneId]);
    let attempts = 0;
    intervals[sceneId] = setInterval(async () => {
        attempts++;
        try {
            const response = await fetch('/check-image-status', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ scene_id: sceneId })
            });
            const result = await response.json();
            if (result.success && result.status === 'completed' && result.image_url) {
                clearInterval(intervals[sceneId]);
                delete intervals[sceneId];
                const sceneCard = document.getElementById(`scene-${sceneId}`);
                sceneCard.setAttribute('data-image-url', result.image_url);
                location.reload();
                showToast('🖼️ Image generated successfully!', 'success');
            } else if (attempts >= 60) {
                clearInterval(intervals[sceneId]);
                delete intervals[sceneId];
                showToast('⚠️ Timeout. Please refresh the page.', 'warning');
            }
        } catch (error) {
            console.error('Polling error:', error);
        }
    }, 3000);
}

// ==================== COMMON FUNCTIONS ====================

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

// ==================== AUDIO UPLOAD FUNCTIONS ====================

function setupAudioUpload() {
    const uploadArea = document.getElementById('uploadArea');
    const audioInput = document.getElementById('audioUpload');
    
    if (!uploadArea || !audioInput) return;
    
    uploadArea.addEventListener('click', () => audioInput.click());
    
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('border-purple-500', 'bg-purple-500/10');
    });
    
    uploadArea.addEventListener('dragleave', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('border-purple-500', 'bg-purple-500/10');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('border-purple-500', 'bg-purple-500/10');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('audio/')) {
            uploadAudioFile(file);
        } else {
            showToast('Please upload a valid audio file', 'error');
        }
    });
    
    audioInput.addEventListener('change', (e) => {
        if (e.target.files[0]) {
            uploadAudioFile(e.target.files[0]);
        }
    });
}

async function uploadAudioFile(file) {
    if (file.size > 10 * 1024 * 1024) {
        showToast('File size must be less than 10MB', 'error');
        return;
    }
    
    const validTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp3'];
    if (!validTypes.includes(file.type)) {
        showToast('Please upload MP3, WAV, or OGG files only', 'error');
        return;
    }
    
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadProgressBar = document.getElementById('uploadProgressBar');
    const uploadStatus = document.getElementById('uploadStatus');
    
    uploadProgress.classList.remove('hidden');
    uploadProgressBar.style.width = '0%';
    uploadStatus.innerHTML = 'Uploading...';
    
    let progress = 0;
    const interval = setInterval(() => {
        progress += 10;
        uploadProgressBar.style.width = `${progress}%`;
        if (progress >= 100) clearInterval(interval);
    }, 200);
    
    setTimeout(() => {
        const audioUrl = URL.createObjectURL(file);
        uploadedAudioUrl = audioUrl;
        currentMusicSource = 'upload';
        
        const preview = document.getElementById('uploadedAudioPreview');
        const fileName = document.getElementById('uploadedFileName');
        const fileSize = document.getElementById('uploadedFileSize');
        const audioSource = document.getElementById('uploadedAudioSource');
        
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        audioSource.src = audioUrl;
        
        preview.classList.remove('hidden');
        uploadProgress.classList.add('hidden');
        
        showToast('✅ Audio uploaded successfully!', 'success');
        applyCustomMusic(audioUrl);
    }, 2000);
}

function removeUploadedAudio() {
    if (uploadedAudioUrl) {
        URL.revokeObjectURL(uploadedAudioUrl);
        uploadedAudioUrl = null;
    }
    currentMusicSource = null;
    
    document.getElementById('uploadedAudioPreview').classList.add('hidden');
    document.getElementById('audioUpload').value = '';
    
    showToast('Audio removed', 'info');
    removeMusicFromEpisode();
}

function applyCustomMusic(audioUrl) {
    currentMusic = audioUrl;
    currentMusicSource = 'upload';
    
    const musicStatus = document.getElementById('musicStatus');
    musicStatus.classList.remove('hidden');
    musicStatus.innerHTML = '<span class="text-green-400">✓</span> Custom audio applied to episode';
    
    showToast('🎵 Custom audio applied to episode!', 'success');
    integrateAudioWithVideo(audioUrl);
}

function integrateAudioWithVideo(audioUrl) {
    let audioElement = document.getElementById('backgroundAudio');
    if (!audioElement) {
        audioElement = document.createElement('audio');
        audioElement.id = 'backgroundAudio';
        audioElement.loop = true;
        document.body.appendChild(audioElement);
    }
    
    audioElement.src = audioUrl;
    audioElement.volume = 0.3;
    
    const video = document.getElementById('episodePlayer');
    if (video) {
        video.addEventListener('play', () => {
            if (currentMusicSource === 'upload' && uploadedAudioUrl) {
                audioElement.currentTime = video.currentTime;
                audioElement.play();
            }
        });
        
        video.addEventListener('pause', () => {
            audioElement.pause();
        });
        
        video.addEventListener('seeked', () => {
            audioElement.currentTime = video.currentTime;
        });
    }
}

function removeMusicFromEpisode() {
    const audioElement = document.getElementById('backgroundAudio');
    if (audioElement) {
        audioElement.pause();
        audioElement.src = '';
    }
    
    const musicStatus = document.getElementById('musicStatus');
    musicStatus.classList.add('hidden');
}

function initAudioUpload() {
    setupAudioUpload();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (isDemoUser) {
        console.log('Demo user mode');
        initDemoProgress();
    } else {
        console.log('Normal user mode - real API calls');
        updateEpisodePreviewCard();
        
        // Auto-start first segment image generation if needed
        const firstScene = document.querySelector('[data-scene-index="0"]');
        if (firstScene) {
            const sceneId = firstScene.getAttribute('data-scene-id');
            const hasImage = firstScene.getAttribute('data-image-url');
            const hasVideo = firstScene.getAttribute('data-video-url');
            
            if ((!hasImage || hasImage === '') && (!hasVideo || hasVideo === '')) {
                console.log('Auto-starting image generation for first segment');
                generateImage(sceneId);
            }
        }
    }
});
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes ping { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(2); opacity: 0; } }

.animate-spin { animation: spin 1s linear infinite; }
.animate-ping { animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite; }
.animate-bounce { animation: bounce 1s ease-in-out infinite; }

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* Blur effects */
.blur-2xl {
    filter: blur(10px);
}
.blur-xl {
    filter: blur(10px);
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #1f2937;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #8b5cf6, #ec4899);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #7c3aed, #db2777);
}

.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #8b5cf6 #1f2937;
}

/* Audio player styling */
audio::-webkit-media-controls-panel {
    background-color: #1f2937;
}

audio::-webkit-media-controls-current-time-display,
audio::-webkit-media-controls-time-remaining-display {
    color: white;
}

audio::-webkit-media-controls-timeline {
    background-color: #374151;
    border-radius: 10px;
}

/* General */
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: #1a1a1a; border-radius: 3px; }
::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #8b5cf6, #ec4899); border-radius: 3px; }

button { transition: all 0.2s ease; }
</style>
@endsection