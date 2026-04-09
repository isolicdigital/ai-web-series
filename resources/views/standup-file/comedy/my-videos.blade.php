@extends('layouts.app')

@section('title', 'My Videos')

@section('css')
<link rel="stylesheet" href="{{ asset('custom/css/dashboard.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
<div class="templates-page">

    @if($videos->count() > 0)
    <div class="templates-header">
        <h1>My Videos</h1>
        <p>All your generated comedy videos</p>
    </div>
    <div class="videos-grid">
        @foreach($videos as $video)
        <div class="video-card" data-video-id="{{ $video->id }}" data-video-url="{{ $video->video_url }}" data-video-title="{{ $video->title }}" data-video-joke="{{ $video->joke ?? '' }}" data-template-name="{{ $video->template->name ?? 'Unknown' }}" data-created-at="{{ $video->created_at->format('M d, Y') }}">
            <div class="video-thumbnail">
                <img src="{{ asset($video->thumbnail_url ?? 'images/default-thumb.jpg') }}" alt="{{ $video->title }}">
                <div class="video-overlay">
                    <button class="view-btn" onclick="openVideoModal(this)">
                        <i class="fas fa-play"></i> View
                    </button>
                    <button class="delete-btn" onclick="deleteVideo({{ $video->id }})">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
            <div class="video-info">
                <h4>{{ $video->title }}</h4>
                <span class="status-badge status-{{ $video->processing_status }}">{{ ucfirst($video->processing_status) }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <div class="pagination-wrapper">
        {{ $videos->links() }}
    </div>
    @else
    <div class="empty-state">
        <i class="fas fa-video"></i>
        <h3>No videos yet</h3>
        <p>Generate your first comedy video from the Comedy Studio</p>
        <a href="{{ route('comedy.index') }}" class="generate-btn">Go to Comedy Studio</a>
    </div>
    @endif
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
                    <p><strong>Joke:</strong> <span id="videoJoke"></span></p>
                    <p><strong>Template:</strong> <span id="videoTemplate"></span></p>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let currentVideoId = null;

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
        $('#downloadVideoBtn').on('click', function() {
            const videoUrl = $('#videoModalSource').attr('src');
            if (videoUrl) {
                const link = document.createElement('a');
                link.href = videoUrl;
                link.download = `comedy-video-${currentVideoId}.mp4`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });

        $('#deleteVideoModalBtn').on('click', function() {
            closeVideoModal();
            deleteVideo(currentVideoId);
        });
    });

    $(document).on('click', function(e) {
        if ($(e.target).hasClass('modal')) {
            closeVideoModal();
        }
    });
</script>
@endsection