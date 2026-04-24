@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black py-[120px] px-4">
    <div class="container mx-auto max-w-7xl">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-10">
            <div>
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-2">My Series</h1>
                <p class="text-gray-400 text-lg">Manage and explore your AI-powered web series creations</p>
            </div>
            <a href="{{ route('web-series.create') }}" 
               class="group px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-pink-500/25 hover:scale-105 w-full md:w-auto justify-center">
                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create New Series
            </a>
        </div>
        
        @if($webSeries->count() > 0)
        <!-- Stats Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-gradient-to-br from-purple-900/30 to-pink-900/30 rounded-2xl p-4 border border-purple-500/20">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-600/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Total Series</p>
                        <p class="text-2xl font-bold text-white">{{ $webSeries->total() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-blue-900/30 to-cyan-900/30 rounded-2xl p-4 border border-blue-500/20">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Total Episodes</p>
                        <p class="text-2xl font-bold text-white">{{ $webSeries->sum('episodes_count') }}</p>
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
                        <p class="text-2xl font-bold text-white">{{ $webSeries->where('status', 'completed')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Series Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($webSeries as $series)
            @php
                $episodes = $series->episodes()->with('scenes')->get();
                $episodeCount = $episodes->count();
                
                $thumbnailUrl = null;
                $firstEpisode = $episodes->first();
                if ($firstEpisode) {
                    $firstScene = $firstEpisode->scenes->first();
                    if ($firstScene && $firstScene->generated_image_url) {
                        $thumbnailUrl = asset($firstScene->generated_image_url);
                    }
                }
                
                $hasMultipleEpisodes = $episodeCount > 1;
            @endphp
            <div class="group bg-gray-900/40 backdrop-blur-lg rounded-2xl border border-gray-800 hover:border-purple-500/50 transition-all duration-300 overflow-hidden hover:transform hover:scale-105">
                <!-- Card Header with Thumbnail Image -->
                <div class="relative h-48 overflow-hidden bg-gradient-to-br from-purple-600/20 to-pink-600/20">
                    @if($thumbnailUrl)
                        <img src="{{ $thumbnailUrl }}" 
                             alt="{{ $series->project_name }}" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-20 h-20 text-white/20 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                            </svg>
                        </div>
                    @endif
                    
                    <!-- Status Badge -->
                    <div class="absolute top-3 right-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $series->status === 'completed' ? 'bg-green-600/80 text-white' : ($series->status === 'generating' ? 'bg-yellow-600/80 text-white' : 'bg-gray-600/80 text-white') }}">
                            {{ $series->status === 'completed' ? 'Completed' : ucfirst($series->status) }}
                        </span>
                    </div>
                    
                    <!-- Episode count overlay -->
                    <div class="absolute bottom-3 left-3">
                        <div class="flex items-center gap-1 bg-black/60 backdrop-blur-sm px-2 py-1 rounded-lg">
                            <svg class="w-3 h-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span class="text-white text-xs">{{ $episodeCount }} Episode{{ $episodeCount !== 1 ? 's' : '' }}</span>
                        </div>
                    </div>
                    
                    <!-- Multiple Episodes Indicator -->
                    @if($hasMultipleEpisodes)
                    <div class="absolute top-3 left-3">
                        <div class="flex items-center gap-1 bg-purple-600/80 backdrop-blur-sm px-2 py-1 rounded-lg">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span class="text-white text-xs">{{ $episodeCount }} Episodes</span>
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- Card Content -->
                <div class="p-5">
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-purple-300 transition line-clamp-1">{{ $series->project_name }}</h3>
                    <p class="text-gray-400 text-sm mb-4 line-clamp-2">{{ Str::limit($series->concept ?? 'No description provided', 100) }}</p>
                    
                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Progress</span>
                            <span>{{ $series->episodes->count() }}/{{ $series->total_episodes ?: 1 }} Episodes</span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-700 rounded-full overflow-hidden">
                            @php
                                $progressPercent = $series->total_episodes ? ($series->episodes->count() / $series->total_episodes) * 100 : 0;
                            @endphp
                            <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                        </div>
                    </div>
                    
                    <!-- Meta Info -->
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>{{ $series->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ $series->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    
                    <!-- Category Badge -->
                    @if($series->category)
                    <div class="mb-3">
                        <span class="text-xs px-2 py-1 rounded-full bg-purple-500/20 text-purple-300">
                            {{ $series->category->name }}
                        </span>
                    </div>
                    @endif
                    
                    <!-- Action Buttons - Single button to go to episodes page -->
                    <div class="flex gap-2 pt-3 border-t border-gray-800">
                        <button onclick="viewSeriesEpisodes({{ $series->id }})" 
                                class="flex-1 px-3 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-lg text-white text-sm font-medium transition-all duration-300 flex items-center justify-center gap-2 group/btn">
                            <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View All Episodes
                        </button>
                        <button onclick="confirmDelete({{ $series->id }}, '{{ $series->project_name }}')" 
                                class="px-3 py-2 bg-red-600/20 hover:bg-red-600 border border-red-500/30 hover:border-red-500 rounded-lg text-red-400 hover:text-white transition-all duration-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="mt-10">
            {{ $webSeries->links() }}
        </div>
        
        @else
        <!-- Empty State -->
        <div class="text-center py-20">
            <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-gradient-to-br from-purple-600/20 to-pink-600/20 flex items-center justify-center">
                <svg class="w-12 h-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-white mb-3">No Series Yet</h3>
            <p class="text-gray-400 mb-6 max-w-md mx-auto">Start your creative journey by creating your first AI-powered web series</p>
            <a href="{{ route('web-series.create') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 hover:scale-105 shadow-lg hover:shadow-pink-500/25">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Your First Series
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal (Only Modal Remaining) -->
<div id="deleteModal" class="fixed inset-0 bg-black/90 backdrop-blur-md z-50 hidden items-center justify-center transition-all duration-300">
    <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl max-w-md w-full mx-4 p-6 border border-red-500/30 shadow-2xl transform transition-all duration-300 scale-95 opacity-0" id="deleteModalContent">
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-500/20 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Delete Series</h3>
            <p class="text-gray-400 mb-2">
                Are you sure you want to delete "<span id="deleteSeriesName" class="text-white font-semibold"></span>"?
            </p>
            <p class="text-red-400 text-sm mb-6">⚠️ This action cannot be undone. All episodes and scenes will be permanently deleted.</p>
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()" 
                        class="flex-1 px-4 py-2.5 bg-gray-700 hover:bg-gray-600 rounded-xl text-white font-medium transition-all duration-300">
                    Cancel
                </button>
                <button id="confirmDeleteBtn" 
                        class="flex-1 px-4 py-2.5 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 rounded-xl text-white font-medium transition-all duration-300 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete Permanently
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let seriesToDelete = null;

// Function to go to episodes page
function viewSeriesEpisodes(seriesId) {
    window.location.href = `/web-series/${seriesId}/episodes`;
}

function confirmDelete(id, name) {
    seriesToDelete = id;
    document.getElementById('deleteSeriesName').textContent = name;
    const modal = document.getElementById('deleteModal');
    const content = document.getElementById('deleteModalContent');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    const content = document.getElementById('deleteModalContent');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        seriesToDelete = null;
    }, 300);
}

document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
    if (!seriesToDelete) return;
    
    const btn = this;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Deleting...';
    btn.disabled = true;
    
    try {
        const response = await fetch(`/web-series/${seriesToDelete}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Series deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error: ' + result.message, 'error');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            closeDeleteModal();
        }
    } catch (error) {
        showToast('Error deleting series: ' + error.message, 'error');
        btn.innerHTML = originalHtml;
        btn.disabled = false;
        closeDeleteModal();
    }
});

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

function showToast(message, type) {
    let toast = document.getElementById('customToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'customToast';
        document.body.appendChild(toast);
    }
    
    const colors = {
        success: 'bg-gradient-to-r from-green-600 to-emerald-600',
        error: 'bg-gradient-to-r from-red-600 to-rose-600',
        info: 'bg-gradient-to-r from-blue-600 to-cyan-600'
    };
    
    toast.className = `fixed bottom-8 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-xl text-white font-medium z-50 transition-all duration-300 ${colors[type]} opacity-0 translate-y-4 shadow-lg`;
    toast.textContent = message;
    toast.style.display = 'block';
    
    setTimeout(() => {
        toast.classList.remove('opacity-0', 'translate-y-4');
        toast.classList.add('opacity-100', 'translate-y-0');
    }, 10);
    
    setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', 'translate-y-4');
        setTimeout(() => {
            toast.style.display = 'none';
        }, 300);
    }, 3000);
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

@keyframes spin {
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@media (max-width: 768px) {
    .py-\[120px\] {
        padding-top: 60px !important;
        padding-bottom: 60px !important;
    }
}
</style>
@endsection