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
                <div class="automation-badge">AI SOUND DIRECTOR</div>
                <h1 class="automation-title">The <span>Foley & Dub Stage</span></h1>
                <p class="automation-subtitle">Bring your scenes to life with voiceovers and cinematic sound effects</p>
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
                <button class="workflow-tab active" data-tab="voiceover">
                    <i class="fa-regular fa-microphone"></i>
                    Voiceover (Dialogue)
                </button>
                <button class="workflow-tab" data-tab="sfx" id="sfx">
                    <i class="fa-regular fa-waveform"></i>
                    Sound Effects (SFX)
                </button>
            </div>

            <!-- Voiceover Tab -->
            <div id="voiceover-tab" class="tab-content active">
                <div class="tab-header">
                    <h3 class="tab-title">
                        <i class="fa-regular fa-microphone"></i>
                        Voiceover Studio
                    </h3>
                </div>

                <!-- Scenes Table -->
                <div id="scenes-table-container" class="scenes-table">
                    <div class="empty-state" id="voiceover-empty-state">
                        <i class="fa-regular fa-folder-open"></i>
                        <h4>No Project Loaded</h4>
                        <p>Select a script project above to start generating voiceovers</p>
                    </div>

                    <div id="scenes-table-body" style="display: none;">
                        <!-- Scene rows will be dynamically loaded here -->
                    </div>
                </div>
            </div>

            <!-- SFX Tab -->
            <div id="sfx-tab" class="tab-content">
                <div class="tab-header">
                    <h3 class="tab-title">
                        <i class="fa-regular fa-waveform"></i>
                        Sound Effects Library
                    </h3>
                </div>

                <!-- SFX Form Container - Hidden until project loaded -->
                <div class="sfx-form-container mb-3" id="sfx-form-container" style="display: none;">
                    <div class="sfx-form-card">
                        <div class="form-group">
                            <label class="form-label">SFX Prompt</label>
                            <textarea class="form-control" id="sfx-prompt" rows="3" placeholder="Describe the sound effect you want... e.g., 'Rain on a tin roof', 'Car tires screeching', 'Cinematic whoosh'"></textarea>
                            <div class="character-counter">
                                <small><span id="sfx-count">0</span>/500 characters</small>
                            </div>
                        </div>
                        <div class="submit-container text-center">
                            <button class="btn-primary-action" id="generate-sfx-btn">
                                <i class="fa-regular fa-wand-magic"></i>
                                Generate Sound Effect
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SFX Results Grid -->
                <div id="sfx-results-container" class="sfx-results" style="display: none;">
                    <div class="tab-header" style="margin-top: 2rem;">
                        <h3 class="tab-title">
                            <i class="fa-regular fa-list"></i>
                            Generated SFX
                        </h3>
                    </div>
                    <div id="sfx-grid" class="sfx-grid">
                        <!-- SFX items will be loaded here -->
                    </div>
                </div>

                <!-- Empty State - Shown when no project loaded or no SFX -->
                <div id="sfx-empty-state" class="empty-state">
                    <i class="fa-regular fa-waveform"></i>
                    <h4>No Project Loaded</h4>
                    <p>Select a script project above to start generating sound effects</p>
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

<!-- Voice Settings Modal - Updated without emotion -->
<div class="modal fade" id="voiceSettingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-regular fa-sliders"></i>
                    Voice Settings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Language</label>
                        <div class="modern-dropdown" id="modal-language-dropdown">
                            <button type="button" class="modern-dropdown-btn w-100" >
                                <img id="modal-selected-language-flag" src="" style="width: 20px; height: 20px;">
                                <span id="modal-selected-language-name">Select Language</span>
                                <span class="dropdown-chevron">▼</span>
                            </button>
                            <div class="dropdown-menu modern-dropdown-menu w-100" id="modal-language-menu">
                                <div class="dropdown-section">
                                    <div class="dropdown-section-title">Languages</div>
                                    <div id="modal-language-options"></div>
                                </div>
                            </div>
                            <input type="hidden" id="modal-selected-language-code">
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Voice</label>
                        <div class="modern-dropdown" id="modal-voice-dropdown">
                            <button type="button" class="modern-dropdown-btn w-100" >
                                <img id="modal-selected-voice-img" src="" style="width: 20px; height: 20px; border-radius: 3px;">
                                <span id="modal-selected-voice-name">Select Voice</span>
                                <span class="dropdown-chevron">▼</span>
                            </button>
                            <div class="dropdown-menu modern-dropdown-menu w-100" id="modal-voice-menu">
                                <div class="dropdown-section">
                                    <div class="dropdown-section-title">Voices</div>
                                    <div id="modal-voice-options"></div>
                                </div>
                            </div>
                            <input type="hidden" id="modal-selected-voice-id">
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Speed</label>
                        <div class="speed-slider-container">
                            <div class="speed-slider-header">
                                <span class="speed-label">Speed: <span id="modal-speed-value">1.0</span>x</span>
                            </div>
                            <input type="range" id="modal-speed-slider" min="0.5" max="1.5" step="0.1" value="1.0" class="speed-slider">
                            <div class="speed-labels">
                                <span>0.5x</span>
                                <span>1.0x</span>
                                <span>1.5x</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Format</label>
                        <div class="modern-dropdown">
                            <button type="button" class="modern-dropdown-btn w-100" >
                                <span id="modal-selected-format-text">MP3</span>
                                <span class="dropdown-chevron">▼</span>
                            </button>
                            <div class="dropdown-menu modern-dropdown-menu w-100">
                                <div class="dropdown-section">
                                    <div class="dropdown-section-title">Format</div>
                                    <button class="dropdown-item modern-item" type="button" data-format="wav">
                                        <span class="item-text">WAV</span>
                                    </button>
                                    <button class="dropdown-item modern-item" type="button" data-format="mp3">
                                        <span class="item-text">MP3</span>
                                    </button>
                                    <button class="dropdown-item modern-item" type="button" data-format="ogg">
                                        <span class="item-text">OGG</span>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="modal-selected-format" value="mp3">
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bitrate</label>
                        <div class="modern-dropdown">
                            <button type="button" class="modern-dropdown-btn w-100" >
                                <span id="modal-selected-bitrate-text">192k</span>
                                <span class="dropdown-chevron">▼</span>
                            </button>
                            <div class="dropdown-menu modern-dropdown-menu w-100">
                                <div class="dropdown-section">
                                    <div class="dropdown-section-title">Bitrate</div>
                                    <button class="dropdown-item modern-item" type="button" data-bitrate="64">
                                        <span class="item-text">64k</span>
                                    </button>
                                    <button class="dropdown-item modern-item" type="button" data-bitrate="128">
                                        <span class="item-text">128k</span>
                                    </button>
                                    <button class="dropdown-item modern-item" type="button" data-bitrate="192">
                                        <span class="item-text">192k</span>
                                    </button>
                                    <button class="dropdown-item modern-item" type="button" data-bitrate="256">
                                        <span class="item-text">256k</span>
                                    </button>
                                    <button class="dropdown-item modern-item" type="button" data-bitrate="320">
                                        <span class="item-text">320k</span>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="modal-selected-bitrate" value="192">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-primary-action" id="save-settings-btn" style="width: auto;">
                    <i class="fa-regular fa-floppy-disk"></i>
                    Save Settings
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Audio Preview Modal -->
<div class="modal fade" id="audioPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-regular fa-music"></i>
                    Audio Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <audio id="previewAudio" controls class="w-100">
                    <source src="" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            </div>
            <div class="modal-footer">
                <a id="download-audio-btn" href="#" class="btn-primary-action" style="width: auto; text-decoration: none;">
                    <i class="fa-regular fa-download"></i>
                    Download Audio
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
    // Pass PHP data to JavaScript
    var voicesByLanguage = @json($voicesByLanguage);
    var languages = @json($languages->keyBy('code'));
    var defaultLanguageCode = '{{ $defaultLanguage->code ?? "english" }}';
    var defaultVoices = @json($defaultLanguageVoices);

    document.addEventListener('DOMContentLoaded', function() {
        // DOM Elements
        const projectSelect = document.getElementById('script-project-select');
        const loadProjectBtn = document.getElementById('load-project-btn');
        const loadingState = document.getElementById('loading-state');
        const voiceoverEmptyState = document.getElementById('voiceover-empty-state');
        const scenesTableBody = document.getElementById('scenes-table-body');
        const sfxTabBtn = document.getElementById('sfx');
        const sfxFormContainer = document.getElementById('sfx-form-container');
        const sfxResultsContainer = document.getElementById('sfx-results-container');
        const sfxEmptyState = document.getElementById('sfx-empty-state');
        const sfxGrid = document.getElementById('sfx-grid');
        const generateSfxBtn = document.getElementById('generate-sfx-btn');
        const sfxPrompt = document.getElementById('sfx-prompt');
        
        // Store current data
        let currentScriptProjectId = null;
        let currentSoundProjectId = null;
        let currentScenes = [];
        
        // Workflow tabs
        const workflowTabs = document.querySelectorAll('.workflow-tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
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
            loadingState.style.display = 'block';
            voiceoverEmptyState.style.display = 'none';
            scenesTableBody.style.display = 'none';
            
            // Hide SFX containers until project loads
            sfxFormContainer.style.display = 'none';
            sfxResultsContainer.style.display = 'none';
            sfxEmptyState.style.display = 'block';
            
            currentScriptProjectId = projectId;
            
            fetch(`/app/user/api/ai-director/sound/${projectId}/scenes`)
                .then(response => response.json())
                .then(data => {
                    loadingState.style.display = 'none';
                    
                    if (data.scenes && data.scenes.length > 0) {
                        currentSoundProjectId = data.sound_project_id;
                        currentScenes = data.scenes;
                        renderVoiceoverScenes(data.scenes);
                        scenesTableBody.style.display = 'block';
                        
                        // Enable SFX tab and show SFX form
                        sfxTabBtn.disabled = false;
                        sfxFormContainer.style.display = 'block';
                        sfxEmptyState.style.display = 'none';
                        
                        // Load SFX tracks
                        loadSFXTracks();
                    } else {
                        voiceoverEmptyState.style.display = 'block';
                        sfxEmptyState.style.display = 'block';
                        sfxFormContainer.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error loading project:', error);
                    loadingState.style.display = 'none';
                    voiceoverEmptyState.style.display = 'block';
                    sfxEmptyState.style.display = 'block';
                    sfxFormContainer.style.display = 'none';
                });
        }

        function renderVoiceoverScenes(scenes) {
            let html = '';
            
            scenes.forEach(scene => {
                const statusMap = { 0: 'idle', 1: 'processing', 2: 'success', 3: 'failed' };
                const status = statusMap[scene.voiceover_status] || 'idle';
                const isProcessing = status === 'processing';
                const settings = scene.voiceover_settings || {};
                
                html += `
                    <div class="scene-row" data-scene-id="${scene.id}" data-scene-number="${scene.scene_number}">
                        <div class="row-content">
                            <div class="col-prompt">
                                <div class="scene-header">
                                    <span class="scene-index">Scene ${scene.scene_number}</span>
                                    <button class="scene-btn settings-btn" data-scene-id="${scene.id}" data-settings='${JSON.stringify(settings)}'>
                                        <i class="fa-regular fa-sliders"></i>
                                        Voice Settings
                                    </button>
                                </div>
                                <textarea class="prompt-textarea voiceover-text" rows="4" placeholder="Voiceover script..." ${isProcessing ? 'disabled' : ''}>${escapeHtml(scene.voiceover_script || '')}</textarea>
                                <div class="action-buttons">
                                    <button class="scene-btn generate-voiceover" data-scene-id="${scene.id}" ${isProcessing ? 'disabled' : ''}>
                                        <i class="fa-regular fa-wand-magic"></i>
                                        ${status === 'success' ? 'Regenerate' : 'Generate'}
                                    </button>
                                </div>
                            </div>
                            <div class="col-output">
                                ${renderVoiceoverOutput(scene)}
                            </div>
                        </div>
                    </div>
                `;
            });
            
            scenesTableBody.innerHTML = html;
            attachVoiceoverEvents();
        }

        function renderVoiceoverOutput(scene) {
            const statusMap = { 0: 'idle', 1: 'processing', 2: 'success', 3: 'failed' };
            const status = statusMap[scene.voiceover_status] || 'idle';
            
            if (status === 'processing') {
                return `
                    <div class="output-state processing">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                        <span>Generating voiceover...</span>
                    </div>
                `;
            } else if (status === 'success' && scene.voiceover_url) {
                return `
                    <div class="output-state success">
                        <div class="audio-icon-block">
                            <i class="fa-solid fa-head-side-headphones"></i>
                            <span>Audio Ready</span>
                        </div>
                        <div class="output-actions">
                            <button class="output-btn play-audio" data-url="${scene.voiceover_url}">
                                <i class="fa-regular fa-circle-play"></i>
                            </button>
                            <a href="${scene.voiceover_url}" class="output-btn" download>
                                <i class="fa-regular fa-download"></i>
                            </a>
                            <button class="output-btn regenerate-voiceover" data-scene-id="${scene.id}">
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
                        <button class="output-btn regenerate-voiceover" data-scene-id="${scene.id}">
                            <i class="fa-regular fa-rotate"></i>
                            Retry
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

        function attachVoiceoverEvents() {
            // Generate voiceover
            document.querySelectorAll('.generate-voiceover').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sceneId = this.dataset.sceneId;
                    const row = this.closest('.scene-row');
                    const text = row.querySelector('.voiceover-text').value;
                    
                    if (!text) {
                        showError('Please enter voiceover text');
                        return;
                    }
                    
                    generateVoiceover(sceneId, text);
                });
            });
            
            // Regenerate voiceover
            document.querySelectorAll('.regenerate-voiceover').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sceneId = this.dataset.sceneId;
                    const row = document.querySelector(`.scene-row[data-scene-id="${sceneId}"]`);
                    const text = row.querySelector('.voiceover-text').value;
                    generateVoiceover(sceneId, text);
                });
            });
            
            // Play audio
            document.querySelectorAll('.play-audio').forEach(btn => {
                btn.addEventListener('click', function() {
                    const url = this.dataset.url;
                    const audioElement = document.getElementById('previewAudio');
                    audioElement.querySelector('source').src = url;
                    audioElement.load();
                    document.getElementById('download-audio-btn').href = url;
                    new bootstrap.Modal(document.getElementById('audioPreviewModal')).show();
                });
            });
            
            // Settings button
            document.querySelectorAll('.settings-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sceneId = this.dataset.sceneId;
                    const settings = JSON.parse(this.dataset.settings);
                    openVoiceSettingsModal(sceneId, settings);
                });
            });
        }

        function generateVoiceover(sceneId, text) {
            const row = document.querySelector(`.scene-row[data-scene-id="${sceneId}"]`);
            const outputDiv = row.querySelector('.col-output');
            
            row.classList.add('generating');
            outputDiv.innerHTML = `
                <div class="output-state processing">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                    <span>Generating voiceover...</span>
                </div>
            `;
            
            fetch('/app/user/api/ai-director/sound/generate-voiceover', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    scene_id: sceneId,
                    text: text,
                    voice_id: voiceSettingsCache[sceneId]?.voice_id || (defaultVoices[0]?.voice_id || ''),
                    language: voiceSettingsCache[sceneId]?.language || defaultLanguageCode,
                    emotion: voiceSettingsCache[sceneId]?.emotion || 'happy',
                    speed: voiceSettingsCache[sceneId]?.speed || 1.0,
                    format: voiceSettingsCache[sceneId]?.format || 'mp3',
                    bitrate: voiceSettingsCache[sceneId]?.bitrate || 192
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'processing') {
                    pollVoiceoverStatus(sceneId);
                } else {
                    showError('Failed to start generation');
                    row.classList.remove('generating');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Network error occurred');
                row.classList.remove('generating');
            });
        }

        function pollVoiceoverStatus(sceneId) {
            const pollInterval = setInterval(() => {
                fetch(`/app/user/api/ai-director/sound/voiceover-status/${sceneId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            clearInterval(pollInterval);
                            updateVoiceoverOutput(sceneId, data.url);
                        } else if (data.status === 'failed') {
                            clearInterval(pollInterval);
                            updateVoiceoverOutput(sceneId, null, true);
                        }
                    })
                    .catch(error => console.error('Polling error:', error));
            }, 5000);
        }

        function updateVoiceoverOutput(sceneId, url, isFailed = false) {
            const row = document.querySelector(`.scene-row[data-scene-id="${sceneId}"]`);
            if (!row) return;
            
            row.classList.remove('generating');
            const outputDiv = row.querySelector('.col-output');
            
            if (url && !isFailed) {
                outputDiv.innerHTML = `
                    <div class="output-state success">
                        <div class="audio-icon-block">
                            <i class="fa-solid fa-head-side-headphones"></i>
                            <span>Audio Ready</span>
                        </div>
                        <div class="output-actions">
                            <button class="output-btn play-audio" data-url="${url}">
                                <i class="fa-regular fa-circle-play"></i>
                            </button>
                            <a href="${url}" class="output-btn" download>
                                <i class="fa-regular fa-download"></i>
                            </a>
                            <button class="output-btn regenerate-voiceover" data-scene-id="${sceneId}">
                                <i class="fa-regular fa-rotate"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                // Reattach events
                outputDiv.querySelector('.play-audio')?.addEventListener('click', function() {
                    const url = this.dataset.url;
                    const audioElement = document.getElementById('previewAudio');
                    audioElement.querySelector('source').src = url;
                    audioElement.load();
                    document.getElementById('download-audio-btn').href = url;
                    new bootstrap.Modal(document.getElementById('audioPreviewModal')).show();
                });
                
                outputDiv.querySelector('.regenerate-voiceover')?.addEventListener('click', function() {
                    const text = row.querySelector('.voiceover-text').value;
                    generateVoiceover(sceneId, text);
                });
            } else {
                outputDiv.innerHTML = `
                    <div class="output-state failed">
                        <i class="fa-regular fa-circle-exclamation"></i>
                        <span>Generation failed</span>
                        <button class="output-btn regenerate-voiceover" data-scene-id="${sceneId}">
                            <i class="fa-regular fa-rotate"></i>
                            Retry
                        </button>
                    </div>
                `;
            }
        }

        
        // Voice settings cache
        let voiceSettingsCache = {};
        let currentSettingsSceneId = null;

        function openVoiceSettingsModal(sceneId, settings) {
            currentSettingsSceneId = sceneId;
            voiceSettingsCache[sceneId] = voiceSettingsCache[sceneId] || settings;
            
            // Populate modal with current settings
            document.getElementById('modal-selected-voice-id').value = voiceSettingsCache[sceneId].voice_id || '';
            document.getElementById('modal-selected-language-code').value = voiceSettingsCache[sceneId].language || defaultLanguageCode;
            document.getElementById('modal-speed-slider').value = voiceSettingsCache[sceneId].speed || 1.0;
            document.getElementById('modal-speed-value').textContent = voiceSettingsCache[sceneId].speed || 1.0;
            document.getElementById('modal-selected-format').value = voiceSettingsCache[sceneId].format || 'mp3';
            document.getElementById('modal-selected-bitrate').value = voiceSettingsCache[sceneId].bitrate || 192;
            
            // Update UI text
            const formatText = (voiceSettingsCache[sceneId].format || 'mp3').toUpperCase();
            document.getElementById('modal-selected-format-text').textContent = formatText;
            document.getElementById('modal-selected-bitrate-text').textContent = (voiceSettingsCache[sceneId].bitrate || 192) + 'k';
            
            // Load languages and voices
            loadLanguagesModal();
            
            // Set default language in dropdown button
            const defaultLang = languages[voiceSettingsCache[sceneId].language || defaultLanguageCode];
            if (defaultLang) {
                document.getElementById('modal-selected-language-name').textContent = defaultLang.name;
                document.getElementById('modal-selected-language-flag').src = defaultLang.flag_url;
            }
            
            // Setup dropdown toggles after modal is shown
            const modalEl = document.getElementById('voiceSettingsModal');
            
            // Remove any existing listeners to avoid duplicates
            modalEl.querySelectorAll('.modern-dropdown-btn').forEach(btn => {
                btn.removeEventListener('click', btn._dropdownHandler);
            });
            
            // Add new click handlers to all dropdown buttons
            modalEl.querySelectorAll('.modern-dropdown-btn').forEach(btn => {
                btn._dropdownHandler = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const parent = this.closest('.modern-dropdown');
                    const menu = parent.querySelector('.modern-dropdown-menu');
                    
                    // Close all other dropdowns
                    modalEl.querySelectorAll('.modern-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.remove('show');
                    });
                    
                    // Toggle current
                    menu.classList.toggle('show');
                };
                
                btn.addEventListener('click', btn._dropdownHandler);
            });
            
            // Close dropdowns when clicking outside
            const closeDropdowns = function(e) {
                if (!modalEl.contains(e.target)) {
                    modalEl.querySelectorAll('.modern-dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            };
            
            document.removeEventListener('click', closeDropdowns);
            document.addEventListener('click', closeDropdowns);
            
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        // After loading languages, add click handler to close dropdown
        function loadLanguagesModal() {
            const languageOptions = document.getElementById('modal-language-options');
            languageOptions.innerHTML = '';
            
            Object.values(languages).forEach(lang => {
                const option = document.createElement('a');
                option.href = '#';
                option.className = 'dropdown-item modern-item language-option-modal';
                option.setAttribute('data-language-code', lang.code);
                option.setAttribute('data-language-flag', lang.flag_url);
                option.setAttribute('data-language-name', lang.name);
                option.innerHTML = `
                    <img src="${lang.flag_url}" style="width: 20px; height: 20px;">
                    <span class="item-text">${lang.name}</span>
                `;
                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    const code = this.dataset.languageCode;
                    const name = this.dataset.languageName;
                    const flag = this.dataset.languageFlag;
                    
                    document.getElementById('modal-selected-language-code').value = code;
                    document.getElementById('modal-selected-language-name').textContent = name;
                    document.getElementById('modal-selected-language-flag').src = flag;
                    
                    updateVoicesModal(code);
                    
                    // Close the dropdown
                    const dropdownMenu = this.closest('.modern-dropdown-menu');
                    if (dropdownMenu) {
                        dropdownMenu.classList.remove('show');
                    }
                });
                languageOptions.appendChild(option);
            });
            
            // Set default language
            const currentLangCode = document.getElementById('modal-selected-language-code').value;
            const currentLang = languages[currentLangCode] || languages[defaultLanguageCode];
            if (currentLang) {
                document.getElementById('modal-selected-language-name').textContent = currentLang.name;
                document.getElementById('modal-selected-language-flag').src = currentLang.flag_url;
                updateVoicesModal(currentLang.code);
            }
        }

        function updateVoicesModal(languageCode) {
            const voices = voicesByLanguage[languageCode] || [];
            const voiceOptions = document.getElementById('modal-voice-options');
            voiceOptions.innerHTML = '';
            
            voices.forEach(voice => {
                const option = document.createElement('a');
                option.href = '#';
                option.className = 'dropdown-item modern-item voice-option-modal';
                option.setAttribute('data-voice-id', voice.voice_id);
                option.setAttribute('data-voice-img', voice.thumbnail_url);
                option.setAttribute('data-voice-name', voice.name);
                option.innerHTML = `
                    <img src="${voice.thumbnail_url}" style="width: 20px; height: 20px; border-radius: 3px;">
                    <span class="item-text">${voice.name}</span>
                `;
                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.dataset.voiceId;
                    const name = this.dataset.voiceName;
                    const img = this.dataset.voiceImg;
                    
                    document.getElementById('modal-selected-voice-id').value = id;
                    document.getElementById('modal-selected-voice-name').textContent = name;
                    document.getElementById('modal-selected-voice-img').src = img;
                    
                    // Close the dropdown
                    const dropdownMenu = this.closest('.modern-dropdown-menu');
                    if (dropdownMenu) {
                        dropdownMenu.classList.remove('show');
                    }
                });
                voiceOptions.appendChild(option);
            });
            
            // Set default voice if exists
            if (voices.length > 0) {
                const currentVoiceId = document.getElementById('modal-selected-voice-id').value;
                let selectedVoice = voices.find(v => v.voice_id == currentVoiceId);
                
                if (!selectedVoice) {
                    selectedVoice = voices[0];
                }
                
                document.getElementById('modal-selected-voice-id').value = selectedVoice.voice_id;
                document.getElementById('modal-selected-voice-name').textContent = selectedVoice.name;
                document.getElementById('modal-selected-voice-img').src = selectedVoice.thumbnail_url;
            } else {
                document.getElementById('modal-selected-voice-id').value = '';
                document.getElementById('modal-selected-voice-name').textContent = 'No voices available';
                document.getElementById('modal-selected-voice-img').src = '';
            }
        }

        // Save settings button
        document.getElementById('save-settings-btn').addEventListener('click', function() {
            if (currentSettingsSceneId) {
                voiceSettingsCache[currentSettingsSceneId] = {
                    voice_id: document.getElementById('modal-selected-voice-id').value,
                    language: document.getElementById('modal-selected-language-code').value,
                    speed: parseFloat(document.getElementById('modal-speed-slider').value),
                    format: document.getElementById('modal-selected-format').value,
                    bitrate: parseInt(document.getElementById('modal-selected-bitrate').value)
                };
                
                // Update settings button data
                const settingsBtn = document.querySelector(`.scene-row[data-scene-id="${currentSettingsSceneId}"] .settings-btn`);
                if (settingsBtn) {
                    settingsBtn.dataset.settings = JSON.stringify(voiceSettingsCache[currentSettingsSceneId]);
                }
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Settings Saved',
                    text: 'Voice settings updated for this scene',
                    timer: 1500,
                    showConfirmButton: false,
                    background: 'rgba(18, 25, 40, 0.95)',
                    color: 'white'
                });
            }
            
            bootstrap.Modal.getInstance(document.getElementById('voiceSettingsModal')).hide();
        });

        // Modal speed slider
        const modalSpeedSlider = document.getElementById('modal-speed-slider');
        if (modalSpeedSlider) {
            modalSpeedSlider.addEventListener('input', function() {
                document.getElementById('modal-speed-value').textContent = this.value;
            });
        }

        // Also add for format and bitrate options
        document.querySelectorAll('#voiceSettingsModal [data-format]').forEach(btn => {
            btn.addEventListener('click', function() {
                const format = this.dataset.format;
                document.getElementById('modal-selected-format').value = format;
                document.getElementById('modal-selected-format-text').textContent = format.toUpperCase();
                
                // Close the dropdown
                const dropdownMenu = this.closest('.modern-dropdown-menu');
                if (dropdownMenu) {
                    dropdownMenu.classList.remove('show');
                }
            });
        });

        document.querySelectorAll('#voiceSettingsModal [data-bitrate]').forEach(btn => {
            btn.addEventListener('click', function() {
                const bitrate = this.dataset.bitrate;
                document.getElementById('modal-selected-bitrate').value = bitrate;
                document.getElementById('modal-selected-bitrate-text').textContent = bitrate + 'k';
                
                // Close the dropdown
                const dropdownMenu = this.closest('.modern-dropdown-menu');
                if (dropdownMenu) {
                    dropdownMenu.classList.remove('show');
                }
            });
        });

        // SFX Functions
        function loadSFXTracks() {
            fetch(`/app/user/api/ai-director/sound/${currentScriptProjectId}/sfx-tracks`)
                .then(response => response.json())
                .then(data => {
                    if (data.sfx_tracks && data.sfx_tracks.length > 0) {
                        renderSFXGrid(data.sfx_tracks);
                        sfxResultsContainer.style.display = 'block';
                        sfxEmptyState.style.display = 'none';
                    } else {
                        sfxResultsContainer.style.display = 'none';
                        // Don't hide empty state if there are no SFX but project is loaded
                        if (sfxFormContainer.style.display === 'block') {
                            sfxEmptyState.innerHTML = `
                                <i class="fa-regular fa-waveform"></i>
                                <h4>No Sound Effects Yet</h4>
                                <p>Generate your first sound effect using the form above</p>
                            `;
                            sfxEmptyState.style.display = 'block';
                        }
                    }
                })
                .catch(error => console.error('Error loading SFX:', error));
        }

        function renderSFXGrid(sfxTracks) {
            sfxGrid.innerHTML = '';
            
            sfxTracks.forEach(sfx => {
                const statusMap = { 0: 'idle', 1: 'processing', 2: 'success', 3: 'failed' };
                const status = statusMap[sfx.status] || 'idle';
                
                const sfxHtml = `
                    <div class="sfx-card" data-sfx-id="${sfx.id}">
                        <div class="sfx-prompt">${escapeHtml(sfx.prompt)}</div>
                        <div class="sfx-meta">
                            <span class="sfx-date"><i class="fa-regular fa-calendar"></i> ${sfx.created_at}</span>
                            <span class="sfx-status status-${status}">${status}</span>
                        </div>
                        <div class="sfx-actions">
                            ${status === 'success' && sfx.url ? `
                                <button class="sfx-play" data-url="${sfx.url}">
                                    <i class="fa-regular fa-circle-play"></i> Play
                                </button>
                                <a href="${sfx.url}" class="sfx-download" download>
                                    <i class="fa-regular fa-download"></i> Download
                                </a>
                            ` : ''}
                            ${status === 'processing' ? `
                                <span class="sfx-processing"><i class="fa-solid fa-circle-notch fa-spin"></i> Processing...</span>
                            ` : ''}
                            ${status === 'failed' ? `
                                <button class="sfx-retry" data-prompt="${escapeHtml(sfx.prompt)}">
                                    <i class="fa-regular fa-rotate"></i> Retry
                                </button>
                            ` : ''}
                            <button class="sfx-delete" data-id="${sfx.id}">
                                <i class="fa-regular fa-trash-can"></i> Delete
                            </button>
                        </div>
                    </div>
                `;
                sfxGrid.innerHTML += sfxHtml;
            });
            
            // Attach SFX events
            document.querySelectorAll('.sfx-play').forEach(btn => {
                btn.addEventListener('click', function() {
                    const url = this.dataset.url;
                    const audioElement = document.getElementById('previewAudio');
                    audioElement.querySelector('source').src = url;
                    audioElement.load();
                    document.getElementById('download-audio-btn').href = url;
                    new bootstrap.Modal(document.getElementById('audioPreviewModal')).show();
                });
            });
            
            document.querySelectorAll('.sfx-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sfxId = this.dataset.id;
                    deleteSFX(sfxId);
                });
            });
            
            document.querySelectorAll('.sfx-retry').forEach(btn => {
                btn.addEventListener('click', function() {
                    const prompt = this.dataset.prompt;
                    generateSFX(prompt);
                });
            });
        }

        function generateSFX(prompt) {
            if (!prompt) {
                prompt = sfxPrompt.value;
            }
            
            if (!prompt) {
                showError('Please enter a sound effect description');
                return;
            }
            
            generateSfxBtn.disabled = true;
            generateSfxBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
            
            fetch('/app/user/api/ai-director/sound/generate-sfx', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    prompt: prompt,
                    script_project_id: currentScriptProjectId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'processing') {
                    // Clear prompt and refresh SFX list
                    sfxPrompt.value = '';
                    updateSfxCount();
                    loadSFXTracks();
                    
                    // Start polling for new SFX
                    pollSFXStatus(data.sfx_id);
                } else {
                    showError('Failed to start generation');
                }
                generateSfxBtn.disabled = false;
                generateSfxBtn.innerHTML = '<i class="fa-regular fa-wand-magic"></i> Generate Sound Effect';
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Network error occurred');
                generateSfxBtn.disabled = false;
                generateSfxBtn.innerHTML = '<i class="fa-regular fa-wand-magic"></i> Generate Sound Effect';
            });
        }

        function pollSFXStatus(sfxId) {
            const pollInterval = setInterval(() => {
                fetch(`/app/user/api/ai-director/sound/sfx-status/${sfxId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success' || data.status === 'failed') {
                            clearInterval(pollInterval);
                            loadSFXTracks();
                        }
                    })
                    .catch(error => console.error('Polling error:', error));
            }, 5000);
        }

        function deleteSFX(sfxId) {
            Swal.fire({
                title: 'Delete Sound Effect?',
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
                    fetch(`/app/user/api/ai-director/sound/sfx/${sfxId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadSFXTracks();
                        }
                    });
                }
            });
        }

        // SFX Character counter
        function updateSfxCount() {
            const text = sfxPrompt.value;
            document.getElementById('sfx-count').textContent = text.length;
        }
        
        sfxPrompt.addEventListener('input', updateSfxCount);
        updateSfxCount();
        
        // Generate SFX button click
        generateSfxBtn.addEventListener('click', function() {
            generateSFX();
        });
        
        // Helper functions
        function escapeHtml(unsafe) {
            if (!unsafe) return '';
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
        
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonColor: '#0783CF',
                background: 'rgba(18, 25, 40, 0.95)',
                color: 'white'
            });
        }
        
        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: message,
                timer: 1500,
                showConfirmButton: false,
                background: 'rgba(18, 25, 40, 0.95)',
                color: 'white'
            });
        }
    });
</script>
@endsection