@extends('layouts.app')

@section('css')
<link href="{{ URL::asset('plugins/sweetalert/sweetalert2.min.css') }}" rel="stylesheet" />
<link href="{{ theme_url('custom/aiml.css') }}" rel="stylesheet" />
@endsection

@section('content')
<div class="section-header mt-7">
    <div class="section-icon">
        <i class="fas fa-copy"></i>
    </div>
    <h1 class="section-title">Clone Any Website Instantly</h1>
    <p class="section-subtitle">
        Transform any webpage into your own customizable template. Enter the details below to get started.
    </p>
</div>

<div class="full-width-section">
    <form id="clonerForm" method="POST" action="{{ route('page-builder.clone', ['id' => 'TEMP_ID']) }}">
        @csrf
        <div class="form-grid form-grid-3 mb-0">
            <div class="form-group">
                <label for="pageTitle" class="form-label">Page Name</label>
                <input type="text" id="pageTitle" name="title" class="form-control" 
                       placeholder="Enter a name for your cloned page" required>
            </div>
            
            <div class="form-group">
                <label for="pageUrl" class="form-label">Source URL</label>
                <input type="url" id="pageUrl" name="url" class="form-control" 
                       placeholder="https://example.com" required>
            </div>
            
            <div class="form-group">
                <label for="aiModel" class="form-label">AI Model</label>
                <select id="aiModel" name="ai_model" class="form-select" required>
                    <option value="">Select AI Model...</option>
                    <optgroup label="OpenAI">
                        <option value="gpt-5">GPT-5</option>
                        <option value="gpt-4.5">GPT-4.5</option>
                        <option value="gpt-4-turbo" selected>GPT-4 Turbo</option>
                    </optgroup>
                    <optgroup label="DeepSeek">
                        <option value="deepseek-v3">DeepSeek V3</option>
                        <option value="deepseek-r1">DeepSeek R1</option>
                    </optgroup>
                    <optgroup label="Anthropic">
                        <option value="claude-4">Claude 4</option>
                        <option value="claude-opus">Claude Opus</option>
                    </optgroup>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary-action" id="cloneButton">
                <i class="fas fa-copy me-2"></i>Start Cloning
            </button>
            
            <div class="checkbox-group">
                <input type="checkbox" class="form-check-input" id="confirmationCheckbox" required>
                <label class="form-check-label" for="confirmationCheckbox">
                    I confirm that I own or have legal rights to clone and use this webpage
                </label>
            </div>
        </div>
    </form>
</div>

<!-- Saved Pages Section -->
<div class="content-section">
    <div class="section-header-compact">
        <h2 class="section-title-compact">
            <i class="fas fa-history"></i>
            Your Cloned Pages
        </h2>
    </div>

    @if($saves->count())
        <div class="content-grid">
            @foreach($saves as $save)
                @php
                    $thumbnailPath = "/pages/{$save->slug}/thumb.png";
                    $hasThumbnail = file_exists(public_path($thumbnailPath));
                @endphp
                
                <div class="content-card">
                    <div class="card-preview">
                        @if($hasThumbnail)
                            <img src="{{ $thumbnailPath }}" alt="{{ $save->title }}" 
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="placeholder" style="display: none;">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        @else
                            <div class="placeholder">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        @endif
                        
                        <div class="card-preview-overlay">
                            <div class="quick-actions">
                                <a href="{{ route('page-builder.show', [
                                    'id' => $save->slug,
                                    'title' => base64_encode($save->title)
                                ]) }}" class="quick-btn" title="Edit" target="_blank">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ url('/p/?v='.base64_encode($save->slug)) }}" class="quick-btn" title="View" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-content">
                        <div class="card-header">
                            <h3 class="card-title" title="{{ $save->title }}">
                                {{ Str::limit($save->title, 50) }}
                            </h3>
                        </div>
                        
                        <div class="card-meta">
                            <div class="card-date">
                                <i class="fas fa-calendar"></i>
                                {{ $save->created_at->format('d M, Y h:i A') }}
                            </div>
                            
                            <div class="card-actions">
                                <a href="{{ route('page-builder.show', [
                                    'id' => $save->slug,
                                    'title' => base64_encode($save->title)
                                ]) }}" class="card-btn btn-edit" target="_blank" title="Edit">
                                    <i class="fas fa-edit"></i>
                                    <span>Edit</span>
                                </a>
                                
                                <a href="{{ url('/p/?v='.base64_encode($save->slug)) }}" class="card-btn btn-view" target="_blank" title="View">
                                    <i class="fas fa-eye"></i>
                                    <span>View</span>
                                </a>
                                
                                <a href="{{ route('page-builder.download', $save->id) }}" class="card-btn btn-download" title="Download">
                                    <i class="fas fa-download"></i>
                                    <span>Zip</span>
                                </a>
                                
                                <form action="{{ route('page-builder.delete', $save->id) }}" method="POST" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="card-btn btn-delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="no-content">
            <i class="fas fa-folder-open"></i>
            <p>No cloned pages found yet.<br>Start by cloning your first website above!</p>
        </div>
    @endif
</div>
@endsection

@section('js')
<script src="{{ URL::asset('plugins/sweetalert/sweetalert2.all.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const clonerForm = document.getElementById('clonerForm');
        const cloneButton = document.getElementById('cloneButton');
        const originalButtonText = cloneButton.innerHTML;

        // Form submission handler
        clonerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const title = document.getElementById('pageTitle').value.trim();
            const url = document.getElementById('pageUrl').value.trim();
            const aiModel = document.getElementById('aiModel').value;
            const confirmed = document.getElementById('confirmationCheckbox').checked;

            // Validation
            if (!title || !url || !aiModel) {
                Swal.fire('Error', 'Please fill in all required fields', 'error');
                return;
            }

            try {
                new URL(url);
            } catch {
                Swal.fire('Error', 'Please enter a valid URL', 'error');
                return;
            }

            if (!confirmed) {
                Swal.fire('Error', 'You must confirm that you have rights to clone this page', 'error');
                return;
            }

            // Generate unique ID and prepare request
            const id = new Date().toISOString().replace(/[-:.TZ]/g, '');
            const clonerUrl = `{{ route('page-builder.clone', ['id' => '__ID__']) }}`.replace('__ID__', id);

            // Show loading state
            cloneButton.disabled = true;
            cloneButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Cloning...';

            Swal.fire({
                title: 'Cloning Page',
                text: `Using ${aiModel} to process your request...`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    
                    // Make AJAX request
                    fetch(clonerUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({ 
                            url: url,
                            ai_model: aiModel 
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();
                        if (data.success) {
                            const finalUrl = `{{ route('page-builder.show', ['id' => '__ID__', 'title' => '__TITLE__']) }}`
                                .replace('__ID__', id)
                                .replace('__TITLE__', btoa(title)) + '?url=' + btoa(url) + '&model=' + aiModel;
                            window.location.href = finalUrl;
                        } else {
                            throw new Error(data.message || 'Cloning failed');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', error.message || 'Cloning failed. Please try again.', 'error');
                        cloneButton.disabled = false;
                        cloneButton.innerHTML = originalButtonText;
                    });
                }
            });
        });

        // Delete confirmation for saved pages
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const pageTitle = this.closest('.content-card').querySelector('.card-title').textContent.trim();

                Swal.fire({
                    title: 'Delete Page?',
                    html: `Are you sure you want to delete <strong>"${pageTitle}"</strong>?<br>This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    background: '#1e293b',
                    color: '#f1f5f9',
                    iconColor: '#ef4444'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // Add real-time validation
        const inputs = document.querySelectorAll('#pageTitle, #pageUrl, #aiModel');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                }
            });
        });

        // Change event for select
        document.getElementById('aiModel').addEventListener('change', function() {
            if (this.value) {
                this.classList.remove('is-invalid');
            }
        });
    });
</script>
@endsection