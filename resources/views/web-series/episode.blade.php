@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
    <!-- Animated Background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-pink-500/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative z-10 container mx-auto px-4 py-[120px] max-w-5xl">
        <!-- Navigation -->
        <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
            <a href="{{ route('web-series.show', $series->id) }}" 
               class="group inline-flex items-center gap-2 text-gray-400 hover:text-white transition-all duration-300 hover:translate-x-[-4px]">
                <i class="fas fa-arrow-left text-sm group-hover:-translate-x-1 transition-transform"></i>
                <span>Back to Series</span>
            </a>
            <div class="flex gap-3">
                @if($scene->scene_number > 1)
                @php
                    $prevScene = $series->scenes->where('scene_number', $scene->scene_number - 1)->first();
                @endphp
                @if($prevScene)
                <a href="{{ route('web-series.scene', ['seriesId' => $series->id, 'sceneId' => $prevScene->id]) }}" 
                   class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-medium transition-all duration-300 hover:scale-105 flex items-center gap-2 shadow-lg">
                    <i class="fas fa-chevron-left text-sm"></i>
                    <span class="hidden sm:inline">Previous</span>
                </a>
                @endif
                @endif
                
                @if($scene->scene_number < $series->total_episodes)
                @php
                    $nextScene = $series->scenes->where('scene_number', $scene->scene_number + 1)->first();
                @endphp
                @if($nextScene)
                <a href="{{ route('web-series.scene', ['seriesId' => $series->id, 'sceneId' => $nextScene->id]) }}" 
                   class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-medium transition-all duration-300 hover:scale-105 flex items-center gap-2 shadow-lg">
                    <span class="hidden sm:inline">Next</span>
                    <i class="fas fa-chevron-right text-sm"></i>
                </a>
                @endif
                @endif
            </div>
        </div>

        <!-- Scene Card -->
        <div class="bg-white/5 backdrop-blur-xl rounded-2xl border border-white/10 overflow-hidden shadow-2xl hover:border-purple-500/30 transition-all duration-300">
            <!-- Scene Header -->
            <div class="border-b border-white/10 p-6 md:p-8 bg-gradient-to-r from-purple-900/40 to-pink-900/40">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shadow-lg">
                        <span class="text-white text-lg font-bold">{{ $scene->scene_number }}</span>
                    </div>
                    <div>
                        <span class="text-purple-400 text-sm font-medium">Scene {{ $scene->scene_number }}</span>
                        <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                            <span><i class="far fa-calendar-alt mr-1"></i> {{ $scene->created_at->format('F j, Y') }}</span>
                            <span>•</span>
                            <span><i class="far fa-clock mr-1"></i> {{ $scene->created_at->format('g:i A') }}</span>
                        </div>
                    </div>
                </div>
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-3 leading-tight">{{ $scene->title }}</h1>
                
                <!-- Scene Progress Indicator -->
                <div class="mt-4 flex items-center gap-3">
                    <div class="flex-1 h-1.5 bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full" style="width: {{ ($scene->scene_number / $series->total_episodes) * 100 }}%"></div>
                    </div>
                    <span class="text-sm text-gray-400">Scene {{ $scene->scene_number }} of {{ $series->total_episodes }}</span>
                </div>
            </div>

            <!-- Scene Content -->
            <div class="p-6 md:p-8 lg:p-10">
                <div class="prose prose-invert prose-lg max-w-none">
                    {!! $scene->content !!}
                </div>
            </div>

            <!-- Scene Footer Actions -->
            <div class="border-t border-white/10 p-6 bg-black/30">
                <div class="flex flex-wrap gap-3 justify-between items-center">
                    <div class="flex flex-wrap gap-2">
                        <button onclick="downloadScene()" 
                                class="group px-4 py-2.5 bg-green-600/20 hover:bg-green-600 border border-green-500/30 hover:border-green-500 rounded-xl text-green-400 hover:text-white transition-all duration-300 flex items-center gap-2">
                            <i class="fas fa-download group-hover:translate-y-1 transition-transform"></i>
                            <span class="text-sm font-medium">Download</span>
                        </button>
                        <button onclick="copyToClipboard()" 
                                class="group px-4 py-2.5 bg-blue-600/20 hover:bg-blue-600 border border-blue-500/30 hover:border-blue-500 rounded-xl text-blue-400 hover:text-white transition-all duration-300 flex items-center gap-2">
                            <i class="fas fa-copy group-hover:scale-110 transition-transform"></i>
                            <span class="text-sm font-medium">Copy</span>
                        </button>
                        <button onclick="regenerateScene()" 
                                class="group px-4 py-2.5 bg-yellow-600/20 hover:bg-yellow-600 border border-yellow-500/30 hover:border-yellow-500 rounded-xl text-yellow-400 hover:text-white transition-all duration-300 flex items-center gap-2">
                            <i class="fas fa-sync-alt group-hover:rotate-180 transition-transform duration-500"></i>
                            <span class="text-sm font-medium">Regenerate</span>
                        </button>
                        <button onclick="printScene()" 
                                class="group px-4 py-2.5 bg-gray-600/20 hover:bg-gray-600 border border-gray-500/30 hover:border-gray-500 rounded-xl text-gray-400 hover:text-white transition-all duration-300 flex items-center gap-2">
                            <i class="fas fa-print"></i>
                            <span class="text-sm font-medium">Print</span>
                        </button>
                    </div>
                    
                    <div class="flex gap-2">
                        @if($scene->scene_number > 1)
                        @php
                            $prevScene = $series->scenes->where('scene_number', $scene->scene_number - 1)->first();
                        @endphp
                        @if($prevScene)
                        <a href="{{ route('web-series.scene', ['seriesId' => $series->id, 'sceneId' => $prevScene->id]) }}" 
                           class="px-4 py-2.5 bg-gray-700/50 hover:bg-gray-700 rounded-xl text-white transition-all duration-300 flex items-center gap-2 hover:translate-x-[-2px]">
                            <i class="fas fa-chevron-left text-sm"></i>
                            <span class="hidden sm:inline">Previous</span>
                        </a>
                        @endif
                        @endif
                        
                        @if($scene->scene_number < $series->total_episodes)
                        @php
                            $nextScene = $series->scenes->where('scene_number', $scene->scene_number + 1)->first();
                        @endphp
                        @if($nextScene)
                        <a href="{{ route('web-series.scene', ['seriesId' => $series->id, 'sceneId' => $nextScene->id]) }}" 
                           class="px-4 py-2.5 bg-gray-700/50 hover:bg-gray-700 rounded-xl text-white transition-all duration-300 flex items-center gap-2 hover:translate-x-[2px]">
                            <span class="hidden sm:inline">Next</span>
                            <i class="fas fa-chevron-right text-sm"></i>
                        </a>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Share Section -->
        <div class="mt-8 text-center">
            <p class="text-gray-500 text-sm mb-3">Share this scene</p>
            <div class="flex justify-center gap-3">
                <button onclick="shareOnTwitter()" class="w-10 h-10 rounded-full bg-[#1DA1F2]/20 hover:bg-[#1DA1F2] text-[#1DA1F2] hover:text-white transition-all duration-300 flex items-center justify-center">
                    <i class="fab fa-twitter"></i>
                </button>
                <button onclick="shareOnFacebook()" class="w-10 h-10 rounded-full bg-[#4267B2]/20 hover:bg-[#4267B2] text-[#4267B2] hover:text-white transition-all duration-300 flex items-center justify-center">
                    <i class="fab fa-facebook-f"></i>
                </button>
                <button onclick="shareOnLinkedIn()" class="w-10 h-10 rounded-full bg-[#0077B5]/20 hover:bg-[#0077B5] text-[#0077B5] hover:text-white transition-all duration-300 flex items-center justify-center">
                    <i class="fab fa-linkedin-in"></i>
                </button>
                <button onclick="copyShareLink()" class="w-10 h-10 rounded-full bg-gray-600/20 hover:bg-gray-600 text-gray-400 hover:text-white transition-all duration-300 flex items-center justify-center">
                    <i class="fas fa-link"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles for Scene Content -->
<style>
    /* Scene Content Styling */
    .prose {
        max-width: none;
        color: #d1d5db;
    }
    
    .prose h1 {
        font-size: 2rem;
        font-weight: 700;
        color: white;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .prose h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #c084fc;
        margin-top: 1.25rem;
        margin-bottom: 0.75rem;
    }
    
    .prose h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #e879f9;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
    }
    
    .prose p {
        line-height: 1.75;
        margin-bottom: 1rem;
    }
    
    .prose strong {
        color: #fbbf24;
        font-weight: 600;
    }
    
    .prose em {
        color: #a78bfa;
        font-style: italic;
    }
    
    .prose ul, .prose ol {
        margin: 1rem 0;
        padding-left: 1.5rem;
    }
    
    .prose li {
        margin: 0.5rem 0;
    }
    
    /* Scene heading styling */
    .prose p:has(strong:contains("SCENE")) {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(236, 72, 153, 0.1));
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        border-left: 3px solid #8b5cf6;
        margin: 1.5rem 0;
    }
    
    /* Scene content styling */
    .scene-content h3 {
        color: #c084fc;
        font-size: 1.3rem;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .scene-content p {
        margin-bottom: 1rem;
    }
    
    /* Responsive spacing */
    @media (max-width: 768px) {
        .py-\[120px\] {
            padding-top: 60px !important;
            padding-bottom: 60px !important;
        }
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #1e1b4b;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #7c3aed;
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #8b5cf6;
    }
    
    /* Print styles */
    @media print {
        .fixed, .bg-gradient-to-br, .container > div:first-child, .flex.gap-3, .mt-8 {
            display: none !important;
        }
        
        .bg-white\/5 {
            background: white !important;
        }
        
        .text-white, .text-purple-400, .text-gray-400 {
            color: black !important;
        }
        
        .p-6, .p-8 {
            padding: 0 !important;
        }
    }
</style>

<script>
function downloadScene() {
    // Get the scene content
    const content = document.querySelector('.prose').cloneNode(true);
    const title = "{{ $scene->title }}";
    const seriesName = "{{ $series->project_name }}";
    
    // Create HTML wrapper
    const html = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>${title} - ${seriesName}</title>
            <style>
                body {
                    font-family: 'Georgia', serif;
                    max-width: 800px;
                    margin: 50px auto;
                    padding: 20px;
                    line-height: 1.6;
                }
                h1 { color: #333; }
                h2 { color: #666; }
                .scene-meta { color: #999; margin-bottom: 30px; }
            </style>
        </head>
        <body>
            <h1>${title}</h1>
            <div class="scene-meta">
                <p>Series: ${seriesName}</p>
                <p>Scene {{ $scene->scene_number }} of {{ $series->total_episodes }}</p>
                <p>Generated: {{ $scene->created_at->format('F j, Y') }}</p>
            </div>
            <hr>
            ${content.innerHTML}
        </body>
        </html>
    `;
    
    const blob = new Blob([html], { type: 'text/html' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${title.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.html`;
    link.click();
    URL.revokeObjectURL(link.href);
}

function copyToClipboard() {
    const content = document.querySelector('.prose').innerText;
    navigator.clipboard.writeText(content).then(() => {
        showToast('Scene content copied to clipboard!', 'success');
    }).catch(() => {
        showToast('Failed to copy content', 'error');
    });
}

function regenerateScene() {
    if (confirm('⚠️ Are you sure you want to regenerate this scene?\n\nThis will replace the current content and cannot be undone.')) {
        const btn = event.currentTarget;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Regenerating...';
        btn.disabled = true;
        
        fetch(`/series/{{ $series->id }}/scene/{{ $scene->id }}/regenerate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Scene regenerated successfully! Reloading...', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('Error: ' + data.message, 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        })
        .catch(error => {
            showToast('Error regenerating scene', 'error');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
}

function printScene() {
    window.print();
}

function shareOnTwitter() {
    const text = encodeURIComponent(`Check out "${$scene->title}" from the series "{{ $series->project_name }}"`);
    const url = encodeURIComponent(window.location.href);
    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
}

function shareOnFacebook() {
    const url = encodeURIComponent(window.location.href);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
}

function shareOnLinkedIn() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent("{{ $scene->title }}");
    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}&title=${title}`, '_blank');
}

function copyShareLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        showToast('Link copied to clipboard!', 'success');
    });
}

function showToast(message, type = 'info') {
    // Create toast element if it doesn't exist
    let toast = document.getElementById('customToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'customToast';
        toast.className = 'fixed bottom-8 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-xl text-white font-medium z-50 transition-all duration-300 opacity-0 translate-y-4';
        document.body.appendChild(toast);
    }
    
    // Set styles based on type
    const colors = {
        success: 'bg-green-600',
        error: 'bg-red-600',
        info: 'bg-blue-600'
    };
    
    toast.className = `fixed bottom-8 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-xl text-white font-medium z-50 transition-all duration-300 ${colors[type] || colors.info}`;
    toast.textContent = message;
    
    // Show toast
    setTimeout(() => {
        toast.classList.remove('opacity-0', 'translate-y-4');
        toast.classList.add('opacity-100', 'translate-y-0');
    }, 10);
    
    // Hide toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', 'translate-y-4');
    }, 3000);
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    // Left arrow key - previous scene
    if (e.key === 'ArrowLeft' && {{ $scene->scene_number > 1 ? 'true' : 'false' }}) {
        @php
            $prevScene = $series->scenes->where('scene_number', $scene->scene_number - 1)->first();
        @endphp
        @if($prevScene)
        window.location.href = '{{ route("web-series.scene", [$series->id, $prevScene->id]) }}';
        @endif
    }
    // Right arrow key - next scene
    if (e.key === 'ArrowRight' && {{ $scene->scene_number < $series->total_episodes ? 'true' : 'false' }}) {
        @php
            $nextScene = $series->scenes->where('scene_number', $scene->scene_number + 1)->first();
        @endphp
        @if($nextScene)
        window.location.href = '{{ route("web-series.scene", [$series->id, $nextScene->id]) }}';
        @endif
    }
});
</script>

<style>
/* Ensure proper spacing on mobile */
@media (max-width: 768px) {
    .py-\[120px\] {
        padding-top: 60px !important;
        padding-bottom: 60px !important;
    }
}
</style>

<script>
// Adjust spacing for mobile devices
function adjustSpacing() {
    const container = document.querySelector('.py-\\[120px\\]');
    if (container) {
        if (window.innerWidth <= 768) {
            container.style.paddingTop = '60px';
            container.style.paddingBottom = '60px';
        } else {
            container.style.paddingTop = '120px';
            container.style.paddingBottom = '120px';
        }
    }
}

adjustSpacing();
window.addEventListener('resize', adjustSpacing);
</script>
@endsection