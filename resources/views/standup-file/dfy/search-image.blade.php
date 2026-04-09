@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="{{ asset('custom/css/dfy.css') }}">
@endsection

@section('content')
<div class="dfy-container">
    <div class="dfy-header">
        <h1 class="dfy-title">{{ $page_title ?? 'Search Images' }}</h1>
        <p class="dfy-subtitle">Discover stunning royalty-free images instantly</p>
    </div>

    <!-- Search Form -->
    <div class="search-form">
        <form id="search-form" action="{{ route('dfy.images') }}" method="GET">
            <div class="form-group">
                <label class="form-label">Keyword</label>
                <input type="text" class="form-control" name="prompt" 
                    value="{{ $prompt ?? '' }}" 
                    placeholder="e.g., nature, technology, business" 
                    maxlength="100" required>
            </div>

            <div class="form-group">
                <label class="form-label">Popular Keywords</label>
                <div class="keyword-pills">
                    @php
                    $popularKeywords = ['Nature', 'Animals', 'Travel', 'Food', 'Technology', 'Business', 'Sports', 'Music', 'Art', 'Health'];
                    @endphp
                    @foreach($popularKeywords as $keyword)
                    <button type="button" class="keyword-btn" data-keyword="{{ $keyword }}">
                        {{ $keyword }}
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Orientation</label>
                <div class="aspect-ratio-selector">
                    <label class="ratio-option {{ (!isset($orientation) || $orientation == 'all') ? 'active' : '' }}">
                        <input type="radio" name="orientation" value="all" style="display: none;" 
                            {{ (!isset($orientation) || $orientation == 'all') ? 'checked' : '' }}>
                        <div class="ratio-icon"><i class="fas fa-expand"></i></div>
                        <div class="ratio-info">
                            <span class="ratio-name">All</span>
                            <span class="ratio-dimension">Any</span>
                        </div>
                        <div class="ratio-check"><i class="fas fa-check-circle"></i></div>
                    </label>
                    <label class="ratio-option {{ (isset($orientation) && $orientation == 'horizontal') ? 'active' : '' }}">
                        <input type="radio" name="orientation" value="horizontal" style="display: none;"
                            {{ (isset($orientation) && $orientation == 'horizontal') ? 'checked' : '' }}>
                        <div class="ratio-icon"><i class="fas fa-arrows-alt-h"></i></div>
                        <div class="ratio-info">
                            <span class="ratio-name">Landscape</span>
                            <span class="ratio-dimension">16:9</span>
                        </div>
                        <div class="ratio-check"><i class="fas fa-check-circle"></i></div>
                    </label>
                    <label class="ratio-option {{ (isset($orientation) && $orientation == 'vertical') ? 'active' : '' }}">
                        <input type="radio" name="orientation" value="vertical" style="display: none;"
                            {{ (isset($orientation) && $orientation == 'vertical') ? 'checked' : '' }}>
                        <div class="ratio-icon"><i class="fas fa-arrows-alt-v"></i></div>
                        <div class="ratio-info">
                            <span class="ratio-name">Portrait</span>
                            <span class="ratio-dimension">9:16</span>
                        </div>
                        <div class="ratio-check"><i class="fas fa-check-circle"></i></div>
                    </label>
                </div>
            </div>

            <input type="hidden" name="image_type" value="all">
            <input type="hidden" name="page" value="1">
            <input type="hidden" name="per_page" value="20">

            <div style="text-align: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary-action" id="search-button">
                    <i class="fas fa-search"></i> Search Images
                </button>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-images"></i> Results
        </h3>
    </div>

    @if(empty($images['images']) || count($images['images']) == 0)
        <div class="empty-state">
            <i class="fas fa-image"></i>
            <h4>No Images Yet</h4>
            <p>Enter a keyword above and click search to start discovering images</p>
            <div class="hint">
                Try one of these popular keywords:
                <div class="keyword-pills" style="margin-top: 1rem; justify-content: center;">
                    @foreach(array_slice($popularKeywords, 0, 6) as $keyword)
                    <button type="button" class="keyword-btn" data-keyword="{{ $keyword }}">
                        {{ $keyword }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="results-grid">
            @foreach($images['images'] as $image)
            <div class="result-card">
                <img src="{{ $image['webformat'] }}" alt="{{ $image['tags'] ?? 'Image' }}" class="result-image" loading="lazy">
                <div class="result-overlay">
                    <button class="result-action view-btn" data-src="{{ $image['large'] ?? $image['largeImageURL'] ?? $image['fullhd'] ?? $image['webformat'] }}">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="result-action download-btn" data-url="{{ $image['large'] ?? $image['largeImageURL'] ?? $image['fullhd'] ?? $image['webformat'] }}">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if(isset($images['total_pages']) && $images['total_pages'] > 1)
        <div class="pagination">
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
            <a href="{{ route('dfy.images', ['prompt' => $prompt, 'orientation' => $orientation, 'page' => 1, 'per_page' => $per_page]) }}" class="page-link">«</a>
            <a href="{{ route('dfy.images', ['prompt' => $prompt, 'orientation' => $orientation, 'page' => $currentPage - 1, 'per_page' => $per_page]) }}" class="page-link">‹</a>
            @endif
            
            @for($i = $start; $i <= $end; $i++)
            <a href="{{ route('dfy.images', ['prompt' => $prompt, 'orientation' => $orientation, 'page' => $i, 'per_page' => $per_page]) }}" 
               class="page-link {{ $currentPage == $i ? 'active' : '' }}">
                {{ $i }}
            </a>
            @endfor
            
            @if($currentPage < $totalPages)
            <a href="{{ route('dfy.images', ['prompt' => $prompt, 'orientation' => $orientation, 'page' => $currentPage + 1, 'per_page' => $per_page]) }}" class="page-link">›</a>
            <a href="{{ route('dfy.images', ['prompt' => $prompt, 'orientation' => $orientation, 'page' => $totalPages, 'per_page' => $per_page]) }}" class="page-link">»</a>
            @endif
        </div>
        @endif
    @endif
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" class="img-fluid" src="" alt="Preview" style="max-height: 70vh; max-width: 100%;">
            </div>
            <div class="modal-footer">
                <button id="modalDownloadBtn" class="btn-primary-action" data-url="#">
                    <i class="fas fa-download"></i> Download Image
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const APP_NAME = 'dfy'; // Change this to your app name
    const form = document.getElementById('search-form');
    if (!form) return;

    // ============================================
    // KEYWORD PILLS - WITH SELECTED CHIP MARKER
    // ============================================
    
    const keywordBtns = document.querySelectorAll('.keyword-btn');

    function setActiveKeyword(activeKeyword) {
        keywordBtns.forEach(btn => {
            if (btn.dataset.keyword.toLowerCase() === activeKeyword.toLowerCase()) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
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
    
    const currentPrompt = document.querySelector('input[name="prompt"]');
    if (currentPrompt && currentPrompt.value) {
        setActiveKeyword(currentPrompt.value);
    }

    // ============================================
    // ORIENTATION SELECTION
    // ============================================
    
    document.querySelectorAll('.ratio-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelectorAll('.ratio-option').forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                const pageInput = document.querySelector('input[name="page"]');
                if (pageInput) pageInput.value = 1;
                form.submit();
            }
        });
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
    // DOWNLOAD FUNCTION - WORKS WITHOUT OPENING NEW WINDOW
    // ============================================
    
    function generateRandomFilename(extension = 'jpg') {
        const timestamp = Date.now();
        const randomString = Math.random().toString(36).substring(2, 10);
        const randomNum = Math.floor(Math.random() * 10000);
        
        // Options for filename format (choose one):
        
        // Option 1: appname_timestamp_random.jpg
        return `${APP_NAME}_${timestamp}_${randomString}.${extension}`;
    }

    function getFileExtension(url, defaultExt = 'jpg') {
        try {
            // Try to get extension from URL
            const urlObj = new URL(url);
            const pathname = urlObj.pathname;
            const ext = pathname.split('.').pop().toLowerCase();
            
            // Valid image extensions
            const validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
            
            if (validExtensions.includes(ext)) {
                return ext;
            }
        } catch (e) {
            // Invalid URL
        }
        
        // Check content-type from response headers as fallback
        return defaultExt;
    }

    async function downloadImage(url) {
        try {
            // Show loading state on the button (optional)
            const downloadBtns = document.querySelectorAll('.download-btn, #modalDownloadBtn');
            let targetBtn = null;
            
            // Find which button was clicked
            for (const btn of downloadBtns) {
                if (btn === event?.target?.closest('.download-btn') || btn === event?.target?.closest('#modalDownloadBtn')) {
                    targetBtn = btn;
                    break;
                }
            }
            
            if (targetBtn) {
                const originalHtml = targetBtn.innerHTML;
                targetBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                targetBtn.disabled = true;
                
                // Fetch the image as a blob
                const response = await fetch(url);
                const blob = await response.blob();
                
                // Get file extension from blob type or URL
                let extension = 'jpg';
                if (blob.type === 'image/png') extension = 'png';
                else if (blob.type === 'image/jpeg') extension = 'jpg';
                else if (blob.type === 'image/gif') extension = 'gif';
                else if (blob.type === 'image/webp') extension = 'webp';
                
                // Generate random filename
                const filename = generateRandomFilename(extension);
                
                // Create blob URL and trigger download
                const blobUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Clean up
                window.URL.revokeObjectURL(blobUrl);
                
                // Restore button
                targetBtn.innerHTML = originalHtml;
                targetBtn.disabled = false;
            } else {
                // Fallback if button not found
                const response = await fetch(url);
                const blob = await response.blob();
                let extension = 'jpg';
                if (blob.type === 'image/png') extension = 'png';
                else if (blob.type === 'image/jpeg') extension = 'jpg';
                
                const filename = generateRandomFilename(extension);
                const blobUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(blobUrl);
            }
        } catch (error) {
            console.error('Download failed:', error);
            alert('Download failed. Please try again.');
            
            // Restore buttons
            const downloadBtns = document.querySelectorAll('.download-btn, #modalDownloadBtn');
            downloadBtns.forEach(btn => {
                if (btn.innerHTML.includes('fa-spinner')) {
                    btn.innerHTML = '<i class="fas fa-download"></i>';
                    btn.disabled = false;
                }
            });
        }
    }
    
    function getFilenameFromUrl(url, defaultName = 'image') {
        try {
            const urlObj = new URL(url);
            const pathname = urlObj.pathname;
            const filename = pathname.split('/').pop();
            if (filename && filename.includes('.')) {
                return filename;
            }
        } catch (e) {}
        return defaultName + '.jpg';
    }

    // ============================================
    // IMAGE MODAL FUNCTIONALITY
    // ============================================
    
    const imageModalElement = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const modalDownloadBtn = document.getElementById('modalDownloadBtn');
    let bootstrapModal = null;
    let currentImageUrl = '';
    
    if (imageModalElement && typeof bootstrap !== 'undefined') {
        bootstrapModal = new bootstrap.Modal(imageModalElement);
    }
    
    // Handle view buttons (open modal)
    document.addEventListener('click', (e) => {
        const viewBtn = e.target.closest('.view-btn');
        if (viewBtn) {
            e.preventDefault();
            e.stopPropagation();
            currentImageUrl = viewBtn.dataset.src;
            if (currentImageUrl && modalImage && modalDownloadBtn) {
                modalImage.src = currentImageUrl;
                modalDownloadBtn.setAttribute('data-url', currentImageUrl);
                if (bootstrapModal) bootstrapModal.show();
            }
        }
    });
    
    // Handle download buttons (direct download without opening window)
    document.addEventListener('click', async (e) => {
        const downloadBtn = e.target.closest('.download-btn');
        if (downloadBtn) {
            e.preventDefault();
            e.stopPropagation();
            const imageUrl = downloadBtn.dataset.url;
            if (imageUrl) {
                const filename = getFilenameFromUrl(imageUrl, 'dfy-image');
                await downloadImage(imageUrl, filename);
            }
        }
    });
    
    // Handle modal download button
    if (modalDownloadBtn) {
        modalDownloadBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            const imageUrl = this.getAttribute('data-url');
            if (imageUrl && imageUrl !== '#') {
                const filename = getFilenameFromUrl(imageUrl, 'dfy-image');
                await downloadImage(imageUrl, filename);
            }
        });
    }
    
    // Reset modal image when closed
    if (imageModalElement && modalImage) {
        imageModalElement.addEventListener('hidden.bs.modal', function() {
            modalImage.src = '';
        });
    }
    
    // ============================================
    // PREVENT FORM RESUBMIT ON PAGINATION CLICK
    // ============================================
    
    document.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', function() {
            const searchBtn = document.getElementById('search-button');
            if (searchBtn) {
                searchBtn.disabled = false;
                searchBtn.innerHTML = '<i class="fas fa-search"></i> Search Images';
            }
        });
    });
});
</script>
@endsection