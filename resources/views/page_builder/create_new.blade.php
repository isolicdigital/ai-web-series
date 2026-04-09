@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('custom/css/dfy.css') }}">
<style>
    /* DFY Templates Page Styles */
    .dfy-templates-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem;
    }

    /* Category Selector - matches aspect ratio selector style */
    .dfy-category-selector {
        margin-bottom: 2rem;
    }

    .dfy-category-selector label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 0.875rem;
    }

    .form-select-custom {
        width: 100%;
        max-width: 300px;
        padding: 0.75rem 1rem;
        background: var(--surface);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        color: var(--text-main);
        font-size: 0.875rem;
        cursor: pointer;
        transition: var(--transition);
    }

    .form-select-custom:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 2px var(--accent-glow);
    }

    /* Templates Grid - matches results-grid */
    .template-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-top: 1rem;
    }

    /* Template Card - matches result-card */
    .template-card {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        background: var(--card-bg);
        border: 1px solid var(--glass-border);
        transition: var(--transition);
        cursor: pointer;
        aspect-ratio: 4 / 3;
    }

    .template-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow);
        border-color: var(--accent);
    }

    .template-thumbnail {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        transition: var(--transition);
    }

    .template-card:hover .template-thumbnail {
        transform: scale(1.05);
    }

    /* Template Overlay - matches result-overlay */
    .template-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.9));
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        opacity: 0;
        transition: var(--transition);
        z-index: 5;
        padding: 1rem;
        text-align: center;
    }

    .template-card:hover .template-overlay {
        opacity: 1;
    }

    .template-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 0.5rem;
        word-break: break-word;
    }

    .template-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    .template-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid var(--glass-border);
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--text-main);
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
    }

    .template-btn.primary {
        background: var(--accent);
        border-color: var(--accent);
    }

    .template-btn.primary:hover {
        background: var(--accent-dark);
        transform: translateY(-2px);
    }

    .template-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
        text-decoration: none;
        color: white;
    }

    /* SweetAlert Dark Theme */
    .swal2-popup {
        background: var(--card-bg) !important;
        border: 1px solid var(--glass-border) !important;
        border-radius: 20px !important;
    }

    .swal2-title {
        color: var(--text-main) !important;
    }

    .swal2-html-container {
        color: var(--text-muted) !important;
    }

    .swal2-input {
        background: var(--surface) !important;
        border: 1px solid var(--glass-border) !important;
        color: var(--text-main) !important;
        border-radius: 12px !important;
        padding: 0.75rem 1rem !important;
    }

    .swal2-input:focus {
        border-color: var(--accent) !important;
        box-shadow: 0 0 0 2px var(--accent-glow) !important;
    }

    .swal2-confirm {
        background: var(--accent) !important;
        border-radius: 40px !important;
    }

    .swal2-cancel {
        background: #6c757d !important;
        border-radius: 40px !important;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .template-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 992px) {
        .template-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
    }

    @media (max-width: 768px) {
        .dfy-templates-container {
            padding: 1rem;
        }
        
        .template-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .form-select-custom {
            max-width: 100%;
        }
    }

    @media (max-width: 480px) {
        .template-grid {
            grid-template-columns: 1fr;
        }
        
        .template-title {
            font-size: 0.9rem;
        }
        
        .template-btn {
            padding: 0.4rem 0.75rem;
            font-size: 0.7rem;
        }
    }
</style>
@endsection

@php
    $endslug = \Str::afterLast(request()->url(), '/');
@endphp

@section('content')
<div class="dfy-templates-container">
    <!-- Header using DFY style -->
    <div class="dfy-header">
        <h1 class="dfy-title">Choose from <span style="color: var(--accent);">Templates</span></h1>
        <p class="dfy-subtitle">Select a template to start building your high-converting page</p>
    </div>

    <!-- Category Selector -->
    <div class="dfy-category-selector">
        <label><i class="fas fa-filter"></i> Filter by Category</label>
        <select id="page_builder_category" class="form-select-custom">
            <option value="">All Categories</option>
            @foreach($cats as $id => $cat)
                <option value="{{ $id }}" {{ (\Str::afterLast(request()->url(), '/') == $id) ? 'selected' : '' }}>
                    {{ $cat }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Templates Grid -->
    <div class="template-grid">
        @forelse($all_templates ?? [] as $temp)
            <div class="template-card">
                <div class="template-thumbnail" style="background-image: url('/builder/assets/templates/{{ $temp['temp_dir'] }}/{{ $temp['name'] }}/thumb.png');"></div>
                <div class="template-overlay">
                    <div class="template-title">{{ $temp['title'] }}</div>
                    <div class="template-actions">
                        <button class="template-btn primary edit-template"
                                data-cat="{{ $temp['temp_dir'] }}"
                                data-dir="{{ $temp['name'] }}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <a href="/builder/assets/templates/{{ $temp['temp_dir'] }}/{{ $temp['name'] }}/" 
                           class="template-btn" target="_blank">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                </div>
            </div>
        @empty
            @php
                $dir = public_path("builder/assets/templates/{$temp_dir}/");
                $templateUrl = array_diff(scandir($dir), ['..', '.']);
            @endphp
            @forelse($templateUrl as $name)
                @php
                    $path = $dir . $name;
                    $files = glob("{$path}/index.html");
                    $title = '';
                    if (!empty($files)) {
                        $content = file_get_contents($files[0]);
                        preg_match('/<title>(.*?)<\/title>/i', $content, $matches);
                        $title = $matches[1] ?? '';
                    }
                @endphp
                <div class="template-card">
                    <div class="template-thumbnail" style="background-image: url('/builder/assets/templates/{{ $temp_dir }}/{{ $name }}/thumb.png');"></div>
                    <div class="template-overlay">
                        <div class="template-title">{{ $title ?: $name }}</div>
                        <div class="template-actions">
                            <button class="template-btn primary edit-template"
                                    data-cat="{{ $temp_dir }}"
                                    data-dir="{{ $name }}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="/builder/assets/templates/{{ $temp_dir }}/{{ $name }}/" 
                               class="template-btn" target="_blank">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-layer-group"></i>
                    <h4>No Templates Found</h4>
                    <p>No templates available in this category yet.</p>
                </div>
            @endforelse
        @endforelse
    </div>
</div>

<!-- Hidden section (kept for compatibility) -->
<div class="section-body d-none">
    <div id="output-status"></div>
    <div class="row">
        <div class="col-12">
            <div class="card border-0">
                <div class="card-body">
                    <div class="album py-5"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Category selector
        const categorySelect = document.getElementById('page_builder_category');
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                const baseUrl = window.location.origin + '/page-builder/';
                const pageType = '{{ isset($basic) ? "create-new" : "dfy-templates" }}';
                window.location.href = baseUrl + pageType + '/' + this.value;
            });
        }
        
        // Edit template buttons
        document.querySelectorAll('.edit-template').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const cat = this.dataset.cat;
                const dir = this.dataset.dir;
                
                Swal.fire({
                    title: 'Name Your Page',
                    text: 'Enter a title for your new page',
                    input: 'text',
                    inputPlaceholder: 'e.g., My Awesome Landing Page',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-check"></i> Use Template',
                    cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                    background: '#121212',
                    color: '#ffffff',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Title is required!';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const titleEncoded = btoa(result.value);
                        const baseUrl = window.location.origin + '/builder/assets/templates/' + cat + '/' + dir + '/';
                        const encodedURL = btoa(baseUrl);
                        let finalUrl = "{{ route('page-builder.show',['id'=>now()->format('YmdHis'),'title'=>'#TITLE']) }}"
                            .replace('#TITLE', titleEncoded) + '?url=' + encodedURL;
                        window.open(finalUrl, '_blank');
                    }
                });
            });
        });
    });
</script>
@endsection