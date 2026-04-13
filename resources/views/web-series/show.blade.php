{{-- resources/views/web-series/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black py-[120px] px-4">
    <div class="container mx-auto max-w-6xl">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('web-series.my-series') }}" class="text-gray-400 hover:text-white transition-colors inline-flex items-center gap-2 group">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to My Series
            </a>
        </div>
        
        <!-- Series Info -->
        <div class="bg-gray-900/50 backdrop-blur-lg rounded-2xl border border-gray-800 p-6 mb-8 hover:border-pink-500/30 transition-all duration-300">
            <h1 class="text-4xl font-bold text-white mb-3">{{ $series->project_name }}</h1>
            <div class="flex flex-wrap gap-3 text-sm">
                <span class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-3 py-1 rounded-full">{{ $series->category }}</span>
                <span class="text-gray-400">{{ $series->scenes->count() }} Scenes</span>
                <span class="text-gray-400">Created {{ $series->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-gray-300 mt-5 leading-relaxed border-l-4 border-pink-500 pl-4">{{ $series->concept }}</p>
        </div>
        
        <!-- Scenes List with Image Prompts -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">All Scenes</h2>
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-700 to-transparent mx-4"></div>
            <span class="text-sm text-gray-500">{{ $series->scenes->count() }} total scenes</span>
        </div>
        
        <div class="space-y-6">
            @foreach($series->scenes as $scene)
            <div class="bg-gray-900/40 backdrop-blur-lg rounded-2xl border border-gray-800 hover:border-pink-500/50 transition-all duration-300 overflow-hidden group">
                <!-- Scene Header -->
                <div class="p-6 border-b border-gray-800 bg-gradient-to-r from-gray-900/50 to-gray-800/30">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-600 to-pink-600 text-white flex items-center justify-center font-bold text-lg shadow-lg group-hover:scale-110 transition-transform">
                                {{ $scene->scene_number }}
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">{{ $scene->title }}</h3>
                                <p class="text-gray-500 text-sm">Scene {{ $scene->scene_number }} of {{ $series->scenes->count() }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            @if($scene->generated_image_url)
                            <span class="bg-green-600/20 text-green-400 text-xs px-3 py-1 rounded-full border border-green-500/30">
                                ✨ Image Generated
                            </span>
                            @endif
                            <span class="bg-blue-600/20 text-blue-400 text-xs px-3 py-1 rounded-full border border-blue-500/30">
                                Scene {{ $scene->scene_number }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Scene Content -->
                <div class="p-6">
                    <div class="text-gray-300 text-sm mb-5 leading-relaxed bg-gray-800/30 rounded-xl p-4">
                        {!! Str::limit($scene->content, 250) !!}
                    </div>
                    
                    <!-- Image Prompt Section -->
                    @if($scene->image_prompt)
                    <div class="bg-gradient-to-r from-purple-900/20 to-pink-900/20 rounded-xl p-5 mb-5 border border-purple-500/20">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-purple-400 font-semibold">AI Image Generation Prompt</h4>
                            </div>
                            <button onclick="copyPrompt({{ $scene->id }})" 
                                    class="text-xs text-gray-400 hover:text-white transition-all duration-300 px-3 py-1.5 rounded-lg bg-gray-800/50 hover:bg-gray-700 border border-gray-700 hover:border-purple-500">
                                📋 Copy Prompt
                            </button>
                        </div>
                        <p id="prompt-{{ $scene->id }}" class="text-gray-300 text-sm leading-relaxed">{{ $scene->image_prompt }}</p>
                    </div>
                    @endif
                    
                    <!-- Generate Image Button and Result -->
                    <div class="flex flex-col gap-4">
                        <div class="flex gap-4 items-center">
                            <button onclick="generateImage({{ $scene->id }})" 
                                    id="generateBtn-{{ $scene->id }}"
                                    class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white text-sm font-medium transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-pink-500/25 hover:scale-105">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>Generate AI Image</span>
                            </button>
                            
                            <!-- Loading Indicator -->
                            <div id="loading-{{ $scene->id }}" class="hidden flex items-center gap-2">
                                <div class="w-5 h-5 border-2 border-pink-500/30 border-t-pink-500 rounded-full animate-spin"></div>
                                <span class="text-gray-400 text-sm">Generating image with AI...</span>
                            </div>
                        </div>
                        
                        <!-- Generated Image Display Area -->
                        <div id="imageResult-{{ $scene->id }}" class="mt-2">
                            @if($scene->generated_image_url)
                            <div class="relative mt-3 rounded-xl overflow-hidden border border-pink-500/30 bg-gray-900/50 group/img">
                                <img src="{{ $scene->generated_image_url }}" alt="Generated scene" class="w-full max-h-96 object-contain rounded-xl transition-transform duration-300 group-hover/img:scale-105">
                                <div class="absolute top-3 right-3 flex gap-2 opacity-0 group-hover/img:opacity-100 transition-all duration-300">
                                    <button onclick="downloadImage('{{ $scene->generated_image_url }}', {{ $scene->id }})" 
                                            class="bg-black/70 hover:bg-gradient-to-r from-purple-600 to-pink-600 p-2.5 rounded-lg text-white transition-all duration-300 hover:scale-110 backdrop-blur-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- View Episode Button -->
        <div class="mt-12 text-center">
            <a href="{{ route('web-series.show', $series->id) }}" 
               class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 hover:scale-105 shadow-lg hover:shadow-pink-500/25">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                View Complete Episode
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="customToast" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-50 hidden"></div>

<script>
// Store generated images in memory
const generatedImages = {};

// Copy prompt to clipboard
function copyPrompt(sceneId) {
    const promptElement = document.getElementById(`prompt-${sceneId}`);
    if (promptElement) {
        navigator.clipboard.writeText(promptElement.innerText);
        showToast('✅ Prompt copied to clipboard!', 'success');
    }
}

// Generate image using AI
async function generateImage(sceneId) {
    const promptElement = document.getElementById(`prompt-${sceneId}`);
    const prompt = promptElement ? promptElement.innerText : '';
    
    if (!prompt) {
        showToast('❌ No prompt available for this scene', 'error');
        return;
    }
    
    // Show loading
    const loadingDiv = document.getElementById(`loading-${sceneId}`);
    const generateBtn = document.getElementById(`generateBtn-${sceneId}`);
    const imageResult = document.getElementById(`imageResult-${sceneId}`);
    
    loadingDiv.classList.remove('hidden');
    generateBtn.disabled = true;
    generateBtn.style.opacity = '0.5';
    const originalBtnText = generateBtn.innerHTML;
    generateBtn.innerHTML = '<div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div><span>Generating...</span>';
    
    try {
        const response = await fetch('/generate-image', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                prompt: prompt,
                scene_id: sceneId
            })
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Server error:', errorText);
            throw new Error(`Server responded with ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success && result.images && result.images.length > 0) {
            // Display the generated image
            const imageUrl = result.images[0];
            const imageHtml = `
                <div class="relative mt-3 rounded-xl overflow-hidden border border-pink-500/30 bg-gray-900/50 group/img">
                    <img src="${imageUrl}" alt="Generated scene" class="w-full max-h-96 object-contain rounded-xl transition-transform duration-300 group-hover/img:scale-105" onerror="this.onerror=null; this.src='https://placehold.co/1024x1024/7c3aed/ffffff?text=Image+Generation+Failed'">
                    <div class="absolute top-3 right-3 flex gap-2 opacity-0 group-hover/img:opacity-100 transition-all duration-300">
                        <button onclick="downloadImage('${imageUrl}', ${sceneId})" 
                                class="bg-black/70 hover:bg-gradient-to-r from-purple-600 to-pink-600 p-2.5 rounded-lg text-white transition-all duration-300 hover:scale-110 backdrop-blur-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            imageResult.innerHTML = imageHtml;
            generatedImages[sceneId] = imageUrl;
            showToast('🎨 Image generated successfully!', 'success');
        } else {
            showToast('❌ Error: ' + (result.message || 'Failed to generate image'), 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('❌ Failed to generate image: ' + error.message, 'error');
    } finally {
        loadingDiv.classList.add('hidden');
        generateBtn.disabled = false;
        generateBtn.style.opacity = '1';
        generateBtn.innerHTML = originalBtnText;
    }
}

// Download image
function downloadImage(imageUrl, sceneId) {
    fetch(imageUrl)
        .then(response => response.blob())
        .then(blob => {
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.href = url;
            link.download = `scene_${sceneId}_image.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            showToast('📸 Image downloaded!', 'success');
        })
        .catch(() => {
            // Fallback for cross-origin images
            const link = document.createElement('a');
            link.href = imageUrl;
            link.download = `scene_${sceneId}_image.png`;
            link.target = '_blank';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showToast('📸 Image opened in new tab', 'info');
        });
}

// Show toast notification
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
    
    toast.className = `fixed bottom-8 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-xl text-white font-medium z-50 transition-all duration-300 ${colors[type] || colors.info} opacity-0 translate-y-4 shadow-lg`;
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
@keyframes spin {
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
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

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #7c3aed, #db2777);
}

/* Loading animation for button */
button:disabled {
    cursor: not-allowed;
}

/* Smooth transitions */
* {
    transition: all 0.2s ease;
}

/* Glass morphism effect */
.bg-gray-900\/40 {
    backdrop-filter: blur(12px);
}
</style>
@endsection