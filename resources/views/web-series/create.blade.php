{{-- resources/views/web-series/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 py-[120px] px-4">
    <div class="container mx-auto max-w-4xl">
        <div class="text-center mb-10">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-3">Create Your Web Series</h1>
            <p class="text-gray-300 text-lg">Create Episode 1 with AI-powered scenes</p>
        </div>

        <!-- Step Indicator -->
        <div class="flex items-center justify-center gap-4 mb-12">
            <div class="flex items-center gap-2" id="step1Indicator">
                <div class="w-8 h-8 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold">1</div>
                <span class="text-white text-sm hidden sm:inline">Series</span>
            </div>
            <div class="w-12 h-px bg-gray-600" id="line1"></div>
            <div class="flex items-center gap-2" id="step2Indicator">
                <div class="w-8 h-8 rounded-full bg-gray-700 text-gray-400 flex items-center justify-center font-bold">2</div>
                <span class="text-gray-400 text-sm hidden sm:inline">Prompt</span>
            </div>
            <div class="w-12 h-px bg-gray-600" id="line2"></div>
            <div class="flex items-center gap-2" id="step3Indicator">
                <div class="w-8 h-8 rounded-full bg-gray-700 text-gray-400 flex items-center justify-center font-bold">3</div>
                <span class="text-gray-400 text-sm hidden sm:inline">Concept</span>
            </div>
            <div class="w-12 h-px bg-gray-600" id="line3"></div>
            <div class="flex items-center gap-2" id="step4Indicator">
                <div class="w-8 h-8 rounded-full bg-gray-700 text-gray-400 flex items-center justify-center font-bold">4</div>
                <span class="text-gray-400 text-sm hidden sm:inline">Scenes</span>
            </div>
        </div>

        <!-- Step 1: Create Series -->
        <div id="step1" class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8">
            <h2 class="text-2xl font-bold text-white mb-6">Step 1: Create Your Series</h2>
            <form id="projectForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-white font-semibold mb-2">Series Name</label>
                    <input type="text" id="project_name" required 
                           placeholder="Enter your web series title"
                           class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white focus:border-purple-500 focus:outline-none">
                </div>
                <div class="mb-6">
                    <label class="block text-white font-semibold mb-2">Category</label>
                    <select id="category" required 
                            class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white focus:border-purple-500 focus:outline-none">
                        <option value="">Select Category</option>
                        <option value="Action">🔥 Action</option>
                        <option value="Drama">🎭 Drama</option>
                        <option value="Comedy">😂 Comedy</option>
                        <option value="Sci-Fi">🚀 Sci-Fi</option>
                        <option value="Fantasy">🐉 Fantasy</option>
                        <option value="Thriller">🔪 Thriller</option>
                        <option value="Romance">💕 Romance</option>
                        <option value="Mystery">🔍 Mystery</option>
                        <option value="Horror">👻 Horror</option>
                        <option value="Adventure">🗺️ Adventure</option>
                    </select>
                </div>
                <button type="submit" 
                        class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition">
                    Create Series & Continue →
                </button>
            </form>
        </div>

        <!-- Step 2: Add Prompt for Episode 1 -->
        <div id="step2" class="hidden bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8">
            <h2 class="text-2xl font-bold text-white mb-6">Step 2: Episode 1 Prompt</h2>
            <form id="promptForm">
                <input type="hidden" id="series_id">
                <div class="mb-4">
                    <label class="block text-white font-semibold mb-2">Describe Episode 1</label>
                    <textarea id="prompt" rows="6" required 
                              placeholder="Example: A young detective discovers a mysterious case that leads her into a world of supernatural phenomena. She must uncover the truth before it's too late..."
                              class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white focus:border-purple-500 focus:outline-none"></textarea>
                    <p class="text-gray-400 text-sm mt-2">💡 Be detailed about what happens in this episode</p>
                </div>
                <button type="submit" id="generateConceptBtn" 
                        class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition">
                    Generate Concept →
                </button>
            </form>
        </div>

        <!-- Step 3: Review & Edit Concept -->
        <div id="step3" class="hidden bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8">
            <h2 class="text-2xl font-bold text-white mb-6">Step 3: Episode 1 Concept</h2>
            <div class="mb-4">
                <label class="block text-white font-semibold mb-2">Generated Concept (Max 600 chars)</label>
                <textarea id="concept" rows="6" maxlength="600" 
                          class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white focus:border-purple-500 focus:outline-none"></textarea>
                <div class="flex justify-between text-sm mt-2">
                    <span class="text-gray-400">✏️ Edit if needed</span>
                    <span id="charCount" class="text-purple-400">0/600 characters</span>
                </div>
            </div>
            <div class="flex gap-3">
                <button id="updateConceptBtn" 
                        class="flex-1 py-2 bg-yellow-600 hover:bg-yellow-700 rounded-xl text-white font-semibold transition">
                    🔄 Update Concept
                </button>
                <button id="saveConceptBtn" 
                        class="flex-1 py-2 bg-green-600 hover:bg-green-700 rounded-xl text-white font-semibold transition">
                    ✅ Save & Continue →
                </button>
            </div>
        </div>

        <!-- Step 4: Generate Scenes -->
        <div id="step4" class="hidden bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8">
            <h2 class="text-2xl font-bold text-white mb-6">Step 4: Generate Scenes for Episode 1</h2>
            <div class="mb-6">
                <label class="block text-white font-semibold mb-3">How many scenes? (5-10)</label>
                <div class="grid grid-cols-6 gap-3">
                    @for($i = 5; $i <= 10; $i++)
                    <label class="cursor-pointer">
                        <input type="radio" name="total_scenes" value="{{ $i }}" class="hidden peer" {{ $i == 5 ? 'checked' : '' }}>
                        <div class="text-center py-3 rounded-xl border-2 border-slate-600 text-white peer-checked:border-purple-500 peer-checked:bg-purple-500/20 transition">
                            {{ $i }}
                        </div>
                    </label>
                    @endfor
                </div>
            </div>
            <button id="generateScenesBtn" 
                    class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition">
                🎬 Generate Scenes for Episode 1 →
            </button>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="fixed inset-0 bg-black/90 backdrop-blur-md z-50 hidden items-center justify-center">
    <div class="text-center">
        <div class="w-16 h-16 border-4 border-purple-500/30 border-t-purple-500 rounded-full animate-spin mx-auto mb-4"></div>
        <h3 class="text-xl font-bold text-white mb-2" id="loadingTitle">Generating...</h3>
        <p class="text-gray-400" id="loadingMessage">Please wait while we create your content</p>
    </div>
</div>

<script>
let currentSeriesId = null;
let currentEpisodeId = null;

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
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 500));
            
            // Check for common HTML responses
            if (text.includes('login') || text.includes('Login')) {
                throw new Error('Please login first. Redirecting to login page...');
            }
            if (text.includes('404') || text.includes('Not Found')) {
                throw new Error('API endpoint not found. Please check your routes.');
            }
            if (text.includes('500') || text.includes('Server Error')) {
                throw new Error('Server error. Please try again later.');
            }
            throw new Error('Invalid server response. Please check your connection.');
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
        if(count > 600) {
            e.target.value = e.target.value.substring(0, 600);
        }
    }
});

// Step 1: Create Series
document.getElementById('projectForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    showLoading('Creating Series', 'Setting up your web series...');
    
    try {
        const result = await apiCall('/series/save-project', 'POST', {
            project_name: document.getElementById('project_name').value,
            category: document.getElementById('category').value
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
        console.error(error);
    }
});

// Step 2: Generate Concept
document.getElementById('promptForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!currentSeriesId) {
        alert('Please create a series first');
        return;
    }
    
    showLoading('Generating Concept', 'AI is creating an engaging concept for Episode 1...');
    
    try {
        const result = await apiCall(`/series/${currentSeriesId}/generate-episode1-concept`, 'POST', {
            prompt: document.getElementById('prompt').value
        });
        
        hideLoading();
        
        if (result.success) {
            document.getElementById('concept').value = result.concept;
            document.getElementById('charCount').textContent = result.concept.length + '/600 characters';
            currentEpisodeId = result.episode_id;
            switchStep(3);
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        hideLoading();
        alert('Error: ' + error.message);
        console.error(error);
    }
});

// Step 3: Update Concept
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
            alert('✅ Concept updated successfully!');
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        hideLoading();
        alert('Error: ' + error.message);
        console.error(error);
    }
});

// Step 3: Save Concept & Continue
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
        console.error(error);
    }
});

// Step 4: Generate Scenes
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
            // Use the redirect_url from response
            if (result.redirect_url) {
                window.location.href = result.redirect_url;
            } else {
                window.location.href = `/series/${currentSeriesId}/episode-1`;
            }
        } else {
            hideLoading();
            alert('Error: ' + result.message);
        }
    } catch (error) { 
        hideLoading();
        alert('Error: ' + error.message);
        console.error(error);
    }
});

function switchStep(step) {
    hideLoading();
    for(let i = 1; i <= 4; i++) {
        const div = document.getElementById(`step${i}`);
        if(div) div.classList.add('hidden');
        
        // Update step indicators
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
            } else if(i === step) {
                if(circle) {
                    circle.classList.remove('bg-gray-700', 'text-gray-400', 'bg-green-600');
                    circle.classList.add('bg-purple-600', 'text-white');
                    circle.innerHTML = step;
                }
                if(text) text.classList.remove('text-gray-400');
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
    
    // Update lines
    for(let i = 1; i <= 3; i++) {
        const line = document.getElementById(`line${i}`);
        if(line) {
            if(i < step) {
                line.classList.remove('bg-gray-600');
                line.classList.add('bg-green-600');
            } else {
                line.classList.remove('bg-green-600');
                line.classList.add('bg-gray-600');
            }
        }
    }
}

function showLoading(title, message) {
    document.getElementById('loadingTitle').textContent = title;
    document.getElementById('loadingMessage').textContent = message;
    document.getElementById('loadingModal').classList.remove('hidden');
    document.getElementById('loadingModal').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loadingModal').style.display = 'none';
}
</script>

<style>
/* Custom animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

#step1, #step2, #step3, #step4 {
    animation: fadeIn 0.5s ease-out;
}

/* Custom radio button styling */
input[type="radio"]:checked + div {
    border-color: #8b5cf6;
    background: rgba(139, 92, 246, 0.2);
}

/* Loading spinner animation */
@keyframes spin {
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .grid-cols-6 {
        grid-template-columns: repeat(3, 1fr);
    }
}
</style>
@endsection