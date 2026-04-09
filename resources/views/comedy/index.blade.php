@extends('layouts.app')

@section('title', 'Comedy Studio')

@section('css')
<link rel="stylesheet" href="{{ asset('custom/css/dashboard.css') }}?v=1.0">
@endsection

@section('hero')
<div class="relative min-h-[85vh] flex items-center justify-center overflow-hidden bg-gradient-to-br from-[#0a0a1a] via-[#1a0a2e] to-[#0a0a2a]">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_40%,rgba(255,45,149,0.15),transparent_50%),radial-gradient(circle_at_70%_60%,rgba(143,77,224,0.15),transparent_50%)] animate-[slowRotate_25s_linear_infinite]"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a1a] via-transparent to-transparent"></div>
    
    <div class="relative z-10 max-w-5xl mx-auto text-center px-6">
        <div class="inline-flex items-center gap-2 bg-white/5 backdrop-blur-sm rounded-full px-4 py-2 border border-white/10 mb-6 animate-float">
            <i class="fas fa-robot text-[#ff2d95] text-sm"></i>
            <span class="text-xs text-white/50">AI-Powered Series Studio</span>
            <i class="fas fa-magic text-[#8f4de0] text-xs"></i>
        </div>
        
        <h1 class="text-5xl md:text-7xl font-bold bg-gradient-to-r from-white via-white/90 to-white/60 bg-clip-text text-transparent mb-4">Create AI Video Series</h1>
        <p class="text-lg md:text-xl text-white/40 max-w-2xl mx-auto mb-10">Describe your episode — AI writes the script, generates scenes, and produces your video</p>
        
        <div class="flex flex-col md:flex-row gap-4 max-w-3xl mx-auto">
            <div class="relative flex-1">
                <select id="templateSelect" class="w-full rounded-2xl border border-white/10 bg-white/5 px-5 py-4 text-white focus:border-[#ff2d95] focus:outline-none focus:ring-4 focus:ring-[#ff2d95]/20 cursor-pointer appearance-none">
                    <option value="" class="bg-[#0a0a1a]">🎬 Select Series Template</option>
                    @foreach($categories as $category)
                        <optgroup label="{{ $category->name }}" class="bg-[#0a0a1a]">
                            @foreach($category->templates as $template)
                                <option value="{{ $template->id }}" class="bg-[#0a0a1a]">{{ $template->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-white/30 pointer-events-none"></i>
            </div>
            
            <input type="text" id="episodePrompt" placeholder="Describe your episode (e.g., 'A comedian's first open mic night goes horribly wrong')" class="flex-[2] rounded-2xl border border-white/10 bg-white/5 px-5 py-4 text-white placeholder-white/30 focus:border-[#ff2d95] focus:outline-none focus:ring-4 focus:ring-[#ff2d95]/20">
            
            <button id="generateEpisodeBtn" class="group relative rounded-2xl bg-gradient-to-r from-[#ff2d95] to-[#8f4de0] px-8 py-4 font-semibold text-white transition-all hover:shadow-2xl hover:shadow-[#ff2d95]/30 active:scale-95 overflow-hidden">
                <span class="relative z-10"><i class="fas fa-wand-magic mr-2"></i>Generate</span>
                <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-500 group-hover:translate-x-full"></div>
            </button>
        </div>
        
        <div id="loadingSpinner" class="hidden mt-8 text-center">
            <div class="inline-block w-10 h-10 border-3 border-[#ff2d95] border-t-transparent rounded-full animate-spin"></div>
            <p class="text-white/40 mt-3 text-sm">AI is writing your episode script...</p>
        </div>
        
        <div id="episodeDisplay" class="hidden mt-8 bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 p-6 text-left relative max-w-2xl mx-auto">
            <button id="dismissEpisodeBtn" class="absolute top-4 right-4 text-white/30 hover:text-[#ff2d95] transition-colors">
                <i class="fas fa-times"></i>
            </button>
            <i class="fas fa-quote-left text-[#ff2d95]/40 text-2xl mb-3"></i>
            <div id="episodeText" class="text-white/80 leading-relaxed text-base"></div>
            <div class="mt-4 pt-3 border-t border-white/10 flex justify-end">
                <button id="useScriptBtn" class="text-sm text-[#8f4de0] hover:text-[#ff2d95] transition"><i class="fas fa-arrow-right mr-1"></i>Use this script</button>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes slowRotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-8px); }
    }
    .animate-float {
        animation: float 4s ease-in-out infinite;
    }
</style>
@endsection

@section('content')
    @php 
        $planLevel = Auth::user()->plan_level;
        $isAdmin = Auth::user()->role === 'admin';
    @endphp
<!-- My Videos Section -->
@if(isset($recentVideos) && $recentVideos->count() > 0)
<div class="recent-section">
    <div class="section-header">
        <div class="header-left">
            <i class="fas fa-history"></i>
            <h2>My Videos</h2>
        </div>
        <a href="{{ route('comedy.my-videos') }}" class="view-all">View all →</a>
    </div>
    
    <div class="videos-grid">
        @foreach($recentVideos as $video)
        <div class="video-card" data-video-id="{{ $video->id }}" data-video-url="{{ $video->video_url }}" data-video-title="{{ $video->title }}" data-video-joke="{{ $video->joke ?? '' }}" data-template-name="{{ $video->template->name ?? 'Unknown' }}" data-created-at="{{ $video->created_at->format('M d, Y') }}">
            <div class="video-thumbnail">
                <img src="{{ asset($video->thumbnail_url ?? 'images/default-thumb.jpg') }}" alt="{{ $video->title }}">
                @if($video->processing_status === 'pending' || $video->processing_status === 'processing')
                <div class="processing-overlay">
                    <div class="processing-spinner"></div>
                    <span>Processing...</span>
                </div>
                @endif
                <div class="video-overlay">
                    @if($video->processing_status === 'completed')
                    <button class="view-btn" onclick="openVideoModal(this)">
                        <i class="fas fa-play"></i> View
                    </button>
                    @endif
                    <button class="delete-btn" onclick="deleteVideo({{ $video->id }})">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
            <div class="video-info">
                <h4>{{ $video->title }}</h4>
                <!-- <span class="views">{{ number_format($video->view_count) }} views</span> -->
                @if($video->processing_status === 'pending' || $video->processing_status === 'processing')
                <span class="status-badge status-{{ $video->processing_status }}">{{ ucfirst($video->processing_status) }}</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Categories with Templates -->
<div class="categories-container">
    @foreach($categories as $category)
    <div class="category-section">
        <div class="section-header">
            <div class="header-left">
                <i class="{{ $category->icon }}"></i>
                <h2>{{ $category->name }}</h2>
            </div>
        </div>
        
        <div class="scroll-wrapper">
            <button class="scroll-arrow left" onclick="scrollContainerLeft(this)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="templates-scroll">
                @foreach($category->templates as $template)
                <div class="template-card" data-template-id="{{ $template->id }}" data-template-name="{{ $template->name }}" data-manual="{{ $template->joke_template['manual'] ?? '' }}">
                    <div class="card-image" style="background-image: url('{{ asset($template->preview_image ?? $template->init_image) }}')"></div>
                    <div class="card-gradient"></div>
                    <div class="card-badges">
                        @if($isAdmin || $planLevel >= 2)
                            <i class="fas fa-star premium-badge" title="Pro Edition"></i>
                        @elseif($planLevel >= 1)
                            <i class="fas fa-infinity basic-badge" title="Unlimited Edition"></i>
                        @endif
                    </div>
                    <div class="card-overlay">
                        <button class="create-btn" onclick="openCreateModal(this)">
                            <i class="fas fa-plus-circle"></i> Create
                        </button>
                    </div>
                    <div class="card-content">
                        <h3>{{ $template->name }}</h3>
                        <p>{{ Str::limit($template->description, 60) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            <button class="scroll-arrow right" onclick="scrollContainerRight(this)">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
    @endforeach
</div>

<!-- Create Modal -->
<div class="create-modal" id="createModal" style="display: none;">
    <div class="create-modal-content">
        <div class="create-modal-header">
            <h3>Create Video</h3>
            <button class="create-modal-close" onclick="closeCreateModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="create-modal-body">
            <div class="create-template-info">
                <span id="createTemplateName"></span>
            </div>
            
            <div class="create-joke-option" style="display: block;">
                <label class="joke-option-label">
                    <input type="checkbox" id="writeOwnCheckbox" checked>
                    <span>Write your own punchline</span>
                </label>
            </div>
            
            <div id="savedJokesContainer" style="margin-bottom: 1rem; display: none;">
                <select id="savedJokesSelect" class="saved-jokes-select">
                    <option value="">-- Select a saved joke --</option>
                </select>
            </div>
            
            <div class="create-joke-input">
                <textarea id="jokeTextarea" rows="4" placeholder="Write your joke here..." class="custom-joke-textarea" maxlength="150"></textarea>
            </div>
            
            <button class="submit-create-btn" id="submitCreateBtn">
                <i class="fas fa-video"></i> Generate Video
            </button>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal" id="previewModal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview</h5>
                <button type="button" class="modal-close" onclick="closePreviewModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <video id="previewVideo" controls style="width: 100%; border-radius: 8px;">
                    <source id="previewVideoSource" src="" type="video/mp4">
                </video>
            </div>
        </div>
    </div>
</div>

<!-- Video View Modal -->
<div class="modal" id="videoModal" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoModalTitle">Video Details</h5>
                <button type="button" class="modal-close" onclick="closeVideoModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <video id="videoModalPlayer" controls style="width: 100%; border-radius: 8px; margin-bottom: 20px;">
                    <source id="videoModalSource" src="" type="video/mp4">
                </video>
                <div class="video-details">
                    <!-- <p><strong>Joke:</strong> <span id="videoJoke"></span></p>
                    <p><strong>Template:</strong> <span id="videoTemplate"></span></p> -->
                    <p><strong>Created:</strong> <span id="videoDate"></span></p>
                    <div class="video-modal-actions">
                        <button class="download-btn" id="downloadVideoBtn">
                            <i class="fas fa-download"></i> Download
                        </button>
                        <button class="delete-video-btn" id="deleteVideoModalBtn">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    let currentJoke = '';
    let currentJokeId = null;
    let typingInterval = null;
    let selectedTemplateId = null;
    let currentVideoId = null;
    let currentManualText = 'Write your joke here...';

    // Scroll functions
    function scrollContainerLeft(btn) {
        $(btn).parent().find('.templates-scroll').animate({ scrollLeft: '-=280' }, 300);
    }

    function scrollContainerRight(btn) {
        $(btn).parent().find('.templates-scroll').animate({ scrollLeft: '+=280' }, 300);
    }

    // Arrow visibility
    $('.templates-scroll').each(function() {
        const scroll = $(this);
        const wrapper = scroll.closest('.scroll-wrapper');
        const leftArrow = wrapper.find('.scroll-arrow.left');
        const rightArrow = wrapper.find('.scroll-arrow.right');
        
        const checkArrows = () => {
            if (scroll.scrollLeft() <= 10) {
                leftArrow.css({ opacity: '0.3', pointerEvents: 'none' });
            } else {
                leftArrow.css({ opacity: '1', pointerEvents: 'auto' });
            }
            if (scroll.scrollLeft() + scroll.innerWidth() >= scroll[0].scrollWidth - 10) {
                rightArrow.css({ opacity: '0.3', pointerEvents: 'none' });
            } else {
                rightArrow.css({ opacity: '1', pointerEvents: 'auto' });
            }
        };
        
        scroll.on('scroll', checkArrows);
        setTimeout(checkArrows, 100);
    });

    // Toast function
    function showToast(message, type = 'success') {
        $('.toast-message').remove();
        const toast = $('<div>').addClass(`toast-message toast-${type}`).html(`<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`);
        $('body').append(toast);
        setTimeout(() => toast.addClass('show'), 10);
        setTimeout(() => {
            toast.removeClass('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Typewriter effect for jokes
    function typeJoke(text, element) {
        let index = 0;
        let cleanText = text.replace(/\*\*/g, '');
        $(element).empty();
        if (typingInterval) clearInterval(typingInterval);
        
        typingInterval = setInterval(() => {
            if (index < cleanText.length) {
                $(element).append(cleanText.charAt(index));
                index++;
            } else {
                clearInterval(typingInterval);
            }
        }, 30);
    }

    function refreshSavedJokes(templateId) {
        $.get(`{{ route("comedy.get-jokes") }}`, { template_id: templateId }, function(response) {
            if (response.success && response.jokes.length > 0) {
                let jokesHtml = '<select id="savedJokesSelect" class="saved-jokes-select">';
                jokesHtml += '<option value="">-- Select a saved joke --</option>';
                response.jokes.forEach(joke => {
                    let shortJoke = joke.generated_joke.length > 60 ? joke.generated_joke.substring(0, 60) + '...' : joke.generated_joke;
                    let encodedJoke = encodeURIComponent(joke.generated_joke);
                    let shortJokeEscaped = escapeHtml(shortJoke);
                    jokesHtml += `<option value="${joke.id}" data-joke="${encodedJoke}" data-id="${joke.id}">${shortJokeEscaped}</option>`;
                });
                jokesHtml += '</select>';
                $('#savedJokesContainer').html(jokesHtml).show();
                
                $('.create-joke-option').show();
                $('#writeOwnCheckbox').prop('checked', false);
                $('#jokeTextarea').val('').attr('placeholder', 'Select a joke from dropdown');
                
                $('#savedJokesSelect').off('change').on('change', function() {
                    const selected = $(this).find('option:selected');
                    if (selected.val()) {
                        let decodedJoke = decodeURIComponent(selected.data('joke'));
                        decodedJoke = decodedJoke.replace(/^"|"$/g, '');
                        currentJoke = decodedJoke;
                        currentJokeId = selected.data('id');
                        $('#jokeTextarea').val(currentJoke).prop('readonly', true);
                    } else {
                        $('#jokeTextarea').val('').prop('readonly', false);
                    }
                });
            } else {
                $('#savedJokesContainer').empty().hide();
                $('.create-joke-option').show();
                $('#writeOwnCheckbox').prop('checked', true);
                $('#jokeTextarea').val('').attr('placeholder', currentManualText || 'Write your joke here...');
                currentJoke = '';
                currentJokeId = null;
            }
        });
    }

    // Generate Joke
    async function generateJoke(prompt) {
        if (!prompt.trim()) {
            showToast('Please enter a topic', 'error');
            return;
        }

        const templateId = selectedTemplateId;
        
        $('#loadingSpinner').show();
        $('#jokeDisplay').hide();
        
        try {
            const response = await fetch('{{ route("comedy.generate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify({ 
                    prompt: prompt,
                    template_id: templateId
                })
            });
            
            const data = await response.json();
            if (data.success) {
                currentJoke = data.joke;
                currentJokeId = data.joke_id;
                $('#jokeDisplay').show();
                typeJoke(currentJoke, '#jokeText');
                
                // Refresh saved jokes if modal is open
                if ($('#createModal').is(':visible')) {
                    refreshSavedJokes(selectedTemplateId);
                }
            } else {
                showToast(data.message || 'Generation failed', 'error');
            }
        } catch (error) {
            showToast('Generation failed', 'error');
        } finally {
            $('#loadingSpinner').hide();
        }
    }

    // Update openCreateModal to display currentJoke
    function openCreateModal(btn) {
        const card = $(btn).closest('.template-card');
        selectedTemplateId = card.data('template-id');
        const selectedTemplateName = card.data('template-name');
        currentManualText = card.data('manual') || 'Write your joke here...';
        
        $('#createTemplateName').text(selectedTemplateName);
        $('#jokeTextarea').val('').prop('readonly', false);
        $('#writeOwnCheckbox').prop('checked', true);
        $('#savedJokesContainer').empty().hide();
        $('.create-joke-option').show();
        $('#jokeTextarea').show().attr('placeholder', currentManualText);
        
        currentJoke = '';
        currentJokeId = null;
        
        refreshSavedJokes(selectedTemplateId);
        
        $('#createModal').fadeIn(200);
    }

    function openPreviewModal(videoUrl) {
        if (!videoUrl) {
            showToast('No preview available', 'error');
            return;
        }
        
        $('#previewVideoSource').attr('src', videoUrl);
        $('#previewVideo')[0].load();
        $('#previewModal').fadeIn(200);
    }

    function closePreviewModal() {
        $('#previewVideo')[0].pause();
        $('#previewModal').fadeOut(200);
    }

    function escapeHtml(text) {
        return $('<div>').text(text).html();
    }

    function closeCreateModal() {
        $('#createModal').fadeOut(200);
    }

    // Video View Modal
    function openVideoModal(btn) {
        const card = $(btn).closest('.video-card');
        currentVideoId = card.data('video-id');
        const videoUrl = card.data('video-url');
        
        $('#videoModalTitle').text(card.data('video-title'));
        $('#videoModalSource').attr('src', videoUrl);
        $('#videoModalPlayer')[0].load();
        $('#videoJoke').text(card.data('video-joke') || 'No joke saved');
        $('#videoTemplate').text(card.data('template-name'));
        $('#videoDate').text(card.data('created-at'));
        $('#videoModal').fadeIn(200);
    }

    function closeVideoModal() {
        $('#videoModalPlayer')[0].pause();
        $('#videoModal').fadeOut(200);
    }

    async function deleteVideo(videoId) {
        const result = await Swal.fire({
            title: 'Delete this video?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e65856',
            cancelButtonColor: '#888888',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            background: '#121212',
            color: '#ffffff'
        });
        
        if (!result.isConfirmed) return;
        
        try {
            const response = await fetch(`/comedy/video/${videoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Video has been deleted.',
                    icon: 'success',
                    confirmButtonColor: '#e65856',
                    background: '#121212',
                    color: '#ffffff',
                    timer: 1500,
                    showConfirmButton: false
                });
                setTimeout(() => location.reload(), 1500);
            }
        } catch (error) {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to delete video.',
                icon: 'error',
                confirmButtonColor: '#e65856',
                background: '#121212',
                color: '#ffffff'
            });
        }
    }

    $(document).ready(function() {
        $('#savedJokesSelect').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            if (selectedOption.val()) {
                currentJoke = selectedOption.data('joke');
                $('#generatedJokeText').val(currentJoke);
            }
        });
        // Select first template by default
        const firstTemplate = $('.template-dropdown-item').first();
        if (firstTemplate.length) {
            const id = firstTemplate.data('id');
            const name = firstTemplate.data('name');
            selectedTemplateId = id;
            $('#selectedTemplateName').text(name);
            firstTemplate.addClass('active');
        }
        // Handle Template Chip selection
        $('.chip').on('click', function() {
            $('.chip').removeClass('active');
            $(this).addClass('active');
        });

        // Checkbox toggle
        $('#writeOwnCheckbox').on('change', function() {
            if ($(this).is(':checked')) {
                $('#savedJokesSelect').val('');
                $('#savedJokesContainer').hide();
                $('#jokeTextarea').show().val('').prop('readonly', false).attr('placeholder', currentManualText || 'Write your joke here...');
                currentJoke = '';
                currentJokeId = null;
            } else {
                if ($('#savedJokesSelect').length && $('#savedJokesSelect option').length > 1) {
                    $('#savedJokesContainer').show();
                    $('#jokeTextarea').val('').prop('readonly', false).attr('placeholder', 'Select a joke from dropdown');
                } else {
                    $(this).prop('checked', true);
                    Swal.fire({
                        title: 'No Saved Jokes',
                        text: 'Generate a joke first from the Comedy Studio',
                        icon: 'info',
                        confirmButtonColor: '#e65856',
                        background: '#121212',
                        color: '#ffffff'
                    });
                }
            }
        });

        // Submit handler
        $('#submitCreateBtn').on('click', async function() {
            let joke;
            let jokeId = null;
            
            if ($('#writeOwnCheckbox').is(':checked')) {
                joke = $('#jokeTextarea').val().trim();
            } else {
                joke = currentJoke;
                jokeId = currentJokeId;
            }
            
            if (!joke) {
                Swal.fire({
                    title: 'Missing Joke',
                    text: 'Please provide a joke',
                    icon: 'warning',
                    confirmButtonColor: '#e65856',
                    background: '#121212',
                    color: '#ffffff'
                });
                return;
            }
            
            $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            
            try {
                const response = await fetch('{{ route("comedy.generate-video") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({ 
                        template_id: selectedTemplateId, 
                        joke: joke,
                        joke_id: jokeId
                    })
                });
                
                const data = await response.json();
                
                // Handle temp block (429 status code)
                if (response.status === 429 && data.temp_block) {
                    closeCreateModal();
                    Swal.fire({
                        title: 'Take a coffee break',
                        text: data.message || `Please wait ${data.wait_minutes} minute(s) before generating another video.`,
                        icon: 'warning',
                        confirmButtonColor: '#e65856',
                        background: '#121212',
                        color: '#ffffff',
                        timer: 3000,
                        timerProgressBar: true
                    });
                    $(this).prop('disabled', false).html('<i class="fas fa-video"></i> Generate Video');
                    return;
                }
                
                // Handle 504 gateway timeout
                if (response.status === 504) {
                    closeCreateModal();
                    Swal.fire({
                        title: 'Video Processing',
                        text: 'Your video is under processing. It may take a few minutes.',
                        icon: 'info',
                        confirmButtonColor: '#e65856',
                        background: '#121212',
                        color: '#ffffff'
                    });
                    setTimeout(() => location.reload(), 3000);
                    $(this).prop('disabled', false).html('<i class="fas fa-video"></i> Generate Video');
                    return;
                }
                
                closeCreateModal();
                
                if (response.ok && data.success) {
                    Swal.fire({
                        title: 'Video Generation Started!',
                        text: 'Your video is being processed. You will be notified when ready.',
                        icon: 'success',
                        confirmButtonColor: '#e65856',
                        background: '#121212',
                        color: '#ffffff',
                        timer: 3000,
                        timerProgressBar: true
                    });
                    setTimeout(() => location.reload(), 3500);
                } else if (response.ok && !data.success) {
                    Swal.fire({
                        title: 'Generation Queued',
                        text: data.message || 'Your video is under processing. Check back in a few minutes.',
                        icon: 'info',
                        confirmButtonColor: '#e65856',
                        background: '#121212',
                        color: '#ffffff'
                    });
                    setTimeout(() => location.reload(), 3000);
                } else {
                    Swal.fire({
                        title: 'Something Unexpected Occurred',
                        text: data.message || 'Please try again in a minute.',
                        icon: 'error',
                        confirmButtonColor: '#e65856',
                        background: '#121212',
                        color: '#ffffff'
                    });
                }
            } catch (error) {
                closeCreateModal();
                Swal.fire({
                    title: 'Something Unexpected Occurred',
                    text: 'Please try again in a minute.',
                    icon: 'error',
                    confirmButtonColor: '#e65856',
                    background: '#121212',
                    color: '#ffffff'
                });
            } finally {
                $(this).prop('disabled', false).html('<i class="fas fa-video"></i> Generate Video');
            }
        });

        // Toggle Menu
        $('#dropdownTrigger').on('click', function(e) {
            e.stopPropagation();
            $('#dropdownMenu').toggleClass('show');
        });

        // Handle Selection
        $('.template-dropdown-item').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            selectedTemplateId = id;
            $('#selectedTemplateName').text(name);
            
            $('.template-dropdown-item').removeClass('active');
            $(this).addClass('active');
            $('#dropdownMenu').removeClass('show');
        });

        // Close on outside click
        $(document).on('click', () => $('#dropdownMenu').removeClass('show'));

        // Update generate function to use selectedTemplateId
        $('#generateJokeBtn').on('click', function() {
            generateJoke($('#jokePrompt').val(), selectedTemplateId);
        });
        $('#jokePrompt').on('keypress', (e) => { if (e.key === 'Enter') generateJoke($('#jokePrompt').val()); });
        $('#dismissJokeBtn').on('click', () => $('#jokeDisplay').hide());
        
        // Modal Background Click
        $(document).on('click', (e) => {
            if ($(e.target).hasClass('modal') || $(e.target).hasClass('create-modal')) {
                $('.modal, .create-modal').fadeOut(200);
                $('video').each(function() { this.pause(); });
            }
        });
        // Download Video Button
        $('#downloadVideoBtn').on('click', function() {
            const videoUrl = $('#videoModalSource').attr('src');
            if (videoUrl) {
                const a = document.createElement('a');
                a.href = videoUrl;
                a.download = videoUrl.split('/').pop();
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                showToast('Download started', 'success');
            } else {
                showToast('No video to download', 'error');
            }
        });

        // Delete Video from Modal
        $('#deleteVideoModalBtn').on('click', async function() {
            if (!currentVideoId) {
                showToast('No video selected', 'error');
                return;
            }
            
            const result = await Swal.fire({
                title: 'Delete this video?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e65856',
                cancelButtonColor: '#888888',
                confirmButtonText: 'Yes, delete it',
                background: '#121212',
                color: '#ffffff'
            });
            
            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/comedy/video/${currentVideoId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        closeVideoModal();
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Video has been deleted.',
                            icon: 'success',
                            confirmButtonColor: '#e65856',
                            background: '#121212',
                            color: '#ffffff',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast('Delete failed', 'error');
                    }
                } catch (error) {
                    showToast('Delete failed', 'error');
                }
            }
        });
    });
</script>
@endsection