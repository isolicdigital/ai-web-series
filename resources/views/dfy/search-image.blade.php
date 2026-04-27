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
                <i class="fas fa-images text-white text-2xl"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent mb-4">
                {{ $page_title ?? 'Search Images' }}
            </h1>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Discover stunning royalty-free images instantly
            </p>
        </div>
        
        <!-- Search Form -->
        <div class="max-w-4xl mx-auto mb-12">
            <form id="search-form" action="{{ route('dfy.images') }}" method="GET" class="space-y-6">
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
                           placeholder="e.g., nature, technology, business, mountains, ocean..." 
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
                        $popularKeywords = ['Nature', 'Animals', 'Travel', 'Food', 'Technology', 'Business', 'Sports', 'Music', 'Art', 'Health', 'Mountains', 'Ocean', 'City', 'Space', 'Love'];
                        @endphp
                        @foreach($popularKeywords as $keyword)
                        <button type="button" 
                                class="keyword-btn px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 {{ isset($prompt) && $prompt == $keyword ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'bg-gray-800/50 text-gray-300 hover:bg-gray-700 border border-gray-700 hover:border-purple-500/50' }}"
                                data-keyword="{{ $keyword }}">
                            {{ $keyword }}
                        </button>
                        @endforeach
                    </div>
                </div>
                
                <!-- Orientation Selector -->
                <div>
                    <label class="block text-white font-semibold mb-3 flex items-center gap-2">
                        <i class="fas fa-arrows-alt text-purple-400 text-sm"></i>
                        Orientation
                    </label>
                    <div class="flex flex-wrap gap-4">
                        <!-- All -->
                        <label class="ratio-option cursor-pointer group">
                            <input type="radio" name="orientation" value="all" class="hidden peer" 
                                {{ (!isset($orientation) || $orientation == 'all') ? 'checked' : '' }}>
                            <div class="flex items-center gap-3 px-5 py-3 rounded-xl bg-gray-800/50 border-2 border-gray-700 peer-checked:border-purple-500 peer-checked:bg-purple-500/20 transition-all duration-300 hover:border-purple-500/50">
                                <div class="w-10 h-10 rounded-lg bg-gray-700/50 flex items-center justify-center">
                                    <i class="fas fa-expand text-purple-400"></i>
                                </div>
                                <div>
                                    <div class="text-white font-medium text-sm">All</div>
                                    <div class="text-gray-500 text-xs">Any orientation</div>
                                </div>
                                <div class="w-5 h-5 rounded-full bg-purple-500 scale-0 peer-checked:scale-100 transition-all duration-200 flex items-center justify-center shadow-lg">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                            </div>
                        </label>
                        
                        <!-- Landscape -->
                        <label class="ratio-option cursor-pointer group">
                            <input type="radio" name="orientation" value="horizontal" class="hidden peer"
                                {{ (isset($orientation) && $orientation == 'horizontal') ? 'checked' : '' }}>
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
                            <input type="radio" name="orientation" value="vertical" class="hidden peer"
                                {{ (isset($orientation) && $orientation == 'vertical') ? 'checked' : '' }}>
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
                    </div>
                </div>
                
                <input type="hidden" name="image_type" value="all">
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="per_page" value="20">
                
                <div class="text-center pt-4">
                    <button type="submit" 
                            id="search-button"
                            class="inline-flex items-center gap-3 px-8 py-3.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 shadow-lg hover:shadow-pink-500/25 transform hover:scale-105">
                        <i class="fas fa-search"></i>
                        <span>Search Images</span>
                    </button>
                </div>
            </form>
        </div>

        
        <!-- Results Grid -->
        @if(empty($images['images']) || count($images['images']) == 0)
        <div class="text-center py-20">
            <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-gradient-to-br from-purple-600/20 to-pink-600/20 flex items-center justify-center">
                <i class="fas fa-image text-purple-400 text-5xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-white mb-3">No Images Yet</h3>
            <p class="text-gray-400 mb-6 max-w-md mx-auto">Enter a keyword above and click search to start discovering images</p>
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
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($images['images'] as $image)
            <div class="group relative bg-gradient-to-br from-gray-900/80 to-gray-800/40 backdrop-blur-lg rounded-xl border border-gray-700/50 hover:border-purple-500/50 transition-all duration-500 overflow-hidden hover:shadow-2xl hover:shadow-purple-500/10 hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden">
                    <img src="{{ $image['webformat'] }}" 
                         alt="{{ $image['tags'] ?? 'Image' }}" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    
                    <!-- Overlay Actions -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center gap-3">
                        <button class="view-btn w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center hover:bg-purple-600 transition-all duration-300 transform hover:scale-110"
                                data-src="{{ $image['large'] ?? $image['largeImageURL'] ?? $image['fullhd'] ?? $image['webformat'] }}">
                            <i class="fas fa-eye text-white text-sm"></i>
                        </button>
                        <button class="download-btn w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center hover:bg-purple-600 transition-all duration-300 transform hover:scale-110"
                                data-url="{{ $image['large'] ?? $image['largeImageURL'] ?? $image['fullhd'] ?? $image['webformat'] }}">
                            <i class="fas fa-download text-white text-sm"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Image Info -->
                <div class="p-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 truncate flex-1">
                            <i class="fas fa-tag mr-1 text-[8px]"></i>
                            {{ Str::limit($image['tags'] ?? 'Image', 30) }}
                        </span>
                        <div class="flex items-center gap-1 text-purple-400">
                            <i class="fas fa-download text-[10px]"></i>
                            <span class="text-xs">{{ rand(100, 9999) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        @if(isset($images['total_pages']) && $images['total_pages'] > 1)
        <div class="flex justify-center items-center gap-2 mt-10 flex-wrap">
            @php
                $currentPage = isset($page) ? $page : 1;
                $totalPages = $images['total_pages'] ?? 1;
                $prompt = isset($prompt) ? $prompt : '';
                $orientation = isset($orientation) ? $orientation : 'all';
                $per_page = isset($per_page) ? $per_page : 20;
                $start = max(1, $currentPage - 2);
                $end = min($totalPages, $currentPage + 2);
            @endphp
            
            @if($currentPage > 1)
            <a href="{{ route('dfy.images', ['prompt' => $prompt, 'orientation' => $orientation, 'page' => 1, 'per_page' => $per_page]) }}" 
               class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-800/50 text-gray-400 hover:bg-purple-600 hover:text-white transition-all duration-300">
                «
            </a>
            <a href="{{ route('dfy.images', ['prompt' => $prompt, 'orientation' => $orientation, 'page' => $currentPage - 1, 'per_page' => $per_page]) }}" 
               class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-800/50 text-gray-400 hover:bg-purple-600 hover:text-white transition-all duration-300">
                ‹
            </a>
            @endif
            
            @for($i = $start; $i <= $end; $i++)
            <a href="{{ route('dfy.images', ['prompt' => $prompt, 'orientation' => $orientation, 'page' => $i, 'per_page' => $per_page]) }}" 
               class="w-10 h-10 flex items-center justify-center rounded-lg transition-all duration-300 {{ $currentPage == $i ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'bg-gray-800/50 text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                {{ $i }}
            </a>
            @endfor
            
            @if($currentPage < $totalPages)
            <a href="{{ route('dfy.images', ['prompt' => $prompt, 'orientation' => $orientation, 'page' => $currentPage + 1, 'per_page' => $per_page]) }}" 
               class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-800/50 text-gray-400 hover:bg-purple-600 hover:text-white transition-all duration-300">
                ›
            </a>
            <a href="{{ route('dfy.images', ['prompt' => $prompt, 'orientation' => $orientation, 'page' => $totalPages, 'per_page' => $per_page]) }}" 
               class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-800/50 text-gray-400 hover:bg-purple-600 hover:text-white transition-all duration-300">
                »
            </a>
            @endif
        </div>
        @endif
        @endif
    </div>
</div>

<!-- Image Preview Modal -->
<div id="imageModal" class="fixed inset-0 bg-black/95 backdrop-blur-xl z-50 hidden items-center justify-center p-4 transition-all duration-300">
    <div class="relative w-full max-w-4xl bg-gradient-to-br from-gray-900 to-black rounded-2xl border border-purple-500/30 overflow-hidden shadow-2xl shadow-purple-500/20">
        
        <!-- Modal Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-700 bg-gradient-to-r from-gray-800/50 to-gray-900/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                    <i class="fas fa-eye text-white"></i>
                </div>
                <h3 class="text-white font-semibold">Image Preview</h3>
            </div>
            <button onclick="closeImageModal()" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6">
            <div class="bg-gray-900/50 rounded-xl p-4">
                <img id="modalImage" class="w-full h-auto rounded-lg" src="" alt="Preview" style="max-height: 60vh; object-fit: contain;">
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-gray-700 bg-gradient-to-r from-gray-800/50 to-gray-900/50 flex justify-end">
            <button id="modalDownloadBtn" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-lg text-white font-medium transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-pink-500/25">
                <i class="fas fa-download"></i>
                Download Image
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
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const APP_NAME = 'dfy';
    const form = document.getElementById('search-form');
    if (!form) return;
    
    // Keyword buttons
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
    
    // Orientation selection
    document.querySelectorAll('.ratio-option').forEach(option => {
        const radio = option.querySelector('input[type="radio"]');
        if (radio && radio.checked) {
            option.classList.add('active');
        }
    });
    
    // Form submission loading state
    form.addEventListener('submit', function() {
        const searchBtn = document.getElementById('search-button');
        if (searchBtn) {
            searchBtn.disabled = true;
            searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
        }
    });
    
    // Download function
    async function downloadImage(url) {
        try {
            const response = await fetch(url);
            const blob = await response.blob();
            
            let extension = 'jpg';
            if (blob.type === 'image/png') extension = 'png';
            else if (blob.type === 'image/jpeg') extension = 'jpg';
            else if (blob.type === 'image/gif') extension = 'gif';
            else if (blob.type === 'image/webp') extension = 'webp';
            
            const timestamp = Date.now();
            const randomString = Math.random().toString(36).substring(2, 8);
            const filename = `${APP_NAME}_${timestamp}_${randomString}.${extension}`;
            
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
                text: 'Your image is being downloaded.',
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
    
    // Image Modal
    let currentImageUrl = '';
    
    window.openImageModal = function(imageUrl) {
        currentImageUrl = imageUrl;
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        if (modalImage) modalImage.src = imageUrl;
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }
    
    window.closeImageModal = function() {
        const modal = document.getElementById('imageModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        const modalImage = document.getElementById('modalImage');
        if (modalImage) modalImage.src = '';
    }
    
    // View button handlers
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const imageUrl = this.dataset.src;
            if (imageUrl) {
                openImageModal(imageUrl);
            }
        });
    });
    
    // Download button handlers
    document.querySelectorAll('.download-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            const imageUrl = this.dataset.url;
            if (imageUrl) {
                await downloadImage(imageUrl);
            }
        });
    });
    
    // Modal download button
    const modalDownloadBtn = document.getElementById('modalDownloadBtn');
    if (modalDownloadBtn) {
        modalDownloadBtn.addEventListener('click', async function() {
            if (currentImageUrl) {
                await downloadImage(currentImageUrl);
            }
        });
    }
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });
    
    // Close modal on outside click
    document.getElementById('imageModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });
});
</script>
@endsection