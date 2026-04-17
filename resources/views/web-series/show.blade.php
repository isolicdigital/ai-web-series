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
        
        <!-- Stats - Fixed division by zero -->
        @php
            $totalScenes = $series->scenes->count();
            $completedVideos = $series->scenes->whereNotNull('video_url')->count();
            $allVideosCompleted = $totalScenes > 0 && $completedVideos == $totalScenes;
        @endphp
        
        <div class="mb-8 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full {{ $allVideosCompleted ? 'bg-blue-500' : 'bg-green-500' }} animate-pulse"></div>
                <span class="text-xs text-gray-400">{{ $completedVideos }} of {{ $totalScenes }} segments completed</span>
            </div>
            <div class="flex gap-1">
                @for($i = 1; $i <= $totalScenes; $i++)
                    <div class="w-2 h-2 rounded-full {{ $i <= $completedVideos ? 'bg-green-500' : 'bg-gray-700' }}"></div>
                @endfor
                @if($allVideosCompleted && $totalScenes > 0)
                    <div class="w-2 h-2 rounded-full bg-blue-500 ml-1"></div>
                @endif
            </div>
        </div>
        
        <!-- Segments Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($series->scenes as $index => $scene)
            @php
                // Sequential unlock: segment unlocks only when previous segment has clip
                $isLocked = false;
                if($index > 0) {
                    $previousScene = $series->scenes[$index - 1];
                    if(!$previousScene->video_url) {
                        $isLocked = true;
                    }
                }
                $isImageCompleted = !is_null($scene->generated_image_url);
                $isVideoCompleted = !is_null($scene->video_url);
                $isProcessing = !$isVideoCompleted && !$isLocked;
            @endphp
            
            <div class="relative group" id="scene-{{ $scene->id }}" data-prompt="{{ $scene->image_prompt }}" data-scene-id="{{ $scene->id }}">
                
                <!-- Locked Overlay -->
                @if($isLocked && !$isVideoCompleted)
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm rounded-xl z-20 flex flex-col items-center justify-center border border-gray-700">
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
                
                <!-- Segment Card -->
                <div class="bg-gradient-to-br from-gray-900/80 to-gray-800/40 rounded-xl border border-gray-700/50 overflow-hidden transition-all duration-500 {{ !$isLocked ? 'hover:border-purple-500/50 hover:shadow-xl hover:shadow-purple-500/10' : 'opacity-80' }}">
                    
                    <!-- Card Header -->
                    <div class="px-4 py-3 border-b border-gray-700/50 bg-gradient-to-r from-gray-800/50 to-gray-900/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold
                                    {{ $isVideoCompleted ? 'bg-gradient-to-r from-blue-500 to-cyan-500 text-white shadow-lg shadow-blue-500/20' : 
                                       ($isProcessing ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg shadow-purple-500/20' : 
                                       ($isLocked ? 'bg-gray-700 text-gray-500' : 'bg-gray-700 text-gray-500')) }}">
                                    @if($isVideoCompleted)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    @elseif($isProcessing)
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    @else
                                        {{ $scene->scene_number }}
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-white font-semibold text-sm">{{ $scene->title }}</h3>
                                    <p class="text-gray-500 text-[10px]">Segment {{ $scene->scene_number }}</p>
                                </div>
                            </div>
                            <div>
                                @if($isVideoCompleted)
                                    <span class="text-[10px] px-2 py-1 rounded-full bg-blue-500/20 text-blue-400 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Completed
                                    </span>
                                @elseif($isProcessing)
                                    <span class="text-[10px] px-2 py-1 rounded-full bg-purple-500/20 text-purple-400 flex items-center gap-1">
                                        <div class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-pulse"></div>
                                        Processing
                                    </span>
                                @elseif($isLocked)
                                    <span class="text-[10px] px-2 py-1 rounded-full bg-gray-700 text-gray-500 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        Locked
                                    </span>
                                @else
                                    <span class="text-[10px] px-2 py-1 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center gap-1">
                                        <div class="w-1.5 h-1.5 rounded-full bg-yellow-400 animate-pulse"></div>
                                        Pending
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card Content - 4:3 Aspect Ratio -->
                    <div class="p-3">
                        @if($isVideoCompleted)
                            <!-- Click Player -->
                            <div class="relative group/video bg-black rounded-lg overflow-hidden" style="aspect-ratio: 4 / 3;">
                                <div class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-purple-900/80 to-pink-900/80 cursor-pointer" id="playOverlay-{{ $scene->id }}">
                                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center shadow-2xl mb-4 hover:scale-110 transition-transform">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-white text-sm font-semibold mb-2">Ready to View</p>
                                    <p class="text-gray-300 text-xs">Click to play your clip</p>
                                </div>
                                <video controls class="w-full h-full object-cover hidden" id="video-{{ $scene->id }}">
                                    <source src="{{ asset($scene->video_url) }}" type="video/mp4">
                                    Your browser does not support the clip tag.
                                </video>
                                <div class="absolute bottom-2 right-2 flex gap-1 opacity-0 group-hover/video:opacity-100 transition">
                                    <button onclick="downloadClick('{{ $scene->video_url }}', {{ $scene->id }})" 
                                            class="bg-black/60 hover:bg-green-600 p-1.5 rounded-lg backdrop-blur-sm transition">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </button>
                                    <button onclick="shareClick('{{ $scene->video_url }}', {{ $scene->id }})" 
                                            class="bg-black/60 hover:bg-blue-600 p-1.5 rounded-lg backdrop-blur-sm transition">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
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
                                            overlay.classList.add('hidden');
                                            video.classList.remove('hidden');
                                            video.play();
                                        });
                                    }
                                })();
                            </script>
                            
                        @elseif($isProcessing)
                            <!-- Single Loader with Blurred Image Background -->
                            <div class="relative bg-gradient-to-br from-gray-800/80 to-gray-900/80 rounded-lg overflow-hidden border border-purple-500/30" style="aspect-ratio: 4 / 3;">
                                <!-- Blurred Background Image -->
                                @if($isImageCompleted && $scene->generated_image_url)
                                <img src="{{ asset($scene->generated_image_url) }}" 
                                     class="absolute inset-0 w-full h-full object-cover filter blur-xl scale-110 opacity-50"
                                     onerror="this.style.display='none'">
                                @else
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-900/30 to-pink-900/30"></div>
                                @endif
                                
                                <!-- Gemini/Veo Style Loading Animation -->
                                <div class="absolute inset-0 flex items-center justify-center z-10">
                                    <div class="relative">
                                        <div class="absolute w-2 h-2 rounded-full bg-purple-400 animate-orbit-1" style="top: -30px; left: -30px;"></div>
                                        <div class="absolute w-2 h-2 rounded-full bg-pink-400 animate-orbit-2" style="top: -25px; right: -35px;"></div>
                                        <div class="absolute w-2 h-2 rounded-full bg-blue-400 animate-orbit-3" style="bottom: -30px; left: -25px;"></div>
                                        <div class="absolute w-2 h-2 rounded-full bg-cyan-400 animate-orbit-4" style="bottom: -25px; right: -30px;"></div>
                                        
                                        <div class="absolute w-24 h-24 rounded-full bg-gradient-to-br from-purple-500/20 to-pink-500/20 animate-pulse-glow" style="top: -40px; left: -40px;"></div>
                                        <div class="absolute w-32 h-32 rounded-full bg-gradient-to-br from-blue-500/10 to-cyan-500/10 animate-pulse-glow-delayed" style="bottom: -50px; right: -50px;"></div>
                                        
                                        <div class="relative">
                                            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-purple-600 via-pink-500 to-purple-600 animate-gemini-pulse shadow-xl shadow-purple-500/30 flex items-center justify-center">
                                                <svg class="w-10 h-10 text-white animate-soft-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                            <div class="absolute inset-0 w-20 h-20 rounded-full border-2 border-purple-400/30 animate-ring-pulse"></div>
                                            <div class="absolute inset-0 w-20 h-20 rounded-full border border-pink-400/20 animate-ring-pulse-delayed" style="transform: scale(1.3);"></div>
                                            <div class="absolute inset-0 w-20 h-20 rounded-full border border-purple-400/10 animate-ring-pulse-slow" style="transform: scale(1.6);"></div>
                                        </div>
                                        
                                        <p class="text-purple-400 text-xs font-medium mt-6 text-center">Creating your clip...</p>
                                        <div class="flex gap-1 justify-center mt-2">
                                            <div class="w-1 h-1 rounded-full bg-purple-400 animate-bounce-dot" style="animation-delay: 0s"></div>
                                            <div class="w-1 h-1 rounded-full bg-pink-400 animate-bounce-dot" style="animation-delay: 0.2s"></div>
                                            <div class="w-1 h-1 rounded-full bg-purple-400 animate-bounce-dot" style="animation-delay: 0.4s"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @if(!$isImageCompleted)
                            <button onclick="generateImage({{ $scene->id }})" id="btn-{{ $scene->id }}" style="display: none;"></button>
                            @endif
                            
                        @elseif($isLocked)
                            <!-- Locked Placeholder -->
                            <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-lg border border-gray-700 flex flex-col items-center justify-center" style="aspect-ratio: 4 / 3;">
                                <div class="text-center p-4">
                                    <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-gray-800 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 text-xs">Locked</p>
                                    <p class="text-gray-600 text-[10px] mt-1">Complete previous segment</p>
                                </div>
                            </div>
                        @else
                            <!-- Pending State -->
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
            
            <!-- Sixth Card: Episode Preview - Fixed division by zero -->
            <div class="relative group">
                <div class="bg-gradient-to-br from-gray-900/80 to-gray-800/40 rounded-xl border border-gray-700/50 overflow-hidden transition-all duration-500 hover:border-blue-500/50 hover:shadow-xl hover:shadow-blue-500/10">
                    
                    <!-- Card Header -->
                    <div class="px-4 py-3 border-b border-gray-700/50 bg-gradient-to-r from-gray-800/50 to-gray-900/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold {{ $allVideosCompleted && $totalScenes > 0 ? 'bg-gradient-to-r from-blue-500 to-cyan-500 text-white shadow-lg shadow-blue-500/20' : 'bg-gray-700 text-gray-500' }}">
                                    @if($allVideosCompleted && $totalScenes > 0)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-white font-semibold text-sm">Episode Preview</h3>
                                    <p class="text-gray-500 text-[10px]">Full Episode</p>
                                </div>
                            </div>
                            <div>
                                @if($allVideosCompleted && $totalScenes > 0)
                                    <span class="text-[10px] px-2 py-1 rounded-full bg-blue-500/20 text-blue-400 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Unlocked
                                    </span>
                                @else
                                    <span class="text-[10px] px-2 py-1 rounded-full bg-gray-700 text-gray-500 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        Locked
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card Content - 4:3 Aspect Ratio -->
                    <div class="p-3">
                        @if($allVideosCompleted && $totalScenes > 0)
                            <!-- Episode Preview Player -->
                            <div class="relative group/video bg-black rounded-lg overflow-hidden" style="aspect-ratio: 4 / 3;">
                                <div class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-blue-900/80 to-cyan-900/80 cursor-pointer" id="episodePlayOverlay">
                                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-600 to-cyan-600 flex items-center justify-center shadow-2xl mb-4 hover:scale-110 transition-transform">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-white text-sm font-semibold mb-2">Full Episode Ready</p>
                                    <p class="text-gray-300 text-xs">Click to watch the complete episode</p>
                                </div>
                                <video controls class="w-full h-full object-cover hidden" id="episodeVideo">
                                    <source src="" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                                <div class="absolute bottom-2 right-2 flex gap-1 opacity-0 group-hover/video:opacity-100 transition">
                                    <button onclick="downloadEpisode()" class="bg-black/60 hover:bg-green-600 p-1.5 rounded-lg backdrop-blur-sm transition">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </button>
                                    <button onclick="shareEpisode()" class="bg-black/60 hover:bg-blue-600 p-1.5 rounded-lg backdrop-blur-sm transition">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <script>
                                (function() {
                                    const overlay = document.getElementById('episodePlayOverlay');
                                    const video = document.getElementById('episodeVideo');
                                    if (overlay && video) {
                                        overlay.addEventListener('click', function() {
                                            const videoUrls = @json($series->scenes->whereNotNull('video_url')->pluck('video_url')->map(function($url) { return asset($url); }));
                                            if (videoUrls.length > 0) {
                                                video.src = videoUrls[0];
                                                overlay.classList.add('hidden');
                                                video.classList.remove('hidden');
                                                video.play();
                                            }
                                        });
                                    }
                                })();
                            </script>
                        @else
                            <!-- Locked Preview Card with safe division -->
                            <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-lg border border-gray-700 flex flex-col items-center justify-center" style="aspect-ratio: 4 / 3;">
                                <div class="text-center p-4">
                                    <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-800 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-400 text-sm font-medium">Episode Preview Locked</p>
                                    <p class="text-gray-500 text-xs mt-2">Complete all {{ $totalScenes }} segments to unlock</p>
                                    <div class="mt-3 w-full bg-gray-700 rounded-full h-1.5">
                                        @php
                                            $progressWidth = ($totalScenes > 0) ? ($completedVideos / $totalScenes) * 100 : 0;
                                        @endphp
                                        <div class="bg-gradient-to-r from-blue-500 to-cyan-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $progressWidth }}%"></div>
                                    </div>
                                    <p class="text-gray-600 text-[10px] mt-2">{{ $completedVideos }}/{{ $totalScenes }} segments completed</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-50 hidden"></div>

<script>
const intervals = {};

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

// Auto-generate for first segment on page load
document.addEventListener('DOMContentLoaded', function() {
    const scenes = document.querySelectorAll('[id^="scene-"]');
    for (let scene of scenes) {
        const sceneId = scene.id.split('-')[1];
        const generateBtn = document.getElementById(`btn-${sceneId}`);
        if (generateBtn) {
            setTimeout(() => {
                generateImage(sceneId);
            }, 1500);
            break;
        }
    }
});

// Generate image (auto-starts clip generation after image is ready)
async function generateImage(sceneId) {
    const sceneCard = document.getElementById(`scene-${sceneId}`);
    const prompt = sceneCard ? sceneCard.getAttribute('data-prompt') : '';
    
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
            updateBlurredBackground(sceneId, result.image_url);
            await generateClip(sceneId);
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

function updateBlurredBackground(sceneId, imageUrl) {
    const sceneCard = document.getElementById(`scene-${sceneId}`);
    const loaderContainer = sceneCard.querySelector('.relative.bg-gradient-to-br.from-gray-800\\/80');
    if (loaderContainer) {
        let existingImg = loaderContainer.querySelector('img');
        if (existingImg) {
            existingImg.src = imageUrl;
        } else {
            const img = document.createElement('img');
            img.src = imageUrl;
            img.className = 'absolute inset-0 w-full h-full object-cover filter blur-xl scale-110 opacity-50';
            img.onerror = function() { this.style.display = 'none'; };
            loaderContainer.insertBefore(img, loaderContainer.firstChild);
        }
    }
}

// Generate clip from image
// Generate clip from image
async function generateClip(sceneId) {
    const sceneCard = document.getElementById(`scene-${sceneId}`);
    
    // Get the image URL correctly
    let imageUrl = '';
    
    // Try to get image URL from the blurred background image
    const blurredImg = sceneCard.querySelector('img');
    if (blurredImg && blurredImg.src) {
        imageUrl = blurredImg.src;
    }
    
    // Alternative: Get from data attribute
    if (!imageUrl) {
        imageUrl = sceneCard.getAttribute('data-image-url');
    }
    
    // If still no image URL, check if there's a generated_image_url in the scene data
    if (!imageUrl) {
        const imageContainer = sceneCard.querySelector('.relative.bg-gradient-to-br.from-gray-800\\/80');
        const img = imageContainer ? imageContainer.querySelector('img') : null;
        if (img && img.src) {
            imageUrl = img.src;
        }
    }
    
    if (!imageUrl) {
        showToast('No image found for this segment', 'error');
        return;
    }
    
    try {
        const response = await fetch('{{ route("generate.scene.video") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                scene_id: sceneId,
                image_url: imageUrl  // Now sending the actual image URL
            })
        });
        
        const result = await response.json();
        
        if (result.success && result.video_url) {
            startClipPolling(sceneId);
            showToast('🎬 Clip generation started...', 'info');
        } else {
            throw new Error(result.message || 'Failed to generate clip');
        }
    } catch (error) {
        console.error('Clip generation error:', error);
        showToast('❌ Error generating clip: ' + error.message, 'error');
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
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ scene_id: sceneId })
            });
            
            const result = await response.json();
            
            if (result.success && result.status === 'completed' && result.image_url) {
                clearInterval(intervals[sceneId]);
                delete intervals[sceneId];
                updateBlurredBackground(sceneId, result.image_url);
                await generateClip(sceneId);
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

function startClipPolling(sceneId) {
    if (intervals[sceneId]) clearInterval(intervals[sceneId]);
    
    let attempts = 0;
    intervals[sceneId] = setInterval(async () => {
        attempts++;
        
        try {
            const response = await fetch(`/check-video-status/${sceneId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (result.success && result.status === 'completed' && result.video_url) {
                clearInterval(intervals[sceneId]);
                delete intervals[sceneId];
                displayClip(sceneId, result.video_url);
                showToast('✅ Clip generated successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
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

function displayClip(sceneId, clipUrl) {
    const sceneCard = document.getElementById(`scene-${sceneId}`);
    const contentContainer = sceneCard.querySelector('.p-3');
    
    contentContainer.innerHTML = `
        <div class="relative group/video bg-black rounded-lg overflow-hidden" style="aspect-ratio: 4 / 3;">
            <div class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-purple-900/80 to-pink-900/80 cursor-pointer" id="playOverlay-${sceneId}">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center shadow-2xl mb-4 hover:scale-110 transition-transform">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-white text-sm font-semibold mb-2">Ready to View</p>
                <p class="text-gray-300 text-xs">Click to play your clip</p>
            </div>
            <video controls class="w-full h-full object-cover hidden" id="video-${sceneId}">
                <source src="${clipUrl}" type="video/mp4">
                Your browser does not support the clip tag.
            </video>
            <div class="absolute bottom-2 right-2 flex gap-1 opacity-0 group-hover/video:opacity-100 transition">
                <button onclick="downloadClick('${clipUrl}', ${sceneId})" class="bg-black/60 hover:bg-green-600 p-1.5 rounded-lg backdrop-blur-sm transition">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                </button>
                <button onclick="shareClick('${clipUrl}', ${sceneId})" class="bg-black/60 hover:bg-blue-600 p-1.5 rounded-lg backdrop-blur-sm transition">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                </button>
            </div>
        </div>
    `;
    
    const overlay = document.getElementById(`playOverlay-${sceneId}`);
    const video = document.getElementById(`video-${sceneId}`);
    if (overlay && video) {
        overlay.addEventListener('click', function() {
            overlay.classList.add('hidden');
            video.classList.remove('hidden');
            video.play();
        });
    }
    
    const headerDiv = sceneCard.querySelector('.border-b');
    if (headerDiv) {
        const statusSpan = headerDiv.querySelector('.text-purple-400.text-\\[10px\\]');
        if (statusSpan) {
            statusSpan.className = 'text-[10px] px-2 py-1 rounded-full bg-blue-500/20 text-blue-400 flex items-center gap-1';
            statusSpan.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Completed';
        }
        
        const iconDiv = headerDiv.querySelector('.w-8.h-8.rounded-lg');
        if (iconDiv) {
            iconDiv.classList.remove('bg-gradient-to-r from-purple-500 to-pink-500');
            iconDiv.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-cyan-500');
            iconDiv.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>';
        }
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

function downloadEpisode() {
    showToast('🎬 Episode preview will be available soon!', 'info');
}

function shareEpisode() {
    showToast('📤 Share episode feature coming soon!', 'info');
}

async function shareClick(videoUrl, sceneId) {
    try {
        if (navigator.share) {
            const response = await fetch(videoUrl);
            const blob = await response.blob();
            const file = new File([blob], `segment_${sceneId}_clip.mp4`, { type: 'video/mp4' });
            await navigator.share({ title: 'AI Generated Segment Clip', files: [file] });
            showToast('📤 Shared successfully!', 'success');
        } else {
            await navigator.clipboard.writeText(videoUrl);
            showToast('Clip URL copied!', 'success');
        }
    } catch (error) {
        if (error.name !== 'AbortError') showToast('Could not share clip', 'error');
    }
}
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }

/* Gemini/Veo Style Animations */
@keyframes gemini-pulse {
    0%, 100% { transform: scale(1); opacity: 1; box-shadow: 0 0 20px rgba(139, 92, 246, 0.5); }
    50% { transform: scale(1.05); opacity: 0.9; box-shadow: 0 0 40px rgba(236, 72, 153, 0.5); }
}

@keyframes ring-pulse {
    0% { transform: scale(1); opacity: 0.6; }
    100% { transform: scale(1.5); opacity: 0; }
}

@keyframes ring-pulse-delayed {
    0% { transform: scale(1.3); opacity: 0.4; }
    100% { transform: scale(2); opacity: 0; }
}

@keyframes ring-pulse-slow {
    0% { transform: scale(1.6); opacity: 0.3; }
    100% { transform: scale(2.5); opacity: 0; }
}

@keyframes pulse-glow {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.2); }
}

@keyframes pulse-glow-delayed {
    0%, 100% { opacity: 0.2; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.3); }
}

@keyframes orbit-1 {
    0% { transform: rotate(0deg) translateX(35px) rotate(0deg); opacity: 1; }
    100% { transform: rotate(360deg) translateX(35px) rotate(-360deg); opacity: 0.5; }
}

@keyframes orbit-2 {
    0% { transform: rotate(120deg) translateX(35px) rotate(-120deg); opacity: 1; }
    100% { transform: rotate(480deg) translateX(35px) rotate(-480deg); opacity: 0.5; }
}

@keyframes orbit-3 {
    0% { transform: rotate(240deg) translateX(35px) rotate(-240deg); opacity: 1; }
    100% { transform: rotate(600deg) translateX(35px) rotate(-600deg); opacity: 0.5; }
}

@keyframes orbit-4 {
    0% { transform: rotate(60deg) translateX(40px) rotate(-60deg); opacity: 0.8; }
    100% { transform: rotate(420deg) translateX(40px) rotate(-420deg); opacity: 0.3; }
}

@keyframes bounce-dot {
    0%, 100% { transform: translateY(0); opacity: 0.3; }
    50% { transform: translateY(-4px); opacity: 1; }
}

@keyframes soft-pulse {
    0%, 100% { transform: scale(1); opacity: 0.8; }
    50% { transform: scale(1.1); opacity: 1; }
}

.animate-gemini-pulse { animation: gemini-pulse 2s ease-in-out infinite; }
.animate-ring-pulse { animation: ring-pulse 2s ease-out infinite; }
.animate-ring-pulse-delayed { animation: ring-pulse-delayed 2.5s ease-out infinite; }
.animate-ring-pulse-slow { animation: ring-pulse-slow 3s ease-out infinite; }
.animate-pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }
.animate-pulse-glow-delayed { animation: pulse-glow-delayed 4s ease-in-out infinite; }
.animate-orbit-1 { animation: orbit-1 3s linear infinite; }
.animate-orbit-2 { animation: orbit-2 3s linear infinite; }
.animate-orbit-3 { animation: orbit-3 3s linear infinite; }
.animate-orbit-4 { animation: orbit-4 4s linear infinite; }
.animate-bounce-dot { animation: bounce-dot 0.8s ease-in-out infinite; }
.animate-soft-pulse { animation: soft-pulse 1.5s ease-in-out infinite; }

.animate-spin { animation: spin 1s linear infinite; }
.animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(0.95); }
}

::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: #1a1a1a; border-radius: 3px; }
::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #8b5cf6, #ec4899); border-radius: 3px; }

button { transition: all 0.2s ease; }
img, video { transition: transform 0.3s ease; }

.backdrop-blur-md { backdrop-filter: blur(12px); }
.filter-blur-xl { filter: blur(24px); }
</style>
@endsection