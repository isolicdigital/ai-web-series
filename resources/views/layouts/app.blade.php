{{-- resources/views/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'Dashboard' }} | AI Web Series Studio</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('custom/brand/favicon.png') }}">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #141414;
            font-family: 'Netflix Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            overflow-x: hidden;
        }
        
        /* Netflix-style scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #2d2d2d;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #e50914;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #f6121d;
        }
        
        /* Row scroll animation */
        .row-scroll {
            transition: transform 0.5s ease;
        }
        
        /* Netflix card hover effect */
        .netflix-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .netflix-card:hover {
            transform: scale(1.05);
            z-index: 10;
        }
        
        .netflix-card:hover .card-overlay {
            opacity: 1;
        }
        
        .card-overlay {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        /* Hero section gradient */
        .hero-gradient {
            background: linear-gradient(77deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.6) 50%, rgba(0,0,0,0.2) 100%);
        }
        
        /* Row container */
        .row-container {
            margin-bottom: -60px;
        }
        
        /* Netflix-style buttons */
        .netflix-btn-primary {
            background: #e50914;
            transition: all 0.2s ease;
        }
        
        .netflix-btn-primary:hover {
            background: #f6121d;
            transform: scale(1.05);
        }
        
        .netflix-btn-secondary {
            background: rgba(109, 109, 110, 0.7);
            transition: all 0.2s ease;
        }
        
        .netflix-btn-secondary:hover {
            background: rgba(109, 109, 110, 0.9);
        }
        
        /* Modal animation */
        @keyframes modalFade {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .modal-animate {
            animation: modalFade 0.3s ease-out;
        }
        
        /* Loading skeleton */
        .skeleton {
            background: linear-gradient(90deg, #2d2d2d 25%, #3d3d3d 50%, #2d2d2d 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
    @yield('css')
</head>
<body>

    @include('layouts.navs')
    @yield('content')

    <!-- Demo Modal -->
    <div id="demoModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/95 backdrop-blur-sm" onclick="closeDemoModal()">
        <div class="relative max-w-4xl w-full mx-4 rounded-lg overflow-hidden shadow-2xl" onclick="event.stopPropagation()">
            <button onclick="closeDemoModal()" class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-black/50 hover:bg-black/70 text-white transition flex items-center justify-center">
                <i class="fas fa-times text-xl"></i>
            </button>
            <video id="demoVideo" class="w-full h-auto" controls autoplay>
                <source src="https://assets.mixkit.co/videos/preview/mixkit-film-production-reel-1080.mp4" type="video/mp4">
            </video>
        </div>
    </div>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('bg-black/95');
                navbar.classList.remove('bg-gradient-to-b', 'from-black/90', 'to-transparent');
            } else {
                navbar.classList.remove('bg-black/95');
                navbar.classList.add('bg-gradient-to-b', 'from-black/90', 'to-transparent');
            }
        });
        
        function scrollToCreator() {
            document.getElementById('creatorSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        function openCreatorModal() {
            const modal = document.getElementById('creatorModal');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            generateModalEpisodeFields();
        }
        
        function closeCreatorModal() {
            const modal = document.getElementById('creatorModal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
        
        function generateModalEpisodeFields() {
            const count = parseInt(document.getElementById('modalEpisodeCount').value) || 5;
            const container = document.getElementById('modalEpisodesContainer');
            container.innerHTML = '';
            
            for (let i = 1; i <= count; i++) {
                container.innerHTML += `
                    <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700">
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-red-600 flex items-center justify-center text-white font-bold text-sm">${i}</div>
                            <div class="flex-1">
                                <input type="text" name="episodes[${i}][title]" required
                                       class="w-full px-3 py-1.5 bg-gray-700 border border-gray-600 rounded-md text-white text-sm focus:outline-none focus:border-red-500 mb-2"
                                       placeholder="Episode ${i} Title">
                                <textarea name="episodes[${i}][description]" rows="2"
                                          class="w-full px-3 py-1.5 bg-gray-700 border border-gray-600 rounded-md text-white text-sm focus:outline-none focus:border-red-500"
                                          placeholder="Episode ${i} Description"></textarea>
                                <input type="hidden" name="episodes[${i}][episode_number]" value="${i}">
                            </div>
                        </div>
                    </div>
                `;
            }
        }
        
        document.getElementById('modalEpisodeCount')?.addEventListener('change', generateModalEpisodeFields);
        
        function watchDemo() {
            const modal = document.getElementById('demoModal');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            const video = document.getElementById('demoVideo');
            video.play();
        }
        
        function closeDemoModal() {
            const modal = document.getElementById('demoModal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
            const video = document.getElementById('demoVideo');
            if (video) {
                video.pause();
                video.currentTime = 0;
            }
        }
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCreatorModal();
                closeDemoModal();
            }
        });
        
        // Generate episodes on page load for main form
        function generateMainEpisodeFields() {
            const count = parseInt(document.getElementById('episodeCount')?.value) || 5;
            const container = document.getElementById('episodesContainer');
            if (!container) return;
            
            container.innerHTML = '';
            
            for (let i = 1; i <= count; i++) {
                container.innerHTML += `
                    <div class="bg-purple-900/20 rounded-xl p-4 mb-4 border border-purple-500/30">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-pink-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-lg flex-shrink-0">
                                ${i}
                            </div>
                            <div class="flex-1">
                                <label class="block text-pink-300 text-sm font-medium mb-1">Episode ${i} Title</label>
                                <input type="text" name="episodes[${i}][title]" required
                                       class="w-full px-4 py-2 bg-[#0d0820] border border-purple-500/40 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 transition mb-3" 
                                       placeholder="Enter episode title...">
                                <label class="block text-pink-300 text-sm font-medium mb-1">Episode ${i} Description</label>
                                <textarea name="episodes[${i}][description]" rows="2"
                                          class="w-full px-4 py-2 bg-[#0d0820] border border-purple-500/40 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 transition"
                                          placeholder="Brief description for episode ${i}..."></textarea>
                                <input type="hidden" name="episodes[${i}][episode_number]" value="${i}">
                            </div>
                        </div>
                    </div>
                `;
            }
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            const episodeCount = document.getElementById('episodeCount');
            if (episodeCount) {
                episodeCount.addEventListener('change', generateMainEpisodeFields);
                generateMainEpisodeFields();
            }
        });
    </script>
</body>
</html>