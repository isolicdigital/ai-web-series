{{-- resources/views/web-series/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black py-[120px] px-4">
    <div class="container mx-auto max-w-7xl">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('web-series.my-series') }}" class="text-gray-400 hover:text-white transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to My Series
            </a>
        </div>
        
        <!-- Series Info -->
        <div class="bg-gray-900/50 rounded-2xl border border-gray-800 p-6 mb-8">
            <div class="flex justify-between items-start flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $series->project_name }}</h1>
                    <div class="flex flex-wrap gap-2 items-center">
                        <span class="bg-purple-600/20 text-purple-300 px-3 py-1 rounded-full text-sm">{{ $series->category ? $series->category->name : 'Uncategorized' }}</span>
                        <span class="text-gray-400 text-sm">{{ $series->scenes->count() }} Scenes</span>
                    </div>
                    <p class="text-gray-300 mt-4 max-w-2xl">{{ $series->concept }}</p>
                </div>
                
                <!-- Generate Video Button -->
                @php
                    $allImagesGenerated = $series->scenes->every(function($scene) {
                        return !is_null($scene->generated_image_url);
                    });
                @endphp
                
                @if($allImagesGenerated && $series->scenes->count() > 0)
                <a href="{{ route('web-series.generate-video', $series->id) }}" 
                   class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 rounded-xl text-white font-semibold transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-green-500/25">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Generate Video
                </a>
                @endif
            </div>
        </div>
        
        <!-- Progress Bar -->
        @php
            $totalScenes = $series->scenes->count();
            $generatedImages = $series->scenes->whereNotNull('generated_image_url')->count();
            $progressPercentage = $totalScenes > 0 ? ($generatedImages / $totalScenes) * 100 : 0;
            $currentUnlockedScene = 1;
            foreach($series->scenes as $index => $scene) {
                if(!$scene->generated_image_url) {
                    $currentUnlockedScene = $index + 1;
                    break;
                }
                $currentUnlockedScene = $index + 2;
            }
        @endphp
        
        <div class="mb-6 bg-gray-900/30 rounded-xl p-4">
            <div class="flex justify-between text-sm text-gray-400 mb-2">
                <span>Image Generation Progress</span>
                <span>{{ $generatedImages }} / {{ $totalScenes }} scenes</span>
            </div>
            <div class="w-full h-2 bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full transition-all duration-500" style="width: {{ $progressPercentage }}%"></div>
            </div>
            <div class="text-xs text-gray-500 mt-2 text-center">
                @if($currentUnlockedScene <= $totalScenes)
                    🔓 Scene {{ $currentUnlockedScene }} is now available to generate
                @else
                    🎉 All scenes completed!
                @endif
            </div>
        </div>
        
        <!-- Scenes -->
        <h2 class="text-2xl font-bold text-white mb-4">All Scenes</h2>
        
        @foreach($series->scenes as $index => $scene)
        @php
            $isLocked = false;
            $previousScene = $series->scenes[$index - 1] ?? null;
            
            if($index === 0) {
                $isLocked = false;
            } else {
                if($previousScene && !$previousScene->generated_image_url) {
                    $isLocked = true;
                } else {
                    $isLocked = false;
                }
            }
            
            $isCompleted = !is_null($scene->generated_image_url);
        @endphp
        
        <div class="bg-gray-900/40 rounded-2xl border border-gray-800 mb-6 overflow-hidden transition-all duration-300 
                    {{ $isLocked ? 'opacity-60' : 'hover:border-purple-500/30' }}" 
             id="scene-{{ $scene->id }}">
            
            <!-- Header -->
            <div class="p-4 border-b border-gray-800 bg-gray-800/30">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full {{ $isCompleted ? 'bg-green-600' : ($isLocked ? 'bg-gray-600' : 'bg-purple-600') }} text-white flex items-center justify-center font-bold">
                            @if($isCompleted)
                                ✓
                            @else
                                {{ $scene->scene_number }}
                            @endif
                        </div>
                        <h3 class="text-xl font-bold text-white">{{ $scene->title }}</h3>
                    </div>
                    @if($isLocked)
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span class="text-yellow-500 text-sm">Locked - Complete Scene {{ $scene->scene_number - 1 }} first</span>
                    </div>
                    @elseif($isCompleted)
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-green-500 text-sm">Completed</span>
                    </div>
                    @else
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="text-purple-500 text-sm">Ready to generate</span>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Two Columns -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                <!-- Left: Prompt -->
                <div class="bg-purple-900/20 rounded-xl p-4">
                    <div class="flex justify-between items-center mb-2 flex-wrap gap-2">
                        <span class="text-purple-400 font-semibold">AI Image Prompt</span>
                        <div class="flex gap-2">
                            @if($isCompleted)
                            <button onclick="editPrompt({{ $scene->id }})" 
                                    class="text-xs bg-white/10 backdrop-blur-md border border-white/20 hover:bg-white/20 hover:border-blue-500/50 px-3 py-1 rounded-lg text-white font-medium transition-all duration-300 flex items-center gap-1.5 group">
                                <svg class="w-3 h-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                <span>Edit</span>
                            </button>
                            @endif
                        </div>
                    </div>
                    <p id="prompt-{{ $scene->id }}" class="text-gray-300 text-sm">{{ $scene->image_prompt }}</p>
                    
                    @if(!$isCompleted)
                        @if(!$isLocked)
                        <button onclick="generateImage({{ $scene->id }})" 
                                id="btn-{{ $scene->id }}"
                                class="w-full mt-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-lg text-white font-medium transition">
                            Generate Image
                        </button>
                        <div id="loading-{{ $scene->id }}" class="hidden text-center mt-2">
                            <div class="inline-block w-5 h-5 border-2 border-purple-500 border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-gray-400 text-sm ml-2">Generating...</span>
                        </div>
                        @else
                        <button disabled 
                                class="w-full mt-4 py-2 bg-gray-600 cursor-not-allowed rounded-lg text-white font-medium opacity-50">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Locked - Complete previous scene
                        </button>
                        @endif
                    @else
                    <div class="mt-4 flex gap-2">
                        <button onclick="regenerateImage({{ $scene->id }})" 
                                id="regenerateBtn-{{ $scene->id }}"
                                class="flex-1 py-2 bg-white/10 backdrop-blur-md border border-white/20 hover:bg-white/20 hover:border-purple-500/50 rounded-lg text-white font-medium transition-all duration-300 flex items-center justify-center gap-2 group">
                            <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span class="relative">
                                Regenerate Image
                                <span class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-purple-500 to-pink-500 scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></span>
                            </span>
                        </button>
                        <div id="regenerating-{{ $scene->id }}" class="hidden text-center mt-2">
                            <div class="inline-block w-5 h-5 border-2 border-purple-500 border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-gray-400 text-sm ml-2">Regenerating...</span>
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- Right: Image -->
                <div class="bg-gray-800/30 rounded-xl p-4 min-h-[250px] flex items-center justify-center">
                    <div id="image-{{ $scene->id }}" class="w-full">
                        @if($scene->generated_image_url)
                        <div class="relative group">
                            <img src="{{ asset($scene->generated_image_url) }}" 
                                 class="w-full rounded-lg max-h-64 object-contain"
                                 onerror="this.src='https://placehold.co/800x600/1a1a1a/7c3aed?text=Click+Generate'">
                            <button onclick="downloadImage('{{ $scene->generated_image_url }}', {{ $scene->id }})" 
                                    class="absolute top-2 right-2 bg-black/50 hover:bg-purple-600 p-2 rounded-lg transition opacity-0 group-hover:opacity-100">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </button>
                        </div>
                        @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-gray-500 text-sm">No image generated yet</p>
                            @if($isLocked)
                            <p class="text-yellow-500 text-xs mt-2">Complete previous scene to unlock</p>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Edit Prompt Modal -->
<div id="editPromptModal" class="fixed inset-0 bg-black/90 backdrop-blur-md z-50 hidden items-center justify-center">
    <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl border border-purple-500/30 max-w-2xl w-full mx-4 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-white">Edit Image Prompt</h3>
        </div>
        <textarea id="editPromptText" rows="8" 
                  class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all duration-300 mb-4"></textarea>
        <input type="hidden" id="editSceneId">
        <div class="flex gap-3">
            <button onclick="closeEditModal()" 
                    class="flex-1 py-2.5 bg-gray-700 hover:bg-gray-600 rounded-xl text-white font-medium transition-all duration-300">
                Cancel
            </button>
            <button onclick="saveEditedPromptAndRegenerate()" 
                    class="flex-1 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-medium transition-all duration-300">
                Save & Regenerate Image
            </button>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-50 hidden"></div>

<script>
// Polling intervals
const intervals = {};

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const colors = { success: 'bg-green-600', error: 'bg-red-600', info: 'bg-blue-600', warning: 'bg-yellow-600' };
    toast.className = `fixed bottom-8 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-xl text-white font-medium z-50 transition-all duration-300 ${colors[type]} opacity-0 translate-y-4 shadow-lg`;
    toast.textContent = message;
    toast.style.display = 'block';
    setTimeout(() => toast.classList.remove('opacity-0', 'translate-y-4'), 10);
    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-4');
        setTimeout(() => toast.style.display = 'none', 300);
    }, 3000);
}

function copyPrompt(sceneId) {
    const prompt = document.getElementById(`prompt-${sceneId}`).innerText;
    navigator.clipboard.writeText(prompt);
    showToast('✅ Prompt copied!', 'success');
}

// Generate image
async function generateImage(sceneId) {
    const prompt = document.getElementById(`prompt-${sceneId}`).innerText;
    const btn = document.getElementById(`btn-${sceneId}`);
    const loading = document.getElementById(`loading-${sceneId}`);
    const imageDiv = document.getElementById(`image-${sceneId}`);
    
    btn.disabled = true;
    btn.classList.add('opacity-50');
    loading.classList.remove('hidden');
    
    imageDiv.innerHTML = `
        <div class="text-center py-8">
            <div class="w-10 h-10 border-4 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
            <p class="text-purple-400 text-sm">Generating image...</p>
            <p class="text-gray-500 text-xs mt-1">This may take 20-30 seconds</p>
        </div>
    `;
    
    try {
        const response = await fetch('/generate-image', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ prompt: prompt, scene_id: sceneId })
        });
        
        const result = await response.json();
        
        if (result.success && result.image_url) {
            displayImage(sceneId, result.image_url);
            showToast('✅ Image generated successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else if (result.processing) {
            showToast('🖼️ Image generation started. Please wait...', 'info');
            startPolling(sceneId);
        } else {
            imageDiv.innerHTML = `<div class="text-center py-8 text-red-400">Error: ${result.message}</div>`;
            showToast('❌ Error: ' + result.message, 'error');
            btn.disabled = false;
            btn.classList.remove('opacity-50');
            loading.classList.add('hidden');
        }
    } catch (error) {
        imageDiv.innerHTML = `<div class="text-center py-8 text-red-400">Network error. Please try again.</div>`;
        showToast('❌ Network error. Please try again.', 'error');
        btn.disabled = false;
        btn.classList.remove('opacity-50');
        loading.classList.add('hidden');
    }
}

// Regenerate image
async function regenerateImage(sceneId) {
    const prompt = document.getElementById(`prompt-${sceneId}`).innerText;
    const btn = document.getElementById(`regenerateBtn-${sceneId}`);
    const loading = document.getElementById(`regenerating-${sceneId}`);
    const imageDiv = document.getElementById(`image-${sceneId}`);
    
    btn.disabled = true;
    btn.classList.add('opacity-50');
    loading.classList.remove('hidden');
    
    imageDiv.innerHTML = `
        <div class="text-center py-8">
            <div class="w-10 h-10 border-4 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
            <p class="text-purple-400 text-sm">Regenerating image...</p>
            <p class="text-gray-500 text-xs mt-1">This may take 20-30 seconds</p>
        </div>
    `;
    
    try {
        const response = await fetch('/generate-image', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ prompt: prompt, scene_id: sceneId })
        });
        
        const result = await response.json();
        
        if (result.success && result.image_url) {
            displayImage(sceneId, result.image_url);
            showToast('✅ Image regenerated successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else if (result.processing) {
            showToast('🖼️ Image regeneration started. Please wait...', 'info');
            startPolling(sceneId);
        } else {
            imageDiv.innerHTML = `<div class="text-center py-8 text-red-400">Error: ${result.message}</div>`;
            showToast('❌ Error: ' + result.message, 'error');
            btn.disabled = false;
            btn.classList.remove('opacity-50');
            loading.classList.add('hidden');
        }
    } catch (error) {
        imageDiv.innerHTML = `<div class="text-center py-8 text-red-400">Network error. Please try again.</div>`;
        showToast('❌ Network error. Please try again.', 'error');
        btn.disabled = false;
        btn.classList.remove('opacity-50');
        loading.classList.add('hidden');
    }
}

// Edit prompt
let currentEditSceneId = null;

function editPrompt(sceneId) {
    currentEditSceneId = sceneId;
    const prompt = document.getElementById(`prompt-${sceneId}`).innerText;
    document.getElementById('editPromptText').value = prompt;
    document.getElementById('editSceneId').value = sceneId;
    document.getElementById('editPromptModal').classList.remove('hidden');
    document.getElementById('editPromptModal').classList.add('flex');
}

function closeEditModal() {
    document.getElementById('editPromptModal').classList.add('hidden');
    document.getElementById('editPromptModal').classList.remove('flex');
}

async function saveEditedPromptAndRegenerate() {
    const newPrompt = document.getElementById('editPromptText').value;
    const sceneId = document.getElementById('editSceneId').value;
    
    if (!newPrompt || newPrompt.length < 10) {
        showToast('Please enter a valid prompt (minimum 10 characters)', 'error');
        return;
    }
    
    closeEditModal();
    
    document.getElementById(`prompt-${sceneId}`).innerText = newPrompt;
    
    const btn = document.getElementById(`regenerateBtn-${sceneId}`);
    const loading = document.getElementById(`regenerating-${sceneId}`);
    const imageDiv = document.getElementById(`image-${sceneId}`);
    
    if (btn) {
        btn.disabled = true;
        btn.classList.add('opacity-50');
    }
    if (loading) loading.classList.remove('hidden');
    
    imageDiv.innerHTML = `
        <div class="text-center py-8">
            <div class="w-10 h-10 border-4 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
            <p class="text-purple-400 text-sm">Regenerating with new prompt...</p>
            <p class="text-gray-500 text-xs mt-1">This may take 20-30 seconds</p>
        </div>
    `;
    
    try {
        const response = await fetch('/generate-image', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ prompt: newPrompt, scene_id: sceneId })
        });
        
        const result = await response.json();
        
        if (result.success && result.image_url) {
            displayImage(sceneId, result.image_url);
            showToast('✅ Image regenerated with new prompt!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else if (result.processing) {
            showToast('🖼️ Image regeneration started. Please wait...', 'info');
            startPolling(sceneId);
        } else {
            imageDiv.innerHTML = `<div class="text-center py-8 text-red-400">Error: ${result.message}</div>`;
            showToast('❌ Error: ' + result.message, 'error');
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-50');
            }
            if (loading) loading.classList.add('hidden');
        }
    } catch (error) {
        imageDiv.innerHTML = `<div class="text-center py-8 text-red-400">Network error. Please try again.</div>`;
        showToast('❌ Network error. Please try again.', 'error');
        if (btn) {
            btn.disabled = false;
            btn.classList.remove('opacity-50');
        }
        if (loading) loading.classList.add('hidden');
    }
}

function startPolling(sceneId) {
    if (intervals[sceneId]) clearInterval(intervals[sceneId]);
    
    let attempts = 0;
    intervals[sceneId] = setInterval(async () => {
        attempts++;
        
        try {
            const response = await fetch('/check-image-status', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ scene_id: sceneId })
            });
            
            const result = await response.json();
            
            if (result.success && result.status === 'completed' && result.image_url) {
                clearInterval(intervals[sceneId]);
                delete intervals[sceneId];
                displayImage(sceneId, result.image_url);
                showToast('✅ Image ready!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else if (attempts >= 30) {
                clearInterval(intervals[sceneId]);
                delete intervals[sceneId];
                showToast('⚠️ Timeout. Please try again.', 'warning');
            }
        } catch (error) {
            console.error('Polling error:', error);
        }
    }, 3000);
}

function displayImage(sceneId, imageUrl) {
    const imageDiv = document.getElementById(`image-${sceneId}`);
    imageDiv.innerHTML = `
        <div class="relative group">
            <img src="${imageUrl}" 
                 class="w-full rounded-lg max-h-64 object-contain"
                 onerror="this.src='https://placehold.co/800x600/1a1a1a/ef4444?text=Load+Error'">
            <button onclick="downloadImage('${imageUrl}', ${sceneId})" 
                    class="absolute top-2 right-2 bg-black/50 hover:bg-purple-600 p-2 rounded-lg transition opacity-0 group-hover:opacity-100">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
            </button>
        </div>
    `;
    
    const sceneCard = document.getElementById(`scene-${sceneId}`);
    if (sceneCard) {
        const headerDiv = sceneCard.querySelector('.flex.items-center.justify-between');
        if (headerDiv) {
            const statusSpan = headerDiv.querySelector('.flex.items-center.gap-2:last-child');
            if (statusSpan) {
                statusSpan.innerHTML = `
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-green-500 text-sm">Completed</span>
                `;
            }
        }
    }
}

function downloadImage(imageUrl, sceneId) {
    fetch(imageUrl)
        .then(r => r.blob())
        .then(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `scene_${sceneId}.png`;
            a.click();
            URL.revokeObjectURL(url);
            showToast('📸 Image downloaded!', 'success');
        })
        .catch(() => {
            window.open(imageUrl, '_blank');
            showToast('📸 Image opened in new tab', 'info');
        });
}
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
.animate-spin { animation: spin 1s linear infinite; }

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
</style>
@endsection

