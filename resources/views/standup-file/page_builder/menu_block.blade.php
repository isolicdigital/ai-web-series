@extends('layouts.app')

@section('title', 'Profit Pages Menu')

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="{{ URL::asset('plugins/sweetalert/sweetalert2.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('custom/css/dfy.css') }}">
<style>
    /* DFY Menu Page Styles */
    .dfy-menu-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem;
    }

    .dfy-menu-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .dfy-menu-badge {
        display: inline-block;
        padding: 0.25rem 1rem;
        background: var(--accent);
        color: white;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .dfy-menu-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, var(--text-main) 0%, var(--accent) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .dfy-menu-subtitle {
        color: var(--text-muted);
        font-size: 1rem;
    }

    /* Menu Grid */
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-top: 1rem;
    }

    .menu-card {
        background: var(--card-bg);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        overflow: hidden;
        transition: var(--transition);
        cursor: pointer;
        text-decoration: none;
        display: block;
    }

    .menu-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow);
        border-color: var(--accent);
    }

    .menu-icon-block {
        width: 100%;
        height: 200px;
        background: var(--surface);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        color: var(--accent);
        transition: var(--transition);
    }

    .menu-card:hover .menu-icon-block {
        background: rgba(230, 88, 86, 0.1);
    }

    .menu-card-body {
        padding: 1.25rem;
        text-align: center;
    }

    .menu-card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 0.5rem;
    }

    .menu-card-description {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .menu-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
    }

    @media (max-width: 768px) {
        .dfy-menu-container {
            padding: 1rem;
        }
        
        .menu-grid {
            grid-template-columns: 1fr;
        }
        
        .menu-icon-block {
            height: 160px;
            font-size: 3rem;
        }
    }
</style>
@endsection

@section('content')
<div class="dfy-menu-container">
    <div class="dfy-menu-header">
        <div class="dfy-menu-badge">Profit Pages</div>
        <h1 class="dfy-menu-title">{{ $page_title ?? 'Page Builder' }}</h1>
        <p class="dfy-menu-subtitle">Choose how you want to create your next high-converting page</p>
    </div>

    <div class="menu-grid">
        <!-- 1-Click Cloner -->
        <div class="menu-card" id="oneClickCloner">
            <div class="menu-icon-block">
                <i class="fas fa-clone"></i>
            </div>
            <div class="menu-card-body">
                <h5 class="menu-card-title">1-Click Cloner</h5>
                <p class="menu-card-description">Clone any existing website with one click</p>
            </div>
        </div>

        <!-- High-Converting Templates -->
        <a href="{{ route('page-builder.create') }}" class="menu-card">
            <div class="menu-icon-block">
                <i class="fas fa-fire"></i>
            </div>
            <div class="menu-card-body">
                <h5 class="menu-card-title">High-Converting Templates</h5>
                <p class="menu-card-description">Start with professionally designed templates</p>
            </div>
        </a>

        <!-- My Campaigns -->
        <a href="{{ route('page-builder.saves') }}" class="menu-card">
            <div class="menu-icon-block">
                <i class="fas fa-folder-open"></i>
            </div>
            <div class="menu-card-body">
                <h5 class="menu-card-title">My Campaigns</h5>
                <p class="menu-card-description">Manage and edit your saved pages</p>
            </div>
        </a>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const oneClickCloner = document.getElementById('oneClickCloner');
    
    if (oneClickCloner) {
        oneClickCloner.addEventListener('click', function() {
            Swal.fire({
                title: '<i class="fas fa-clone"></i> 1-Click Cloner',
                html: `
                    <div style="display: flex; flex-direction: column; gap: 20px; text-align: left;">
                        <div>
                            <label style="display: block; text-align: left; margin-bottom: 8px; color: #e0e0ff; font-size: 0.875rem; font-weight: 500;">
                                <i class="fas fa-heading"></i> Page Name
                            </label>
                            <input id="swal-title" class="swal2-input" placeholder="e.g., My Awesome Landing Page" style="width: 100%; margin: 0; background: #1a1a26; color: white; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
                        </div>
                        <div>
                            <label style="display: block; text-align: left; margin-bottom: 8px; color: #e0e0ff; font-size: 0.875rem; font-weight: 500;">
                                <i class="fas fa-link"></i> Website URL
                            </label>
                            <input id="swal-url" class="swal2-input" placeholder="https://example.com" style="width: 100%; margin: 0; background: #1a1a26; color: white; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; display: flex; align-items: center; gap: 10px; cursor: pointer; color: #a0a0a0;">
                                <input type="checkbox" id="swal-confirm-checkbox" style="width: 16px; height: 16px; cursor: pointer;">
                                I confirm that I own or have permission to use this page
                            </label>
                        </div>
                    </div>
                `,
                background: '#121212',
                color: '#ffffff',
                confirmButtonColor: '#E65856',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-clone"></i> Start Cloning',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                showCancelButton: true,
                focusConfirm: false,
                preConfirm: () => {
                    const title = document.getElementById('swal-title')?.value.trim();
                    const url = document.getElementById('swal-url')?.value.trim();
                    const isChecked = document.getElementById('swal-confirm-checkbox')?.checked;

                    if (!title || !url) {
                        Swal.showValidationMessage('Both fields are required');
                        return false;
                    }

                    try {
                        new URL(url);
                    } catch (e) {
                        Swal.showValidationMessage('Please enter a valid URL (include https://)');
                        return false;
                    }

                    if (!isChecked) {
                        Swal.showValidationMessage('You must confirm that you have permission to use this page');
                        return false;
                    }

                    return { title, url };
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const title = result.value.title;
                    const url = result.value.url;
                    const id = new Date().toISOString().replace(/[-:.TZ]/g, '');
                    
                    let clonerUrl = "{{ route('page-builder.clone', ['id' => '__ID__']) }}".replace('__ID__', id);

                    Swal.fire({
                        title: 'Cloning page...',
                        text: 'Please wait while we clone your website',
                        background: '#121212',
                        color: '#ffffff',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                            
                            fetch(clonerUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                                },
                                body: JSON.stringify({ url: url })
                            })
                            .then(response => response.json())
                            .then(data => {
                                Swal.close();
                                if (data.success) {
                                    const titleEncoded = btoa(title);
                                    const urlEncoded = btoa(url);
                                    let finalUrl = "{{ route('page-builder.show', ['id' => '__ID__', 'title' => '__TITLE__']) }}"
                                        .replace('__ID__', id)
                                        .replace('__TITLE__', titleEncoded) + '?url=' + urlEncoded;
                                    
                                    window.location.href = finalUrl;
                                } else {
                                    Swal.fire('Error', data.message || 'Cloning failed', 'error');
                                }
                            })
                            .catch(error => {
                                Swal.close();
                                Swal.fire('Error', 'Cloning failed: ' + error.message, 'error');
                            });
                        }
                    });
                }
            });
        });
    }
});
</script>
@endsection