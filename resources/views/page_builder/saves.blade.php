@extends('layouts.app')

@section('title', 'DFY Pages')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
        font-weight: 600 !important;
    }
    
    .swal2-cancel {
        background: #374151 !important;
        border-radius: 40px !important;
        font-weight: 600 !important;
    }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-black to-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header Section -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 mb-6 shadow-lg">
                <i class="fas fa-file-alt text-white text-2xl"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent mb-4">
                {{ $page_title ?? 'My Pages' }}
            </h1>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Manage and edit your DFY pages
            </p>
        </div>
        
        <!-- Create Button -->
        <div class="flex justify-end mb-8">
            <a href="{{ route('page-builder.dfy') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 shadow-lg hover:shadow-pink-500/25 transform hover:scale-105">
                <i class="fas fa-plus"></i>
                Create New Page
            </a>
        </div>
        
        @if ($saves->count())
        <!-- Desktop Table View (hidden on mobile) -->
        <div class="hidden md:block overflow-hidden rounded-2xl border border-gray-700/50 bg-gradient-to-br from-gray-900/80 to-gray-800/40 backdrop-blur-lg">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700/50 bg-gradient-to-r from-gray-800/50 to-gray-900/50">
                        <th class="px-6 py-4 text-left text-white font-semibold text-sm">
                            <i class="fas fa-heading text-purple-400 mr-2"></i> Title
                        </th>
                        <th class="px-6 py-4 text-left text-white font-semibold text-sm">
                            <i class="fas fa-calendar-alt text-purple-400 mr-2"></i> Created
                        </th>
                        <th class="px-6 py-4 text-left text-white font-semibold text-sm">
                            <i class="fas fa-cog text-purple-400 mr-2"></i> Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($saves as $save)
                    <tr class="border-b border-gray-700/50 hover:bg-white/5 transition-colors duration-300">
                        <td class="px-6 py-4">
                            <div class="text-white font-medium text-sm">{{ $save->title }}</div>
                            @if($save->slug)
                            <div class="text-gray-500 text-xs mt-1 flex items-center gap-1">
                                <i class="fas fa-link text-purple-400 text-[10px]"></i>
                                <span>{{ Str::limit($save->slug, 35) }}</span>
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-300 text-sm flex items-center gap-2">
                                <i class="fas fa-calendar-day text-purple-400 text-xs"></i>
                                {{ $save->created_at->format('d M, Y') }}
                            </div>
                            <div class="text-gray-500 text-xs mt-1 flex items-center gap-2">
                                <i class="fas fa-clock text-purple-400 text-[10px]"></i>
                                {{ $save->created_at->format('h:i A') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('page-builder.show', ['id' => $save->slug, 'title' => base64_encode($save->title)]) }}" 
                                   class="w-9 h-9 rounded-lg bg-purple-500/20 hover:bg-purple-600 text-purple-400 hover:text-white flex items-center justify-center transition-all duration-300 transform hover:scale-110"
                                   title="Edit" target="_blank">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                <a href="{{ url('/p/?v='.base64_encode($save->slug)) }}" 
                                   class="w-9 h-9 rounded-lg bg-blue-500/20 hover:bg-blue-600 text-blue-400 hover:text-white flex items-center justify-center transition-all duration-300 transform hover:scale-110"
                                   title="View" target="_blank">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <a href="{{ route('page-builder.download', $save->id) }}" 
                                   class="w-9 h-9 rounded-lg bg-green-500/20 hover:bg-green-600 text-green-400 hover:text-white flex items-center justify-center transition-all duration-300 transform hover:scale-110"
                                   title="Download">
                                    <i class="fas fa-download text-sm"></i>
                                </a>
                                <button type="button" 
                                        class="w-9 h-9 rounded-lg bg-red-500/20 hover:bg-red-600 text-red-400 hover:text-white flex items-center justify-center transition-all duration-300 transform hover:scale-110"
                                        onclick="confirmDelete({{ $save->id }}, '{{ addslashes($save->title) }}')" 
                                        title="Delete">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                                <form id="delete-form-{{ $save->id }}" 
                                      action="{{ route('page-builder.delete', $save->id) }}" 
                                      method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Mobile Card View (visible on mobile) -->
        <div class="md:hidden space-y-4">
            @foreach($saves as $save)
            <div class="bg-gradient-to-br from-gray-900/80 to-gray-800/40 backdrop-blur-lg rounded-2xl border border-gray-700/50 p-5 hover:border-purple-500/50 transition-all duration-300">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h3 class="text-white font-semibold text-base mb-1">{{ $save->title }}</h3>
                        @if($save->slug)
                        <p class="text-gray-500 text-xs flex items-center gap-1">
                            <i class="fas fa-link text-purple-400 text-[10px]"></i>
                            {{ Str::limit($save->slug, 30) }}
                        </p>
                        @endif
                    </div>
                    <div class="flex items-center gap-1">
                        <a href="{{ route('page-builder.show', ['id' => $save->slug, 'title' => base64_encode($save->title)]) }}" 
                           class="w-8 h-8 rounded-lg bg-purple-500/20 hover:bg-purple-600 text-purple-400 hover:text-white flex items-center justify-center transition-all duration-300"
                           target="_blank">
                            <i class="fas fa-edit text-xs"></i>
                        </a>
                        <button type="button" 
                                class="w-8 h-8 rounded-lg bg-red-500/20 hover:bg-red-600 text-red-400 hover:text-white flex items-center justify-center transition-all duration-300"
                                onclick="confirmDelete({{ $save->id }}, '{{ addslashes($save->title) }}')">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-700/50">
                    <div class="flex items-center gap-2 text-gray-500 text-xs">
                        <i class="fas fa-calendar-day text-purple-400"></i>
                        <span>{{ $save->created_at->format('d M, Y') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ url('/p/?v='.base64_encode($save->slug)) }}" 
                           class="text-gray-400 hover:text-blue-400 transition-colors text-xs flex items-center gap-1"
                           target="_blank">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <span class="text-gray-600">|</span>
                        <a href="{{ route('page-builder.download', $save->id) }}" 
                           class="text-gray-400 hover:text-green-400 transition-colors text-xs flex items-center gap-1">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        @if(method_exists($saves, 'links') && $saves->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $saves->links() }}
        </div>
        @endif
        
        @else
        <!-- Empty State -->
        <div class="text-center py-20">
            <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-gradient-to-br from-purple-600/20 to-pink-600/20 flex items-center justify-center">
                <i class="fas fa-file-alt text-purple-400 text-5xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-white mb-3">No Pages Yet</h3>
            <p class="text-gray-400 mb-6 max-w-md mx-auto">Create your first DFY page to get started</p>
            <a href="{{ route('page-builder.dfy') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 shadow-lg hover:shadow-pink-500/25 transform hover:scale-105">
                <i class="fas fa-plus"></i>
                Create Your First Page
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(pageId, pageTitle) {
    Swal.fire({
        title: 'Delete Page?',
        html: `<div class="text-center">
                <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4"></i>
                <p class="text-white mb-2">Are you sure you want to delete "<strong class="text-purple-400">${pageTitle}</strong>"?</p>
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
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
                background: '#1a1a1a',
                color: '#ffffff',
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            document.getElementById(`delete-form-${pageId}`).submit();
        }
    });
}
</script>
@endsection