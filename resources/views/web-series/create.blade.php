@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black py-[120px] px-4">
    <div class="container mx-auto max-w-4xl">
        <!-- Header Section -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 mb-4 shadow-lg">
                <i class="fas fa-film text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-3 bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">Create Your Web Series</h1>
            <p class="text-gray-300 text-lg">Bring your story to life with AI-powered segments</p>
        </div>

        <!-- Step Indicator -->
        <div class="flex items-center justify-center gap-4 mb-12">
            <div class="flex items-center gap-2" id="step1Indicator">
                <div class="w-10 h-10 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold shadow-lg">1</div>
                <span class="text-white text-sm hidden sm:inline font-medium">Series</span>
            </div>
            <div class="w-16 h-px bg-gradient-to-r from-purple-500 to-transparent" id="line1"></div>
            <div class="flex items-center gap-2" id="step2Indicator">
                <div class="w-10 h-10 rounded-full bg-gray-700 text-gray-400 flex items-center justify-center font-bold">2</div>
                <span class="text-gray-400 text-sm hidden sm:inline">Prompt</span>
            </div>
            <div class="w-16 h-px bg-gray-700" id="line2"></div>
            <div class="flex items-center gap-2" id="step3Indicator">
                <div class="w-10 h-10 rounded-full bg-gray-700 text-gray-400 flex items-center justify-center font-bold">3</div>
                <span class="text-gray-400 text-sm hidden sm:inline">Concept</span>
            </div>
        </div>

        <!-- Step 1: Create Series -->
        <div id="step1" class="bg-gray-900/50 backdrop-blur-lg rounded-2xl border border-gray-800 p-8 transform transition-all duration-500 hover:border-purple-500/50">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center shadow-lg">
                    <i class="fas fa-tv text-white text-lg"></i>
                </div>
                <h2 class="text-2xl font-bold text-white">Create Your Series</h2>
            </div>
            
            <form id="projectForm">
                @csrf
                <div class="mb-6">
                    <label class="block text-white font-semibold mb-2 flex items-center gap-2">
                        <i class="fas fa-heading text-purple-400 text-sm"></i>
                        Series Name
                    </label>
                    <input type="text" id="project_name" required 
                           placeholder="e.g., The Chronicles of AI, Future Tales, Mystery Manor"
                           class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all duration-300">
                    <p class="text-gray-500 text-xs mt-2">Give your web series a unique and memorable title</p>
                </div>
                
                <div class="mb-8">
                    <label class="block text-white font-semibold mb-3 flex items-center gap-2">
                        <i class="fas fa-tags text-purple-400 text-sm"></i>
                        Select Genre
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        @foreach($categories as $category)
                        @php
                            $template = \App\Models\CategoryTemplate::where('category_id', $category->id)
                                ->where('is_active', true)
                                ->first();
                            
                            $imageUrl = null;
                            if ($template && $template->init_image) {
                                $imagePath = str_replace(['/public/', 'public/'], '', $template->init_image);
                                $imagePath = ltrim($imagePath, '/');
                                if (file_exists(public_path($imagePath))) {
                                    $imageUrl = asset($imagePath);
                                }
                            }
                        @endphp
                        <label class="category-card cursor-pointer group">
                            <input type="radio" name="category_id" value="{{ $category->id }}" class="hidden peer" required>
                            <div class="relative overflow-hidden rounded-xl border-2 border-gray-700 bg-gray-800/30 transition-all duration-300 peer-checked:border-purple-500 peer-checked:bg-purple-500/20 peer-checked:shadow-lg hover:border-gray-500 hover:bg-gray-700/50 cursor-pointer">
                                <div class="relative h-32 overflow-hidden">
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" 
                                             alt="{{ $category->name }}" 
                                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-purple-900/50 to-pink-900/50 flex items-center justify-center">
                                            <i class="fas {{ $category->icon ?? 'fa-tag' }} text-4xl text-purple-400/70"></i>
                                        </div>
                                    @endif
                                    
                                    <div class="absolute top-2 right-2 w-6 h-6 rounded-full bg-purple-500 scale-0 peer-checked:scale-100 transition-transform duration-300 flex items-center justify-center shadow-lg z-10">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="p-3 text-center">
                                    <div class="text-white font-semibold text-sm">{{ $category->name }}</div>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <p class="text-gray-500 text-xs mt-3">Choose the genre that best fits your story</p>
                </div>
                
                <button type="submit" 
                        class="group w-full py-3.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 flex items-center justify-center gap-2 shadow-lg hover:shadow-pink-500/25 hover:scale-[1.02]">
                    <span>Create Series</span>
                    <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </button>
            </form>
        </div>

        <!-- Step 2: Add Prompt for Episode 1 -->
        <div id="step2" class="hidden bg-gray-900/50 backdrop-blur-lg rounded-2xl border border-gray-800 p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center shadow-lg">
                    <i class="fas fa-edit text-white text-lg"></i>
                </div>
                <h2 class="text-2xl font-bold text-white">Episode 1 Prompt</h2>
            </div>
            
            <form id="promptForm">
                <input type="hidden" id="series_id">
                <div class="mb-4">
                    <label class="block text-white font-semibold mb-2 flex items-center gap-2">
                        <i class="fas fa-pen-fancy text-purple-400 text-sm"></i>
                        Describe Episode 1
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
                    <button type="button" onclick="goBackToStep(1)" 
                            class="px-6 py-3 bg-gray-700 hover:bg-gray-600 rounded-xl text-white font-semibold transition-all duration-300 flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Back
                    </button>
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
                <h2 class="text-2xl font-bold text-white">Episode Concept</h2>
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

<!-- Full Page Loader with Changing Text -->
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
        <p id="loaderMessage" class="text-gray-400 text-lg mb-6">Our AI is generating 5 unique segments for your episode</p>
        
        <div class="max-w-md mx-auto">
            <div class="w-full h-1.5 bg-gray-800 rounded-full overflow-hidden">
                <div id="progressBarFill" class="h-full bg-gradient-to-r from-purple-500 via-pink-500 to-purple-500 rounded-full transition-all duration-300" style="width: 0%; background-size: 200% 100%;"></div>
            </div>
            <p id="progressText" class="text-gray-500 text-xs mt-3">Initializing...</p>
        </div>
    </div>
</div>

<!-- Edit Prompt Modal -->
<div id="editPromptModal" class="fixed inset-0 bg-black/90 backdrop-blur-md z-50 hidden items-center justify-center">
    <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl border border-purple-500/30 p-6 max-w-2xl w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="editModalContent">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                <i class="fas fa-edit text-white text-lg"></i>
            </div>
            <h3 class="text-xl font-bold text-white">Edit Your Prompt</h3>
        </div>
        <textarea id="editPromptText" rows="6" 
                  class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all duration-300 mb-4"></textarea>
        <div class="flex gap-3">
            <button onclick="closeEditModal()" 
                    class="flex-1 py-2.5 bg-gray-700 hover:bg-gray-600 rounded-xl text-white font-medium transition-all duration-300">
                <i class="fas fa-times mr-2"></i>Cancel
            </button>
            <button onclick="saveEditedPromptAndRegenerate()" 
                    class="flex-1 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-medium transition-all duration-300">
                <i class="fas fa-save mr-2"></i>Save & Regenerate
            </button>
        </div>
    </div>
</div>

<script>
let currentSeriesId = null;
let currentEpisodeId = null;
let currentPrompt = '';
let currentConcept = '';
let loaderTextInterval = null;

// Character counter for prompt only
document.addEventListener('input', function(e) {
    if(e.target.id === 'prompt') {
        document.getElementById('promptCharCount').textContent = e.target.value.length;
    }
});

// Go back to previous step
function goBackToStep(step) {
    if (step === 1 || step === 2) {
        if (confirm('Are you sure you want to go back? Your current progress will be lost.')) {
            switchStep(step);
        }
    } else {
        switchStep(step);
    }
}

// Helper function for API calls
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
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 500));
            
            if (text.includes('login')) throw new Error('Please login first');
            if (text.includes('404')) throw new Error('API endpoint not found');
            if (text.includes('500')) throw new Error('Server error. Please try again later.');
            throw new Error('Invalid server response');
        }
        
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

// Full Page Loader with Changing Text
function showFullPageLoader() {
    const loader = document.getElementById('fullPageLoader');
    const content = document.getElementById('loaderContent');
    loader.classList.remove('hidden');
    loader.classList.add('flex');
    setTimeout(() => {
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }, 10);
    
    // Start changing text every 2 seconds
    startLoaderTextRotation();
}

function hideFullPageLoader() {
    // Clear text rotation interval
    if (loaderTextInterval) {
        clearInterval(loaderTextInterval);
        loaderTextInterval = null;
    }
    
    const loader = document.getElementById('fullPageLoader');
    const content = document.getElementById('loaderContent');
    content.classList.remove('scale-100');
    content.classList.add('scale-95');
    setTimeout(() => {
        loader.classList.add('hidden');
        loader.classList.remove('flex');
    }, 300);
}

function startLoaderTextRotation() {
    // Clear existing interval
    if (loaderTextInterval) {
        clearInterval(loaderTextInterval);
    }
    
    const messages = [
        "Analyzing your story concept...",
        "Creating unique characters...",
        "Building engaging plot twists...",
        "Developing emotional scenes...",
        "Structuring your narrative...",
        "Adding creative details...",
        "Polishing your episode...",
        "Almost there...",
        "Finalizing your segments..."
    ];
    
    let messageIndex = 0;
    const messageElement = document.getElementById('loaderMessage');
    const titleElement = document.getElementById('loaderTitle');
    
    const titles = [
        "Creating Your Segments",
        "Building Story Elements",
        "Developing Characters",
        "Crafting Plot",
        "Adding Emotion",
        "Structuring Narrative",
        "Polishing Details",
        "Almost Ready",
        "Finalizing"
    ];
    
    // Change text every 2 seconds
    loaderTextInterval = setInterval(() => {
        if (messageElement) {
            messageIndex = (messageIndex + 1) % messages.length;
            messageElement.textContent = messages[messageIndex];
            
            // Fade animation
            messageElement.style.opacity = '0';
            setTimeout(() => {
                if (messageElement) messageElement.style.opacity = '1';
            }, 200);
            
            // Update title occasionally
            if (titleElement && messageIndex % 2 === 0) {
                titleElement.style.opacity = '0';
                setTimeout(() => {
                    if (titleElement) {
                        titleElement.textContent = titles[messageIndex];
                        titleElement.style.opacity = '1';
                    }
                }, 200);
            }
        }
    }, 2000);
}

function updateLoaderProgress(step, message) {
    const progressPercent = (step / 5) * 100;
    document.getElementById('progressBarFill').style.width = progressPercent + '%';
    document.getElementById('progressText').textContent = message || 'Processing...';
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

// Step 1: Create Series
document.getElementById('projectForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const selectedCategory = document.querySelector('input[name="category_id"]:checked');
    if (!selectedCategory) {
        alert('Please select a category');
        return;
    }
    
    try {
        const result = await apiCall('/series/save-project', 'POST', {
            project_name: document.getElementById('project_name').value,
            category_id: selectedCategory.value
        });
        
        if (result.success) {
            currentSeriesId = result.series_id;
            document.getElementById('series_id').value = currentSeriesId;
            switchStep(2);
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        alert('Error: ' + error.message);
    }
});

// Step 2: Generate Concept with full page loader
document.getElementById('promptForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!currentSeriesId) {
        alert('Please create a series first');
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
    
    // Show full page loader
    showConceptLoader();
    
    try {
        const result = await apiCall(`/series/${currentSeriesId}/generate-episode1-concept`, 'POST', {
            prompt: currentPrompt
        });
        
        if (result.success) {
            currentConcept = result.concept;
            currentEpisodeId = result.episode_id;
            document.getElementById('concept').value = currentConcept;
            
            // Simulate progress steps
            updateLoaderMessage('Analyzing your story...');
            await new Promise(resolve => setTimeout(resolve, 800));
            
            updateLoaderMessage('Generating creative concept...');
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            updateLoaderMessage('Refining story elements...');
            await new Promise(resolve => setTimeout(resolve, 800));
            
            updateLoaderMessage('Finalizing your concept...');
            await new Promise(resolve => setTimeout(resolve, 600));
            
            hideConceptLoader();
            switchStep(3);
        } else {
            hideConceptLoader();
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        hideConceptLoader();
        alert('Error: ' + error.message);
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});

// Show concept loader
function showConceptLoader() {
    const existingLoader = document.getElementById('conceptLoader');
    if (existingLoader) existingLoader.remove();
    
    if (window.conceptTextInterval) clearInterval(window.conceptTextInterval);
    
    const loaderHTML = `
        <div id="conceptLoader" class="fixed inset-0 bg-black/95 backdrop-blur-xl z-[200] flex items-center justify-center">
            <div class="text-center">
                <div class="relative w-32 h-32 mx-auto mb-6">
                    <div class="absolute inset-0 border-4 border-purple-500/20 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-t-purple-500 border-r-pink-500 border-b-purple-500 border-l-pink-500 rounded-full animate-spin"></div>
                    <div class="absolute inset-2 border-2 border-purple-500/10 rounded-full"></div>
                    <div class="absolute inset-4 bg-gradient-to-br from-purple-600/20 to-pink-600/20 rounded-full"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <i class="fas fa-brain text-purple-400 text-4xl animate-pulse"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">AI is Thinking</h3>
                <p id="conceptLoaderMessage" class="text-gray-400 text-lg">Creating your unique concept...</p>
                <div class="flex gap-2 justify-center mt-4">
                    <div class="w-2 h-2 rounded-full bg-purple-400 animate-bounce" style="animation-delay: 0s"></div>
                    <div class="w-2 h-2 rounded-full bg-pink-400 animate-bounce" style="animation-delay: 0.2s"></div>
                    <div class="w-2 h-2 rounded-full bg-purple-400 animate-bounce" style="animation-delay: 0.4s"></div>
                </div>
                <div class="mt-6 w-64 h-1.5 bg-gray-700 rounded-full overflow-hidden mx-auto">
                    <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full animate-progress" style="width: 0%; animation: progress 6s ease-out forwards;"></div>
                </div>
                <p class="text-gray-500 text-sm mt-4">Please wait...</p>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', loaderHTML);
    
    const conceptMessages = [
        "Creating your unique concept...",
        "Analyzing story elements...",
        "Building character arcs...",
        "Crafting engaging plot twists...",
        "Developing emotional moments...",
        "Structuring your narrative...",
        "Adding creative details...",
        "Almost there...",
        "Finalizing your concept!"
    ];
    
    let msgIndex = 0;
    const msgElement = document.getElementById('conceptLoaderMessage');
    
    window.conceptTextInterval = setInterval(() => {
        if (msgElement) {
            msgIndex = (msgIndex + 1) % conceptMessages.length;
            msgElement.textContent = conceptMessages[msgIndex];
            msgElement.style.opacity = '0';
            setTimeout(() => {
                if (msgElement) msgElement.style.opacity = '1';
            }, 200);
        }
    }, 2000);
    
    if (!document.querySelector('#loaderStyles')) {
        const style = document.createElement('style');
        style.id = 'loaderStyles';
        style.textContent = `
            @keyframes spin { to { transform: rotate(360deg); } }
            @keyframes bounce {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }
            @keyframes pulse {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.8; transform: scale(0.95); }
            }
            @keyframes progress {
                0% { width: 0%; }
                100% { width: 100%; }
            }
            .animate-spin { animation: spin 1s linear infinite; }
            .animate-bounce { animation: bounce 0.8s ease-in-out infinite; }
            .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
            .animate-progress { animation: progress 6s ease-out forwards; }
            #conceptLoaderMessage { transition: opacity 0.2s ease-in-out; min-height: 60px; }
        `;
        document.head.appendChild(style);
    }
}

function hideConceptLoader() {
    if (window.conceptTextInterval) {
        clearInterval(window.conceptTextInterval);
        window.conceptTextInterval = null;
    }
    const loader = document.getElementById('conceptLoader');
    if (loader) {
        loader.style.opacity = '0';
        loader.style.transition = 'opacity 0.3s ease';
        setTimeout(() => loader.remove(), 300);
    }
}

function updateLoaderMessage(message) {
    const loaderMessage = document.getElementById('loaderMessage');
    if (loaderMessage) {
        loaderMessage.textContent = message;
    }
}

// Regenerate Concept
document.getElementById('regenerateConceptBtn')?.addEventListener('click', async () => {
    if (!currentSeriesId) {
        alert('No series found');
        return;
    }
    
    const btn = document.getElementById('regenerateConceptBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Regenerating...';
    btn.disabled = true;
    
    showConceptLoader();
    
    try {
        const result = await apiCall(`/series/${currentSeriesId}/generate-episode1-concept`, 'POST', {
            prompt: currentPrompt
        });
        
        if (result.success) {
            currentConcept = result.concept;
            document.getElementById('concept').value = currentConcept;
            await new Promise(resolve => setTimeout(resolve, 2000));
            hideConceptLoader();
            alert('✅ Concept regenerated successfully!');
        } else {
            hideConceptLoader();
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        hideConceptLoader();
        alert('Error: ' + error.message);
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});

// Continue button - Save concept and generate segments with full page loader
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

// Edit Prompt Functions
function editPrompt() {
    const modal = document.getElementById('editPromptModal');
    const content = document.getElementById('editModalContent');
    document.getElementById('editPromptText').value = currentPrompt;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeEditModal() {
    const modal = document.getElementById('editPromptModal');
    const content = document.getElementById('editModalContent');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}

async function saveEditedPromptAndRegenerate() {
    const newPrompt = document.getElementById('editPromptText').value;
    if (newPrompt.length < 10) {
        alert('Please enter a more detailed prompt (minimum 10 characters)');
        return;
    }
    
    currentPrompt = newPrompt;
    document.getElementById('prompt').value = currentPrompt;
    closeEditModal();
    
    const btn = document.getElementById('regenerateConceptBtn');
    const originalText = btn?.innerHTML;
    if (btn) {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Regenerating...';
        btn.disabled = true;
    }
    
    showConceptLoader();
    
    try {
        const result = await apiCall(`/series/${currentSeriesId}/generate-episode1-concept`, 'POST', {
            prompt: currentPrompt
        });
        
        if (result.success) {
            currentConcept = result.concept;
            document.getElementById('concept').value = currentConcept;
            await new Promise(resolve => setTimeout(resolve, 2000));
            hideConceptLoader();
            alert('✅ Concept regenerated with your new prompt!');
        } else {
            hideConceptLoader();
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        hideConceptLoader();
        alert('Error: ' + error.message);
    } finally {
        if (btn) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
}

function switchStep(step) {
    for(let i = 1; i <= 3; i++) {
        const div = document.getElementById(`step${i}`);
        if(div) div.classList.add('hidden');
        
        const indicator = document.getElementById(`step${i}Indicator`);
        if(indicator) {
            const circle = indicator.querySelector('div');
            const text = indicator.querySelector('span');
            
            if(i < step) {
                if(circle) {
                    circle.classList.remove('bg-gray-700', 'text-gray-400');
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
    
    document.getElementById(`step${step}`).classList.remove('hidden');
    
    for(let i = 1; i <= 2; i++) {
        const line = document.getElementById(`line${i}`);
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
</script>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes spin { to { transform: rotate(360deg); } }
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(0.95); }
}
@keyframes progress {
    0% { width: 0%; }
    100% { width: 100%; }
}

.animate-bounce { animation: bounce 0.5s ease-in-out 3; }
.animate-spin { animation: spin 1s linear infinite; }
.animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }

#step1, #step2, #step3 {
    animation: fadeIn 0.5s ease-out;
}

.category-card:hover {
    transform: translateY(-3px);
}

.peer:checked ~ div {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.3);
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

#fullPageLoader, #conceptLoader {
    transition: all 0.3s ease;
}

#progressBarFill {
    transition: width 0.5s ease;
}
</style>
@endsection