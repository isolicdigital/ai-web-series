@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ theme_url('custom/standup.css') }}">
@endsection

@section('content')

<div class="standup-templates">

    <!-- Header Section -->
    <div class="templates-header">
        <div class="header-badge">
            <i class="fas fa-mask"></i> Choose Your AI Comedian
        </div>
        <h1>Select Your Comedy Persona</h1>
        <p class="header-subtitle">Pick from our library of AI comedians or create your own custom persona</p>
    </div>

    <!-- Templates Grid - Featured Comedians -->
    <div class="templates-section">
        <div class="section-title-wrapper">
            <h2>Featured Comedians</h2>
            <p>Professionally designed AI personas ready to perform</p>
        </div>
        
        <div class="templates-grid">
            @foreach($templates as $template)
            <div class="template-card">
                <div class="template-card-image">
                    <img src="{{ asset($template->preview_image ?? $template->init_image) }}" alt="{{ $template->name }}">
                    <div class="template-overlay">
                        <button class="template-use-btn use-template" data-template-id="{{ $template->id }}" data-template-name="{{ $template->name }}">
                            <i class="fas fa-microphone-alt"></i> Use This Comedian
                        </button>
                    </div>
                </div>
                <div class="template-card-content">
                    <h3 class="template-name">{{ $template->name }}</h3>
                    <p class="template-description">{{ $template->description }}</p>
                    <div class="template-meta">
                        <span class="template-badge">
                            <i class="fas fa-star"></i> Featured
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Custom Comedians Section -->
    @if($customComedians->count() > 0)
    <div class="custom-section">
        <div class="section-title-wrapper">
            <h2>Your Custom Comedians</h2>
            <p>Personalities created just for you</p>
        </div>
        
        <div class="comedians-grid">
            @foreach($customComedians as $comedian)
            <div class="comedian-card">
                <div class="comedian-card-image">
                    <img src="{{ asset($comedian->final_image) }}" alt="{{ $comedian->name }}">
                    <div class="comedian-overlay">
                        <button class="comedian-select-btn select-comedian" data-comedian-id="{{ $comedian->id }}">
                            <i class="fas fa-check-circle"></i> Select
                        </button>
                    </div>
                </div>
                <div class="comedian-card-content">
                    <h4 class="comedian-name">{{ $comedian->name }}</h4>
                    <span class="comedian-type custom">
                        <i class="fas fa-user-astronaut"></i> Custom
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- System Comedians Section -->
    <div class="system-section">
        <div class="section-title-wrapper">
            <h2>System Comedians</h2>
            <p>Ready-to-use AI personalities</p>
        </div>
        
        <div class="comedians-grid">
            @foreach($systemComedians as $comedian)
            <div class="comedian-card">
                <div class="comedian-card-image">
                    <img src="{{ asset($comedian->final_image) }}" alt="{{ $comedian->name }}">
                    <div class="comedian-overlay">
                        <button class="comedian-select-btn select-comedian" data-comedian-id="{{ $comedian->id }}">
                            <i class="fas fa-check-circle"></i> Select
                        </button>
                    </div>
                </div>
                <div class="comedian-card-content">
                    <h4 class="comedian-name">{{ $comedian->name }}</h4>
                    <span class="comedian-type system">
                        <i class="fas fa-robot"></i> System
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>

<!-- Face Upload Modal -->
<div class="modal fade" id="faceUploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-astronaut"></i>
                    Create Your Custom Comedian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="modal-info">
                    <i class="fas fa-info-circle"></i>
                    <p>Upload a clear front-facing photo to create your personalized AI comedian</p>
                </div>
                
                <form id="faceUploadForm" enctype="multipart/form-data">
                    <input type="hidden" id="selectedTemplateId">
                    
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="upload-text">Click or drag photo here</div>
                        <div class="upload-subtext">Supports JPG, PNG (Max 10MB)</div>
                        <input type="file" name="target_image" class="file-input" accept="image/*" required>
                    </div>
                    
                    <div class="preview-container d-none" id="imagePreview">
                        <img id="previewImage" src="" alt="Preview">
                        <button type="button" class="remove-preview" id="removePreview">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div id="uploadProgress" class="upload-progress d-none">
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                        <p class="progress-text">Creating your comedian...</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-modal" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn-primary-modal" id="submitFaceUpload">
                    <i class="fas fa-magic"></i> Create Comedian
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Template selection
    document.querySelectorAll('.use-template').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('selectedTemplateId').value = btn.dataset.templateId;
            const modal = new bootstrap.Modal(document.getElementById('faceUploadModal'));
            modal.show();
        });
    });
    
    // File upload preview
    const fileInput = document.querySelector('.file-input');
    const uploadArea = document.getElementById('uploadArea');
    const previewContainer = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    const removePreview = document.getElementById('removePreview');
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                previewImage.src = event.target.result;
                uploadArea.classList.add('d-none');
                previewContainer.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
    });
    
    removePreview.addEventListener('click', function() {
        fileInput.value = '';
        uploadArea.classList.remove('d-none');
        previewContainer.classList.add('d-none');
    });
    
    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            fileInput.files = e.dataTransfer.files;
            const event = new Event('change');
            fileInput.dispatchEvent(event);
        }
    });
    
    // Submit form
    document.getElementById('submitFaceUpload').addEventListener('click', async () => {
        const form = document.getElementById('faceUploadForm');
        const fileInput = form.querySelector('input[type="file"]');
        
        if (!fileInput.files[0]) {
            alert('Please upload a photo');
            return;
        }
        
        const formData = new FormData(form);
        formData.append('template_id', document.getElementById('selectedTemplateId').value);
        
        const progressBar = document.getElementById('uploadProgress');
        progressBar.classList.remove('d-none');
        
        try {
            const response = await fetch('/standup/comedian/create', {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
                body: formData
            });
            
            const data = await response.json();
            if (data.track_id) {
                pollComedianStatus(data.track_id);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Something went wrong');
            progressBar.classList.add('d-none');
        }
    });
});

function pollComedianStatus(trackId) {
    const interval = setInterval(async () => {
        try {
            const response = await fetch(`/standup/comedian/status/${trackId}`);
            const data = await response.json();
            
            if (data.status === 'completed') {
                clearInterval(interval);
                location.reload();
            } else if (data.status === 'failed') {
                clearInterval(interval);
                alert('Comedian creation failed. Please try again.');
                location.reload();
            }
        } catch (error) {
            console.error('Polling error:', error);
            clearInterval(interval);
        }
    }, 3000);
}
</script>
@endsection