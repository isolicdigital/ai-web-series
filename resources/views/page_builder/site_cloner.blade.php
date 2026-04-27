@extends('layouts.app')

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
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
    
    .swal2-confirm {
        background: linear-gradient(135deg, #ef4444, #ec4899) !important;
        border-radius: 40px !important;
    }
    
    .swal2-cancel {
        background: #374151 !important;
        border-radius: 40px !important;
    }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-black to-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header Section -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 mb-6 shadow-lg">
                <i class="fas fa-copy text-white text-2xl"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent mb-4">
                Clone Any Website Instantly
            </h1>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Transform any webpage into your own customizable template. Enter the details below to get started.
            </p>
        </div>
        
        <!-- Cloner Form -->
        <div class="max-w-3xl mx-auto mb-16">
            <form id="clonerForm" method="POST" action="{{ route('page-builder.clone', ['id' => 'TEMP_ID']) }}" class="space-y-6">
                @csrf
                
                <!-- Page Name -->
                <div>
                    <label class="block text-white font-semibold mb-2 flex items-center gap-2">
                        <i class="fas fa-heading text-purple-400 text-sm"></i>
                        Page Name
                    </label>
                    <input type="text" id="pageTitle" name="title" 
                           class="w-full px-5 py-3.5 bg-gray-800/50 border border-gray-700 rounded-xl text-white placeholder-gray-400 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" 
                           placeholder="Enter a name for your cloned page" required>
                </div>
                
                <!-- Source URL -->
                <div>
                    <label class="block text-white font-semibold mb-2 flex items-center gap-2">
                        <i class="fas fa-link text-purple-400 text-sm"></i>
                        Source URL
                    </label>
                    <input type="url" id="pageUrl" name="url" 
                           class="w-full px-5 py-3.5 bg-gray-800/50 border border-gray-700 rounded-xl text-white placeholder-gray-400 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" 
                           placeholder="https://example.com" required>
                </div>
                
                <!-- AI Model -->
                <div>
                    <label class="block text-white font-semibold mb-2 flex items-center gap-2">
                        <i class="fas fa-brain text-purple-400 text-sm"></i>
                        AI Model
                    </label>
                    <select id="aiModel" name="ai_model" 
                            class="w-full px-5 py-3.5 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all duration-300 cursor-pointer" required>
                        <option value="" class="bg-gray-900">Select AI Model...</option>
                        <optgroup label="OpenAI" class="bg-gray-900">
                            <option value="gpt-5" class="bg-gray-900">GPT-5</option>
                            <option value="gpt-4.5" class="bg-gray-900">GPT-4.5</option>
                            <option value="gpt-4-turbo" class="bg-gray-900" selected>GPT-4 Turbo</option>
                        </optgroup>
                        <optgroup label="DeepSeek" class="bg-gray-900">
                            <option value="deepseek-v3" class="bg-gray-900">DeepSeek V3</option>
                            <option value="deepseek-r1" class="bg-gray-900">DeepSeek R1</option>
                        </optgroup>
                        <optgroup label="Anthropic" class="bg-gray-900">
                            <option value="claude-4" class="bg-gray-900">Claude 4</option>
                            <option value="claude-opus" class="bg-gray-900">Claude Opus</option>
                        </optgroup>
                    </select>
                </div>
                
                <!-- Clone Button -->
                <button type="submit" id="cloneButton" 
                        class="w-full py-3.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 flex items-center justify-center gap-2 shadow-lg hover:shadow-pink-500/25 transform hover:scale-[1.02]">
                    <i class="fas fa-copy"></i>
                    Start Cloning
                </button>
                
                <!-- Confirmation Checkbox -->
                <div class="flex items-start gap-3 p-4 bg-gray-800/30 rounded-xl border border-gray-700">
                    <input type="checkbox" id="confirmationCheckbox" 
                           class="mt-0.5 w-4 h-4 rounded border-gray-600 text-purple-600 focus:ring-purple-500 focus:ring-offset-0" required>
                    <label for="confirmationCheckbox" class="text-gray-400 text-sm">
                        I confirm that I own or have legal rights to clone and use this webpage
                    </label>
                </div>
            </form>
        </div>
        
        <!-- Saved Pages Section -->
        <div>
            <div class="flex items-center gap-3 mb-6 pb-2 border-b border-gray-800">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                    <i class="fas fa-history text-white text-sm"></i>
                </div>
                <h2 class="text-2xl font-bold text-white">Your Cloned Pages</h2>
            </div>
            
            @if($saves->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($saves as $save)
                @php
                    $thumbnailPath = "/pages/{$save->slug}/thumb.png";
                    $hasThumbnail = file_exists(public_path($thumbnailPath));
                @endphp
                
                <div class="group bg-gradient-to-br from-gray-900/80 to-gray-800/40 backdrop-blur-lg rounded-2xl border border-gray-700/50 hover:border-purple-500/50 transition-all duration-500 overflow-hidden hover:shadow-2xl hover:shadow-purple-500/10 hover:-translate-y-1">
                    <!-- Card Preview -->
                    <div class="relative h-48 overflow-hidden bg-gray-800">
                        @if($hasThumbnail)
                            <img src="{{ $thumbnailPath }}" alt="{{ $save->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="placeholder hidden absolute inset-0 flex items-center justify-center bg-gradient-to-br from-purple-600/20 to-pink-600/20">
                                <i class="fas fa-file-alt text-5xl text-purple-400"></i>
                            </div>
                        @else
                            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-purple-600/20 to-pink-600/20">
                                <i class="fas fa-file-alt text-5xl text-purple-400"></i>
                            </div>
                        @endif
                        
                        <!-- Overlay Actions -->
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center gap-3">
                            <a href="{{ route('page-builder.show', ['id' => $save->slug, 'title' => base64_encode($save->title)]) }}" 
                               class="w-10 h-10 rounded-full bg-purple-600/90 hover:bg-purple-500 flex items-center justify-center transition-all duration-300 transform hover:scale-110"
                               target="_blank" title="Edit">
                                <i class="fas fa-edit text-white text-sm"></i>
                            </a>
                            <a href="{{ url('/p/?v='.base64_encode($save->slug)) }}" 
                               class="w-10 h-10 rounded-full bg-blue-600/90 hover:bg-blue-500 flex items-center justify-center transition-all duration-300 transform hover:scale-110"
                               target="_blank" title="View">
                                <i class="fas fa-eye text-white text-sm"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Card Content -->
                    <div class="p-4">
                        <div class="flex items justify-between mb-3">
                            <h3 class="text-white font-semibold text-sm line-clamp-1 flex-1" title="{{ $save->title }}">
                                {{ Str::limit($save->title, 40) }}
                            </h3>
                        </div>
                        
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                            <div class="flex items-center gap-1">
                                <i class="fas fa-calendar text-purple-400 text-[10px]"></i>
                                <span>{{ $save->created_at->format('d M, Y') }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="fas fa-clock text-purple-400 text-[10px]"></i>
                                <span>{{ $save->created_at->format('h:i A') }}</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 pt-3 border-t border-gray-700/50">
                            <a href="{{ route('page-builder.show', ['id' => $save->slug, 'title' => base64_encode($save->title)]) }}" 
                               class="flex-1 py-1.5 rounded-lg bg-purple-600/20 hover:bg-purple-600 text-purple-400 hover:text-white text-center text-xs font-medium transition-all duration-300 flex items-center justify-center gap-1"
                               target="_blank">
                                <i class="fas fa-edit text-[10px]"></i>
                                Edit
                            </a>
                            <a href="{{ route('page-builder.download', $save->id) }}" 
                               class="flex-1 py-1.5 rounded-lg bg-green-600/20 hover:bg-green-600 text-green-400 hover:text-white text-center text-xs font-medium transition-all duration-300 flex items-center justify-center gap-1">
                                <i class="fas fa-download text-[10px]"></i>
                                Zip
                            </a>
                            <form action="{{ route('page-builder.delete', $save->id) }}" method="POST" class="delete-form flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full py-1.5 rounded-lg bg-red-600/20 hover:bg-red-600 text-red-400 hover:text-white text-center text-xs font-medium transition-all duration-300 flex items-center justify-center gap-1">
                                    <i class="fas fa-trash text-[10px]"></i>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <!-- Empty State -->
            <div class="text-center py-20">
                <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-gradient-to-br from-purple-600/20 to-pink-600/20 flex items-center justify-center">
                    <i class="fas fa-folder-open text-purple-400 text-5xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-3">No Cloned Pages Yet</h3>
                <p class="text-gray-400 mb-6 max-w-md mx-auto">Start by cloning your first website above!</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                Swal.fire({
                    title: 'Error',
                    text: 'Please fill in all required fields',
                    icon: 'error',
                    confirmButtonColor: '#8b5cf6',
                    background: '#1a1a1a',
                    color: '#ffffff'
                });
                return;
            }

            try {
                new URL(url);
            } catch {
                Swal.fire({
                    title: 'Error',
                    text: 'Please enter a valid URL',
                    icon: 'error',
                    confirmButtonColor: '#8b5cf6',
                    background: '#1a1a1a',
                    color: '#ffffff'
                });
                return;
            }

            if (!confirmed) {
                Swal.fire({
                    title: 'Error',
                    text: 'You must confirm that you have rights to clone this page',
                    icon: 'error',
                    confirmButtonColor: '#8b5cf6',
                    background: '#1a1a1a',
                    color: '#ffffff'
                });
                return;
            }

            // Generate unique ID and prepare request
            const id = new Date().toISOString().replace(/[-:.TZ]/g, '');
            const clonerUrl = `{{ route('page-builder.clone', ['id' => '__ID__']) }}`.replace('__ID__', id);

            // Show loading state
            cloneButton.disabled = true;
            cloneButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Cloning...';

            Swal.fire({
                title: 'Cloning Page',
                html: `<div class="flex flex-col items-center gap-3">
                        <i class="fas fa-spinner fa-pulse text-3xl text-purple-400"></i>
                        <p class="text-gray-400">Using ${aiModel} to process your request...</p>
                       </div>`,
                allowOutsideClick: false,
                showConfirmButton: false,
                background: '#1a1a1a',
                color: '#ffffff',
                didOpen: () => {
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
                        Swal.fire({
                            title: 'Error',
                            text: error.message || 'Cloning failed. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#8b5cf6',
                            background: '#1a1a1a',
                            color: '#ffffff'
                        });
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

                const card = this.closest('.group');
                const pageTitle = card.querySelector('.line-clamp-1')?.textContent.trim() || 'this page';

                Swal.fire({
                    title: 'Delete Page?',
                    html: `<div class="text-center">
                            <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4"></i>
                            <p class="text-white mb-2">Are you sure you want to delete <strong class="text-purple-400">"${pageTitle}"</strong>?</p>
                            <p class="text-gray-500 text-sm">This action cannot be undone.</p>
                           </div>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="fas fa-trash mr-2"></i> Yes, delete it!',
                    cancelButtonText: '<i class="fas fa-times mr-2"></i> Cancel',
                    background: '#1a1a1a',
                    color: '#ffffff',
                    customClass: {
                        confirmButton: 'px-5 py-2.5 rounded-xl font-semibold',
                        cancelButton: 'px-5 py-2.5 rounded-xl font-semibold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endsection