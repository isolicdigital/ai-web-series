@extends('layouts.app')

@section('css')
    <!-- Sweet Alert CSS -->
    <link href="{{URL::asset('plugins/sweetalert/sweetalert2.min.css')}}" rel="stylesheet" />
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-black to-gray-900 py-24 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 mb-6 shadow-lg">
                <i class="fas fa-music text-white text-2xl"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent mb-4">
                AI Audio Generator
            </h1>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Elevate Your Films with AI-Composed Audio Tailored to Every Scene!
            </p>
        </div>
        
        <!-- Search Form -->
        <div class="max-w-4xl mx-auto mb-12">
            <form id="openai-form" action="" method="get">
                @csrf
                <div class="relative">
                    <div class="flex flex-col md:flex-row gap-3">
                        <div class="flex-1">
                            <input type="text" 
                                   class="w-full px-6 py-4 bg-gray-800/50 border border-gray-700 rounded-xl text-white placeholder-gray-400 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" 
                                   id="prompt" 
                                   name="prompt" 
                                   placeholder="Describe the audio you want to generate..." 
                                   value="{{ isset($prompt) ? $prompt : '' }}" 
                                   required>
                        </div>
                        <button type="submit" 
                                id="audio-generate" 
                                class="px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl text-white font-semibold transition-all duration-300 flex items-center justify-center gap-2 shadow-lg hover:shadow-pink-500/25 transform hover:scale-105">
                            <i class="fas fa-wand-magic"></i>
                            <span>Generate Audio</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Audio Grid -->
        <div class="mt-12">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                        <i class="fas fa-music text-purple-400"></i>
                        Generated Audio
                    </h2>
                    <p class="text-gray-500 text-sm mt-1">Your AI-generated audio tracks</p>
                </div>
                <div class="text-sm text-gray-500">
                    {{ sizeof($audios) }} track(s) generated
                </div>
            </div>
            
            @if(sizeof($audios) == 0)
            <!-- Empty State -->
            <div class="text-center py-20">
                <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-gradient-to-br from-purple-600/20 to-pink-600/20 flex items-center justify-center">
                    <i class="fas fa-music-slash text-purple-400 text-5xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-3">No Audio Generated Yet</h3>
                <p class="text-gray-400 mb-6 max-w-md mx-auto">Start creating amazing audio tracks by describing what you need above</p>
                <div class="inline-flex items-center gap-2 text-purple-400">
                    <i class="fas fa-arrow-up"></i>
                    <span class="text-sm">Enter a prompt and click Generate</span>
                </div>
            </div>
            @else
            <!-- Audio Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($audios as $index => $aud)
                <div class="group bg-gradient-to-br from-gray-900/80 to-gray-800/40 backdrop-blur-lg rounded-2xl border border-gray-700/50 hover:border-purple-500/50 transition-all duration-500 overflow-hidden hover:shadow-2xl hover:shadow-purple-500/10 hover:-translate-y-1 cursor-pointer" onclick="toggleAudioPlayer(this)">
                    
                    <!-- Audio Visualizer / Cover -->
                    <div class="relative h-48 overflow-hidden bg-gradient-to-br from-purple-600/20 to-pink-600/20">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center transform group-hover:scale-110 transition-transform duration-500">
                                <div class="w-20 h-20 mx-auto rounded-2xl bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center shadow-lg">
                                    <i class="fas fa-music text-white text-3xl"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Animated bars on hover -->
                        <div class="absolute inset-0 flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="w-1 h-8 bg-purple-400 rounded-full animate-pulse" style="animation-delay: 0s"></div>
                            <div class="w-1 h-12 bg-pink-400 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                            <div class="w-1 h-6 bg-purple-400 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                            <div class="w-1 h-10 bg-pink-400 rounded-full animate-pulse" style="animation-delay: 0.6s"></div>
                            <div class="w-1 h-8 bg-purple-400 rounded-full animate-pulse" style="animation-delay: 0.8s"></div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="absolute top-3 right-3 flex gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <a href="{{$aud['url']}}" download class="w-9 h-9 rounded-full bg-black/60 backdrop-blur-sm flex items-center justify-center hover:bg-purple-600 transition-all duration-300 transform hover:scale-110">
                                <i class="fas fa-download text-white text-sm"></i>
                            </a>
                            <button class="play-btn w-9 h-9 rounded-full bg-black/60 backdrop-blur-sm flex items-center justify-center hover:bg-purple-600 transition-all duration-300 transform hover:scale-110" data-url="{{$aud['url']}}" data-title="{{ $aud['title'] }}">
                                <i class="fas fa-play text-white text-sm"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Card Content -->
                    <div class="p-4">
                        <h4 class="text-white font-semibold text-sm mb-2 line-clamp-1 group-hover:text-purple-300 transition">
                            {{ Str::limit($aud['title'], 50) }}
                        </h4>
                        
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs px-2 py-1 rounded-full bg-purple-500/20 text-purple-300">
                                <i class="fas fa-robot mr-1 text-[10px]"></i>
                                AI Generated
                            </span>
                            <span class="text-xs text-green-400 flex items-center gap-1">
                                <i class="fas fa-check-circle text-[10px]"></i>
                                Ready
                            </span>
                        </div>
                        
                        <!-- Hidden Audio Player -->
                        <div class="audio-player hidden mt-3">
                            <audio controls class="w-full h-10 rounded-lg">
                                <source src="{{$aud['url']}}" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                        </div>
                        
                        <div class="flex items-center justify-between text-xs text-gray-500 mt-3 pt-2 border-t border-gray-800/50">
                            <div class="flex items-center gap-1">
                                <i class="fas fa-calendar-alt text-[10px]"></i>
                                <span>{{ \Carbon\Carbon::now()->format('M d, Y') }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="fas fa-clock text-[10px]"></i>
                                <span>{{ \Carbon\Carbon::now()->format('h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Audio Player Modal -->
<div id="audioModal" class="fixed inset-0 bg-black/95 backdrop-blur-xl z-50 hidden items-center justify-center p-4 transition-all duration-300">
    <div class="relative w-full max-w-md bg-gradient-to-br from-gray-900 to-black rounded-2xl border border-purple-500/30 overflow-hidden shadow-2xl shadow-purple-500/20">
        
        <!-- Modal Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-700 bg-gradient-to-r from-gray-800/50 to-gray-900/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                    <i class="fas fa-music text-white"></i>
                </div>
                <div>
                    <h3 class="text-white font-semibold text-sm" id="modalAudioTitle">Audio Preview</h3>
                    <p class="text-gray-400 text-xs">AI Generated Audio</p>
                </div>
            </div>
            <button onclick="closeAudioModal()" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6">
            <div class="bg-gradient-to-br from-purple-600/10 to-pink-600/10 rounded-xl p-6">
                <div class="text-center mb-4">
                    <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center shadow-lg mb-3">
                        <i class="fas fa-headphones text-white text-2xl"></i>
                    </div>
                    <div class="flex justify-center gap-1 mt-2">
                        <div class="w-1 h-6 bg-purple-400 rounded-full animate-pulse" style="animation-delay: 0s"></div>
                        <div class="w-1 h-8 bg-pink-400 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                        <div class="w-1 h-5 bg-purple-400 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                        <div class="w-1 h-7 bg-pink-400 rounded-full animate-pulse" style="animation-delay: 0.6s"></div>
                        <div class="w-1 h-6 bg-purple-400 rounded-full animate-pulse" style="animation-delay: 0.8s"></div>
                    </div>
                </div>
                <audio id="modalAudioPlayer" controls class="w-full rounded-lg">
                    <source src="" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-gray-700 bg-gradient-to-r from-gray-800/50 to-gray-900/50 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></div>
                <span class="text-xs text-gray-400">Ready to play</span>
            </div>
            <button onclick="downloadCurrentAudio()" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-lg text-white text-sm font-medium transition-all duration-300 flex items-center gap-2">
                <i class="fas fa-download"></i>
                Download
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes pulse {
        0%, 100% { transform: scaleY(1); }
        50% { transform: scaleY(1.5); }
    }
    
    .animate-pulse {
        animation: pulse 1s ease-in-out infinite;
    }
    
    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
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
    
    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(to bottom, #7c3aed, #db2777);
    }
</style>

<script src="{{URL::asset('plugins/sweetalert/sweetalert2.all.min.js')}}"></script>
<script>
let currentAudioUrl = null;
let currentAudioTitle = null;

// Form submission
document.getElementById('openai-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const generateBtn = document.getElementById('audio-generate');
    const originalText = generateBtn.innerHTML;
    generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating...';
    generateBtn.disabled = true;
    
    this.submit();
});

// Toggle audio player on card click
function toggleAudioPlayer(card) {
    const audioPlayer = card.querySelector('.audio-player');
    const playIcon = card.querySelector('.play-btn i');
    
    if (audioPlayer.classList.contains('hidden')) {
        // Close all other players
        document.querySelectorAll('.audio-player').forEach(player => {
            if (player !== audioPlayer && !player.classList.contains('hidden')) {
                player.classList.add('hidden');
                const otherAudio = player.querySelector('audio');
                if (otherAudio) otherAudio.pause();
            }
        });
        
        audioPlayer.classList.remove('hidden');
        const audio = audioPlayer.querySelector('audio');
        if (audio) audio.play();
    } else {
        audioPlayer.classList.add('hidden');
        const audio = audioPlayer.querySelector('audio');
        if (audio) audio.pause();
    }
}

// Play audio in modal
document.querySelectorAll('.play-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        const audioUrl = this.getAttribute('data-url');
        const audioTitle = this.getAttribute('data-title') || 'Audio Track';
        
        currentAudioUrl = audioUrl;
        currentAudioTitle = audioTitle;
        
        document.getElementById('modalAudioTitle').textContent = audioTitle;
        const modalAudio = document.getElementById('modalAudioPlayer');
        modalAudio.src = audioUrl;
        modalAudio.load();
        modalAudio.play();
        
        document.getElementById('audioModal').classList.remove('hidden');
        document.getElementById('audioModal').classList.add('flex');
    });
});

function closeAudioModal() {
    const modal = document.getElementById('audioModal');
    const audio = document.getElementById('modalAudioPlayer');
    audio.pause();
    audio.src = '';
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function downloadCurrentAudio() {
    if (currentAudioUrl) {
        const a = document.createElement('a');
        a.href = currentAudioUrl;
        a.download = currentAudioTitle?.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.mp3' || 'audio.mp3';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        
        Swal.fire({
            title: 'Download Started!',
            text: 'Your audio is being downloaded.',
            icon: 'success',
            confirmButtonColor: '#8b5cf6',
            background: '#1a1a1a',
            color: '#fff',
            timer: 2000,
            showConfirmButton: false
        });
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAudioModal();
    }
});

// Close modal on outside click
document.getElementById('audioModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAudioModal();
    }
});
</script>
@endsection