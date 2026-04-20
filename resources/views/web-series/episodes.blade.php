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
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">{{ $series->project_name }}</h1>
            <div class="flex gap-3 mt-3">
                <span class="text-purple-400 text-xs px-3 py-1 rounded-full bg-purple-500/20">{{ $series->category ? $series->category->name : 'Uncategorized' }}</span>
                <span class="text-gray-500 text-xs px-3 py-1 rounded-full bg-gray-800/50">{{ $episodes->count() }} Episodes</span>
            </div>
        </div>
        
        <!-- Stats Summary -->
        @php
            $allEpisodes = $series->episodes()->get();
            $totalScenes = $allEpisodes->sum(function($ep) { return $ep->scenes->count(); });
            $completedScenes = $allEpisodes->sum(function($ep) { return $ep->scenes->whereNotNull('video_url')->count(); });
            $totalEpisodes = $allEpisodes->count();
            $completedEpisodes = $allEpisodes->where('status', 'completed')->count();
            $isDemoUser = auth()->id() == 141;
        @endphp
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-gradient-to-br from-purple-900/30 to-pink-900/30 rounded-2xl p-4 border border-purple-500/20">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-600/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Total Episodes</p>
                        <p class="text-2xl font-bold text-white">{{ $totalEpisodes }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-blue-900/30 to-cyan-900/30 rounded-2xl p-4 border border-blue-500/20">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Total Scenes</p>
                        <p class="text-2xl font-bold text-white">{{ $totalScenes }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-green-900/30 to-emerald-900/30 rounded-2xl p-4 border border-green-500/20">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-600/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Completed</p>
                        <p class="text-2xl font-bold text-white">{{ $completedEpisodes }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Create Episode Button -->
        <div class="mb-8 flex justify-end">
            <a href="{{ route('web-series.create-episode', $series->id) }}" 
               class="group px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-pink-500/25 hover:scale-105">
                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create New Episode
            </a>
        </div>
        
        <!-- Episodes Grid -->
        @if($episodes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($episodes as $episode)
            @php
                $thumbnailUrl = null;
                $firstScene = $episode->scenes->first();
                if ($firstScene && $firstScene->generated_image_url) {
                    $thumbnailUrl = asset($firstScene->generated_image_url);
                }
                $scenesCount = $episode->scenes->count();
                $completedScenesCount = $episode->scenes->whereNotNull('video_url')->count();
                $progressPercent = $scenesCount > 0 ? ($completedScenesCount / $scenesCount) * 100 : 0;
                $isCompleted = $episode->status === 'completed';
            @endphp
            <div class="group bg-gray-900/40 backdrop-blur-lg rounded-2xl border border-gray-800 hover:border-purple-500/50 transition-all duration-300 overflow-hidden hover:transform hover:scale-105 cursor-pointer" 
                 onclick="event.stopPropagation(); {{ $isCompleted ? 'openVideoModal(' . $episode->id . ', \'' . addslashes($episode->title) . '\', ' . $episode->episode_number . ')' : 'viewSeries(' . $series->id . ')' }}">
                <!-- Card Header -->
                <div class="relative h-48 overflow-hidden bg-gradient-to-br from-purple-600/20 to-pink-600/20">
                    @if($thumbnailUrl)
                        <img src="{{ $thumbnailUrl }}" 
                             alt="{{ $episode->title }}" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-20 h-20 text-white/20 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                    
                    <!-- Episode Number Badge -->
                    <div class="absolute top-3 right-3">
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $isCompleted ? 'bg-gradient-to-r from-green-500 to-emerald-500' : 'bg-gradient-to-r from-purple-600 to-pink-600' }} text-white shadow-lg">
                            Episode {{ $episode->episode_number }}
                        </span>
                    </div>
                    
                    <!-- Scenes Count -->
                    <div class="absolute bottom-3 left-3">
                        <div class="flex items-center gap-1 bg-black/60 backdrop-blur-sm px-2 py-1 rounded-lg">
                            <svg class="w-3 h-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"></path>
                            </svg>
                            <span class="text-white text-xs">{{ $scenesCount }} Scenes</span>
                        </div>
                    </div>
                    
                    <!-- Progress Badge / Completed Badge -->
                    <div class="absolute bottom-3 right-3">
                        @if($isCompleted)
                            <div class="bg-green-500/80 backdrop-blur-sm px-2 py-1 rounded-lg">
                                <span class="text-white text-xs flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Ready
                                </span>
                            </div>
                        @else
                            <div class="bg-black/60 backdrop-blur-sm px-2 py-1 rounded-lg">
                                <span class="text-white text-xs">{{ $completedScenesCount }}/{{ $scenesCount }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Play Icon Overlay for Completed Episodes -->
                    @if($isCompleted)
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <div class="w-16 h-16 rounded-full bg-green-500/80 flex items-center justify-center transform scale-90 group-hover:scale-100 transition-transform duration-300">
                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Card Content -->
                <div class="p-5">
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-purple-300 transition line-clamp-1">{{ $episode->title }}</h3>
                    <p class="text-gray-400 text-sm mb-4 line-clamp-2">{{ Str::limit($episode->concept ?? 'No description provided', 100) }}</p>
                    
                    <!-- Progress Bar (only show if not completed) -->
                    @if(!$isCompleted)
                        <div class="mb-4">
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>Progress</span>
                                <span>{{ $completedScenesCount }}/{{ $scenesCount }} Scenes</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                            </div>
                        </div>
                    @else
                        <div class="mb-4">
                            <div class="flex items-center justify-center gap-2 text-green-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm font-medium">Episode Ready to Watch</span>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Status and Date -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs px-2 py-1 rounded-full {{ $isCompleted ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                            {{ $isCompleted ? 'Ready to Watch' : 'In Progress' }}
                        </span>
                        <div class="flex items-center gap-1 text-gray-500 text-xs">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ $episode->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    
                    <!-- Action Button -->
                    <div class="mt-4 pt-3 border-t border-gray-800">
                        <button onclick="event.stopPropagation(); {{ $isCompleted ? 'openVideoModal(' . $episode->id . ', \'' . addslashes($episode->title) . '\', ' . $episode->episode_number . ')' : 'viewSeries(' . $series->id . ')' }}" 
                                class="w-full py-2 {{ $isCompleted ? 'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700' : 'bg-gradient-to-r from-purple-600/20 to-pink-600/20 hover:from-purple-600 hover:to-pink-600' }} rounded-lg {{ $isCompleted ? 'text-white' : 'text-purple-400 hover:text-white' }} text-sm font-medium transition-all duration-300 flex items-center justify-center gap-2">
                            @if($isCompleted)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                </svg>
                                Watch Episode
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                                Continue Editing
                            @endif
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        @if(method_exists($episodes, 'links'))
        <div class="mt-10">
            {{ $episodes->links() }}
        </div>
        @endif
        
        @else
        <!-- Empty State -->
        <div class="text-center py-20">
            <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-gradient-to-br from-purple-600/20 to-pink-600/20 flex items-center justify-center">
                <svg class="w-12 h-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-white mb-3">No Episodes Yet</h3>
            <p class="text-gray-400 mb-6 max-w-md mx-auto">Start your creative journey by creating your first episode for this series</p>
            <a href="{{ route('web-series.create-episode', $series->id) }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 hover:scale-105 shadow-lg hover:shadow-pink-500/25">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create First Episode
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Video Modal -->
<div id="videoModal" class="fixed inset-0 bg-black/95 backdrop-blur-xl z-50 hidden items-center justify-center p-4" style="display: none;">
    <div class="relative w-full max-w-6xl bg-gradient-to-br from-gray-900 to-black rounded-2xl border border-gray-700 overflow-hidden shadow-2xl shadow-purple-500/20">
        
        <!-- Modal Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-700 bg-gradient-to-r from-gray-800/50 to-gray-900/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent" id="modalEpisodeTitle">Episode Title</h2>
                    <p class="text-gray-400 text-sm" id="modalEpisodeInfo">Episode 1</p>
                </div>
            </div>
            <button onclick="closeVideoModal()" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Video Player -->
        <div class="relative bg-black">
            <video id="modalVideoPlayer" controls class="w-full" style="max-height: 70vh;">
                <source src="" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        
        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-gray-700 bg-gradient-to-r from-gray-800/50 to-gray-900/50 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                <span class="text-sm text-gray-400">Full Episode - Ready to Watch</span>
            </div>
            <button onclick="downloadCurrentVideo()" class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg text-white text-sm font-medium transition-all duration-300 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download Episode
            </button>
        </div>
    </div>
</div>

<script>
let currentVideoUrl = null;
let currentEpisodeId = null;
const isDemoUser = {{ auth()->user() && auth()->user()->demo_mode ? 'true' : 'false' }};

function editEpisode(seriesId, episodeNumber) {
    window.location.href = `/web-series/${seriesId}/episodes/${episodeNumber}/edit`;
}

function openVideoModal(episodeId, episodeTitle, episodeNumber) {
    // Set modal title
    document.getElementById('modalEpisodeTitle').textContent = episodeTitle;
    document.getElementById('modalEpisodeInfo').textContent = `Episode ${episodeNumber}`;
    
    // Show loading state
    const videoPlayer = document.getElementById('modalVideoPlayer');
    videoPlayer.innerHTML = '<source src="" type="video/mp4">';
    
    // Show modal
    document.getElementById('videoModal').style.display = 'flex';
    
    // For demo user (ID 141), get video from demo controller
    if (isDemoUser) {
        // Use the demo video endpoint - get video based on episode number
        const demoVideoUrl = `/demo/video/${episodeNumber}`;
        currentVideoUrl = demoVideoUrl;
        currentEpisodeId = episodeId;
        
        videoPlayer.src = demoVideoUrl;
        videoPlayer.load();
        videoPlayer.play().catch(e => console.log('Auto-play prevented:', e));
        
        showToast('🎬 Loading episode...', 'info');
    } else {
        // For real users, fetch from API
        fetch(`/web-series/${episodeId}/full-video`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.video_url) {
                    currentVideoUrl = data.video_url;
                    currentEpisodeId = episodeId;
                    videoPlayer.src = data.video_url;
                    videoPlayer.load();
                    videoPlayer.play().catch(e => console.log('Auto-play prevented:', e));
                } else {
                    showToast(data.message || 'Failed to load episode', 'error');
                    closeVideoModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to load episode', 'error');
                closeVideoModal();
            });
    }
}

function closeVideoModal() {
    const videoPlayer = document.getElementById('modalVideoPlayer');
    if (videoPlayer) {
        videoPlayer.pause();
        videoPlayer.src = '';
    }
    document.getElementById('videoModal').style.display = 'none';
}

function viewSeries(seriesId) {
    window.location.href = `/web-series/${seriesId}`;
}

function downloadCurrentVideo() {
    if (currentVideoUrl) {
        const a = document.createElement('a');
        a.href = currentVideoUrl;
        a.download = `episode_${currentEpisodeId}.mp4`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        showToast('📥 Download started!', 'success');
    } else {
        showToast('No video available to download', 'warning');
    }
}

function showToast(message, type = 'success') {
    // Create toast element if it doesn't exist
    let toast = document.getElementById('customToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'customToast';
        toast.className = 'fixed bottom-8 left-1/2 transform -translate-x-1/2 z-50 hidden';
        document.body.appendChild(toast);
    }
    
    const colors = { 
        success: 'bg-green-600', 
        error: 'bg-red-600', 
        info: 'bg-blue-600', 
        warning: 'bg-yellow-600' 
    };
    
    toast.className = `fixed bottom-8 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded-lg text-white text-sm font-medium z-50 transition-all duration-300 ${colors[type]} opacity-0 translate-y-4 shadow-lg`;
    toast.textContent = message;
    toast.style.display = 'block';
    
    setTimeout(() => toast.classList.remove('opacity-0', 'translate-y-4'), 10);
    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-4');
        setTimeout(() => toast.style.display = 'none', 300);
    }, 3000);
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeVideoModal();
    }
});

// Also add the viewEpisode function for backward compatibility
function viewEpisode(seriesId, episodeNumber) {
    window.location.href = `/web-series/${seriesId}?episode=${episodeNumber}`;
}
</script>

<style>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@keyframes spin { to { transform: rotate(360deg); } }
@keyframes ping { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(2); opacity: 0; } }

.animate-spin { animation: spin 1s linear infinite; }
.animate-ping { animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite; }

@media (max-width: 768px) {
    .py-\[120px\] {
        padding-top: 60px !important;
        padding-bottom: 60px !important;
    }
}
</style>
@endsection