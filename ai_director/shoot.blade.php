@extends('layouts.app')

@section('css')
<link href="{{ theme_url('custom/aidirector.css') }}" rel="stylesheet" />
<link href="{{URL::asset('plugins/sweetalert/sweetalert2.min.css')}}" rel="stylesheet" />
@endsection

@section('content')
<div class="page-body mt-2">
    <div class="container">
        <div class="py-10 px-10">
            <!-- Header -->
            <div class="automation-header">
                <div class="automation-badge">AI MOVIE SHOOT DIRECTOR</div>
                <h1 class="automation-title">The <span>Virtual Set</span></h1>
                <p class="automation-subtitle">Transform your script into visuals — scene by scene, on your terms</p>
            </div>

            <!-- Project Selector -->
            <div class="project-selector-container">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Select Script Project</label>
                    <div class="selector-with-action">
                        <select class="form-select" id="script-project-select" required>
                            <option value="">Choose a script project...</option>
                            @foreach($scriptProjects as $script)
                            <option value="{{ $script->id }}" data-scenes="{{ $script->scene_count }}">
                                {{ $script->title }} ({{ $script->scene_count }} scenes) - {{ $script->created_at->format('M d, Y') }}
                            </option>
                            @endforeach
                        </select>
                        <button class="btn-primary-action" id="load-project-btn" disabled>
                            <i class="fa-regular fa-folder-open"></i>
                            Load Project
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Tabs -->
            <div class="workflow-tabs">
                <button class="workflow-tab active" data-tab="image">
                    <i class="fa-regular fa-image"></i>
                    Image Generation (Storyboard)
                </button>
                <button class="workflow-tab" data-tab="video" disabled id="video-tab-btn">
                    <i class="fa-regular fa-film"></i>
                    Video Generation (Cinematography)
                </button>
            </div>

            <!-- Image Generation Tab -->
            <div id="image-tab" class="tab-content active">
                <div class="tab-header">
                    <h3 class="tab-title">
                        <i class="fa-regular fa-image"></i>
                        Storyboard Frames
                    </h3>
                    <div class="tab-actions">
                        <button class="btn-text-action" id="download-all-images" style="display: none;">
                            <i class="fa-regular fa-download"></i>
                            Download All Images (.zip)
                        </button>
                    </div>
                </div>

                <!-- Scenes Table -->
                <div id="scenes-table-container" class="scenes-table">
                    <div class="empty-state" id="image-empty-state">
                        <i class="fa-regular fa-folder-open"></i>
                        <h4>No Project Loaded</h4>
                        <p>Select a script project above to start generating storyboards</p>
                    </div>

                    <div id="scenes-table-body" style="display: none;">
                        <!-- Scene rows will be dynamically loaded here -->
                    </div>
                </div>
            </div>

            <!-- Video Generation Tab -->
            <div id="video-tab" class="tab-content">
                <div class="tab-header">
                    <h3 class="tab-title">
                        <i class="fa-regular fa-film"></i>
                        Cinematic Clips
                    </h3>
                    <div class="tab-actions">
                        <button class="btn-text-action" id="download-all-videos" style="display: none;">
                            <i class="fa-regular fa-download"></i>
                            Download All Clips (.zip)
                        </button>
                    </div>
                </div>

                <!-- Video Scenes Table -->
                <div id="video-scenes-table-container" class="scenes-table">
                    <div class="empty-state" id="video-empty-state">
                        <i class="fa-regular fa-film"></i>
                        <h4>No Images Ready</h4>
                        <p>Generate images first to enable video creation</p>
                    </div>

                    <div id="video-scenes-table-body" style="display: none;">
                        <!-- Video scene rows will be dynamically loaded here -->
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loading-state" style="display: none;">
                <div class="loading-card">
                    <div class="loading-spinner">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                    </div>
                    <h3 class="loading-title">Loading Project</h3>
                    <p class="loading-subtitle">Preparing your scenes...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Prompt Modal -->
<div class="modal fade" id="editPromptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-regular fa-pen-to-square"></i>
                    Edit Visual Prompt
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <textarea id="editPromptText" class="form-control" rows="6"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-primary-action" id="savePromptBtn" style="width: auto;">
                    <i class="fa-regular fa-floppy-disk"></i>
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-regular fa-image"></i>
                    Image Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" class="img-fluid" src="" alt="Preview" style="max-height: 70vh;">
            </div>
            <div class="modal-footer">
                <a id="download-image-btn" href="#" class="btn-primary-action" style="width: auto; text-decoration: none;">
                    <i class="fa-regular fa-download"></i>
                    Download Image
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Video Preview Modal -->
<div class="modal fade" id="videoPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-regular fa-film"></i>
                    Video Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <video id="previewVideo" class="img-fluid" controls style="max-height: 70vh;">
                    <source src="" type="video/mp4">
                </video>
            </div>
            <div class="modal-footer">
                <a id="download-video-btn" href="#" class="btn-primary-action" style="width: auto; text-decoration: none;">
                    <i class="fa-regular fa-download"></i>
                    Download Video
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{URL::asset('plugins/sweetalert/sweetalert2.all.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript">
    // Helper function to escape HTML
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    document.addEventListener('DOMContentLoaded', function() {
        // DOM Elements
        const projectSelect = document.getElementById('script-project-select');
        const loadProjectBtn = document.getElementById('load-project-btn');
        const selectorContainer = document.querySelector('.project-selector-container');
        const loadingState = document.getElementById('loading-state');
        const imageEmptyState = document.getElementById('image-empty-state');
        const scenesTableBody = document.getElementById('scenes-table-body');
        const videoScenesTableBody = document.getElementById('video-scenes-table-body');
        const videoTabBtn = document.getElementById('video-tab-btn');
        const downloadAllImagesBtn = document.getElementById('download-all-images');
        const downloadAllVideosBtn = document.getElementById('download-all-videos');
        
        // Store current project data
        let currentShootProjectId = null;
        let currentScriptProjectId = null;
        
        // Workflow tabs
        const workflowTabs = document.querySelectorAll('.workflow-tab');
        const tabContents = document.querySelectorAll('.tab-content');

        // Pulse the container first
        if (selectorContainer) {
            selectorContainer.classList.add('pulse-container');
            setTimeout(() => {
                selectorContainer.classList.remove('pulse-container');
            }, 2400);
        }

        // Then pulse the dropdown
        if (projectSelect) {
            setTimeout(() => {
                projectSelect.classList.add('pulse-select');
                setTimeout(() => {
                    projectSelect.classList.remove('pulse-select');
                }, 2400);
            }, 300);
        }

        // When dropdown value changes, pulse the button
        if (projectSelect && loadProjectBtn) {
            projectSelect.addEventListener('change', function() {
                if (this.value) {
                    loadProjectBtn.classList.add('pulse-btn');
                    setTimeout(() => {
                        loadProjectBtn.classList.remove('pulse-btn');
                    }, 2400);
                }
            });
        }
        
        workflowTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                if (this.disabled) return;
                
                workflowTabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                this.classList.add('active');
                document.getElementById(this.dataset.tab + '-tab').classList.add('active');
            });
        });

        // Enable/disable load button based on selection
        projectSelect.addEventListener('change', function() {
            loadProjectBtn.disabled = !this.value;
        });

        // Load project button click
        loadProjectBtn.addEventListener('click', function() {
            const projectId = projectSelect.value;
            if (!projectId) return;
            
            loadProject(projectId);
        });

        // Load project data
        function loadProject(projectId) {
            // Show loading
            loadingState.style.display = 'block';
            imageEmptyState.style.display = 'none';
            scenesTableBody.style.display = 'none';
            
            currentScriptProjectId = projectId;
            
            // Fetch project scenes
            fetch(`/app/user/api/ai-director/script/${projectId}/scenes`)
                .then(response => response.json())
                .then(data => {
                    loadingState.style.display = 'none';
                    
                    if (data.scenes && data.scenes.length > 0) {
                        currentShootProjectId = data.shoot_project_id;
                        renderImageScenes(data.scenes);
                        scenesTableBody.style.display = 'block';
                        downloadAllImagesBtn.style.display = 'inline-flex';
                        
                        // Check if any images exist to enable video tab
                        const hasImages = data.scenes.some(s => s.image_status === 2);
                        if (hasImages) {
                            videoTabBtn.disabled = false;
                            loadVideoScenes();
                        }
                    } else {
                        imageEmptyState.style.display = 'block';
                        imageEmptyState.innerHTML = `
                            <i class="fa-regular fa-file-lines"></i>
                            <h4>No Scenes Found</h4>
                            <p>This script project has no scenes</p>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading project:', error);
                    loadingState.style.display = 'none';
                    imageEmptyState.style.display = 'block';
                    imageEmptyState.innerHTML = `
                        <i class="fa-regular fa-circle-exclamation"></i>
                        <h4>Failed to Load</h4>
                        <p>Could not load project scenes</p>
                    `;
                });
        }

        // Render image scenes table
        function renderImageScenes(scenes) {
            let html = '';
            
            scenes.forEach((scene, index) => {
                const frameId = scene.frame_id || '';
                const statusMap = { 0: 'idle', 1: 'processing', 2: 'success', 3: 'failed' };
                const status = statusMap[scene.image_status] || 'idle';
                const isProcessing = status === 'processing';
                
                // Check if previous scene is ready (for scene > 1)
                const prevScene = scenes[index - 1];
                const isPrevReady = index === 0 || (prevScene && prevScene.image_status === 2);
                const isGenerateDisabled = !isPrevReady || isProcessing || (status === 'processing');
                
                html += `
                    <div class="scene-row ${isProcessing ? 'generating' : ''}" data-frame-id="${frameId}" data-scene-id="${scene.id}" data-scene-number="${scene.scene_number}">
                        <div class="row-content">
                            <div class="col-prompt">
                                <div class="scene-header">
                                    <span class="scene-index">Scene ${scene.scene_number}</span>
                                    ${index > 0 ? `<span class="chain-indicator ${isPrevReady ? 'ready' : 'locked'}">
                                        <i class="fa-solid ${isPrevReady ? 'fa-link' : 'fa-lock'}"></i>
                                        ${isPrevReady ? 'Ready' : 'Previous scene required'}
                                    </span>` : ''}
                                </div>
                                <textarea class="prompt-textarea" rows="3" placeholder="Visual prompt for this scene..." ${isProcessing ? 'disabled' : ''}>${escapeHtml(scene.visual_prompt || '')}</textarea>
                                <div class="action-buttons">
                                    <button class="scene-btn generate-image" data-frame-id="${frameId}" data-scene-id="${scene.id}" data-prompt="${escapeHtml(scene.visual_prompt || '')}" ${isGenerateDisabled ? 'disabled' : ''}>
                                        <i class="fa-regular fa-wand-magic"></i>
                                        ${status === 'success' ? 'Regenerate' : 'Generate'}
                                    </button>
                                    <button class="scene-btn delete-scene" data-frame-id="${frameId}" data-scene-id="${scene.id}" ${!frameId || isProcessing ? 'disabled' : ''}>
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                                ${!isPrevReady && index > 0 ? `
                                    <div class="chain-warning">
                                        <i class="fa-regular fa-circle-exclamation"></i>
                                        Generate Scene ${index} first to unlock this scene
                                    </div>
                                ` : ''}
                            </div>
                            <div class="col-output">
                                ${renderImageOutput(scene)}
                            </div>
                        </div>
                    </div>
                `;
            });
            
            scenesTableBody.innerHTML = html;
            attachImageSceneEvents();
        }

        function renderImageOutput(scene) {
            const statusMap = { 0: 'idle', 1: 'processing', 2: 'success', 3: 'failed' };
            const status = statusMap[scene.image_status] || 'idle';
            
            if (status === 'processing') {
                return `
                    <div class="output-state processing">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                        <span>Generating image...</span>
                    </div>
                `;
            } else if (status === 'success' && scene.image_url) {
                return `
                    <div class="output-state success">
                        <img src="${scene.image_url}" alt="Scene ${scene.scene_number}" class="output-thumbnail">
                        <div class="output-actions">
                            <button class="output-btn view-image" data-src="${scene.image_url}">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                            <a href="${scene.image_url}" class="output-btn" download>
                                <i class="fa-regular fa-download"></i>
                            </a>
                            <button class="output-btn regenerate-image" data-frame-id="${scene.frame_id}" data-scene-id="${scene.id}" data-prompt="${scene.visual_prompt || ''}">
                                <i class="fa-regular fa-rotate"></i>
                            </button>
                        </div>
                    </div>
                `;
            } else if (status === 'failed') {
                return `
                    <div class="output-state failed">
                        <i class="fa-regular fa-circle-exclamation"></i>
                        <span>Generation failed</span>
                        <button class="output-btn regenerate-image" data-frame-id="${scene.frame_id}" data-scene-id="${scene.id}" data-prompt="${scene.visual_prompt || ''}">
                            <i class="fa-regular fa-rotate"></i>
                        </button>
                    </div>
                `;
            } else {
                return `
                    <div class="output-state idle">
                        <i class="fa-regular fa-hourglass"></i>
                        <span>Not generated</span>
                    </div>
                `;
            }
        }

        function attachImageSceneEvents() {
            // Generate image buttons
            document.querySelectorAll('.generate-image').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sceneId = this.dataset.sceneId;
                    const frameId = this.dataset.frameId;
                    const prompt = this.dataset.prompt || this.closest('.scene-row').querySelector('.prompt-textarea').value;
                    
                    // Disable button to prevent double clicks
                    this.disabled = true;
                    
                    generateImage(frameId, prompt, this);
                });
            });

            // Regenerate image buttons
            document.querySelectorAll('.regenerate-image').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sceneId = this.dataset.sceneId;
                    const frameId = this.dataset.frameId;
                    const prompt = this.dataset.prompt;
                    
                    showEditPromptModal(sceneId, frameId, prompt, true);
                });
            });

            // Delete scene buttons
            document.querySelectorAll('.delete-scene').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sceneId = this.dataset.sceneId;
                    const frameId = this.dataset.frameId;
                    
                    Swal.fire({
                        title: 'Delete Scene?',
                        text: 'This will remove this scene from the storyboard',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete',
                        background: 'rgba(18, 25, 40, 0.95)',
                        color: 'white'
                    }).then((result) => {
                        if (result.isConfirmed && frameId) {
                            deleteFrame(frameId, sceneId);
                        }
                    });
                });
            });

            // View image buttons
            document.querySelectorAll('.view-image').forEach(btn => {
                btn.addEventListener('click', function() {
                    const src = this.dataset.src;
                    document.getElementById('previewImage').src = src;
                    document.getElementById('download-image-btn').href = src;
                    
                    const imageModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
                    imageModal.show();
                });
            });
        }

        function generateImage(frameId, prompt, button) {
            const row = document.querySelector(`[data-frame-id="${frameId}"]`);
            if (row) {
                row.classList.add('generating');
                // Disable the generate button
                const generateBtn = row.querySelector('.generate-image');
                if (generateBtn) generateBtn.disabled = true;
            } else {
                button.disabled = false;
                return;
            }
            
            const outputDiv = row.querySelector('.col-output');
            
            outputDiv.innerHTML = `
                <div class="output-state processing">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                    <span>Generating image...</span>
                </div>
            `;
            
            fetch('/app/user/api/ai-director/shoot/generate-image', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    frame_id: frameId,
                    prompt: prompt
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'processing') {
                    pollImageGeneration(frameId);
                } else {
                    showError('Failed to start generation');
                    row.classList.remove('generating');
                    const generateBtn = row.querySelector('.generate-image');
                    if (generateBtn) generateBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Network error occurred');
                row.classList.remove('generating');
                const generateBtn = row.querySelector('.generate-image');
                if (generateBtn) generateBtn.disabled = false;
            });
        }

        function pollImageGeneration(frameId) {
            const pollInterval = setInterval(() => {
                fetch(`/app/user/api/ai-director/shoot/image-status/${frameId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            clearInterval(pollInterval);
                            updateFrameOutput(frameId, data);
                            
                            // Enable video tab and refresh
                            videoTabBtn.disabled = false;
                            if (currentProjectData) {
                                // Update local data
                                const sceneIndex = currentProjectData.scenes.findIndex(s => s.frame_id == frameId);
                                if (sceneIndex !== -1) {
                                    currentProjectData.scenes[sceneIndex].image_status = 2;
                                    currentProjectData.scenes[sceneIndex].image_url = data.image_url;
                                }
                                // Refresh video scenes
                                renderVideoScenes(currentProjectData.scenes);
                            }
                        } else if (data.status === 'failed') {
                            clearInterval(pollInterval);
                            updateFrameOutput(frameId, { status: 'failed' });
                        }
                    })
                    .catch(error => {
                        console.error('Polling error:', error);
                    });
            }, 5000);
        }

        function updateFrameOutput(frameId, data) {
            const row = document.querySelector(`[data-frame-id="${frameId}"]`);
            if (row) {
                row.classList.remove('generating');
                const generateBtn = row.querySelector('.generate-image');
                if (generateBtn) generateBtn.disabled = false;
            }
            if (!row) return;
            
            const outputDiv = row.querySelector('.col-output');
            
            if (data.status === 'success' && data.image_url) {
                outputDiv.innerHTML = `
                    <div class="output-state success">
                        <img src="${data.image_url}" alt="Scene" class="output-thumbnail">
                        <div class="output-actions">
                            <button class="output-btn view-image" data-src="${data.image_url}">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                            <a href="${data.image_url}" class="output-btn" download>
                                <i class="fa-regular fa-download"></i>
                            </a>
                            <button class="output-btn regenerate-image" data-frame-id="${frameId}" data-scene-id="${row.dataset.sceneId}" data-prompt="${data.prompt || ''}">
                                <i class="fa-regular fa-rotate"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                // Reattach events
                outputDiv.querySelector('.view-image')?.addEventListener('click', function() {
                    document.getElementById('previewImage').src = this.dataset.src;
                    document.getElementById('download-image-btn').href = this.dataset.src;
                    new bootstrap.Modal(document.getElementById('imagePreviewModal')).show();
                });
                
                outputDiv.querySelector('.regenerate-image')?.addEventListener('click', function() {
                    const sceneId = this.dataset.sceneId;
                    const frameId = this.dataset.frameId;
                    const prompt = this.dataset.prompt;
                    showEditPromptModal(sceneId, frameId, prompt, true);
                });
                
                // Unlock next scene if this one is complete
                const currentSceneNumber = parseInt(row.dataset.sceneNumber);
                const nextRow = document.querySelector(`.scene-row[data-scene-number="${currentSceneNumber + 1}"]`);
                if (nextRow) {
                    const nextBtn = nextRow.querySelector('.generate-image');
                    const chainIndicator = nextRow.querySelector('.chain-indicator');
                    const chainWarning = nextRow.querySelector('.chain-warning');
                    
                    if (nextBtn) nextBtn.disabled = false;
                    if (chainIndicator) {
                        chainIndicator.classList.remove('locked');
                        chainIndicator.classList.add('ready');
                        chainIndicator.innerHTML = '<i class="fa-solid fa-link"></i> Ready';
                    }
                    if (chainWarning) chainWarning.remove();
                }
                
            } else {
                outputDiv.innerHTML = `
                    <div class="output-state failed">
                        <i class="fa-regular fa-circle-exclamation"></i>
                        <span>Generation failed</span>
                        <button class="output-btn regenerate-image" data-frame-id="${frameId}" data-scene-id="${row.dataset.sceneId}" data-prompt="${data.prompt || ''}">
                            <i class="fa-regular fa-rotate"></i>
                            Retry
                        </button>
                    </div>
                `;
                
                outputDiv.querySelector('.regenerate-image')?.addEventListener('click', function() {
                    const sceneId = this.dataset.sceneId;
                    const frameId = this.dataset.frameId;
                    const prompt = this.dataset.prompt;
                    generateImage(frameId, prompt, this);
                });
            }
        }

        function showEditPromptModal(sceneId, frameId, prompt, isRegenerate = false) {
            const editModal = new bootstrap.Modal(document.getElementById('editPromptModal'));
            document.getElementById('editPromptText').value = prompt;
            
            document.getElementById('savePromptBtn').onclick = function() {
                const newPrompt = document.getElementById('editPromptText').value;
                editModal.hide();
                
                if (isRegenerate && frameId) {
                    generateImage(frameId, newPrompt);
                } else {
                    savePromptOnly(frameId, newPrompt);
                }
            };
            
            editModal.show();
        }

        function savePromptOnly(frameId, prompt) {
            if (!frameId) return;
            
            fetch(`/app/user/api/ai-director/shoot/frame/${frameId}/prompt`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ prompt: prompt })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: 'Prompt updated successfully',
                        timer: 1500,
                        showConfirmButton: false,
                        background: 'rgba(18, 25, 40, 0.95)',
                        color: 'white'
                    });
                }
            });
        }

        function deleteFrame(frameId, sceneId) {
            fetch(`/app/user/api/ai-director/shoot/frame/${frameId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-scene-id="${sceneId}"]`).remove();
                    
                    // Check if any scenes left
                    if (document.querySelectorAll('.scene-row').length === 0) {
                        location.reload();
                    }
                }
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonColor: 'var(--blue-light)',
                background: 'rgba(18, 25, 40, 0.95)',
                color: 'white'
            });
        }

        // Store current project data globally
        let currentProjectData = null;

        function loadProject(projectId) {
            // Show loading
            loadingState.style.display = 'block';
            imageEmptyState.style.display = 'none';
            scenesTableBody.style.display = 'none';
            
            currentScriptProjectId = projectId;
            
            // Fetch project scenes
            fetch(`/app/user/api/ai-director/script/${projectId}/scenes`)
                .then(response => response.json())
                .then(data => {
                    loadingState.style.display = 'none';
                    
                    if (data.scenes && data.scenes.length > 0) {
                        currentProjectData = data; // Store full data
                        currentShootProjectId = data.shoot_project_id;
                        renderImageScenes(data.scenes);
                        scenesTableBody.style.display = 'block';
                        downloadAllImagesBtn.style.display = 'inline-flex';
                        
                        // Check for any images ready for video tab
                        const hasImages = data.scenes.some(s => s.image_status === 2);
                        if (hasImages) {
                            videoTabBtn.disabled = false;
                            // Use the same scenes data for video tab
                            renderVideoScenes(data.scenes);
                            document.getElementById('video-empty-state').style.display = 'none';
                            videoScenesTableBody.style.display = 'block';
                            downloadAllVideosBtn.style.display = 'inline-flex';
                        } else {
                            document.getElementById('video-empty-state').style.display = 'block';
                            videoScenesTableBody.style.display = 'none';
                        }
                    } else {
                        imageEmptyState.style.display = 'block';
                        imageEmptyState.innerHTML = `
                            <i class="fa-regular fa-file-lines"></i>
                            <h4>No Scenes Found</h4>
                            <p>This script project has no scenes</p>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading project:', error);
                    loadingState.style.display = 'none';
                    imageEmptyState.style.display = 'block';
                    imageEmptyState.innerHTML = `
                        <i class="fa-regular fa-circle-exclamation"></i>
                        <h4>Failed to Load</h4>
                        <p>Could not load project scenes</p>
                    `;
                });
        }

        function renderVideoScenes(scenes) {
            let html = '';
            
            // Filter only scenes with successful images
            const videoScenes = scenes.filter(scene => scene.image_status === 2);
            
            if (videoScenes.length === 0) {
                document.getElementById('video-empty-state').style.display = 'block';
                videoScenesTableBody.style.display = 'none';
                return;
            }
            
            document.getElementById('video-empty-state').style.display = 'none';
            videoScenesTableBody.style.display = 'block';
            
            videoScenes.forEach(scene => {
                const statusMap = { 0: 'idle', 1: 'processing', 2: 'success', 3: 'failed' };
                const status = statusMap[scene.video_status] || 'idle';
                const isProcessing = status === 'processing';
                
                html += `
                    <div class="scene-row video-row ${isProcessing ? 'generating' : ''}" data-frame-id="${scene.frame_id}">
                        <div class="row-content">
                            <!-- Column 1: Source Image Preview -->
                            <div class="col-preview">
                                <div class="source-preview">
                                    <img src="${scene.image_url}" alt="Source" class="source-thumbnail">
                                </div>
                            </div>
                            
                            <!-- Column 2: Generate Button -->
                            <div class="col-actions">
                                <button class="scene-btn generate-video" data-frame-id="${scene.frame_id}" ${isProcessing ? 'disabled' : ''}>
                                    <i class="fa-regular fa-film"></i>
                                    Generate Video
                                </button>
                            </div>
                            
                            <!-- Column 3: Output Area -->
                            <div class="col-output">
                                ${renderVideoOutput(scene)}
                            </div>
                        </div>
                    </div>
                `;
            });
            
            videoScenesTableBody.innerHTML = html;
            attachVideoSceneEvents();
        }

        function renderVideoOutput(scene) {
            const statusMap = { 0: 'idle', 1: 'processing', 2: 'success', 3: 'failed' };
            const status = statusMap[scene.video_status] || 'idle';
            
            if (status === 'processing') {
                return `
                    <div class="output-state processing">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                        <span>Rendering video...</span>
                    </div>
                `;
            } else if (status === 'success' && scene.video_url) {
                return `
                    <div class="output-state success">
                        <video class="output-thumbnail" src="${scene.video_url}" muted></video>
                        <div class="output-actions">
                            <button class="output-btn play-video" data-src="${scene.video_url}">
                                <i class="fa-regular fa-circle-play"></i>
                            </button>
                            <a href="${scene.video_url}" class="output-btn" download>
                                <i class="fa-regular fa-download"></i>
                            </a>
                        </div>
                    </div>
                `;
            } else if (status === 'failed') {
                return `
                    <div class="output-state failed">
                        <i class="fa-regular fa-circle-exclamation"></i>
                        <span>Failed</span>
                        <button class="output-btn retry-video" data-frame-id="${scene.frame_id}">
                            <i class="fa-regular fa-rotate"></i>
                            Retry
                        </button>
                    </div>
                `;
            } else {
                return `
                    <div class="output-state idle">
                        <i class="fa-regular fa-hourglass"></i>
                        <span>Ready to generate</span>
                    </div>
                `;
            }
        }

        function attachVideoSceneEvents() {
            document.querySelectorAll('.generate-video').forEach(btn => {
                btn.addEventListener('click', function() {
                    const frameId = this.dataset.frameId;
                    const row = this.closest('.video-row');
                    
                    generateVideo(frameId);
                });
            });

            document.querySelectorAll('.play-video').forEach(btn => {
                btn.addEventListener('click', function() {
                    const src = this.dataset.src;
                    const videoElement = document.getElementById('previewVideo');
                    videoElement.querySelector('source').src = src;
                    videoElement.load();
                    document.getElementById('download-video-btn').href = src;
                    
                    new bootstrap.Modal(document.getElementById('videoPreviewModal')).show();
                });
            });

            document.querySelectorAll('.retry-video').forEach(btn => {
                btn.addEventListener('click', function() {
                    const frameId = this.dataset.frameId;
                    const row = document.querySelector(`.video-row[data-frame-id="${frameId}"]`);
                    
                    generateVideo(frameId);
                });
            });
        }

        function generateVideo(frameId) {
            const row = document.querySelector(`.video-row[data-frame-id="${frameId}"]`);
            if (!row) return;
            
            const outputDiv = row.querySelector('.col-output');
            
            outputDiv.innerHTML = `
                <div class="output-state processing">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                    <span>Rendering video...</span>
                </div>
            `;
            
            // Add generating class to row for overlay
            row.classList.add('generating');
            
            fetch('/app/user/api/ai-director/shoot/generate-video', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    frame_id: frameId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'processing') {
                    pollVideoGeneration(frameId);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to start video generation');
                row.classList.remove('generating');
            });
        }

        function pollVideoGeneration(frameId) {
            const pollInterval = setInterval(() => {
                fetch(`/app/user/api/ai-director/shoot/video-status/${frameId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            clearInterval(pollInterval);
                            updateVideoOutput(frameId, data);
                        } else if (data.status === 'failed') {
                            clearInterval(pollInterval);
                            updateVideoOutput(frameId, { status: 'failed' });
                        }
                    })
                    .catch(error => {
                        console.error('Polling error:', error);
                    });
            }, 3000);
        }

        function updateVideoOutput(frameId, data) {
            const row = document.querySelector(`.video-row[data-frame-id="${frameId}"]`);
            if (!row) return;
            
            const outputDiv = row.querySelector('.col-output');
            
            // Remove generating overlay
            row.classList.remove('generating');
            
            if (data.status === 'success' && data.video_url) {
                outputDiv.innerHTML = `
                    <div class="output-state success">
                        <video class="output-thumbnail" src="${data.video_url}" muted></video>
                        <div class="output-actions">
                            <button class="output-btn play-video" data-src="${data.video_url}">
                                <i class="fa-regular fa-circle-play"></i>
                            </button>
                            <a href="${data.video_url}" class="output-btn" download>
                                <i class="fa-regular fa-download"></i>
                            </a>
                        </div>
                    </div>
                `;
                
                outputDiv.querySelector('.play-video')?.addEventListener('click', function() {
                    const src = this.dataset.src;
                    const videoElement = document.getElementById('previewVideo');
                    videoElement.querySelector('source').src = src;
                    videoElement.load();
                    document.getElementById('download-video-btn').href = src;
                    new bootstrap.Modal(document.getElementById('videoPreviewModal')).show();
                });
            } else {
                outputDiv.innerHTML = `
                    <div class="output-state failed">
                        <i class="fa-regular fa-circle-exclamation"></i>
                        <span>Failed</span>
                        <button class="output-btn retry-video" data-frame-id="${frameId}">
                            <i class="fa-regular fa-rotate"></i>
                            Retry
                        </button>
                    </div>
                `;
                
                outputDiv.querySelector('.retry-video')?.addEventListener('click', function() {
                    generateVideo(frameId);
                });
            }
        }

        // Download all images
        downloadAllImagesBtn.addEventListener('click', function() {
            if (currentShootProjectId) {
                window.open(`/app/user/api/ai-director/shoot/${currentShootProjectId}/download-images`);
            }
        });

        // Download all videos
        downloadAllVideosBtn.addEventListener('click', function() {
            if (currentShootProjectId) {
                window.open(`/app/user/api/ai-director/shoot/${currentShootProjectId}/download-videos`);
            }
        });
    });
</script>
@endsection