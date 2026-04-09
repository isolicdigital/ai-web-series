{{-- resources/views/dfy/search-video.blade.php --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="{{ asset('custom/css/dfy.css') }}">
@endsection

@section('content')
<div class="dfy-container">
    <div class="dfy-header">
        <h1 class="dfy-title">{{ $page_title ?? 'Search Videos' }}</h1>
        <p class="dfy-subtitle">Turn any keyword into stunning videos instantly</p>
    </div>

    <!-- Search Form -->
    <div class="search-form">
        <form id="search-form" action="{{ route('dfy.videos') }}" method="GET">
            <div class="form-group">
                <label class="form-label">Keyword</label>
                <input type="text" class="form-control" name="prompt" 
                    value="{{ $prompt ?? '' }}" 
                    placeholder="e.g., sunset beach, mountain landscape" 
                    maxlength="100" required>
            </div>

            <div class="form-group">
                <label class="form-label">Popular Keywords</label>
                <div class="keyword-pills">
                    @php
                    $popularKeywords = [
                        'Sunset Beach', 'Mountain Landscape', 'City Skyline', 
                        'Forest Nature', 'Abstract Art', 'Cosmic Space',
                        'Underwater Ocean', 'Winter Snow', 'Summer Tropical',
                        'Desert Sand', 'Wildlife Animals', 'Urban Architecture'
                    ];
                    @endphp
                    @foreach($popularKeywords as $keyword)
                    <button type="button" class="keyword-btn" data-keyword="{{ $keyword }}">
                        {{ $keyword }}
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Aspect Ratio</label>
                <div class="aspect-ratio-selector">
                    <label class="ratio-option {{ (!isset($type) || $type == 'landscape') ? 'active' : '' }}">
                        <input type="radio" name="type" value="landscape" style="display: none;" 
                            {{ (!isset($type) || $type == 'landscape') ? 'checked' : '' }}>
                        <div class="ratio-icon"><i class="fas fa-arrows-alt-h"></i></div>
                        <div class="ratio-info">
                            <span class="ratio-name">Landscape</span>
                            <span class="ratio-dimension">16:9</span>
                        </div>
                        <div class="ratio-check"><i class="fas fa-check-circle"></i></div>
                    </label>
                    <label class="ratio-option {{ (isset($type) && $type == 'portrait') ? 'active' : '' }}">
                        <input type="radio" name="type" value="portrait" style="display: none;"
                            {{ (isset($type) && $type == 'portrait') ? 'checked' : '' }}>
                        <div class="ratio-icon"><i class="fas fa-arrows-alt-v"></i></div>
                        <div class="ratio-info">
                            <span class="ratio-name">Portrait</span>
                            <span class="ratio-dimension">9:16</span>
                        </div>
                        <div class="ratio-check"><i class="fas fa-check-circle"></i></div>
                    </label>
                    <label class="ratio-option {{ (isset($type) && $type == 'square') ? 'active' : '' }}">
                        <input type="radio" name="type" value="square" style="display: none;"
                            {{ (isset($type) && $type == 'square') ? 'checked' : '' }}>
                        <div class="ratio-icon"><i class="fas fa-square"></i></div>
                        <div class="ratio-info">
                            <span class="ratio-name">Square</span>
                            <span class="ratio-dimension">1:1</span>
                        </div>
                        <div class="ratio-check"><i class="fas fa-check-circle"></i></div>
                    </label>
                </div>
            </div>

            <input type="hidden" name="page" value="1">
            <input type="hidden" name="per_page" value="12">

            <div style="text-align: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary-action" id="search-button">
                    <i class="fas fa-search"></i> Search Videos
                </button>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-video"></i> Results
        </h3>
    </div>

    @if(empty($videos) || count($videos) == 0)
    <div class="empty-state">
        <i class="fas fa-video"></i>
        <h4>No Videos Yet</h4>
        <p>Enter a keyword above and click search to start discovering videos</p>
        <div class="keyword-pills" style="justify-content: center; margin-top: 1rem;">
            @foreach(array_slice($popularKeywords, 0, 6) as $keyword)
            <button type="button" class="keyword-btn" data-keyword="{{ $keyword }}">
                {{ $keyword }}
            </button>
            @endforeach
        </div>
    </div>
    @else
    <div class="results-grid">
        @foreach($videos as $index => $vid)
        <div class="result-card video-card" data-video-index="{{ $index }}">
            <!-- Thumbnail image - using 'thumb' from Pexels/Pixabay -->
            <img class="video-thumbnail" 
                 src="{{ $vid['thumb'] ?? 'https://placehold.co/320x180/1a1a26/a0a0a0?text=No+Preview' }}" 
                 alt="Video thumbnail"
                 loading="lazy">
            
            <!-- Video element (hidden, plays on hover) - using 'video' from Pexels/Pixabay -->
            <video class="video-element" loop muted preload="metadata" style="display: none;">
                <source src="{{ $vid['video'] ?? '' }}" type="video/mp4">
            </video>
            
            <!-- Overlay with action buttons -->
            <div class="result-overlay">
                <button class="result-action view-btn" data-video-src="{{ $vid['video'] ?? '' }}" title="Preview">
                    <i class="fas fa-play"></i>
                </button>
                <button class="result-action download-btn" data-video-url="{{ $vid['video'] ?? '' }}" title="Download">
                    <i class="fas fa-download"></i>
                </button>
            </div>
            
            <!-- Optional: video info tooltip -->
            <div class="video-info-tooltip">
                <span class="duration">{{ $vid['duration'] ?? '' }}</span>
            </div>
        </div>
        @endforeach
    </div>

    @if(isset($total_pages) && $total_pages > 1)
    <div class="pagination">
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
        <a href="{{ route('dfy.videos', ['prompt' => $prompt, 'type' => $type, 'page' => 1, 'per_page' => $per_page]) }}" class="page-link">«</a>
        <a href="{{ route('dfy.videos', ['prompt' => $prompt, 'type' => $type, 'page' => $currentPage - 1, 'per_page' => $per_page]) }}" class="page-link">‹</a>
        @endif
        
        @for($i = $start; $i <= $end; $i++)
        <a href="{{ route('dfy.videos', ['prompt' => $prompt, 'type' => $type, 'page' => $i, 'per_page' => $per_page]) }}" 
           class="page-link {{ $currentPage == $i ? 'active' : '' }}">
            {{ $i }}
        </a>
        @endfor
        
        @if($currentPage < $totalPages)
        <a href="{{ route('dfy.videos', ['prompt' => $prompt, 'type' => $type, 'page' => $currentPage + 1, 'per_page' => $per_page]) }}" class="page-link">›</a>
        <a href="{{ route('dfy.videos', ['prompt' => $prompt, 'type' => $type, 'page' => $totalPages, 'per_page' => $per_page]) }}" class="page-link">»</a>
        @endif
    </div>
    @endif
    @endif
</div>

<!-- Video Preview Modal -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-video"></i> Video Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body p-0">
                <video id="modalVideo" class="w-100" controls playsinline>
                    <source src="" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('search-form');
    if (!form) return;

    // ============================================
    // KEYWORD PILLS - WITH SELECTED CHIP MARKER
    // ============================================
    
    const keywordBtns = document.querySelectorAll('.keyword-btn');
    
    // Function to update active pill
    function setActiveKeyword(activeKeyword) {
        keywordBtns.forEach(btn => {
            if (btn.dataset.keyword.toLowerCase() === activeKeyword.toLowerCase()) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }
    
    // Add click handlers to keyword pills
    keywordBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const keyword = this.dataset.keyword;
            const promptInput = document.querySelector('input[name="prompt"]');
            if (promptInput) {
                promptInput.value = keyword;
            }
            
            // Mark this pill as active
            setActiveKeyword(keyword);
            
            // Submit the form
            form.submit();
        });
    });
    
    // On page load, highlight the pill matching current search
    const currentPrompt = document.querySelector('input[name="prompt"]');
    if (currentPrompt && currentPrompt.value) {
        setActiveKeyword(currentPrompt.value);
    }

    // ============================================
    // ASPECT RATIO SELECTION
    // ============================================
    
    document.querySelectorAll('.ratio-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelectorAll('.ratio-option').forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    // ============================================
    // FORM SUBMISSION
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
    
    document.querySelectorAll('.result-card.video-card').forEach((card) => {
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
    // MODAL FUNCTIONALITY
    // ============================================
    
    const videoModalElement = document.getElementById('videoModal');
    const modalVideo = document.getElementById('modalVideo');
    let bootstrapModal = null;
    
    if (videoModalElement && typeof bootstrap !== 'undefined') {
        bootstrapModal = new bootstrap.Modal(videoModalElement);
    }
    
    document.addEventListener('click', (e) => {
        const viewBtn = e.target.closest('.view-btn');
        if (viewBtn) {
            e.preventDefault();
            e.stopPropagation();
            const videoSrc = viewBtn.dataset.videoSrc;
            if (videoSrc && modalVideo) {
                const sourceElement = modalVideo.querySelector('source');
                if (sourceElement) {
                    sourceElement.src = videoSrc;
                    modalVideo.load();
                    if (bootstrapModal) bootstrapModal.show();
                }
            }
        }
        
        const downloadBtn = e.target.closest('.download-btn');
        if (downloadBtn) {
            e.preventDefault();
            e.stopPropagation();
            const videoUrl = downloadBtn.dataset.videoUrl;
            if (videoUrl) {
                const link = document.createElement('a');
                link.href = videoUrl;
                link.download = 'video_' + Date.now() + '.mp4';
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
    });
    
    if (videoModalElement && modalVideo) {
        videoModalElement.addEventListener('hidden.bs.modal', function() {
            modalVideo.pause();
            modalVideo.currentTime = 0;
            const sourceElement = modalVideo.querySelector('source');
            if (sourceElement) sourceElement.src = '';
            modalVideo.load();
        });
    }
});
</script>
@endsection