@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black py-[120px] px-4">
    <div class="container mx-auto max-w-4xl">
        <!-- Header Section -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 mb-4 shadow-lg">
                <i class="fas fa-tv text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-3 bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">Create New Episode</h1>
            <p class="text-gray-300 text-lg">For series: <span class="text-purple-400 font-semibold">{{ $series->project_name }}</span></p>
        </div>

        <!-- Step Indicator -->
        <div class="flex items-center justify-center gap-4 mb-12">
            <div class="flex items-center gap-2" id="step2Indicator">
                <div class="w-10 h-10 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold shadow-lg">2</div>
                <span class="text-white text-sm hidden sm:inline font-medium">Prompt</span>
            </div>
            <div class="w-16 h-px bg-gray-700" id="line2"></div>
            <div class="flex items-center gap-2" id="step3Indicator">
                <div class="w-10 h-10 rounded-full bg-gray-700 text-gray-400 flex items-center justify-center font-bold">3</div>
                <span class="text-gray-400 text-sm hidden sm:inline">Concept</span>
            </div>
        </div>

        <!-- Step 2: Add Prompt for Episode -->
        <div id="step2" class="bg-gray-900/50 backdrop-blur-lg rounded-2xl border border-gray-800 p-8 transform transition-all duration-500 hover:border-purple-500/50">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center shadow-lg">
                    <i class="fas fa-edit text-white text-lg"></i>
                </div>
                <h2 class="text-2xl font-bold text-white">
                    Episode {{ $nextEpisodeNumber }} Prompt
                </h2>
            </div>
            
            <form id="promptForm">
                @csrf
                <input type="hidden" id="series_id" value="{{ $series->id }}">
                <div class="mb-4">
                    <label class="block text-white font-semibold mb-2 flex items-center gap-2">
                        <i class="fas fa-pen-fancy text-purple-400 text-sm"></i>
                        Describe Episode {{ $nextEpisodeNumber }}
                    </label>
                    <textarea id="prompt" rows="6" required 
                              placeholder="Example: A young detective discovers a mysterious case that leads her into a world of supernatural phenomena. She must uncover the truth before it's too late..."
                              class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all duration-300"></textarea>
                    <p class="text-gray-400 text-sm mt-2">💡 Be detailed about what happens in this episode (minimum 50 characters)</p>
                    <div class="text-right text-xs text-gray-500 mt-1">
                        <span id="promptCharCount">0</span> characters
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <a href="{{ route('web-series.show', $series->id) }}" 
                       class="px-6 py-3 bg-gray-700 hover:bg-gray-600 rounded-xl text-white font-semibold transition-all duration-300 flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Back
                    </a>
                    <button type="submit" id="generateConceptBtn" 
                            class="flex-1 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 flex items-center justify-center gap-2 shadow-lg hover:shadow-pink-500/25">
                        <i class="fas fa-magic"></i>
                        Generate Concept
                    </button>
                </div>
            </form>
        </div>

        <!-- Step 3: Review & Edit Concept -->
        <div id="step3" class="hidden bg-gray-900/50 backdrop-blur-lg rounded-2xl border border-gray-800 p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center shadow-lg">
                    <i class="fas fa-brain text-white text-lg"></i>
                </div>
                <h2 class="text-2xl font-bold text-white">Episode {{ $nextEpisodeNumber }} Concept</h2>
            </div>
            
            <div class="mb-6">
                <label class="block text-white font-semibold mb-2 flex items-center gap-2">
                    <i class="fas fa-align-left text-purple-400"></i>
                    Generated Concept
                </label>
                <textarea id="concept" rows="10" 
                          class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all duration-300"></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="goBackToStep(2)" 
                        class="px-6 py-2.5 bg-gray-700 hover:bg-gray-600 rounded-xl text-white font-semibold transition-all duration-300 flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </button>
                <button id="regenerateConceptBtn" 
                        class="flex-1 py-2.5 bg-yellow-600 hover:bg-yellow-700 rounded-xl text-white font-semibold transition-all duration-300 flex items-center justify-center gap-2">
                    <i class="fas fa-sync-alt"></i>
                    Regenerate
                </button>
                <button id="continueBtn" 
                        class="flex-1 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 flex items-center justify-center gap-2 shadow-lg hover:shadow-pink-500/25">
                    <i class="fas fa-check-circle"></i>
                    Continue to Generate Segments
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Full Page Loader -->
<div id="fullPageLoader" class="fixed inset-0 bg-black/95 backdrop-blur-xl z-[100] hidden items-center justify-center">
    <div class="text-center transform transition-all duration-500 scale-95" id="loaderContent">
        <div id="spinnerContainer" class="mb-8">
            <div class="relative w-32 h-32 mx-auto">
                <div class="absolute inset-0 border-4 border-purple-500/20 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-t-purple-500 border-r-pink-500 border-b-purple-500 border-l-pink-500 rounded-full animate-spin"></div>
                <div class="absolute inset-2 border-2 border-purple-500/10 rounded-full"></div>
                <div class="absolute inset-4 bg-gradient-to-br from-purple-600/20 to-pink-600/20 rounded-full"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <i class="fas fa-film text-purple-400 text-4xl animate-pulse"></i>
                </div>
            </div>
        </div>
        
        <div id="successContainer" class="hidden mb-8">
            <div class="relative w-32 h-32 mx-auto">
                <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-500 rounded-full animate-ping opacity-75"></div>
                <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-500 rounded-full"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-16 h-16 text-white animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div id="workingBadge" class="inline-flex items-center gap-2 bg-purple-500/20 backdrop-blur-sm px-4 py-2 rounded-full mb-4 border border-purple-500/30">
            <div class="w-2 h-2 rounded-full bg-purple-400 animate-pulse"></div>
            <span class="text-purple-400 text-sm font-medium">AI WORKING</span>
        </div>
        
        <div id="successBadge" class="hidden inline-flex items-center gap-2 bg-green-500/20 backdrop-blur-sm px-4 py-2 rounded-full mb-4 border border-green-500/30">
            <div class="w-2 h-2 rounded-full bg-green-400"></div>
            <span class="text-green-400 text-sm font-medium">COMPLETE!</span>
        </div>
        
        <h2 id="loaderTitle" class="text-3xl font-bold text-white mb-3">Creating Your Segments</h2>
        <p id="loaderMessage" class="text-gray-400 text-lg mb-6">Our AI is generating segments for your episode</p>
        
        <div class="max-w-md mx-auto">
            <div class="w-full h-1.5 bg-gray-800 rounded-full overflow-hidden">
                <div id="progressBarFill" class="h-full bg-gradient-to-r from-purple-500 via-pink-500 to-purple-500 rounded-full transition-all duration-300" style="width: 0%; background-size: 200% 100%;"></div>
            </div>
            <p id="progressText" class="text-gray-500 text-xs mt-3">Initializing...</p>
        </div>
    </div>
</div>

<script>
let currentSeriesId = {{ $series->id }};
let currentEpisodeId = null;
let currentPrompt = '';
let currentConcept = '';
let nextEpisodeNumber = {{ $nextEpisodeNumber }};

// Character counter for prompt
document.addEventListener('input', function(e) {
    if(e.target.id === 'prompt') {
        document.getElementById('promptCharCount').textContent = e.target.value.length;
    }
});

function goBackToStep(step) {
    if (step === 2) {
        if (confirm('Are you sure you want to go back? Your current progress will be lost.')) {
            window.location.href = '{{ route("web-series.show", $series->id) }}';
        }
    } else {
        switchStep(step);
    }
}

async function apiCall(url, method, data) {
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: data ? JSON.stringify(data) : null
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || `HTTP ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

function showFullPageLoader() {
    const loader = document.getElementById('fullPageLoader');
    const content = document.getElementById('loaderContent');
    loader.classList.remove('hidden');
    loader.classList.add('flex');
    setTimeout(() => {
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }, 10);
}

function hideFullPageLoader() {
    const loader = document.getElementById('fullPageLoader');
    const content = document.getElementById('loaderContent');
    content.classList.remove('scale-100');
    content.classList.add('scale-95');
    setTimeout(() => {
        loader.classList.add('hidden');
        loader.classList.remove('flex');
    }, 300);
}

function updateLoaderProgress(step, message) {
    const progressSteps = {
        1: 'Saving your concept...',
        2: 'Analyzing story elements...',
        3: 'Creating segment structures...',
        4: 'Generating segment details...',
        5: 'Finalizing your episode...'
    };
    
    const progressPercent = (step / 5) * 100;
    document.getElementById('progressBarFill').style.width = progressPercent + '%';
    document.getElementById('progressText').textContent = message || progressSteps[step] || 'Processing...';
}

async function showSuccessAndRedirect(redirectUrl) {
    const spinnerContainer = document.getElementById('spinnerContainer');
    const successContainer = document.getElementById('successContainer');
    const workingBadge = document.getElementById('workingBadge');
    const successBadge = document.getElementById('successBadge');
    const loaderTitle = document.getElementById('loaderTitle');
    const loaderMessage = document.getElementById('loaderMessage');
    const progressBarFill = document.getElementById('progressBarFill');
    const progressText = document.getElementById('progressText');
    
    progressBarFill.style.width = '100%';
    progressText.textContent = 'Complete! Redirecting...';
    
    setTimeout(() => {
        spinnerContainer.classList.add('hidden');
        successContainer.classList.remove('hidden');
        workingBadge.classList.add('hidden');
        successBadge.classList.remove('hidden');
        loaderTitle.textContent = 'All Segments Created!';
        loaderMessage.textContent = 'Your episode is ready. Redirecting to series...';
        successContainer.classList.add('animate-bounce');
    }, 500);
    
    setTimeout(() => {
        window.location.href = redirectUrl;
    }, 2500);
}

// Step 2: Generate Concept
document.getElementById('promptForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!currentSeriesId) {
        alert('Series ID not found. Please create a series first.');
        window.location.href = '{{ route("web-series.create") }}';
        return;
    }
    
    currentPrompt = document.getElementById('prompt').value;
    if (currentPrompt.length < 10) {
        alert('Please enter a more detailed prompt (minimum 10 characters)');
        return;
    }
    
    const btn = document.getElementById('generateConceptBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    btn.disabled = true;
    
    try {
        // Send episode_number along with prompt
        const result = await apiCall(`/series/${currentSeriesId}/generate-episode1-concept`, 'POST', {
            prompt: currentPrompt,
            episode_number: nextEpisodeNumber
        });
        
        if (result.success) {
            currentConcept = result.concept;
            currentEpisodeId = result.episode_id;
            document.getElementById('concept').value = currentConcept;
            switchStep(3);
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        alert('Error: ' + error.message);
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});

/// Regenerate Concept
document.getElementById('regenerateConceptBtn')?.addEventListener('click', async () => {
    if (!currentSeriesId) {
        alert('No series found');
        return;
    }
    
    const btn = document.getElementById('regenerateConceptBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Regenerating...';
    btn.disabled = true;
    
    try {
        const result = await apiCall(`/series/${currentSeriesId}/generate-episode1-concept`, 'POST', {
            prompt: currentPrompt,
            episode_number: nextEpisodeNumber
        });
        
        if (result.success) {
            currentConcept = result.concept;
            document.getElementById('concept').value = currentConcept;
            alert('✅ Concept regenerated successfully!');
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        alert('Error: ' + error.message);
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});

// Continue button - Save concept and generate segments
document.getElementById('continueBtn').addEventListener('click', async () => {
    if (!currentSeriesId) {
        alert('No series found');
        return;
    }
    
    const updatedConcept = document.getElementById('concept').value;
    
    try {
        showFullPageLoader();
        
        updateLoaderProgress(1, 'Saving your concept...');
        await new Promise(resolve => setTimeout(resolve, 800));
        
        updateLoaderProgress(2, 'Analyzing story elements...');
        await apiCall(`/series/${currentSeriesId}/update-episode1-concept`, 'POST', {
            concept: updatedConcept
        });
        await new Promise(resolve => setTimeout(resolve, 800));
        
        updateLoaderProgress(3, 'Creating segment structures...');
        await new Promise(resolve => setTimeout(resolve, 500));
        
        updateLoaderProgress(4, 'Generating segment details...');
        
        const result = await apiCall(`/series/${currentSeriesId}/generate-episode1-scenes`, 'POST', {
            total_scenes: 5
        });
        
        updateLoaderProgress(5, 'Finalizing your episode...');
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        if (result.success) {
            await showSuccessAndRedirect(`/web-series/${currentSeriesId}`);
        } else {
            hideFullPageLoader();
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        hideFullPageLoader();
        alert('Error: ' + error.message);
    }
});

function switchStep(step) {
    for(let i = 2; i <= 3; i++) {
        const div = document.getElementById(`step${i}`);
        if(div) div.classList.add('hidden');
        
        const indicator = document.getElementById(`step${i}Indicator`);
        if(indicator) {
            const circle = indicator.querySelector('div');
            const text = indicator.querySelector('span');
            
            if(i < step) {
                if(circle) {
                    circle.classList.remove('bg-gray-700', 'text-gray-400', 'bg-purple-600');
                    circle.classList.add('bg-green-600', 'text-white');
                    circle.innerHTML = '✓';
                }
                if(text) text.classList.remove('text-gray-400');
                if(text) text.classList.add('text-white');
            } else if(i === step) {
                if(circle) {
                    circle.classList.remove('bg-gray-700', 'text-gray-400', 'bg-green-600');
                    circle.classList.add('bg-purple-600', 'text-white');
                    circle.innerHTML = step;
                }
                if(text) text.classList.remove('text-gray-400');
                if(text) text.classList.add('text-white');
            } else {
                if(circle) {
                    circle.classList.remove('bg-purple-600', 'bg-green-600', 'text-white');
                    circle.classList.add('bg-gray-700', 'text-gray-400');
                    circle.innerHTML = i;
                }
                if(text) text.classList.add('text-gray-400');
            }
        }
    }
    
    const targetStep = document.getElementById(`step${step}`);
    if(targetStep) targetStep.classList.remove('hidden');
    
    for(let i = 1; i <= 1; i++) {
        const line = document.getElementById(`line2`);
        if(line) {
            if(i < step) {
                line.classList.remove('bg-gray-700');
                line.classList.add('bg-gradient-to-r', 'from-purple-500', 'to-pink-500');
            } else {
                line.classList.remove('bg-gradient-to-r', 'from-purple-500', 'to-pink-500');
                line.classList.add('bg-gray-700');
            }
        }
    }
}

// We're on step 2 directly
switchStep(2);
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(0.95); }
}

.animate-spin { animation: spin 1s linear infinite; }
.animate-bounce { animation: bounce 0.5s ease-in-out 3; }
.animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }

#step2, #step3 {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

input:focus, textarea:focus {
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
}

@media (max-width: 768px) {
    .py-\[120px\] {
        padding-top: 60px !important;
        padding-bottom: 60px !important;
    }
}

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
@endsection