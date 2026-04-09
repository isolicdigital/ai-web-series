@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ theme_url('custom/standup.css') }}">
@endsection

@section('content')

<div class="standup-video-generator">

    <!-- Header Section -->
    <div class="generator-header">
        <div class="header-badge">
            <i class="fas fa-video"></i> AI Video Production
        </div>
        <h1>Generate Your Stand-Up Video</h1>
        <p class="header-subtitle">Watch your AI comedian bring the script to life on stage</p>
    </div>

    <div class="generator-container">
        <!-- Info Cards -->
        <div class="info-grid">
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="info-content">
                    <span class="info-label">Comedian</span>
                    <h4 class="info-value">{{ $comedian->name ?? 'Selected Comedian' }}</h4>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="info-content">
                    <span class="info-label">Script Preview</span>
                    <p class="info-preview">{{ substr($script->generated_script ?? 'Ready to perform', 0, 80) }}...</p>
                </div>
            </div>
        </div>

        <!-- Main Action Card -->
        <div class="action-card">
            <div class="action-card-inner">
                <!-- Generate Button -->
                <button id="generateVideoBtn" class="btn-generate-video">
                    <i class="fas fa-play-circle"></i>
                    <span>Generate Video</span>
                </button>
                <p class="action-hint">This may take 2-3 minutes. Your AI comedian is getting ready!</p>
                
                <!-- Progress Indicator -->
                <div id="videoProgress" class="video-progress d-none">
                    <div class="progress-ring">
                        <div class="progress-spinner"></div>
                    </div>
                    <div class="progress-steps">
                        <div class="step" id="step1">
                            <i class="fas fa-microphone-alt"></i>
                            <span>Preparing comedian</span>
                        </div>
                        <div class="step" id="step2">
                            <i class="fas fa-file-alt"></i>
                            <span>Analyzing script</span>
                        </div>
                        <div class="step" id="step3">
                            <i class="fas fa-video"></i>
                            <span>Rendering video</span>
                        </div>
                    </div>
                    <p class="progress-text">Generating your stand-up performance...</p>
                </div>
                
                <!-- Video Result -->
                <div id="videoResult" class="video-result d-none">
                    <div class="video-wrapper">
                        <video controls class="video-player">
                            <source id="videoSource" src="" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    <div class="video-actions">
                        <a id="downloadLink" class="btn-download" download>
                            <i class="fas fa-download"></i> Download Video
                        </a>
                        <button class="btn-new" onclick="location.reload()">
                            <i class="fas fa-plus"></i> Create Another
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips Section -->
        <div class="tips-section">
            <div class="tips-icon">
                <i class="fas fa-lightbulb"></i>
            </div>
            <div class="tips-content">
                <h4>Pro Tips for Best Results</h4>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Scripts with natural pacing work best</li>
                    <li><i class="fas fa-check-circle"></i> Add pauses and stage directions for better delivery</li>
                    <li><i class="fas fa-check-circle"></i> Videos are optimized for social media sharing</li>
                </ul>
            </div>
        </div>
    </div>

</div>

@endsection

@section('js')
<script>
const comedianId = {{ $comedianId }};
const scriptId = {{ $scriptId }};

// Progress step animation
function updateProgressStep(step) {
    const steps = ['step1', 'step2', 'step3'];
    steps.forEach((s, index) => {
        const element = document.getElementById(s);
        if (index < step) {
            element.classList.add('completed');
            element.classList.remove('active');
        } else if (index === step) {
            element.classList.add('active');
            element.classList.remove('completed');
        } else {
            element.classList.remove('active', 'completed');
        }
    });
}

let currentStep = 0;
let stepInterval;

document.getElementById('generateVideoBtn').addEventListener('click', async () => {
    const btn = document.getElementById('generateVideoBtn');
    const progress = document.getElementById('videoProgress');
    
    btn.disabled = true;
    btn.style.opacity = '0.5';
    progress.classList.remove('d-none');
    
    // Animate progress steps
    stepInterval = setInterval(() => {
        if (currentStep < 2) {
            currentStep++;
            updateProgressStep(currentStep);
        }
    }, 8000);
    
    updateProgressStep(0);
    
    const response = await fetch('/standup/video/generate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ comedian_id: comedianId, script_id: scriptId })
    });
    
    const data = await response.json();
    pollVideoStatus(data.video_id);
});

function pollVideoStatus(videoId) {
    const interval = setInterval(async () => {
        const response = await fetch(`/standup/videos`);
        const videos = await response.json();
        const video = videos.find(v => v.id === videoId);
        
        if (video && video.status === 'completed') {
            clearInterval(interval);
            clearInterval(stepInterval);
            
            // Complete all steps
            updateProgressStep(3);
            
            // Show result
            setTimeout(() => {
                document.getElementById('videoProgress').classList.add('d-none');
                document.getElementById('videoResult').classList.remove('d-none');
                document.getElementById('videoSource').src = video.video_url;
                document.getElementById('downloadLink').href = video.video_url;
                document.getElementById('generateVideoBtn').disabled = false;
                document.getElementById('generateVideoBtn').style.opacity = '1';
            }, 500);
            
        } else if (video && video.status === 'failed') {
            clearInterval(interval);
            clearInterval(stepInterval);
            
            document.getElementById('videoProgress').classList.add('d-none');
            document.getElementById('generateVideoBtn').disabled = false;
            document.getElementById('generateVideoBtn').style.opacity = '1';
            
            Swal.fire({
                icon: 'error',
                title: 'Generation Failed',
                text: 'Something went wrong. Please try again.',
                confirmButtonColor: '#E65856'
            });
        }
    }, 5000);
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (stepInterval) clearInterval(stepInterval);
});
</script>
@endsection