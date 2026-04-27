{{-- resources/views/dfy/search-video.blade.php --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-black to-gray-900 py-24 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header Section -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 mb-6 shadow-lg">
                <i class="fas fa-video text-white text-2xl"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent mb-4">
                {{ $page_title ?? 'Search Videos' }}
            </h1>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Turn any keyword into stunning videos instantly
            </p>
        </div>
        
        <!-- Search Form -->
        <div class="max-w-4xl mx-auto mb-12">
            <form id="search-form" action="{{ route('dfy.videos') }}" method="GET" class="space-y-6">
                <!-- Keyword Input -->
                <div>
                    <label class="block text-white font-semibold mb-3 flex items-center gap-2">
                        <i class="fas fa-keyboard text-purple-400 text-sm"></i>
                        Keyword
                    </label>
                    <input type="text" 
                           class="w-full px-5 py-3.5 bg-gray-800/50 border border-gray-700 rounded-xl text-white placeholder-gray-400 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" 
                           name="prompt" 
                           value="{{ $prompt ?? '' }}" 
                           placeholder="e.g., sunset beach, mountain landscape, city skyline" 
                           maxlength="100" 
                           required>
                </div>
                
                <!-- Popular Keywords -->
                <div>
                    <label class="block text-white font-semibold mb-3 flex items-center gap-2">
                        <i class="fas fa-fire text-orange-400 text-sm"></i>
                        Popular Keywords
                    </label>
                    <div class="flex flex-wrap gap-3">
                        @php
                        $popularKeywords = [
                            'Sunset Beach', 'Mountain Landscape', 'City Skyline', 
                            'Forest Nature', 'Abstract Art', 'Cosmic Space',
                            'Underwater Ocean', 'Winter Snow', 'Summer Tropical',
                            'Desert Sand', 'Wildlife Animals', 'Urban Architecture',
                            'Aerial Drone', 'Time Lapse', 'Slow Motion'
                        ];
                        @endphp
                        @foreach($popularKeywords as $keyword)
                        <button type="button" 
                                class="keyword-btn px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 {{ isset($prompt) && strtolower($prompt) == strtolower($keyword) ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'bg-gray-800/50 text-gray-300 hover:bg-gray-700 border border-gray-700 hover:border-purple-500/50' }}"
                                data-keyword="{{ $keyword }}">
                            {{ $keyword }}
                        </button>
                        @endforeach
                    </div>
                </div>
                
                <!-- Aspect Ratio Selector -->
                <div>
                    <label class="block text-white font-semibold mb-3 flex items-center gap-2">
                        <i class="fas fa-arrows-alt text-purple-400 text-sm"></i>
                        Aspect Ratio
                    </label>
                    <div class="flex flex-wrap gap-4">
                        <!-- Landscape -->
                        <label class="ratio-option cursor-pointer group">
                            <input type="radio" name="type" value="landscape" class="hidden peer" 
                                {{ (!isset($type) || $type == 'landscape') ? 'checked' : '' }}>
                            <div class="flex items-center gap-3 px-5 py-3 rounded-xl bg-gray-800/50 border-2 border-gray-700 peer-checked:border-purple-500 peer-checked:bg-purple-500/20 transition-all duration-300 hover:border-purple-500/50">
                                <div class="w-10 h-10 rounded-lg bg-gray-700/50 flex items-center justify-center">
                                    <i class="fas fa-arrows-alt-h text-blue-400"></i>
                                </div>
                                <div>
                                    <div class="text-white font-medium text-sm">Landscape</div>
                                    <div class="text-gray-500 text-xs">16:9</div>
                                </div>
                                <div class="w-5 h-5 rounded-full bg-purple-500 scale-0 peer-checked:scale-100 transition-all duration-200 flex items-center justify-center shadow-lg">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                            </div>
                        </label>
                        
                        <!-- Portrait -->
                        <label class="ratio-option cursor-pointer group">
                            <input type="radio" name="type" value="portrait" class="hidden peer"
                                {{ (isset($type) && $type == 'portrait') ? 'checked' : '' }}>
                            <div class="flex items-center gap-3 px-5 py-3 rounded-xl bg-gray-800/50 border-2 border-gray-700 peer-checked:border-purple-500 peer-checked:bg-purple-500/20 transition-all duration-300 hover:border-purple-500/50">
                                <div class="w-10 h-10 rounded-lg bg-gray-700/50 flex items-center justify-center">
                                    <i class="fas fa-arrows-alt-v text-pink-400"></i>
                                </div>
                                <div>
                                    <div class="text-white font-medium text-sm">Portrait</div>
                                    <div class="text-gray-500 text-xs">9:16</div>
                                </div>
                                <div class="w-5 h-5 rounded-full bg-purple-500 scale-0 peer-checked:scale-100 transition-all duration-200 flex items-center justify-center shadow-lg">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                            </div>
                        </label>
                        
                        <!-- Square -->
                        <label class="ratio-option cursor-pointer group">
                            <input type="radio" name="type" value="square" class="hidden peer"
                                {{ (isset($type) && $type == 'square') ? 'checked' : '' }}>
                            <div class="flex items-center gap-3 px-5 py-3 rounded-xl bg-gray-800/50 border-2 border-gray-700 peer-checked:border-purple-500 peer-checked:bg-purple-500/20 transition-all duration-300 hover:border-purple-500/50">
                                <div class="w-10 h-10 rounded-lg bg-gray-700/50 flex items-center justify-center">
                                    <i class="fas fa-square text-green-400"></i>
                                </div>
                                <div>
                                    <div class="text-white font-medium text-sm">Square</div>
                                    <div class="text-gray-500 text-xs">1:1</div>
                                </div>
                                <div class="w-5 h-5 rounded-full bg-purple-500 scale-0 peer-checked:scale-100 transition-all duration-200 flex items-center justify-center shadow-lg">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="per_page" value="12">
                
                <div class="text-center pt-4">
                    <button type="submit" 
                            id="search-button"
                            class="inline-flex items-center gap-3 px-8 py-3.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 shadow-lg hover:shadow-pink-500/25 transform hover:scale-105">
                        <i class="fas fa-search"></i>
                        <span>Search Videos</span>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Results Section Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-video text-purple-400"></i>
                    Results
                </h2>
                <p class="text-gray-500 text-sm mt-1">Your searched videos</p>
            </div>
            @if(!empty($videos) && count($videos) > 0)
            <div class="text-sm text-gray-500">
                {{ count($videos) }} videos found
            </div>
            @endif
        </div>
        
        <!-- Results Grid -->
        @if(empty($videos) || count($videos) == 0)
        <div class="text-center py-20">
            <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-gradient-to-br from-purple-600/20 to-pink-600/20 flex items-center justify-center">
                <i class="fas fa-video text-purple-400 text-5xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-white mb-3">No Videos Yet</h3>
            <p class="text-gray-400 mb-6 max-w-md mx-auto">Enter a keyword above and click search to start discovering videos</p>
            <div class="inline-block p-4 bg-gray-800/30 rounded-xl">
                <p class="text-gray-500 text-sm mb-3">Try one of these popular keywords:</p>
                <div class="flex flex-wrap gap-2 justify-center">
                    @foreach(array_slice($popularKeywords, 0, 8) as $keyword)
                    <button type="button" class="keyword-suggestion px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-800 text-gray-300 hover:bg-purple-600 hover:text-white transition-all duration-300" data-keyword="{{ $keyword }}">
                        {{ $keyword }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
            @foreach($videos as $index => $vid)
            <div class="video-card group relative bg-gradient-to-br from-gray-900/80 to-gray-800/40 backdrop-blur-lg rounded-xl border border-gray-700/50 hover:border-purple-500/50 transition-all duration-500 overflow-hidden hover:shadow-2xl hover:shadow-purple-500/10 hover:-translate-y-1 cursor-pointer"
                 data-video-index="{{ $index }}">
                
                <!-- Thumbnail Image -->
                <div class="relative aspect-video overflow-hidden">
                    <img class="video-thumbnail w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" 
                         src="{{ $vid['thumb'] ?? 'https://placehold.co/640x360/1a1a2e/6b7280?text=No+Preview' }}" 
                         alt="Video thumbnail"
                         loading="lazy">
                    
                    <!-- Duration Badge -->
                    @if(isset($vid['duration']) && $vid['duration'])
                    <div class="absolute bottom-2 right-2 px-2 py-1 rounded-md bg-black/70 backdrop-blur-sm text-white text-xs font-medium">
                        <i class="fas fa-clock mr-1 text-[10px]"></i>
                        {{ $vid['duration'] }}
                    </div>
                    @endif
                    
                    <!-- Play Icon Overlay -->
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center">
                        <div class="w-12 h-12 rounded-full bg-purple-600/90 backdrop-blur-sm flex items-center justify-center transform scale-90 group-hover:scale-100 transition-transform duration-300">
                            <i class="fas fa-play text-white text-sm ml-0.5"></i>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="absolute top-2 right-2 flex gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                        <button class="view-btn w-8 h-8 rounded-full bg-black/60 backdrop-blur-sm flex items-center justify-center hover:bg-purple-600 transition-all duration-300 transform hover:scale-110"
                                data-video-src="{{ $vid['video'] ?? '' }}">
                            <i class="fas fa-play text-white text-xs"></i>
                        </button>
                        <button class="download-btn w-8 h-8 rounded-full bg-black/60 backdrop-blur-sm flex items-center justify-center hover:bg-purple-600 transition-all duration-300 transform hover:scale-110"
                                data-video-url="{{ $vid['video'] ?? '' }}">
                            <i class="fas fa-download text-white text-xs"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Video Info -->
                <div class="p-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400 truncate flex-1">
                            <i class="fas fa-tag mr-1 text-[8px]"></i>
                            {{ Str::limit($vid['title'] ?? $vid['tags'] ?? 'Video', 40) }}
                        </span>
                        <div class="flex items-center gap-1 text-purple-400">
                            <i class="fas fa-download text-[10px]"></i>
                            <span class="text-xs">{{ $vid['views'] ?? rand(100, 9999) }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Hidden Video Element for Hover Preview -->
                <video class="video-element hidden" loop muted preload="metadata">
                    <source src="{{ $vid['video'] ?? '' }}" type="video/mp4">
                </video>
            </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        @if(isset($total_pages) && $total_pages > 1)
        <div class="flex justify-center items-center gap-2 mt-10 flex-wrap">
            @php
                $currentPage = isset($page) ? $page : 1;
                $totalPages = isset($total_pages) ? $total_pages : 1;
                $prompt = isset($prompt) ? $prompt : '';
                $type = isset($type) ? $type : 'landscape';
                $per_page = isset($per_page) ? $per_page : 12;
                $start = max(1, $currentPage - 2);
                $end = min($totalPages, $currentPage + 2);
            @endphp
            
            @if($currentPage > 1)
            <a href="{{ route('dfy.videos', ['prompt' => $prompt, 'type' => $type, 'page' => 1, 'per_page' => $per_page]) }}" 
               class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-800/50 text-gray-400 hover:bg-purple-600 hover:text-white transition-all duration-300">
                «
            </a>
            <a href="{{ route('dfy.videos', ['prompt' => $prompt, 'type' => $type, 'page' => $currentPage - 1, 'per_page' => $per_page]) }}" 
               class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-800/50 text-gray-400 hover:bg-purple-600 hover:text-white transition-all duration-300">
                ‹
            </a>
            @endif
            
            @for($i = $start; $i <= $end; $i++)
            <a href="{{ route('dfy.videos', ['prompt' => $prompt, 'type' => $type, 'page' => $i, 'per_page' => $per_page]) }}" 
               class="w-10 h-10 flex items-center justify-center rounded-lg transition-all duration-300 {{ $currentPage == $i ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'bg-gray-800/50 text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                {{ $i }}
            </a>
            @endfor
            
            @if($currentPage < $totalPages)
            <a href="{{ route('dfy.videos', ['prompt' => $prompt, 'type' => $type, 'page' => $currentPage + 1, 'per_page' => $per_page]) }}" 
               class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-800/50 text-gray-400 hover:bg-purple-600 hover:text-white transition-all duration-300">
                ›
            </a>
            <a href="{{ route('dfy.videos', ['prompt' => $prompt, 'type' => $type, 'page' => $totalPages, 'per_page' => $per_page]) }}" 
               class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-800/50 text-gray-400 hover:bg-purple-600 hover:text-white transition-all duration-300">
                »
            </a>
            @endif
        </div>
        @endif
        @endif
    </div>
</div>

<!-- Video Preview Modal -->
<div id="videoModal" class="fixed inset-0 bg-black/95 backdrop-blur-xl z-50 hidden items-center justify-center p-4 transition-all duration-300">
    <div class="relative w-full max-w-4xl bg-gradient-to-br from-gray-900 to-black rounded-2xl border border-purple-500/30 overflow-hidden shadow-2xl shadow-purple-500/20">
        
        <!-- Modal Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-700 bg-gradient-to-r from-gray-800/50 to-gray-900/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                    <i class="fas fa-video text-white"></i>
                </div>
                <h3 class="text-white font-semibold">Video Preview</h3>
            </div>
            <button onclick="closeVideoModal()" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-4">
            <video id="modalVideo" class="w-full rounded-lg" controls playsinline>
                <source src="" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        
        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-gray-700 bg-gradient-to-r from-gray-800/50 to-gray-900/50 flex justify-end">
            <button id="modalDownloadBtn" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-lg text-white font-medium transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-pink-500/25">
                <i class="fas fa-download"></i>
                Download Video
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #1a1a1a;
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(to bottom, #8b5cf6, #ec4899);
        border-radius: 4px;
    }
    
    .video-card {
        cursor: pointer;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('search-form');
    if (!form) return;
    
    // ============================================
    // KEYWORD PILLS - WITH SELECTED CHIP MARKER
    // ============================================
    
    const keywordBtns = document.querySelectorAll('.keyword-btn');
    const keywordSuggestionBtns = document.querySelectorAll('.keyword-suggestion');
    
    function setActiveKeyword(activeKeyword) {
        keywordBtns.forEach(btn => {
            const btnKeyword = btn.dataset.keyword;
            if (btnKeyword && btnKeyword.toLowerCase() === activeKeyword.toLowerCase()) {
                btn.classList.remove('bg-gray-800/50', 'text-gray-300', 'hover:bg-gray-700');
                btn.classList.add('bg-gradient-to-r', 'from-purple-600', 'to-pink-600', 'text-white', 'shadow-lg');
            } else {
                btn.classList.remove('bg-gradient-to-r', 'from-purple-600', 'to-pink-600', 'text-white', 'shadow-lg');
                btn.classList.add('bg-gray-800/50', 'text-gray-300', 'hover:bg-gray-700');
            }
        });
    }
    
    keywordBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const keyword = this.dataset.keyword;
            const promptInput = document.querySelector('input[name="prompt"]');
            if (promptInput) {
                promptInput.value = keyword;
            }
            setActiveKeyword(keyword);
            const pageInput = document.querySelector('input[name="page"]');
            if (pageInput) pageInput.value = 1;
            form.submit();
        });
    });
    
    keywordSuggestionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const keyword = this.dataset.keyword;
            const promptInput = document.querySelector('input[name="prompt"]');
            if (promptInput) {
                promptInput.value = keyword;
            }
            const pageInput = document.querySelector('input[name="page"]');
            if (pageInput) pageInput.value = 1;
            form.submit();
        });
    });
    
    const currentPrompt = document.querySelector('input[name="prompt"]');
    if (currentPrompt && currentPrompt.value) {
        setActiveKeyword(currentPrompt.value);
    }
    
    // ============================================
    // ASPECT RATIO SELECTION
    // ============================================
    
    document.querySelectorAll('.ratio-option').forEach(option => {
        const radio = option.querySelector('input[type="radio"]');
        if (radio && radio.checked) {
            option.classList.add('active');
        }
    });
    
    // ============================================
    // FORM SUBMISSION LOADING STATE
    // ============================================
    
    form.addEventListener('submit', function() {
        const searchBtn = document.getElementById('search-button');
        if (searchBtn) {
            searchBtn.disabled = true;
            searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
        }
    });
    
    // ============================================
    // VIDEO HOVER PREVIEW
    // ============================================
    
    document.querySelectorAll('.video-card').forEach((card) => {
        const img = card.querySelector('.video-thumbnail');
        const video = card.querySelector('.video-element');
        
        if (!video || !img) return;
        
        const source = video.querySelector('source');
        const videoUrl = source ? source.src : video.src;
        
        if (!videoUrl) return;
        
        video.preload = 'metadata';
        video.load();
        
        let hoverTimeout = null;
        let playPromise = null;
        
        card.addEventListener('mouseenter', () => {
            if (hoverTimeout) clearTimeout(hoverTimeout);
            
            hoverTimeout = setTimeout(() => {
                img.style.display = 'none';
                video.style.display = 'block';
                video.currentTime = 0;
                playPromise = video.play();
                if (playPromise) {
                    playPromise.catch(error => {
                        if (error.name !== 'AbortError') {
                            console.log('Play error:', error.name);
                        }
                    });
                }
            }, 50);
        });
        
        card.addEventListener('mouseleave', () => {
            if (hoverTimeout) {
                clearTimeout(hoverTimeout);
                hoverTimeout = null;
            }
            
            if (video) {
                video.pause();
                video.currentTime = 0;
                video.style.display = 'none';
            }
            img.style.display = 'block';
            if (playPromise) playPromise = null;
        });
    });
    
    // ============================================
    // VIDEO MODAL FUNCTIONALITY
    // ============================================
    
    let currentVideoUrl = '';
    
    window.openVideoModal = function(videoUrl) {
        currentVideoUrl = videoUrl;
        const modal = document.getElementById('videoModal');
        const modalVideo = document.getElementById('modalVideo');
        const sourceElement = modalVideo.querySelector('source');
        if (sourceElement) {
            sourceElement.src = videoUrl;
            modalVideo.load();
        }
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modalVideo.play().catch(e => console.log('Play error:', e));
        }
    }
    
    window.closeVideoModal = function() {
        const modal = document.getElementById('videoModal');
        const modalVideo = document.getElementById('modalVideo');
        if (modalVideo) {
            modalVideo.pause();
            modalVideo.currentTime = 0;
        }
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
    
    // View button handlers
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const videoSrc = this.dataset.videoSrc;
            if (videoSrc) {
                openVideoModal(videoSrc);
            }
        });
    });
    
    // Download function
    async function downloadVideo(url) {
        try {
            const response = await fetch(url);
            const blob = await response.blob();
            
            const timestamp = Date.now();
            const randomString = Math.random().toString(36).substring(2, 8);
            const filename = `video_${timestamp}_${randomString}.mp4`;
            
            const blobUrl = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = blobUrl;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(blobUrl);
            
            Swal.fire({
                title: 'Download Started!',
                text: 'Your video is being downloaded.',
                icon: 'success',
                confirmButtonColor: '#8b5cf6',
                background: '#1a1a1a',
                color: '#fff',
                timer: 2000,
                showConfirmButton: false
            });
        } catch (error) {
            console.error('Download failed:', error);
            Swal.fire({
                title: 'Download Failed',
                text: 'Please try again.',
                icon: 'error',
                confirmButtonColor: '#8b5cf6',
                background: '#1a1a1a',
                color: '#fff'
            });
        }
    }
    
    // Download button handlers
    document.querySelectorAll('.download-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            const videoUrl = this.dataset.videoUrl;
            if (videoUrl) {
                await downloadVideo(videoUrl);
            }
        });
    });
    
    // Modal download button
    const modalDownloadBtn = document.getElementById('modalDownloadBtn');
    if (modalDownloadBtn) {
        modalDownloadBtn.addEventListener('click', async function() {
            if (currentVideoUrl) {
                await downloadVideo(currentVideoUrl);
            }
        });
    }
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeVideoModal();
        }
    });
    
    // Close modal on outside click
    document.getElementById('videoModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeVideoModal();
        }
    });
});
</script>
@endsection