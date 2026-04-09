@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ theme_url('custom/standup.css') }}">
@endsection

@section('content')

<div class="standup-script-generator">

    <!-- Header Section -->
    <div class="generator-header">
        <div class="header-badge">
            <i class="fas fa-pen-fancy"></i> Write Your Comedy
        </div>
        <h1>Generate Your Stand-Up Script</h1>
        <p class="header-subtitle">Let AI craft the perfect comedy routine based on your ideas</p>
    </div>

    <div class="generator-grid">
        <!-- Left Panel - Form -->
        <div class="generator-form-panel">
            <div class="form-card">
                <div class="form-card-header">
                    <i class="fas fa-microphone-alt"></i>
                    <h3>Script Settings</h3>
                </div>
                
                <form id="scriptForm">
                    <!-- Selected Comedian -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user-circle"></i> Selected Comedian
                        </label>
                        <div id="selectedComedianDisplay" class="comedian-display">
                            @if(session('selected_comedian_id'))
                                <div class="comedian-selected">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Comedian selected</span>
                                </div>
                            @else
                                <div class="comedian-missing">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>No comedian selected</span>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn-outline-sm" onclick="window.location.href='/standup/templates'">
                            <i class="fas fa-exchange-alt"></i> Change Comedian
                        </button>
                    </div>
                    
                    <!-- Topic / Idea -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lightbulb"></i> Topic / Idea
                        </label>
                        <textarea name="topic" class="form-control" rows="4" placeholder="e.g., Dating fails, office life, technology, family gatherings, or any funny situation..." required></textarea>
                        <div class="form-hint">Describe what you want the comedy to be about</div>
                    </div>
                    
                    <!-- Tone Selection -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-face-smile"></i> Tone
                        </label>
                        <select name="tone" class="form-select">
                            <option value="">Default - Mixed</option>
                            <option value="sarcastic">🎭 Sarcastic & Witty</option>
                            <option value="dark">🌑 Dark Humor</option>
                            <option value="wholesome">✨ Wholesome & Heartfelt</option>
                            <option value="absurd">🌀 Absurd & Surreal</option>
                            <option value="observational">👀 Observational</option>
                            <option value="self-deprecating">🤷 Self-Deprecating</option>
                        </select>
                    </div>
                    
                    <!-- Duration -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-clock"></i> Duration
                        </label>
                        <div class="duration-options">
                            <label class="duration-option">
                                <input type="radio" name="duration" value="1">
                                <span>1 min</span>
                            </label>
                            <label class="duration-option">
                                <input type="radio" name="duration" value="3" checked>
                                <span>3 min</span>
                            </label>
                            <label class="duration-option">
                                <input type="radio" name="duration" value="5">
                                <span>5 min</span>
                            </label>
                            <label class="duration-option">
                                <input type="radio" name="duration" value="10">
                                <span>10 min</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn-generate" id="generateScriptBtn">
                        <i class="fas fa-magic"></i> Generate Script
                    </button>
                    
                    <!-- Loading Indicator -->
                    <div id="loadingIndicator" class="loading-indicator d-none">
                        <div class="loading-spinner"></div>
                        <p>Crafting your comedy routine...</p>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Right Panel - Script Output -->
        <div class="generator-output-panel">
            <div class="output-card">
                <div class="output-card-header">
                    <i class="fas fa-file-alt"></i>
                    <h3>Your Script</h3>
                    <span class="script-status" id="scriptStatus"></span>
                </div>
                
                <div class="output-content">
                    <div id="scriptOutput" class="script-display">
                        <div class="empty-state">
                            <i class="fas fa-microphone-alt"></i>
                            <p>Your generated script will appear here</p>
                            <span>Select a comedian and enter a topic to begin</span>
                        </div>
                    </div>
                </div>
                
                <div id="scriptActions" class="output-actions d-none">
                    <button class="btn-edit" id="editScriptBtn">
                        <i class="fas fa-edit"></i> Edit Script
                    </button>
                    <button class="btn-regenerate" id="regenerateScriptBtn">
                        <i class="fas fa-sync-alt"></i> Regenerate
                    </button>
                    <button class="btn-proceed" id="useForVideoBtn">
                        <i class="fas fa-video"></i> Use for Video
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('js')
<script>
let currentScriptId = null;
let currentComedianId = {{ session('selected_comedian_id', 'null') }};

// Update comedian display
function updateComedianDisplay() {
    const display = document.getElementById('selectedComedianDisplay');
    if (currentComedianId) {
        display.innerHTML = `
            <div class="comedian-selected">
                <i class="fas fa-check-circle"></i>
                <span>Comedian ready to perform</span>
            </div>
        `;
    } else {
        display.innerHTML = `
            <div class="comedian-missing">
                <i class="fas fa-exclamation-triangle"></i>
                <span>No comedian selected - please choose one first</span>
            </div>
        `;
    }
}

updateComedianDisplay();

document.getElementById('scriptForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!currentComedianId) {
        Swal.fire({
            icon: 'warning',
            title: 'No Comedian Selected',
            text: 'Please select a comedian first',
            confirmButtonColor: '#E65856'
        });
        return;
    }
    
    const topic = document.querySelector('[name="topic"]').value;
    if (!topic.trim()) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Topic',
            text: 'Please enter a topic for your comedy routine',
            confirmButtonColor: '#E65856'
        });
        return;
    }
    
    const formData = new FormData(e.target);
    formData.append('comedian_id', currentComedianId);
    
    document.getElementById('generateScriptBtn').disabled = true;
    document.getElementById('loadingIndicator').classList.remove('d-none');
    document.getElementById('scriptOutput').innerHTML = `
        <div class="generating-state">
            <div class="loading-spinner"></div>
            <p>AI is writing your comedy...</p>
            <span>Crafting the perfect punchlines</span>
        </div>
    `;
    
    const response = await fetch('/standup/script/generate', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: formData
    });
    
    const data = await response.json();
    pollScriptStatus(data.script_id);
});

function pollScriptStatus(scriptId) {
    const interval = setInterval(async () => {
        const response = await fetch(`/standup/script/${scriptId}`);
        const data = await response.json();
        
        if (data.status === 'completed') {
            clearInterval(interval);
            document.getElementById('generateScriptBtn').disabled = false;
            document.getElementById('loadingIndicator').classList.add('d-none');
            document.getElementById('scriptOutput').innerHTML = `
                <div class="script-content">${data.generated_script.replace(/\n/g, '<br>')}</div>
            `;
            document.getElementById('scriptActions').classList.remove('d-none');
            document.getElementById('scriptStatus').innerHTML = '<i class="fas fa-check-circle"></i> Ready';
            currentScriptId = scriptId;
        } else if (data.status === 'failed') {
            clearInterval(interval);
            document.getElementById('generateScriptBtn').disabled = false;
            document.getElementById('loadingIndicator').classList.add('d-none');
            document.getElementById('scriptOutput').innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Script generation failed</p>
                    <span>Please try again</span>
                </div>
            `;
        }
    }, 2000);
}

document.getElementById('editScriptBtn').addEventListener('click', () => {
    const currentScript = document.querySelector('.script-content')?.innerText.replace(/<br>/g, '\n') || '';
    const newContent = prompt('Edit your script:', currentScript);
    if (newContent) {
        fetch(`/standup/script/${currentScriptId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ script_content: newContent })
        }).then(() => {
            document.getElementById('scriptOutput').innerHTML = `
                <div class="script-content">${newContent.replace(/\n/g, '<br>')}</div>
            `;
            Swal.fire({
                icon: 'success',
                title: 'Script Updated',
                toast: true,
                timer: 2000,
                showConfirmButton: false
            });
        });
    }
});

document.getElementById('regenerateScriptBtn').addEventListener('click', async () => {
    document.getElementById('loadingIndicator').classList.remove('d-none');
    document.getElementById('scriptActions').classList.add('d-none');
    document.getElementById('scriptOutput').innerHTML = `
        <div class="generating-state">
            <div class="loading-spinner"></div>
            <p>Regenerating your comedy...</p>
        </div>
    `;
    
    const response = await fetch(`/standup/script/${currentScriptId}/regenerate`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    
    const data = await response.json();
    pollScriptStatus(currentScriptId);
});

document.getElementById('useForVideoBtn').addEventListener('click', () => {
    window.location.href = `/standup/video-generator?comedian_id=${currentComedianId}&script_id=${currentScriptId}`;
});
</script>
@endsection