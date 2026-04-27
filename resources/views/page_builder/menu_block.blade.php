@extends('layouts.app')

@section('title', 'Profit Pages Menu')

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="{{ URL::asset('plugins/sweetalert/sweetalert2.min.css') }}" rel="stylesheet" />
<style>
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #1a1a1a;
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(to bottom, #8b5cf6, #ec4899);
        border-radius: 4px;
    }
    
    /* SweetAlert Dark Theme */
    .swal2-popup {
        background: #1a1a1a !important;
        border: 1px solid rgba(139, 92, 246, 0.3) !important;
        border-radius: 20px !important;
    }
    
    .swal2-title {
        color: #ffffff !important;
    }
    
    .swal2-html-container {
        color: #a0a0a0 !important;
    }
    
    .swal2-input {
        background: #1f2937 !important;
        border: 1px solid #374151 !important;
        color: #ffffff !important;
        border-radius: 12px !important;
        padding: 12px 16px !important;
    }
    
    .swal2-input:focus {
        border-color: #8b5cf6 !important;
        box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.2) !important;
    }
    
    .swal2-confirm {
        background: linear-gradient(135deg, #8b5cf6, #ec4899) !important;
        border-radius: 40px !important;
        font-weight: 600 !important;
    }
    
    .swal2-cancel {
        background: #374151 !important;
        border-radius: 40px !important;
        font-weight: 600 !important;
    }
    
    .swal2-validation-message {
        background: #1f2937 !important;
        color: #f87171 !important;
    }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-black to-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        
        <!-- Header Section -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 text-white text-xs font-semibold mb-4 shadow-lg">
                <i class="fas fa-chart-line mr-2 text-xs"></i>
                Profit Pages
            </div>
            <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent mb-4">
                {{ $page_title ?? 'Page Builder' }}
            </h1>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Choose how you want to create your next high-converting page
            </p>
        </div>
        
        <!-- Menu Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- 1-Click Cloner Card -->
            <div class="group bg-gradient-to-br from-gray-900/80 to-gray-800/40 backdrop-blur-lg rounded-2xl border border-gray-700/50 hover:border-purple-500/50 transition-all duration-500 overflow-hidden hover:shadow-2xl hover:shadow-purple-500/10 hover:-translate-y-1 cursor-pointer" id="oneClickCloner">
                <div class="p-8 text-center">
                    <div class="w-24 h-24 mx-auto rounded-2xl bg-gradient-to-br from-purple-600/20 to-pink-600/20 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-500">
                        <i class="fas fa-clone text-5xl text-purple-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-purple-300 transition-colors">1-Click Cloner</h3>
                    <p class="text-gray-400 text-sm">Clone any existing website with one click</p>
                </div>
                <div class="px-8 pb-8">
                    <div class="w-full h-1 bg-gray-700 rounded-full overflow-hidden">
                        <div class="w-0 h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full group-hover:w-full transition-all duration-700"></div>
                    </div>
                </div>
            </div>

            <!-- High-Converting Templates Card -->
            <a href="{{ route('page-builder.create') }}" class="group bg-gradient-to-br from-gray-900/80 to-gray-800/40 backdrop-blur-lg rounded-2xl border border-gray-700/50 hover:border-purple-500/50 transition-all duration-500 overflow-hidden hover:shadow-2xl hover:shadow-purple-500/10 hover:-translate-y-1 cursor-pointer block">
                <div class="p-8 text-center">
                    <div class="w-24 h-24 mx-auto rounded-2xl bg-gradient-to-br from-orange-600/20 to-red-600/20 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-500">
                        <i class="fas fa-fire text-5xl text-orange-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-orange-300 transition-colors">High-Converting Templates</h3>
                    <p class="text-gray-400 text-sm">Start with professionally designed templates</p>
                </div>
                <div class="px-8 pb-8">
                    <div class="w-full h-1 bg-gray-700 rounded-full overflow-hidden">
                        <div class="w-0 h-full bg-gradient-to-r from-orange-500 to-red-500 rounded-full group-hover:w-full transition-all duration-700"></div>
                    </div>
                </div>
            </a>

            <!-- My Campaigns Card -->
            <a href="{{ route('page-builder.saves') }}" class="group bg-gradient-to-br from-gray-900/80 to-gray-800/40 backdrop-blur-lg rounded-2xl border border-gray-700/50 hover:border-purple-500/50 transition-all duration-500 overflow-hidden hover:shadow-2xl hover:shadow-purple-500/10 hover:-translate-y-1 cursor-pointer block">
                <div class="p-8 text-center">
                    <div class="w-24 h-24 mx-auto rounded-2xl bg-gradient-to-br from-green-600/20 to-emerald-600/20 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-500">
                        <i class="fas fa-folder-open text-5xl text-green-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-green-300 transition-colors">My Campaigns</h3>
                    <p class="text-gray-400 text-sm">Manage and edit your saved pages</p>
                </div>
                <div class="px-8 pb-8">
                    <div class="w-full h-1 bg-gray-700 rounded-full overflow-hidden">
                        <div class="w-0 h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full group-hover:w-full transition-all duration-700"></div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Hidden CSRF Meta Tag if not present -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const oneClickCloner = document.getElementById('oneClickCloner');
    
    if (oneClickCloner) {
        oneClickCloner.addEventListener('click', function() {
            Swal.fire({
                title: '<i class="fas fa-clone text-purple-400"></i> 1-Click Cloner',
                html: `
                    <div class="space-y-4 text-left">
                        <div>
                            <label class="block text-left text-gray-300 text-sm font-medium mb-2">
                                <i class="fas fa-heading text-purple-400 mr-2"></i> Page Name
                            </label>
                            <input id="swal-title" class="swal2-input w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" 
                                   placeholder="e.g., My Awesome Landing Page" 
                                   style="margin: 0;">
                        </div>
                        <div>
                            <label class="block text-left text-gray-300 text-sm font-medium mb-2">
                                <i class="fas fa-link text-purple-400 mr-2"></i> Website URL
                            </label>
                            <input id="swal-url" class="swal2-input w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" 
                                   placeholder="https://example.com" 
                                   style="margin: 0;">
                        </div>
                        <div>
                            <label class="flex items-center gap-3 cursor-pointer text-gray-400 text-sm">
                                <input type="checkbox" id="swal-confirm-checkbox" class="w-4 h-4 rounded border-gray-600 text-purple-600 focus:ring-purple-500 focus:ring-offset-0">
                                <span>I confirm that I own or have permission to use this page</span>
                            </label>
                        </div>
                    </div>
                `,
                background: '#1a1a1a',
                color: '#ffffff',
                confirmButtonColor: '#8b5cf6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-clone mr-2"></i> Start Cloning',
                cancelButtonText: '<i class="fas fa-times mr-2"></i> Cancel',
                showCancelButton: true,
                focusConfirm: false,
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'px-6 py-2.5 rounded-xl font-semibold',
                    cancelButton: 'px-6 py-2.5 rounded-xl font-semibold'
                },
                preConfirm: () => {
                    const title = document.getElementById('swal-title')?.value.trim();
                    const url = document.getElementById('swal-url')?.value.trim();
                    const isChecked = document.getElementById('swal-confirm-checkbox')?.checked;

                    if (!title || !url) {
                        Swal.showValidationMessage('Both fields are required');
                        return false;
                    }

                    if (title.length < 3) {
                        Swal.showValidationMessage('Page name must be at least 3 characters');
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
                        html: '<div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-pulse text-3xl text-purple-400"></i><p class="text-gray-400 mt-2">Please wait while we clone your website</p></div>',
                        background: '#1a1a1a',
                        color: '#ffffff',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            fetch(clonerUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
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
                                    Swal.fire({
                                        title: 'Error',
                                        text: data.message || 'Cloning failed',
                                        icon: 'error',
                                        confirmButtonColor: '#8b5cf6',
                                        background: '#1a1a1a',
                                        color: '#ffffff'
                                    });
                                }
                            })
                            .catch(error => {
                                Swal.close();
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Cloning failed: ' + error.message,
                                    icon: 'error',
                                    confirmButtonColor: '#8b5cf6',
                                    background: '#1a1a1a',
                                    color: '#ffffff'
                                });
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