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
            <p class="text-gray-400 text-sm">Generate videos for each scene using AI</p>
        </div>
        
        <!-- Scenes List - Enhanced Layout with Image Left, Video Right -->
        <div class="space-y-8">
            @foreach($series->scenes as $scene)
            <div class="bg-gray-900/40 rounded-2xl border border-gray-800 overflow-hidden hover:border-purple-500/30 transition-all duration-300" id="scene-row-{{ $scene->id }}">
                <!-- Scene Header -->
                <div class="bg-gradient-to-r from-purple-900/30 to-pink-900/30 px-6 py-4 border-b border-gray-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 text-white flex items-center justify-center font-bold text-sm shadow-lg">
                                {{ $scene->scene_number }}
                            </div>
                            <h3 class="text-lg font-bold text-white">{{ $scene->title }}</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2 py-1 rounded-full bg-purple-500/20 text-purple-300">
                                Scene {{ $scene->scene_number }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Two Columns: Generated Image (Left) + Video Preview (Right) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-6">
                    <!-- LEFT COLUMN: Generated Image -->
                    <div class="space-y-4">
                        <div class="bg-gray-800/30 rounded-xl p-4">
                            <h4 class="text-purple-400 font-semibold text-sm mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Generated Image
                            </h4>
                            <div class="bg-black rounded-lg overflow-hidden border border-gray-700">
                                @if($scene->generated_image_url)
                                    <img src="{{ asset($scene->generated_image_url) }}" 
                                         alt="{{ $scene->title }}" 
                                         class="w-full object-cover">
                                @else
                                    <div class="aspect-video flex items-center justify-center">
                                        <div class="text-center">
                                            <svg class="w-12 h-12 mx-auto text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <p class="text-gray-500 text-sm">No image generated yet</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- AI Prompt Section -->
                        <div class="bg-purple-900/20 rounded-xl p-4 border border-purple-500/30">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-purple-400 font-semibold flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                    AI Image Prompt
                                </span>
                                <button onclick="copyPrompt({{ $scene->id }})" 
                                        class="text-xs bg-gray-700 hover:bg-gray-600 px-2 py-1 rounded text-white transition flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    Copy
                                </button>
                            </div>
                            <p id="prompt-{{ $scene->id }}" class="text-gray-300 text-sm leading-relaxed">{{ $scene->image_prompt }}</p>
                        </div>
                    </div>
                    
                    <!-- RIGHT COLUMN: Video Preview & Controls -->
                    <div class="space-y-4">
                        <div class="bg-gray-800/30 rounded-xl p-4">
                            <h4 class="text-green-400 font-semibold text-sm mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Video Preview
                            </h4>
                            
                            <div id="videoPreview-{{ $scene->id }}" class="bg-black rounded-lg aspect-video flex items-center justify-center border border-gray-700 overflow-hidden">
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
                                            class="flex-1 py-2 bg-green-600 hover:bg-green-700 rounded-lg text-white text-sm transition flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        Download
                                    </button>
                                    <button onclick="shareVideo({{ $scene->id }})" 
                                            class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-white text-sm transition flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                        </svg>
                                        Share
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Loading Indicator -->
                        <div id="loading-{{ $scene->id }}" class="hidden">
                            <div class="bg-gray-800/50 rounded-xl p-4 text-center">
                                <div class="inline-block w-6 h-6 border-2 border-purple-500 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-gray-400 text-sm ml-2">Generating video, please wait...</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- BOTTOM: Generate Video Button (Full Width) -->
                <div class="px-6 pb-6">
                    <button onclick="generateSceneVideo({{ $scene->id }})" 
                            id="generateBtn-{{ $scene->id }}"
                            class="w-full py-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 flex items-center justify-center gap-3 shadow-lg hover:shadow-pink-500/25 group">
                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Generate Video for "{{ $scene->title }}"
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </button>
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
    
    // Disable button and show loading
    generateBtn.disabled = true;
    generateBtn.classList.add('opacity-50', 'cursor-not-allowed');
    loading.classList.remove('hidden');
    
    // Show generating state in preview
    videoPreview.innerHTML = `
        <div class="text-center py-8">
            <div class="w-10 h-10 border-4 border-purple-500/30 border-t-purple-500 rounded-full animate-spin mx-auto mb-3"></div>
            <p class="text-purple-400 text-sm">AI is creating your video...</p>
            <p class="text-gray-500 text-xs mt-1">This may take a moment</p>
        </div>
    `;
    
    try {
        // Get the image URL for this scene
        const imageUrl = @json($series->scenes->pluck('generated_image_url', 'id'))[sceneId];
        
        if (!imageUrl) {
            throw new Error('No generated image found for this scene');
        }
        
        // Make API call to generate video
        const response = await fetch('{{ route("generate.scene.video") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                scene_id: sceneId,
                image_url: imageUrl
            })
        });
        
        const result = await response.json();
        
        if (result.success && result.video_url) {
            // Hide preview, show video player
            videoPreview.classList.add('hidden');
            videoResult.classList.remove('hidden');
            
            // Set video source
            const videoSource = document.getElementById(`videoSource-${sceneId}`);
            const video = document.getElementById(`generatedVideo-${sceneId}`);
            videoSource.src = result.video_url;
            video.load();
            
            // Store URL for download
            videoUrls[sceneId] = result.video_url;
            
            showToast('✅ Video generated successfully!', 'success');
            
            // Play video automatically
            video.play().catch(e => console.log('Autoplay prevented:', e));
        } else {
            throw new Error(result.message || 'Failed to generate video');
        }
    } catch (error) {
        console.error('Video generation error:', error);
        
        // Reset preview with error message
        videoPreview.innerHTML = `
            <div class="text-center py-8">
                <svg class="w-12 h-12 mx-auto text-red-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-red-400 text-sm">Error generating video</p>
                <p class="text-gray-500 text-xs mt-1">${error.message}</p>
                <button onclick="retryGeneration(${sceneId})" 
                        class="mt-3 px-4 py-1.5 bg-purple-600 hover:bg-purple-700 rounded-lg text-white text-xs transition">
                    Try Again
                </button>
            </div>
        `;
        showToast('❌ ' + error.message, 'error');
    } finally {
        // Re-enable button
        generateBtn.disabled = false;
        generateBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        loading.classList.add('hidden');
    }
}

function retryGeneration(sceneId) {
    // Reset the preview area
    const videoPreview = document.getElementById(`videoPreview-${sceneId}`);
    const videoResult = document.getElementById(`videoResult-${sceneId}`);
    
    videoResult.classList.add('hidden');
    videoPreview.classList.remove('hidden');
    videoPreview.innerHTML = `
        <div class="text-center">
            <svg class="w-12 h-12 mx-auto text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-gray-500 text-sm">Click generate to create video</p>
        </div>
    `;
    
    // Trigger generation again
    generateSceneVideo(sceneId);
}

function downloadVideo(sceneId) {
    const video = document.getElementById(`generatedVideo-${sceneId}`);
    if (video && video.src && video.src !== '') {
        const link = document.createElement('a');
        link.href = video.src;
        link.download = `scene_${sceneId}_video.mp4`;
        link.click();
        showToast('📥 Download started!', 'success');
    } else {
        showToast('No video available to download', 'warning');
    }
}

async function shareVideo(sceneId) {
    const video = document.getElementById(`generatedVideo-${sceneId}`);
    if (video && video.src) {
        try {
            if (navigator.share) {
                // Try to fetch video as blob for sharing
                const response = await fetch(video.src);
                const blob = await response.blob();
                const file = new File([blob], `scene_${sceneId}_video.mp4`, { type: 'video/mp4' });
                
                await navigator.share({
                    title: 'AI Generated Scene Video',
                    text: 'Check out my AI-generated scene video!',
                    files: [file]
                });
                showToast('📤 Shared successfully!', 'success');
            } else {
                // Fallback: copy video URL to clipboard
                await navigator.clipboard.writeText(video.src);
                showToast('Video URL copied to clipboard!', 'success');
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                showToast('Could not share video', 'error');
            }
        }
    } else {
        showToast('No video available to share', 'warning');
    }
}

// Auto-refresh video generation status every 5 seconds if there are pending videos
let pendingVideos = @json($series->scenes->where('video_status', 'pending')->pluck('id'));
if (pendingVideos.length > 0) {
    setInterval(() => {
        pendingVideos.forEach(sceneId => {
            checkVideoStatus(sceneId);
        });
    }, 5000);
}

async function checkVideoStatus(sceneId) {
    try {
        const response = await fetch(`{{ url("check-video-status") }}/${sceneId}`, {
            headers: {
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        
        if (result.success && result.video_url && result.status === 'completed') {
            // Update UI with completed video
            const videoPreview = document.getElementById(`videoPreview-${sceneId}`);
            const videoResult = document.getElementById(`videoResult-${sceneId}`);
            const generateBtn = document.getElementById(`generateBtn-${sceneId}`);
            
            videoPreview.classList.add('hidden');
            videoResult.classList.remove('hidden');
            
            const videoSource = document.getElementById(`videoSource-${sceneId}`);
            videoSource.src = result.video_url;
            document.getElementById(`generatedVideo-${sceneId}`).load();
            videoUrls[sceneId] = result.video_url;
            
            generateBtn.disabled = false;
            generateBtn.classList.remove('opacity-50');
            
            showToast('✅ Video is ready!', 'success');
            
            // Remove from pending array
            pendingVideos = pendingVideos.filter(id => id !== sceneId);
        }
    } catch (error) {
        console.error('Status check error:', error);
    }
}
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
.animate-spin { animation: spin 1s linear infinite; }

.aspect-video {
    aspect-ratio: 16 / 9;
}

/* Smooth transitions */
button {
    transition: all 0.3s ease;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #1a1a1a;
}
::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #8b5cf6, #ec4899);
    border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #7c3aed, #db2777);
}
</style>
@endsection