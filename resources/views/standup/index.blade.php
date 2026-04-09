<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Comedy Studio | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('custom/css/comedy.css') }}">
</head>
<body>
    <div class="dashboard">
        <!-- Phase A: Hero Section -->
        <div class="hero-section">
            <div class="hero-bg"></div>
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h1 class="hero-title">Write Your Joke</h1>
                <p class="hero-subtitle">Describe what you want to joke about</p>
                <div class="search-bar">
                    <div class="search-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <input type="text" id="jokePrompt" placeholder="e.g., dating fails, office life, technology problems..." class="search-input">
                    <button class="generate-btn" id="generateJokeBtn">Generate Joke</button>
                </div>
            </div>
        </div>

        <!-- Phase B: Comedy Categories -->
        <div class="nav-icons">
            @foreach($categories as $category)
            <div class="icon-item" data-category="{{ $category->slug }}">
                <div class="icon-circle">
                    <i class="{{ $category->icon ?? 'fas fa-microphone-alt' }}" style="font-size: 24px;"></i>
                </div>
                <span>{{ $category->name }}</span>
            </div>
            @endforeach
        </div>

        <!-- Generated Joke Display -->
        <div class="joke-display" id="jokeDisplay" style="display: none;">
            <div class="joke-card">
                <div class="joke-header">
                    <i class="fas fa-microphone-alt"></i>
                    <h3>Your AI Generated Joke</h3>
                </div>
                <div class="joke-content" id="jokeText"></div>
                <div class="joke-actions">
                    <button class="action-btn regenerate" id="regenerateBtn">
                        <i class="fas fa-sync-alt"></i> Regenerate
                    </button>
                    <button class="action-btn create-video" id="createVideoBtn">
                        <i class="fas fa-video"></i> Create Video
                    </button>
                    <button class="action-btn copy-joke" id="copyJokeBtn">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div class="loading-spinner" id="loadingSpinner" style="display: none;">
            <div class="spinner"></div>
            <p>Crafting your joke...</p>
        </div>

        <!-- Featured Templates Section -->
        <div class="featured-section">
            <div class="section-header">
                <h2>Popular Comedy Templates</h2>
                <a href="{{ route('comedy.templates') }}" class="view-all">View all →</a>
            </div>
            <div class="templates-grid">
                @foreach($featuredTemplates ?? [] as $template)
                <div class="template-card">
                    <div class="card-image" style="background-image: url('{{ asset($template->preview_image) }}')"></div>
                    <div class="card-gradient"></div>
                    <div class="card-content">
                        <h3>{{ $template->name }}</h3>
                        <p>{{ $template->description }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Videos Section -->
        @if(isset($recentVideos) && $recentVideos->count() > 0)
        <div class="recent-section">
            <div class="section-header">
                <h2>Your Recent Videos</h2>
                <a href="{{ route('comedy.videos') }}" class="view-all">View all →</a>
            </div>
            <div class="videos-grid">
                @foreach($recentVideos as $video)
                <div class="video-card">
                    <div class="video-thumbnail">
                        <img src="{{ asset($video->thumbnail_url) }}" alt="{{ $video->title }}">
                        <div class="video-overlay">
                            <button class="play-btn">
                                <i class="fas fa-play"></i>
                            </button>
                        </div>
                    </div>
                    <div class="video-info">
                        <h4>{{ $video->title }}</h4>
                        <span class="views">{{ number_format($video->view_count) }} views</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const generateBtn = document.getElementById('generateJokeBtn');
            const promptInput = document.getElementById('jokePrompt');
            const jokeDisplay = document.getElementById('jokeDisplay');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const jokeText = document.getElementById('jokeText');
            const regenerateBtn = document.getElementById('regenerateBtn');
            const createVideoBtn = document.getElementById('createVideoBtn');
            const copyJokeBtn = document.getElementById('copyJokeBtn');
            
            let currentJoke = '';
            let currentPrompt = '';
            
            async function generateJoke(prompt) {
                if (!prompt.trim()) {
                    alert('Please enter a topic for your joke');
                    return;
                }
                
                loadingSpinner.style.display = 'flex';
                jokeDisplay.style.display = 'none';
                
                try {
                    const response = await fetch('{{ route("comedy.generate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ prompt: prompt })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        currentJoke = data.joke;
                        currentPrompt = prompt;
                        jokeText.innerHTML = currentJoke.replace(/\n/g, '<br>');
                        jokeDisplay.style.display = 'block';
                    } else {
                        alert(data.message || 'Failed to generate joke');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Something went wrong. Please try again.');
                } finally {
                    loadingSpinner.style.display = 'none';
                }
            }
            
            generateBtn.addEventListener('click', () => {
                generateJoke(promptInput.value);
            });
            
            promptInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    generateJoke(promptInput.value);
                }
            });
            
            regenerateBtn.addEventListener('click', () => {
                generateJoke(currentPrompt);
            });
            
            copyJokeBtn.addEventListener('click', () => {
                navigator.clipboard.writeText(currentJoke);
                alert('Joke copied to clipboard!');
            });
            
            createVideoBtn.addEventListener('click', () => {
                window.location.href = '{{ route("comedy.create-video") }}?joke=' + encodeURIComponent(currentJoke);
            });
        });
    </script>
</body>
</html>