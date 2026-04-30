@extends('layouts.app')

@section('title', 'Image to Video Generator')

@section('styles')
<style>
.tool-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.tool-grid {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 2rem;
    align-items: start;
}

/* Form Panel */
.form-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 28px;
    padding: 1.75rem;
    position: sticky;
    top: 2rem;
}

.form-header {
    text-align: center;
    margin-bottom: 1.75rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--border);
}

.form-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.form-icon i {
    font-size: 1.75rem;
    color: white;
}

.form-header h2 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.form-header p {
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.form-input, .form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 14px;
    color: var(--text-primary);
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.form-input:focus, .form-select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(232, 93, 40, 0.1);
}

.form-textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 14px;
    color: var(--text-primary);
    font-size: 0.85rem;
    resize: vertical;
    font-family: inherit;
    min-height: 100px;
}

.char-counter {
    text-align: right;
    font-size: 0.7rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

/* Settings Row */
.settings-group {
    margin: 1.5rem 0;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.setting-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 14px;
}

.setting-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.setting-left i {
    color: var(--primary);
    width: 20px;
}

.setting-left span {
    color: var(--text-primary);
    font-size: 0.85rem;
}

.setting-right {
    color: var(--text-primary);
    font-size: 0.85rem;
}

/* Slider */
.slider-group {
    margin: 1rem 0;
}

.slider-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.slider-label {
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.slider-value {
    font-size: 0.8rem;
    color: var(--primary);
    font-weight: 500;
}

.slider {
    width: 100%;
    height: 4px;
    -webkit-appearance: none;
    background: var(--border);
    border-radius: 2px;
}

.slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 16px;
    height: 16px;
    background: var(--primary);
    border-radius: 50%;
    cursor: pointer;
}

.generate-btn {
    width: 100%;
    padding: 0.875rem;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border: none;
    border-radius: 100px;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1rem;
}

.generate-btn:hover {
    transform: translateY(-2px);
    opacity: 0.9;
}

.generate-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Output Panel */
.output-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 28px;
    overflow: hidden;
}

.output-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.output-title {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-weight: 600;
    color: var(--text-primary);
}

.output-title i {
    color: var(--primary);
}

.output-actions {
    display: flex;
    gap: 0.5rem;
}

.output-action {
    background: transparent;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 0.4rem 0.8rem;
    color: var(--text-secondary);
    font-size: 0.7rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.output-action:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.output-content {
    padding: 1.75rem;
    min-height: 500px;
    max-height: 600px;
    overflow-y: auto;
    display: flex;
    align-items: center;
    justify-content: center;
}

.video-preview {
    width: 100%;
    border-radius: 16px;
    overflow: hidden;
}

.video-preview video {
    width: 100%;
    border-radius: 16px;
}

.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    color: var(--text-secondary);
}

.loading-state i {
    font-size: 2.5rem;
    color: var(--primary);
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    color: var(--text-secondary);
}

.empty-state i {
    font-size: 2.5rem;
    color: var(--text-muted);
}

.error-message {
    background: rgba(239, 68, 68, 0.08);
    border-radius: 14px;
    padding: 0.75rem;
    color: #ef4444;
    font-size: 0.8rem;
    display: none;
    margin-bottom: 1rem;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    text-decoration: none;
    margin-bottom: 1.5rem;
}

.back-link:hover {
    color: var(--primary);
}

@media (max-width: 1024px) {
    .tool-grid {
        grid-template-columns: 1fr;
    }
    .form-panel {
        position: static;
    }
}
</style>
@endsection

@section('content')
<div class="tool-container">
    <a href="{{ route('contents.index') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to tools
    </a>

    <div class="tool-grid">
        <!-- Left: Form Panel -->
        <div class="form-panel">
            <div class="form-header">
                <div class="form-icon">
                    <i class="fas fa-video"></i>
                </div>
                <h2>Image to Video</h2>
                <p>Transform your images into stunning AI-generated videos</p>
            </div>

            <form id="videoForm">
                @csrf

                <div class="form-group">
                    <label class="form-label">Project Name</label>
                    <input type="text" name="title" class="form-input" placeholder="Enter project name" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Image URL</label>
                    <input type="url" name="image_url" class="form-input" placeholder="https://example.com/image.jpg" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Prompt</label>
                    <textarea name="prompt" class="form-textarea" placeholder="Describe how the image should animate..." required></textarea>
                    <div class="char-counter"><span id="charCount">0</span>/500</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Resolution</label>
                    <select name="resolution" class="form-select">
                        <option value="480p">480p</option>
                        <option value="720p">720p</option>
                        <option value="1080p">1080p</option>
                    </select>
                </div>

                <div class="slider-group">
                    <div class="slider-header">
                        <span class="slider-label">Duration (seconds)</span>
                        <span class="slider-value" id="durationValue">5</span>
                    </div>
                    <input type="range" name="duration" class="slider" min="3" max="10" value="5" id="durationSlider">
                </div>

                <div class="slider-group">
                    <div class="slider-header">
                        <span class="slider-label">Frames Per Second</span>
                        <span class="slider-value" id="fpsValue">16</span>
                    </div>
                    <input type="range" name="fps" class="slider" min="12" max="24" value="16" id="fpsSlider">
                </div>

                <div class="settings-group">
                    <div class="setting-row">
                        <div class="setting-left">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Fast Generation</span>
                        </div>
                        <div class="setting-right">
                            <input type="checkbox" name="go_fast" value="true" checked>
                        </div>
                    </div>
                </div>

                <div id="formError" class="error-message"></div>

                <button type="submit" class="generate-btn" id="generateBtn">
                    <i class="fas fa-magic"></i> Generate Video
                </button>
            </form>
        </div>

        <!-- Right: Output Panel -->
        <div class="output-panel">
            <div class="output-header">
                <div class="output-title">
                    <i class="fas fa-film"></i>
                    <span>Generated Video</span>
                </div>
                <div class="output-actions">
                    <button class="output-action" id="downloadBtn" style="display: none;">
                        <i class="fas fa-download"></i> Download
                    </button>
                    <button class="output-action" id="clearBtn">
                        <i class="fas fa-trash-alt"></i> Clear
                    </button>
                </div>
            </div>
            <div class="output-content" id="outputContent">
                <div id="loadingState" class="loading-state" style="display: none;">
                    <i class="fas fa-circle-notch"></i>
                    <span>Generating your video... This may take a few minutes.</span>
                </div>
                <div id="videoPreview" class="video-preview" style="display: none;">
                    <video id="videoPlayer" controls autoplay loop></video>
                </div>
                <div id="emptyState" class="empty-state">
                    <i class="fas fa-video"></i>
                    <span>Your generated video will appear here</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('videoForm');
    const generateBtn = document.getElementById('generateBtn');
    const promptTextarea = document.querySelector('textarea[name="prompt"]');
    const charCountSpan = document.getElementById('charCount');
    const durationSlider = document.getElementById('durationSlider');
    const durationValue = document.getElementById('durationValue');
    const fpsSlider = document.getElementById('fpsSlider');
    const fpsValue = document.getElementById('fpsValue');
    const loadingState = document.getElementById('loadingState');
    const videoPreview = document.getElementById('videoPreview');
    const videoPlayer = document.getElementById('videoPlayer');
    const emptyState = document.getElementById('emptyState');
    const downloadBtn = document.getElementById('downloadBtn');
    
    // Character counter
    promptTextarea.addEventListener('input', function() {
        charCountSpan.textContent = this.value.length;
    });
    
    // Sliders
    durationSlider.addEventListener('input', function() {
        durationValue.textContent = this.value;
    });
    
    fpsSlider.addEventListener('input', function() {
        fpsValue.textContent = this.value;
    });
    
    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const title = document.querySelector('input[name="title"]').value.trim();
        const imageUrl = document.querySelector('input[name="image_url"]').value.trim();
        const prompt = promptTextarea.value.trim();
        
        if (!title || !imageUrl || !prompt) {
            showError('Please fill in all required fields.');
            return;
        }
        
        generateBtn.disabled = true;
        loadingState.style.display = 'flex';
        emptyState.style.display = 'none';
        videoPreview.style.display = 'none';
        downloadBtn.style.display = 'none';
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch('{{ route("contents.image-to-video") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(Object.fromEntries(formData))
            });
            
            const data = await response.json();
            
            loadingState.style.display = 'none';
            
            if (data.success) {
                videoPlayer.src = data.video_url;
                videoPreview.style.display = 'block';
                downloadBtn.style.display = 'inline-flex';
                downloadBtn.onclick = () => {
                    const a = document.createElement('a');
                    a.href = data.video_url;
                    a.download = `${title}.mp4`;
                    a.click();
                };
            } else {
                showError(data.message || 'Generation failed.');
                emptyState.style.display = 'flex';
            }
        } catch (error) {
            loadingState.style.display = 'none';
            showError('An error occurred. Please try again.');
            emptyState.style.display = 'flex';
        } finally {
            generateBtn.disabled = false;
        }
    });
    
    // Clear button
    document.getElementById('clearBtn').addEventListener('click', () => {
        videoPlayer.src = '';
        videoPreview.style.display = 'none';
        emptyState.style.display = 'flex';
        downloadBtn.style.display = 'none';
    });
    
    function showError(message) {
        const errorDiv = document.getElementById('formError');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        setTimeout(() => {
            errorDiv.style.display = 'none';
        }, 5000);
    }
});
</script>
@endsection