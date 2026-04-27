@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-black to-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header Section -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 mb-6 shadow-lg">
                <i class="fas fa-layer-group text-white text-2xl"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent mb-4">
                Choose from <span class="text-purple-400">Templates</span>
            </h1>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Select a template to start building your high-converting page
            </p>
        </div>
        
        <!-- Category Filter -->
        <div class="max-w-md mx-auto mb-10">
            <label class="block text-white font-semibold mb-3 flex items-center gap-2">
                <i class="fas fa-filter text-purple-400 text-sm"></i>
                Filter by Category
            </label>
            <select id="page_builder_category" 
                    class="w-full px-5 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all duration-300 cursor-pointer">
                <option value="" class="bg-gray-900">All Categories</option>
                @foreach($cats as $id => $cat)
                    <option value="{{ $id }}" class="bg-gray-900" {{ (\Str::afterLast(request()->url(), '/') == $id) ? 'selected' : '' }}>
                        {{ $cat }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Templates Grid -->
        @php
            $hasTemplates = !empty($all_templates) && count($all_templates) > 0;
        @endphp
        
        @if($hasTemplates)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($all_templates ?? [] as $temp)
            <div class="template-card group relative bg-gradient-to-br from-gray-900/80 to-gray-800/40 backdrop-blur-lg rounded-xl border border-gray-700/50 hover:border-purple-500/50 transition-all duration-500 overflow-hidden hover:shadow-2xl hover:shadow-purple-500/10 hover:-translate-y-1">
                <!-- Template Thumbnail -->
                <div class="relative aspect-video overflow-hidden bg-gray-800">
                    <div class="template-thumbnail w-full h-full bg-cover bg-center transition-transform duration-500 group-hover:scale-110"
                         style="background-image: url('/builder/assets/templates/{{ $temp['temp_dir'] }}/{{ $temp['name'] }}/thumb.png');">
                    </div>
                    
                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col items-center justify-center p-4">
                        <h3 class="text-white font-semibold text-base mb-3 text-center">{{ $temp['title'] }}</h3>
                        <div class="flex gap-3">
                            <button class="edit-template px-4 py-2 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white text-sm font-medium transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-pink-500/25 transform hover:scale-105"
                                    data-cat="{{ $temp['temp_dir'] }}"
                                    data-dir="{{ $temp['name'] }}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="/builder/assets/templates/{{ $temp['temp_dir'] }}/{{ $temp['name'] }}/" 
                               class="px-4 py-2 rounded-lg bg-gray-700/50 hover:bg-gray-600 text-white text-sm font-medium transition-all duration-300 flex items-center gap-2 transform hover:scale-105"
                               target="_blank">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Template Title Bar -->
                <div class="p-3 border-t border-gray-700/50">
                    <h4 class="text-white font-medium text-sm truncate">{{ $temp['title'] }}</h4>
                </div>
            </div>
            @empty
            @endforelse
        </div>
        @else
        <!-- Fallback for when $all_templates is empty (using directory scan) -->
        @php
            $dir = public_path("builder/assets/templates/{$temp_dir}/");
            $templateUrl = is_dir($dir) ? array_diff(scandir($dir), ['..', '.']) : [];
        @endphp
        @if(!empty($templateUrl))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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
                <div class="template-card group relative bg-gradient-to-br from-gray-900/80 to-gray-800/40 backdrop-blur-lg rounded-xl border border-gray-700/50 hover:border-purple-500/50 transition-all duration-500 overflow-hidden hover:shadow-2xl hover:shadow-purple-500/10 hover:-translate-y-1">
                    <div class="relative aspect-video overflow-hidden bg-gray-800">
                        <div class="template-thumbnail w-full h-full bg-cover bg-center transition-transform duration-500 group-hover:scale-110"
                             style="background-image: url('/builder/assets/templates/{{ $temp_dir }}/{{ $name }}/thumb.png');">
                        </div>
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col items-center justify-center p-4">
                            <h3 class="text-white font-semibold text-base mb-3 text-center">{{ $title ?: $name }}</h3>
                            <div class="flex gap-3">
                                <button class="edit-template px-4 py-2 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white text-sm font-medium transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-pink-500/25 transform hover:scale-105"
                                        data-cat="{{ $temp_dir }}"
                                        data-dir="{{ $name }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <a href="/builder/assets/templates/{{ $temp_dir }}/{{ $name }}/" 
                                   class="px-4 py-2 rounded-lg bg-gray-700/50 hover:bg-gray-600 text-white text-sm font-medium transition-all duration-300 flex items-center gap-2 transform hover:scale-105"
                                   target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 border-t border-gray-700/50">
                        <h4 class="text-white font-medium text-sm truncate">{{ $title ?: $name }}</h4>
                    </div>
                </div>
            @empty
            @endforelse
        </div>
        @else
        <!-- Empty State -->
        <div class="text-center py-20">
            <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-gradient-to-br from-purple-600/20 to-pink-600/20 flex items-center justify-center">
                <i class="fas fa-layer-group text-purple-400 text-5xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-white mb-3">No Templates Found</h3>
            <p class="text-gray-400 mb-6 max-w-md mx-auto">No templates available in this category yet.</p>
        </div>
        @endif
        @endif
    </div>
</div>

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
    
    .template-thumbnail {
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
</style>

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
                    background: '#1a1a1a',
                    color: '#ffffff',
                    confirmButtonColor: '#8b5cf6',
                    cancelButtonColor: '#6b7280',
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