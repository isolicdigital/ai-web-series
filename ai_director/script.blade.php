@extends('layouts.app')

@section('css')
<link href="{{ theme_url('custom/aidirector.css') }}" rel="stylesheet" />
<!-- Sweet Alert CSS -->
<link href="{{URL::asset('plugins/sweetalert/sweetalert2.min.css')}}" rel="stylesheet" />
@endsection

@section('content')
<div class="page-body mt-2">
    <div class="container">
        <div class="py-10 px-10">
            <!-- Header -->
            <div class="automation-header">
                <div class="automation-badge">AI SCRIPT DIRECTOR</div>
                <h1 class="automation-title">The <span>Blueprint</span></h1>
                <p class="automation-subtitle">Your digital screenwriter's desk — craft compelling narratives scene by scene</p>
            </div>

            <!-- Script Form -->
            <div class="script-form-container">
                <form id="script-form" method="POST" action="{{ route('aidirector.api.script.generate') }}">
                    @csrf
                    <input type="hidden" name="model_id" value="{{ $scriptModel->id }}">
                    <!-- <input type="hidden" name="demo_mode" value="1"> -->

                    <!-- Project Title -->
                    <div class="form-group">
                        <label class="form-label">Project Title</label>
                        <input type="text" class="form-control" name="title" placeholder="e.g., Cyberpunk Detective, Fantasy Epic" maxlength="100" required>
                    </div>

                    <!-- Story Prompt + Scene Count Side by Side -->
                    <div class="form-row">
                        <div class="form-group" style="flex: 2;">
                            <label class="form-label">Story Prompt</label>
                            <textarea class="form-control" name="prompt" rows="4" placeholder="Describe your story concept in a few sentences..." required></textarea>
                            <div class="character-counter">
                                <small><span id="prompt-count">0</span>/500 characters</small>
                            </div>
                        </div>

                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">Scene Count</label>
                            <div class="scene-selector">
                                <div class="setting-header">
                                    <span class="setting-value-display" id="scene-value">5 scenes</span>
                                </div>
                                <div class="slider-wrapper">
                                    <input type="range" class="custom-slider" name="scene_count" id="scene-slider" min="3" max="10" value="5" step="1">
                                    <div class="slider-labels">
                                        <span class="slider-label">3</span>
                                        <span class="slider-label">4</span>
                                        <span class="slider-label">5</span>
                                        <span class="slider-label">6</span>
                                        <span class="slider-label">7</span>
                                        <span class="slider-label">8</span>
                                        <span class="slider-label">9</span>
                                        <span class="slider-label">10</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Style Selector -->
                    <div class="form-group">
                        <label class="form-label">Style</label>
                        <div class="style-selector">
                            @php
                            $styles = [
                                'noir' => ['icon' => 'fa-solid fa-skull', 'name' => 'Noir'],
                                'cinematic' => ['icon' => 'fa-solid fa-clapperboard', 'name' => 'Cinematic'],
                                'anime' => ['icon' => 'fa-solid fa-dragon', 'name' => 'Anime'],
                                'documentary' => ['icon' => 'fa-solid fa-earth-americas', 'name' => 'Documentary'],
                                'comedy' => ['icon' => 'fa-solid fa-face-smile', 'name' => 'Comedy'],
                                'drama' => ['icon' => 'fa-solid fa-masks-theater', 'name' => 'Drama'],
                                'action' => ['icon' => 'fa-solid fa-bolt', 'name' => 'Action'],
                                'sci-fi' => ['icon' => 'fa-solid fa-rocket', 'name' => 'Sci-Fi'],
                                'fantasy' => ['icon' => 'fa-solid fa-wand-sparkles', 'name' => 'Fantasy'],
                                'horror' => ['icon' => 'fa-solid fa-ghost', 'name' => 'Horror']
                            ];
                            @endphp

                            @foreach($styles as $key => $style)
                            <label class="style-option {{ $loop->first ? 'active' : '' }}" data-style="{{ $key }}">
                                <input type="radio" name="style" value="{{ $key }}" style="display: none;" {{ $loop->first ? 'checked' : '' }}>
                                <div class="style-icon">
                                    <i class="{{ $style['icon'] }}"></i>
                                </div>
                                <span class="style-name">{{ $style['name'] }}</span>
                                <div class="style-check">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <!-- Submit Button -->
                    <div class="submit-container text-center">
                        <button type="submit" class="btn-primary-action" id="generate-button">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            Generate Script
                        </button>
                    </div>
                </form>
            </div>

            <!-- Script Board (Results) -->
            <div class="script-board" id="script-board" style="margin-top: 3rem; display: none;">
                <div class="section-header-compact">
                    <h3 class="section-title-compact">
                        <i class="fa-solid fa-pen-fancy"></i>
                        Script Board
                    </h3>
                    <div class="board-actions">
                        <button class="btn-text-action" id="regenerate-all-btn" style="display: none;">
                            <i class="fa-solid fa-rotate"></i>
                            Regenerate All
                        </button>
                        <button class="btn-text-action" id="export-btn" style="display: none;">
                            <i class="fa-solid fa-download"></i>
                            Export
                        </button>
                    </div>
                </div>

                <div id="scenes-container" class="scenes-grid">
                    <!-- Scenes will be loaded here dynamically -->
                </div>

                <div id="export-menu" class="export-dropdown" style="display: none;">
                    <button class="export-option" data-type="scripts">
                        <i class="fa-solid fa-file-lines"></i>
                        Download Scripts (.txt)
                    </button>
                    <button class="export-option" data-type="prompts">
                        <i class="fa-solid fa-code"></i>
                        Download Prompts (.json)
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loading-state" style="display: none;">
                <div class="loading-card">
                    <div class="loading-spinner">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                    </div>
                    <h3 class="loading-title">Crafting Your Script</h3>
                    <p class="loading-subtitle">The AI is weaving your narrative...</p>
                </div>
            </div>

            <!-- History Section (Saved Projects) -->
            <div class="history-section" style="margin-top: 4rem;">
                <div class="section-header-compact">
                    <h3 class="section-title-compact">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Recent Scripts
                    </h3>
                </div>

                @if($projects->isEmpty())
                <div class="empty-state">
                    <i class="fa-solid fa-pen"></i>
                    <h4>No Scripts Yet</h4>
                    <p>Your generated scripts will appear here</p>
                </div>
                @else
                <div class="project-accordion">
                    @foreach($projects as $project)
                    <div class="accordion-item" data-project-id="{{ $project->id }}">
                        <div class="accordion-header">
                            <div class="project-info">
                                <i class="fa-solid fa-file-lines"></i>
                                <div>
                                    <div class="project-title">{{ $project->title }}</div>
                                    <div class="project-date">
                                        <i class="fa-regular fa-calendar"></i>
                                        {{ $project->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>
                            <div class="project-actions">
                                <button class="btn-download-project download-script" data-id="{{ $project->id }}">
                                    <i class="fa-solid fa-download"></i>
                                </button>
                                <button class="btn-delete-project delete-script" data-id="{{ $project->id }}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="accordion-content">
                            <div class="days-container">
                                @foreach($project->scenes->sortBy('scene_number') as $scene)
                                <div class="day-block">
                                    <div class="day-header">
                                        <span class="day-title">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                            Scene {{ $scene->scene_number }}
                                        </span>
                                        <button class="btn-regenerate-scene" data-scene-id="{{ $scene->id }}">
                                            <i class="fa-solid fa-rotate"></i>
                                            Regenerate
                                        </button>
                                    </div>
                                    <div class="day-texts">
                                        <div class="text-block" data-type="visual" data-scene="{{ $scene->scene_number }}">
                                            <div class="text-header">
                                                <span class="text-number">Visual Prompt</span>
                                                <div class="text-actions">
                                                    <button class="btn-text-action copy-text" data-content="{{ $scene->visual_prompt }}">
                                                        <i class="fa-regular fa-copy"></i>
                                                    </button>
                                                    <button class="btn-text-action view-text" data-content="{{ $scene->visual_prompt }}">
                                                        <i class="fa-regular fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="text-preview">{{ Str::limit($scene->visual_prompt, 150) }}</div>
                                        </div>
                                        <div class="text-block" data-type="voiceover" data-scene="{{ $scene->scene_number }}">
                                            <div class="text-header">
                                                <span class="text-number">Voiceover Script</span>
                                                <div class="text-actions">
                                                    <button class="btn-text-action copy-text" data-content="{{ $scene->voiceover_script }}">
                                                        <i class="fa-regular fa-copy"></i>
                                                    </button>
                                                    <button class="btn-text-action view-text" data-content="{{ $scene->voiceover_script }}">
                                                        <i class="fa-regular fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="text-preview">{{ Str::limit($scene->voiceover_script, 150) }}</div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-regular fa-file-lines"></i>
                    <span id="preview-title">Full Text</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="preview-content" class="preview-text-content"></div>
            </div>
            <div class="modal-footer">
                <button class="btn-primary-action" id="copy-preview-btn" style="width: auto;">
                    <i class="fa-regular fa-copy"></i>
                    Copy to Clipboard
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="{{URL::asset('plugins/sweetalert/sweetalert2.all.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Scene count slider
        const sceneSlider = document.getElementById('scene-slider');
        const sceneValue = document.getElementById('scene-value');
        
        if (sceneSlider && sceneValue) {
            sceneSlider.addEventListener('input', function() {
                const val = this.value;
                sceneValue.textContent = val + (val == 1 ? ' scene' : ' scenes');
            });
        }
        
        // Character counter
        const promptInput = document.querySelector('textarea[name="prompt"]');
        const promptCount = document.getElementById('prompt-count');
        
        if (promptInput && promptCount) {
            promptInput.addEventListener('input', function() {
                promptCount.textContent = this.value.length;
            });
        }
        
        // Style selector
        document.querySelectorAll('.style-option').forEach(function(option) {
            option.addEventListener('click', function() {
                document.querySelectorAll('.style-option').forEach(function(opt) {
                    opt.classList.remove('active');
                });
                this.classList.add('active');
                const radio = this.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;
            });
        });
        
        // Form submission
        const form = document.getElementById('script-form');
        const generateBtn = document.getElementById('generate-button');
        const scriptBoard = document.getElementById('script-board');
        const scenesContainer = document.getElementById('scenes-container');
        const loadingState = document.getElementById('loading-state');
        
        // Remove credit modal references since we're not using credits
        
        if (form && generateBtn) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Disable button and show loading
                generateBtn.disabled = true;
                generateBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
                
                // Hide any previous results, show loading
                if (scriptBoard) scriptBoard.style.display = 'none';
                // Scroll to loading state
                if (loadingState) {
                    loadingState.style.display = 'block';
                    loadingState.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                const formData = new FormData(form);
                setTimeout(() => {
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {                        
                        if (data.status === 'processing') {
                            // Start polling for results
                            pollScriptGeneration(data.project_id);
                        } else if (data.status === 'complete') {
                            // Demo mode or already complete - display immediately
                            if (loadingState) loadingState.style.display = 'none';
                            displayScriptResults(data.project);
                            generateBtn.disabled = false;
                            generateBtn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate Script';
                        } else {
                            // Show error
                            Swal.fire({
                                icon: 'error',
                                title: 'Generation Failed',
                                text: data.message || 'An error occurred',
                                confirmButtonColor: '#0783CF',
                                background: 'rgba(18, 25, 40, 0.95)',
                                color: 'white'
                            });
                            
                            // Reset button
                            generateBtn.disabled = false;
                            generateBtn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate Script';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (loadingState) loadingState.style.display = 'none';
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to start generation',
                            confirmButtonColor: '#0783CF',
                            background: 'rgba(18, 25, 40, 0.95)',
                            color: 'white'
                        });
                        
                        generateBtn.disabled = false;
                        generateBtn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate Script';
                    });
                },3000);
            });
        }
        
        // Poll for script generation
        function pollScriptGeneration(projectId) {
            let isComplete = false;
            
            const pollInterval = setInterval(function() {
                // Don't poll if already complete
                if (isComplete) return;
                
                fetch(`/app/user/api/ai-director/script/${projectId}/status`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'complete') {
                            if (loadingState) loadingState.style.display = 'none';
                            isComplete = true;
                            clearInterval(pollInterval);
                            if (generateBtn) {
                                generateBtn.disabled = false;
                                generateBtn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate Script';
                            }
                            displayScriptResults(data.project);
                        } else if (data.status === 'failed') {
                            isComplete = true;
                            clearInterval(pollInterval);
                            
                            if (loadingState) loadingState.style.display = 'none';
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Generation Failed',
                                text: 'The script generation failed. Please try again.',
                                confirmButtonColor: '#0783CF',
                                background: 'rgba(18, 25, 40, 0.95)',
                                color: 'white'
                            });
                            
                            if (generateBtn) {
                                generateBtn.disabled = false;
                                generateBtn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate Script';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Polling error:', error);
                    });
            }, 2000);
            
            // Stop polling after 60 seconds only if not already complete
            setTimeout(function() {
                if (!isComplete) {
                    clearInterval(pollInterval);
                    if (loadingState) loadingState.style.display = 'none';
                    
                    Swal.fire({
                        icon: 'warning',
                        title: 'Taking Longer Than Expected',
                        text: 'Your script is still generating. You can check back in the history section.',
                        confirmButtonColor: '#0783CF',
                        background: 'rgba(18, 25, 40, 0.95)',
                        color: 'white'
                    });
                    
                    if (generateBtn) {
                        generateBtn.disabled = false;
                        generateBtn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate Script';
                    }
                }
            }, 60000);
        }
        
        // Display script results
        function displayScriptResults(project) {
            if (!scenesContainer) return;
            
            scenesContainer.innerHTML = '';
            
            project.scenes.forEach(function(scene) {
                const sceneHtml = `
                    <div class="scene-card" data-scene-id="${scene.id}" data-project-id="${project.id}">
                        <div class="scene-header">
                            <h4 class="scene-title">Scene ${scene.scene_number}</h4>
                            <button class="btn-regenerate-scene" data-scene-id="${scene.id}">
                                <i class="fa-solid fa-rotate"></i>
                                Regenerate
                            </button>
                        </div>
                        <div class="scene-tabs">
                            <div class="tab-buttons">
                                <button class="tab-btn active" data-tab="visual-${scene.id}">
                                    <i class="fa-regular fa-image"></i>
                                    Visual Prompt
                                </button>
                                <button class="tab-btn" data-tab="voiceover-${scene.id}">
                                    <i class="fa-regular fa-message"></i>
                                    Voiceover Script
                                </button>
                            </div>
                            <div class="tab-contents">
                                <div class="tab-content active" id="visual-${scene.id}">
                                    <div class="scene-text">${scene.visual_prompt.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>
                                    <div class="text-actions">
                                        <button class="btn-text-action copy-text" data-content="${scene.visual_prompt.replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;')}">
                                            <i class="fa-regular fa-copy"></i>
                                            Copy
                                        </button>
                                    </div>
                                </div>
                                <div class="tab-content" id="voiceover-${scene.id}">
                                    <div class="scene-text">${scene.voiceover_script.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>
                                    <div class="text-actions">
                                        <button class="btn-text-action copy-text" data-content="${scene.voiceover_script.replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;')}">
                                            <i class="fa-regular fa-copy"></i>
                                            Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                scenesContainer.innerHTML += sceneHtml;
            });
            
            // Show the script board
            if (scriptBoard) scriptBoard.style.display = 'block';
            
            // Scroll to results
            if (scriptBoard) scriptBoard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            // Re-attach event listeners for new buttons
            attachSceneEventListeners();
        }
        
        // Attach event listeners to scene elements
        function attachSceneEventListeners() {
            // Tab switching - use event delegation on scenes container
            const scenesContainer = document.getElementById('scenes-container');
            if (scenesContainer) {
                scenesContainer.addEventListener('click', function(e) {
                    const tabBtn = e.target.closest('.tab-btn');
                    if (!tabBtn) return;
                    
                    e.preventDefault();
                    const tabId = tabBtn.dataset.tab;
                    
                    // Remove active class from all tabs in this scene
                    const parent = tabBtn.closest('.scene-tabs');
                    if (!parent) return;
                    
                    parent.querySelectorAll('.tab-btn').forEach(function(b) {
                        b.classList.remove('active');
                    });
                    parent.querySelectorAll('.tab-content').forEach(function(c) {
                        c.classList.remove('active');
                    });
                    
                    // Activate clicked tab
                    tabBtn.classList.add('active');
                    const tabContent = document.getElementById(tabId);
                    if (tabContent) tabContent.classList.add('active');
                });
            }
            
            // Copy text buttons
            document.querySelectorAll('.copy-text').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const content = this.dataset.content;
                    
                    navigator.clipboard.writeText(content).then(function() {
                        // Show temporary success
                        const originalHtml = btn.innerHTML;
                        btn.innerHTML = '<i class="fa-regular fa-check"></i> Copied!';
                        setTimeout(function() {
                            btn.innerHTML = originalHtml;
                        }, 2000);
                    }).catch(function(err) {
                        console.error('Could not copy text: ', err);
                    });
                });
            });
            
            // Regenerate scene buttons
            document.querySelectorAll('.btn-regenerate-scene').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const sceneId = this.dataset.sceneId;
                    
                    Swal.fire({
                        title: 'Regenerate Scene?',
                        text: 'This will replace the current scene content',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0783CF',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, regenerate',
                        background: 'rgba(18, 25, 40, 0.95)',
                        color: 'white'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            regenerateScene(sceneId);
                        }
                    });
                });
            });
        }
        
        // Regenerate scene function
        function regenerateScene(sceneId) {
            fetch(`/app/user/api/ai-director/script/scene/${sceneId}/regenerate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'processing') {
                    Swal.fire({
                        title: 'Regenerating...',
                        text: 'Your scene is being regenerated',
                        icon: 'info',
                        confirmButtonColor: '#0783CF',
                        background: 'rgba(18, 25, 40, 0.95)',
                        color: 'white',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Refresh page after short delay
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: data.message || 'Could not regenerate scene',
                        confirmButtonColor: '#0783CF',
                        background: 'rgba(18, 25, 40, 0.95)',
                        color: 'white'
                    });
                }
            });
        }
        
        // Delete project
        document.querySelectorAll('.delete-script').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const projectId = this.dataset.id;
                
                Swal.fire({
                    title: 'Delete Project?',
                    text: 'This action cannot be undone',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete',
                    background: 'rgba(18, 25, 40, 0.95)',
                    color: 'white'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteProject(projectId);
                    }
                });
            });
        });
        
        function deleteProject(projectId) {
            fetch(`/app/user/api/ai-director/script/project/${projectId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted',
                        text: data.message,
                        confirmButtonColor: '#0783CF',
                        background: 'rgba(18, 25, 40, 0.95)',
                        color: 'white',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        }
        
        // Download project
        document.querySelectorAll('.download-script').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const projectId = this.dataset.id;
                window.location.href = `/app/user/api/ai-director/script/${projectId}/export`;
            });
        });
        
        // Accordion functionality
        document.querySelectorAll('.accordion-header').forEach(function(header) {
            header.addEventListener('click', function() {
                const content = this.nextElementSibling;
                this.classList.toggle('active');
                if (content) content.classList.toggle('active');
                
                const icon = this.querySelector('.project-info i');
                if (icon) {
                    if (content && content.classList.contains('active')) {
                        icon.className = 'fa-solid fa-folder-open';
                    } else {
                        icon.className = 'fa-solid fa-file-lines';
                    }
                }
            });
        });
        
        // Preview modal functionality
        const previewModalElement = document.getElementById('previewModal');
        let previewModal = null;
        if (previewModalElement && typeof bootstrap !== 'undefined') {
            previewModal = new bootstrap.Modal(previewModalElement);
        }
        
        document.querySelectorAll('.view-text').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const content = this.dataset.content;
                const block = this.closest('.text-block');
                const type = block ? block.dataset.type : 'text';
                
                const previewTitle = document.getElementById('preview-title');
                const previewContent = document.getElementById('preview-content');
                
                if (previewTitle) {
                    previewTitle.textContent = type === 'voiceover' ? 'Voiceover Script' : 'Visual Prompt';
                }
                if (previewContent) {
                    previewContent.textContent = content;
                }
                
                if (previewModal) previewModal.show();
            });
        });
        
        // Copy from preview
        const copyPreviewBtn = document.getElementById('copy-preview-btn');
        if (copyPreviewBtn) {
            copyPreviewBtn.addEventListener('click', function() {
                const previewContent = document.getElementById('preview-content');
                if (!previewContent) return;
                
                const content = previewContent.textContent;
                
                navigator.clipboard.writeText(content).then(function() {
                    const originalHtml = copyPreviewBtn.innerHTML;
                    copyPreviewBtn.innerHTML = '<i class="fa-regular fa-check"></i> Copied!';
                    setTimeout(function() {
                        copyPreviewBtn.innerHTML = originalHtml;
                    }, 2000);
                });
            });
        }
        
        // Remove all credit and export related code
    });
</script>
@endsection
