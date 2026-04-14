{{-- resources/views/web-series/create.blade.php --}}
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
            <p class="text-gray-300 text-lg">Bring your story to life with AI-powered scenes</p>
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
            <div class="w-16 h-px bg-gray-700" id="line3"></div>
            <div class="flex items-center gap-2" id="step4Indicator">
                <div class="w-10 h-10 rounded-full bg-gray-700 text-gray-400 flex items-center justify-center font-bold">4</div>
                <span class="text-gray-400 text-sm hidden sm:inline">Scenes</span>
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
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                        @foreach($categories as $category)
                        <label class="category-card cursor-pointer group">
                            <input type="radio" name="category_id" value="{{ $category->id }}" class="hidden peer" required>
                            <div class="text-center p-3 rounded-xl border-2 border-gray-700 bg-gray-800/30 transition-all duration-300 peer-checked:border-purple-500 peer-checked:bg-purple-500/20 peer-checked:shadow-lg hover:border-gray-500 hover:bg-gray-700/50 cursor-pointer">
                                <div class="text-3xl mb-2 group-hover:scale-110 transition-transform text-purple-400">
                                    <i class="fas {{ $category->icon }}"></i>
                                </div>
                                <div class="text-white text-sm font-medium">{{ $category->name }}</div>
                                <div class="text-gray-500 text-xs mt-1 hidden md:block">{{ Str::limit($category->description, 30) }}</div>
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
            
            <div class="mb-6 p-4 bg-gradient-to-r from-purple-900/20 to-pink-900/20 rounded-xl border border-purple-500/30">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-purple-400 font-semibold flex items-center gap-2">
                        <i class="fas fa-pen-fancy"></i>
                        Your Original Prompt
                    </label>
                    <button type="button" onclick="editPrompt()" 
                            class="text-xs text-gray-400 hover:text-white transition px-3 py-1.5 rounded-lg bg-gray-800/50 hover:bg-gray-700 border border-gray-700 hover:border-purple-500">
                        <i class="fas fa-edit mr-1"></i> Edit Prompt
                    </button>
                </div>
                <p id="originalPromptDisplay" class="text-gray-300 text-sm leading-relaxed"></p>
            </div>
            
            <div class="mb-6">
                <label class="block text-white font-semibold mb-2 flex items-center gap-2">
                    <i class="fas fa-align-left text-purple-400"></i>
                    Generated Concept (Max 600 chars)
                </label>
                <textarea id="concept" rows="8" maxlength="600" 
                          class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all duration-300"></textarea>
                <div class="flex justify-between text-sm mt-2">
                    <span class="text-gray-400"><i class="fas fa-edit mr-1"></i>Edit if needed</span>
                    <span id="charCount" class="text-purple-400 font-medium">0/600 characters</span>
                </div>
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
                <button id="updateConceptBtn" 
                        class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-xl text-white font-semibold transition-all duration-300">
                    <i class="fas fa-save"></i> Update
                </button>
                <button id="saveConceptBtn" 
                        class="flex-1 py-2.5 bg-green-600 hover:bg-green-700 rounded-xl text-white font-semibold transition-all duration-300">
                    <i class="fas fa-check-circle"></i> Save & Continue
                </button>
            </div>
        </div>

        <!-- Step 4: Generate Scenes -->
        <div id="step4" class="hidden bg-gray-900/50 backdrop-blur-lg rounded-2xl border border-gray-800 p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center shadow-lg">
                    <i class="fas fa-layer-group text-white text-lg"></i>
                </div>
                <h2 class="text-2xl font-bold text-white">Generate Scenes</h2>
            </div>
            
            <div class="mb-6 p-4 bg-gradient-to-r from-purple-900/20 to-pink-900/20 rounded-xl border border-purple-500/30">
                <p class="text-purple-400 font-semibold mb-2 flex items-center gap-2">
                    <i class="fas fa-pen-fancy"></i>
                    Your Prompt
                </p>
                <p id="finalPromptDisplay" class="text-gray-300 text-sm mb-4 leading-relaxed"></p>
                <p class="text-purple-400 font-semibold mb-2 flex items-center gap-2">
                    <i class="fas fa-brain"></i>
                    Generated Concept
                </p>
                <p id="finalConceptDisplay" class="text-gray-300 text-sm leading-relaxed"></p>
            </div>
            
            <div class="mb-8">
                <label class="block text-white font-semibold mb-3 text-center">How many scenes?</label>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                    @for($i = 5; $i <= 10; $i++)
                    <label class="cursor-pointer scene-count-card">
                        <input type="radio" name="total_scenes" value="{{ $i }}" class="hidden peer" {{ $i == 5 ? 'checked' : '' }}>
                        <div class="text-center py-3 rounded-xl border-2 border-gray-700 text-white font-semibold text-lg transition-all duration-300 peer-checked:border-purple-500 peer-checked:bg-purple-500/20 peer-checked:shadow-lg hover:border-gray-500">
                            {{ $i }}
                        </div>
                    </label>
                    @endfor
                </div>
                <p class="text-gray-500 text-xs text-center mt-3">Choose between 5 to 10 scenes for your episode</p>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="goBackToStep(3)" 
                        class="px-6 py-3.5 bg-gray-700 hover:bg-gray-600 rounded-xl text-white font-semibold transition-all duration-300 flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </button>
                <button id="generateScenesBtn" 
                        class="flex-1 py-3.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 flex items-center justify-center gap-2 shadow-lg hover:shadow-pink-500/25 hover:scale-[1.02]">
                    <i class="fas fa-magic"></i>
                    Generate Scenes for Episode 1
                </button>
            </div>
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
            <button onclick="saveEditedPrompt()" 
                    class="flex-1 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-medium transition-all duration-300">
                <i class="fas fa-save mr-2"></i>Save & Regenerate
            </button>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="fixed inset-0 bg-black/90 backdrop-blur-md z-50 hidden items-center justify-center">
    <div class="text-center transform transition-all duration-300 scale-95" id="loadingContent">
        <div class="w-20 h-20 mx-auto mb-4">
            <div class="w-full h-full border-4 border-purple-500/30 border-t-purple-500 rounded-full animate-spin"></div>
        </div>
        <i class="fas fa-microchip text-purple-400 text-4xl mb-2"></i>
        <h3 class="text-xl font-bold text-white mb-2" id="loadingTitle">Generating...</h3>
        <p class="text-gray-400" id="loadingMessage">Please wait while we create your content</p>
        <div class="mt-4 w-48 h-1 bg-gray-800 rounded-full overflow-hidden mx-auto">
            <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full animate-pulse" style="width: 60%"></div>
        </div>
    </div>
</div>

<script>
let currentSeriesId = null;
let currentEpisodeId = null;
let currentPrompt = '';

// Go back to previous step
function goBackToStep(step) {
    if (step === 1) {
        if (confirm('Are you sure you want to go back? Your current progress will be lost.')) {
            switchStep(1);
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

// Character counter
document.addEventListener('input', function(e) {
    if(e.target.id === 'concept') {
        let count = e.target.value.length;
        document.getElementById('charCount').textContent = count + '/600 characters';
        if(count > 600) e.target.value = e.target.value.substring(0, 600);
    }
    if(e.target.id === 'prompt') {
        document.getElementById('promptCharCount').textContent = e.target.value.length;
    }
});

// Step 1: Create Series
document.getElementById('projectForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    showLoading('Creating Series', 'Setting up your web series...');
    
    const selectedCategory = document.querySelector('input[name="category_id"]:checked');
    if (!selectedCategory) {
        hideLoading();
        alert('Please select a category');
        return;
    }
    
    try {
        const result = await apiCall('/series/save-project', 'POST', {
            project_name: document.getElementById('project_name').value,
            category_id: selectedCategory.value
        });
        
        hideLoading();
        
        if (result.success) {
            currentSeriesId = result.series_id;
            document.getElementById('series_id').value = currentSeriesId;
            switchStep(2);
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        hideLoading();
        alert('Error: ' + error.message);
    }
});

// Step 2: Generate Concept
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
    
    showLoading('Generating Concept', 'AI is creating an engaging concept for Episode 1...');
    
    try {
        const result = await apiCall(`/series/${currentSeriesId}/generate-episode1-concept`, 'POST', {
            prompt: currentPrompt
        });
        
        hideLoading();
        
        if (result.success) {
            document.getElementById('concept').value = result.concept;
            document.getElementById('charCount').textContent = result.concept.length + '/600 characters';
            document.getElementById('originalPromptDisplay').textContent = currentPrompt;
            document.getElementById('finalPromptDisplay').textContent = currentPrompt;
            document.getElementById('finalConceptDisplay').textContent = result.concept;
            currentEpisodeId = result.episode_id;
            switchStep(3);
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        hideLoading();
        alert('Error: ' + error.message);
    }
});

// Regenerate Concept
document.getElementById('regenerateConceptBtn')?.addEventListener('click', async () => {
    if (!currentSeriesId) {
        alert('No series found');
        return;
    }
    
    showLoading('Regenerating Concept', 'AI is creating a new concept...');
    
    try {
        const result = await apiCall(`/series/${currentSeriesId}/generate-episode1-concept`, 'POST', {
            prompt: currentPrompt
        });
        
        hideLoading();
        
        if (result.success) {
            document.getElementById('concept').value = result.concept;
            document.getElementById('charCount').textContent = result.concept.length + '/600 characters';
            document.getElementById('finalConceptDisplay').textContent = result.concept;
            alert('✅ Concept regenerated successfully!');
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        hideLoading();
        alert('Error: ' + error.message);
    }
});

// Update Concept
document.getElementById('updateConceptBtn').addEventListener('click', async () => {
    if (!currentSeriesId) {
        alert('No series found');
        return;
    }
    
    showLoading('Updating Concept', 'Saving your changes...');
    
    try {
        const result = await apiCall(`/series/${currentSeriesId}/update-episode1-concept`, 'POST', {
            concept: document.getElementById('concept').value
        });
        
        hideLoading();
        
        if (result.success) {
            document.getElementById('finalConceptDisplay').textContent = document.getElementById('concept').value;
            alert('✅ Concept updated successfully!');
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        hideLoading();
        alert('Error: ' + error.message);
    }
});

// Save Concept & Continue
document.getElementById('saveConceptBtn').addEventListener('click', async () => {
    if (!currentSeriesId) {
        alert('No series found');
        return;
    }
    
    showLoading('Saving Concept', 'Preparing to generate scenes...');
    
    try {
        const result = await apiCall(`/series/${currentSeriesId}/update-episode1-concept`, 'POST', {
            concept: document.getElementById('concept').value
        });
        
        if (result.success) {
            switchStep(4);
        } else {
            hideLoading();
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        hideLoading();
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

async function saveEditedPrompt() {
    const newPrompt = document.getElementById('editPromptText').value;
    if (newPrompt.length < 10) {
        alert('Please enter a more detailed prompt (minimum 10 characters)');
        return;
    }
    
    currentPrompt = newPrompt;
    document.getElementById('prompt').value = currentPrompt;
    closeEditModal();
    
    showLoading('Regenerating Concept', 'AI is creating a new concept with your updated prompt...');
    
    try {
        const result = await apiCall(`/series/${currentSeriesId}/generate-episode1-concept`, 'POST', {
            prompt: currentPrompt
        });
        
        hideLoading();
        
        if (result.success) {
            document.getElementById('concept').value = result.concept;
            document.getElementById('charCount').textContent = result.concept.length + '/600 characters';
            document.getElementById('originalPromptDisplay').textContent = currentPrompt;
            document.getElementById('finalPromptDisplay').textContent = currentPrompt;
            document.getElementById('finalConceptDisplay').textContent = result.concept;
            alert('✅ Concept regenerated with your new prompt!');
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        hideLoading();
        alert('Error: ' + error.message);
    }
}

// Step 4: Generate Scenes
document.getElementById('generateScenesBtn').addEventListener('click', async () => {
    if (!currentSeriesId) {
        alert('No series found');
        return;
    }
    
    const totalScenes = document.querySelector('input[name="total_scenes"]:checked').value;
    showLoading('Generating Scenes', `Creating ${totalScenes} scenes for Episode 1...`);
    
    try {
        const result = await apiCall(`/series/${currentSeriesId}/generate-episode1-scenes`, 'POST', {
            total_scenes: totalScenes
        });
        
        if (result.success) {
            if (result.redirect_url) {
                window.location.href = result.redirect_url;
            } else {
                window.location.href = `/series/${currentSeriesId}`;
            }
        } else {
            hideLoading();
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        hideLoading();
        alert('Error: ' + error.message);
    }
});

function switchStep(step) {
    hideLoading();
    for(let i = 1; i <= 4; i++) {
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
    
    for(let i = 1; i <= 3; i++) {
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

function showLoading(title, message) {
    const modal = document.getElementById('loadingModal');
    const content = document.getElementById('loadingContent');
    document.getElementById('loadingTitle').textContent = title;
    document.getElementById('loadingMessage').textContent = message;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }, 10);
}

function hideLoading() {
    const modal = document.getElementById('loadingModal');
    const content = document.getElementById('loadingContent');
    content.classList.remove('scale-100');
    content.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}
</script>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

#step1, #step2, #step3, #step4 {
    animation: fadeIn 0.5s ease-out;
}

.animate-spin {
    animation: spin 1s linear infinite;
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Category card hover effects */
.category-card:hover div {
    transform: translateY(-2px);
}

.category-card input:checked + div {
    animation: glow 0.5s ease-out;
}

/* Scene count card effects */
.scene-count-card:hover div {
    transform: translateY(-2px);
    border-color: #8b5cf6;
}

.scene-count-card input:checked + div {
    border-color: #8b5cf6;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(236, 72, 153, 0.1));
    box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
}

@keyframes glow {
    0% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(139, 92, 246, 0); }
    100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0); }
}

/* Focus effects */
input:focus, textarea:focus {
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
}

/* Radio button styling */
input[type="radio"]:checked + div {
    border-color: #8b5cf6;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(236, 72, 153, 0.1));
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .py-\[120px\] {
        padding-top: 60px !important;
        padding-bottom: 60px !important;
    }
}

@media (max-width: 640px) {
    .grid-cols-6 {
        grid-template-columns: repeat(3, 1fr);
    }
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
</style>
@endsection