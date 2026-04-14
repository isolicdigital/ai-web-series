@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black py-[120px] px-4">
    <div class="container mx-auto max-w-7xl">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('web-series.show', $series->id) }}" class="text-gray-400 hover:text-white transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Series
            </a>
        </div>
        
        <!-- Series Info -->
        <div class="bg-gray-900/50 rounded-2xl border border-gray-800 p-6 mb-8">
            <h1 class="text-2xl font-bold text-white mb-2">Generate Video: {{ $series->project_name }}</h1>
            <p class="text-gray-400 text-sm">Generate videos for each scene</p>
        </div>
        
        <!-- Scenes List - Row Wise Layout -->
        <div class="space-y-6">
            @foreach($series->scenes as $scene)
            <div class="bg-gray-900/40 rounded-2xl border border-gray-800 overflow-hidden hover:border-purple-500/30 transition-all duration-300" id="scene-row-{{ $scene->id }}">
                <!-- Scene Header -->
                <div class="bg-gradient-to-r from-purple-900/30 to-pink-900/30 px-6 py-3 border-b border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold text-sm">
                            {{ $scene->scene_number }}
                        </div>
                        <h3 class="text-lg font-bold text-white">{{ $scene->title }}</h3>
                    </div>
                </div>
                
                <!-- Two Columns for each scene -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-6">
                    <!-- Left Column: Prompt and Generate Button -->
                    <div class="space-y-4">
                        <div class="bg-purple-900/20 rounded-xl p-4 border border-purple-500/30">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-purple-400 font-semibold flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    AI Image Prompt
                                </span>
                                <button onclick="copyPrompt({{ $scene->id }})" 
                                        class="text-xs bg-gray-700 hover:bg-gray-600 px-2 py-1 rounded text-white transition">
                                    Copy Prompt
                                </button>
                            </div>
                            <p id="prompt-{{ $scene->id }}" class="text-gray-300 text-sm leading-relaxed">{{ $scene->image_prompt }}</p>
                        </div>
                        
                        <!-- Generate Video Button -->
                        <button onclick="generateSceneVideo({{ $scene->id }})" 
                                id="generateBtn-{{ $scene->id }}"
                                class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 flex items-center justify-center gap-2 shadow-lg hover:shadow-pink-500/25">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Generate Video
                        </button>
                        
                        <div id="loading-{{ $scene->id }}" class="hidden text-center mt-2">
                            <div class="inline-block w-5 h-5 border-2 border-purple-500 border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-gray-400 text-sm ml-2">Generating video...</span>
                        </div>
                    </div>
                    
                    <!-- Right Column: Video Preview -->
                    <div class="bg-gray-800/30 rounded-xl p-4">
                        <h4 class="text-green-400 font-semibold text-sm mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Video Preview
                        </h4>
                        
                        <div id="videoPreview-{{ $scene->id }}" class="bg-black rounded-lg aspect-video flex items-center justify-center border border-gray-700">
                            <div class="text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-gray-500 text-sm">Click generate to create video</p>
                            </div>
                        </div>
                        
                        <div id="videoResult-{{ $scene->id }}" class="hidden mt-3">
                            <video id="generatedVideo-{{ $scene->id }}" controls class="w-full rounded-lg">
                                <source id="videoSource-{{ $scene->id }}" src="" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div class="flex gap-2 mt-3">
                                <button onclick="downloadVideo({{ $scene->id }})" 
                                        class="flex-1 py-1.5 bg-green-600 hover:bg-green-700 rounded-lg text-white text-sm transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download
                                </button>
                                <button onclick="shareVideo({{ $scene->id }})" 
                                        class="flex-1 py-1.5 bg-blue-600 hover:bg-blue-700 rounded-lg text-white text-sm transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                    </svg>
                                    Share
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
// Store generated video URLs
const videoUrls = {};

function copyPrompt(sceneId) {
    const prompt = document.getElementById(`prompt-${sceneId}`).innerText;
    navigator.clipboard.writeText(prompt);
    showToast('✅ Prompt copied!', 'success');
}

function showToast(message, type) {
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        document.body.appendChild(toast);
    }
    
    const colors = {
        success: 'bg-green-600',
        error: 'bg-red-600',
        info: 'bg-blue-600',
        warning: 'bg-yellow-600'
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

async function generateSceneVideo(sceneId) {
    const generateBtn = document.getElementById(`generateBtn-${sceneId}`);
    const loading = document.getElementById(`loading-${sceneId}`);
    const videoPreview = document.getElementById(`videoPreview-${sceneId}`);
    const videoResult = document.getElementById(`videoResult-${sceneId}`);
    
    generateBtn.disabled = true;
    generateBtn.classList.add('opacity-50');
    loading.classList.remove('hidden');
    
    videoPreview.innerHTML = `
        <div class="text-center py-8">
            <div class="w-10 h-10 border-4 border-purple-500/30 border-t-purple-500 rounded-full animate-spin mx-auto mb-3"></div>
            <p class="text-purple-400 text-sm">Generating video...</p>
            <p class="text-gray-500 text-xs mt-1">Please wait</p>
        </div>
    `;
    
    try {
        // Get the image URL for this scene
        const imageUrl = @json($series->scenes->pluck('generated_image_url', 'id'))[sceneId];
        
        // Make API call to generate video
        const response = await fetch('/generate-scene-video', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                scene_id: sceneId,
                image_url: imageUrl
            })
        });
        
        const result = await response.json();
        
        if (result.success && result.video_url) {
            videoPreview.classList.add('hidden');
            videoResult.classList.remove('hidden');
            document.getElementById(`videoSource-${sceneId}`).src = result.video_url;
            document.getElementById(`generatedVideo-${sceneId}`).load();
            videoUrls[sceneId] = result.video_url;
            showToast('✅ Video generated successfully!', 'success');
        } else {
            videoPreview.innerHTML = `
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-red-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-red-400 text-sm">Error generating video</p>
                    <p class="text-gray-500 text-xs">${result.message || 'Unknown error'}</p>
                </div>
            `;
            showToast('❌ Error generating video', 'error');
        }
    } catch (error) {
        videoPreview.innerHTML = `
            <div class="text-center py-8">
                <svg class="w-12 h-12 mx-auto text-red-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-red-400 text-sm">Network error</p>
                <p class="text-gray-500 text-xs">${error.message}</p>
            </div>
        `;
        showToast('❌ Network error', 'error');
    } finally {
        generateBtn.disabled = false;
        generateBtn.classList.remove('opacity-50');
        loading.classList.add('hidden');
    }
}

function downloadVideo(sceneId) {
    const video = document.getElementById(`generatedVideo-${sceneId}`);
    if (video && video.src) {
        const link = document.createElement('a');
        link.href = video.src;
        link.download = `scene_${sceneId}_video.mp4`;
        link.click();
        showToast('📸 Download started!', 'success');
    }
}

function shareVideo(sceneId) {
    const video = document.getElementById(`generatedVideo-${sceneId}`);
    if (video && video.src) {
        if (navigator.share) {
            navigator.share({
                title: 'Scene Video',
                text: 'Check out my AI-generated scene video!',
                url: video.src
            });
        } else {
            showToast('Share feature not supported on this browser', 'info');
        }
    }
}
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
.animate-spin { animation: spin 1s linear infinite; }

.aspect-video {
    aspect-ratio: 16 / 9;
}
</style>
@endsection