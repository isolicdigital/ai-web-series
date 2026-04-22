<?php
// app/Http/Controllers/DemoController.php

namespace App\Http\Controllers;

use App\Models\WebSeries;
use App\Models\Episode;
use App\Models\Scene;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DemoController extends Controller
{
    /**
     * Check if current user is demo user
     */
    private function isDemoUser()
    {
        return Auth::check() && Auth::user()->demo_mode == true;
    }

    /**
     * Get current user ID
     */
    private function getUserId()
    {
        return Auth::id();
    }
      public function getDashboardStats()
    {
        Log::info('DemoController: getDashboardStats called');
        
        return response()->json([
            'success' => true,
            'stats' => [
                'total_series' => 3,
                'total_episodes' => 15,
                'available_credits' => 999,
                'completion_rate' => 75,
                'completed_series' => 2
            ]
        ]);
    }

    /**
     * Get pre-defined demo concept based on prompt (no API call)
     * 3 Main Concepts: Cat & Dog, Dog & Cat, Space Adventure
     */
    public function getDemoConcept($prompt = null)
    {
        // 3 Main Concepts
        $concepts = [
            'power, desire' => "In the elite and glamorous underworld of San Francisco, SHADOW DEAL follows four lives forever bound by power, desire, loyalty, and betrayal. Peter is a mysterious and dangerous man who moves through the shadows like he owns them — calculating, fearless, and always one step ahead. But tonight, someone is watching him. Lizz is a stunning and wealthy woman who appears to have everything the world can offer. Yet she sits alone in her luxury penthouse, waiting for one message, one call, that will shatter everything she thought she knew. Jeff is the sharpest mind in the room — the only one who sees through the beautiful lies and asks the questions no one else dares to ask. And Jenn has seen too much, knows too much — and in this world, that makes her the most dangerous person of all. In SHADOW DEAL, greed and love look exactly the same. Loyalty and betrayal wear the same face. And when the truth finally rises from the shadows, no one will be safe, no one will be ready, and nothing will ever be the same again.",
            
            'dog and cat' => "In the elite and glamorous underworld of San Francisco, SHADOW DEAL follows four lives forever bound by power, desire, loyalty, and betrayal. Peter is a mysterious and dangerous man who moves through the shadows like he owns them — calculating, fearless, and always one step ahead. But tonight, someone is watching him. Lizz is a stunning and wealthy woman who appears to have everything the world can offer. Yet she sits alone in her luxury penthouse, waiting for one message, one call, that will shatter everything she thought she knew. Jeff is the sharpest mind in the room — the only one who sees through the beautiful lies and asks the questions no one else dares to ask. And Jenn has seen too much, knows too much — and in this world, that makes her the most dangerous person of all. In SHADOW DEAL, greed and love look exactly the same. Loyalty and betrayal wear the same face. And when the truth finally rises from the shadows, no one will be safe, no one will be ready, and nothing will ever be the same again.",
            
            'space' => "In the elite and glamorous underworld of San Francisco, SHADOW DEAL follows four lives forever bound by power, desire, loyalty, and betrayal. Peter is a mysterious and dangerous man who moves through the shadows like he owns them — calculating, fearless, and always one step ahead. But tonight, someone is watching him. Lizz is a stunning and wealthy woman who appears to have everything the world can offer. Yet she sits alone in her luxury penthouse, waiting for one message, one call, that will shatter everything she thought she knew. Jeff is the sharpest mind in the room — the only one who sees through the beautiful lies and asks the questions no one else dares to ask. And Jenn has seen too much, knows too much — and in this world, that makes her the most dangerous person of all. In SHADOW DEAL, greed and love look exactly the same. Loyalty and betrayal wear the same face. And when the truth finally rises from the shadows, no one will be safe, no one will be ready, and nothing will ever be the same again."
        ];
        
        // If prompt is provided, try to match with keywords
        if ($prompt && !empty($prompt)) {
            $promptLower = strtolower($prompt);
            
            // Cat and Dog keywords
            $catDogKeywords = ['cat', 'dog', 'cyber', 'neon', 'tech', 'robot', 'ai', 'future', 'hacker', 'digital', 'code'];
            foreach ($catDogKeywords as $keyword) {
                if (strpos($promptLower, $keyword) !== false) {
                    return $concepts['cat and dog'];
                }
            }
            
            // Dog and Cat keywords (Fantasy)
            $fantasyKeywords = ['magic', 'dragon', 'elf', 'kingdom', 'sword', 'wizard', 'fantasy', 'medieval', 'castle'];
            foreach ($fantasyKeywords as $keyword) {
                if (strpos($promptLower, $keyword) !== false) {
                    return $concepts['dog and cat'];
                }
            }
            
            // Space keywords
            $spaceKeywords = ['space', 'star', 'galaxy', 'alien', 'planet', 'cosmic', 'mars', 'moon', 'solar'];
            foreach ($spaceKeywords as $keyword) {
                if (strpos($promptLower, $keyword) !== false) {
                    return $concepts['space'];
                }
            }
        }
        
        // Return random concept if no match
        $randomConcept = $concepts[array_rand($concepts)];
        return $randomConcept;
    }

    /**
     * Get concept title based on prompt
     */
    public function getConceptTitle($prompt = null)
    {
        $titles = [
            'cat and dog' => 'The elite and glamorous underworld of San Francisco',
            'dog and cat' => 'The Dragon Riders of Aethelgard',
            'space' => 'Horizon: The Final Frontier'
        ];
        
        if ($prompt && !empty($prompt)) {
            $promptLower = strtolower($prompt);
            
            $catDogKeywords = ['cat', 'dog', 'cyber', 'neon', 'tech', 'robot', 'ai', 'hacker', 'digital'];
            foreach ($catDogKeywords as $keyword) {
                if (strpos($promptLower, $keyword) !== false) {
                    return $titles['cat and dog'];
                }
            }
            
            $fantasyKeywords = ['magic', 'dragon', 'elf', 'kingdom', 'sword', 'wizard'];
            foreach ($fantasyKeywords as $keyword) {
                if (strpos($promptLower, $keyword) !== false) {
                    return $titles['dog and cat'];
                }
            }
            
            $spaceKeywords = ['space', 'star', 'galaxy', 'alien', 'planet', 'mars'];
            foreach ($spaceKeywords as $keyword) {
                if (strpos($promptLower, $keyword) !== false) {
                    return $titles['space'];
                }
            }
        }
        
        return $titles[array_rand($titles)];
    }

    /**
     * Get demo scenes data based on theme
     */
    public function getDemoScenesData($episodeId, $seriesId, $totalScenes = 5, $theme = null)
    {
        // Detect theme from series if not provided
        if (!$theme) {
            $series = WebSeries::find($seriesId);
            $concept = $series ? $series->concept : '';
            $theme = $this->detectThemeFromConcept($concept);
        }
        
        // Scene sets for different themes
        $sceneSets = [
            'power, desire' => [
                1 => [
                    'title' => 'Where Loyalty Dies in the Dark',
                    'description' => 'K9-7 patrols the bustling neon marketplace, his cybernetic eyes scanning for threats. Holographic advertisements flicker above as drones zip through the crowded streets.',
                    'image_prompt' => 'A futuristic neon-lit marketplace in Neo-Tokyo with holographic advertisements and flying drones, cyberpunk aesthetic, purple and pink neon lights',
                    'image_url' => '/demo/images/image-1.png',
                    'video_url' => '/demo/video/sample1.mp4'
                ],
                2 => [
                    'title' => 'Power, Lies, and the Price of Knowing Too Much',
                    'description' => 'A thrilling chase across the skyline as K9-7 pursues Whisk3r. They leap between buildings, dodging holographic billboards and security drones.',
                    'image_prompt' => 'A cybernetic wolfhound chasing a feline hacker across neon-lit rooftops with city lights below, dynamic action pose, cinematic lighting',
                    'image_url' => '/demo/images/image-2.png',
                    'video_url' => '/demo/video/sample2.mp4'
                ],
                3 => [
                    'title' => 'In a World of Secrets, Trust Is Fatal',
                    'description' => 'Whisk3r reveals her hidden underground lair filled with advanced hacking equipment. Monitors display surveillance feeds from across the city.',
                    'image_prompt' => 'A secret underground hacker lair filled with monitors and advanced technology, cyberpunk aesthetic, blue neon lighting',
                    'image_url' => '/demo/images/image-3.png',
                    'video_url' => '/demo/video/sample3.mp4'
                ],
                4 => [
                    'title' => 'Everyone Has Something to Lose',
                    'description' => 'Unlikely allies join forces. K9-7 and Whisk3r shake hands, agreeing to work together to stop the rogue AI threatening their city.',
                    'image_prompt' => 'A cybernetic wolfhound and a feline hacker shaking hands in a neon-lit room, partnership moment, warm lighting',
                    'image_url' => '/demo/images/image-4.png',
                    'video_url' => '/demo/video/sample4.mp4'
                ],
                5 => [
                    'title' => 'When Truth Comes Out, Blood Follows',
                    'description' => 'The ultimate battle against the rogue AI. K9-7 and Whisk3r combine their skills to upload the virus and save Neo-Tokyo from digital destruction.',
                    'image_prompt' => 'Epic battle scene with neon lights and digital effects against a rogue AI, heroic pose, dramatic lighting',
                    'image_url' => '/demo/images/image-5.png',
                    'video_url' => '/demo/video/sample5.mp4'
                ]
            ],
            'dog and cat' => [
                1 => [
                    'title' => 'The Prophecy Revealed',
                    'description' => 'In the ancient library of Aethelgard, Elara discovers a dusty scroll that reveals her true destiny as the last Dragon Rider.',
                    'image_prompt' => 'A young woman discovering an ancient prophecy scroll in a mystical library, magical lighting, fantasy art style',
                    'image_url' => '/demo/images/image-1.png',
                    'video_url' => '/demo/video/sample1.mp4'
                ],
                2 => [
                    'title' => 'The Dragon\'s Egg',
                    'description' => 'Deep within the Crystal Caves, Elara finds a glowing dragon egg that hatches, revealing her companion Ember.',
                    'image_prompt' => 'A glowing dragon egg hatching in a crystal cave, magical energy surrounding it, fantasy artwork',
                    'image_url' => '/demo/images//image-2.png',
                    'video_url' => '/demo/video/sample2.mp4'
                ],
                3 => [
                    'title' => 'The Shadow Lord\'s Attack',
                    'description' => 'Dark forces attack the kingdom, forcing Elara and Ember to flee with the help of a rogue knight.',
                    'image_prompt' => 'Dark shadow creatures attacking a fantasy kingdom, epic battle scene, dramatic lighting',
                    'image_url' => '/demo/images/image-3.png',
                    'video_url' => '/demo/video/sample3.mp4'
                ],
                4 => [
                    'title' => 'The Unlikely Alliance',
                    'description' => 'Elara, Ember, Sir Cedric the knight, and Princess Aria the elf join forces to defeat the Shadow Lord.',
                    'image_prompt' => 'A diverse group of fantasy heroes standing together, determined expressions, epic fantasy art',
                    'image_url' => '/demo/images/image-4.png',
                    'video_url' => '/demo/video/sample4.mp4'
                ],
                5 => [
                    'title' => 'The Final Battle',
                    'description' => 'The ultimate showdown against the Shadow Lord. Elara and Ember unleash the ancient Dragon Riders\' power.',
                    'image_prompt' => 'Epic final battle between dragon riders and dark lord, magical explosions, heroic fantasy art',
                    'image_url' => '/demo/images/image-5.png',
                    'video_url' => '/demo/video/sample5.mp4'
                ]
            ],
            'space' => [
                1 => [
                    'title' => 'The Discovery',
                    'description' => 'On a routine mission to Mars, Captain Drake and his crew discover a mysterious alien artifact buried beneath the surface.',
                    'image_prompt' => 'Astronauts discovering an alien artifact on Mars, dramatic lighting, sci-fi aesthetic',
                    'image_url' => '/demo/images/images/image-1.png',
                    'video_url' => '/demo/video/sample1.mp4'
                ],
                2 => [
                    'title' => 'The Countdown Begins',
                    'description' => 'The artifact activates, triggering a countdown that could destroy the solar system. The crew must act fast.',
                    'image_prompt' => 'A glowing alien artifact with holographic countdown display, tense atmosphere, sci-fi',
                    'image_url' => '/demo/images/image-2.png',
                    'video_url' => '/demo/video/sample2.mp4'
                ],
                3 => [
                    'title' => 'Through the Asteroid Field',
                    'description' => 'The Horizon navigates through a dangerous asteroid field to reach the ancient alien temple on Jupiter\'s moon.',
                    'image_prompt' => 'Spaceship flying through dangerous asteroid field, epic space scene, cinematic lighting',
                    'image_url' => '/demo/images/image-3.png',
                    'video_url' => '/demo/video/sample3.mp4'
                ],
                4 => [
                    'title' => 'The Alien Revelation',
                    'description' => 'Inside the temple, the crew discovers the true purpose of the artifact - it\'s a test for humanity.',
                    'image_prompt' => 'Ancient alien temple interior with mysterious technology, atmospheric lighting, sci-fi',
                    'image_url' => '/demo/images/image-4.png',
                    'video_url' => '/demo/video/sample4.mp4'
                ],
                5 => [
                    'title' => 'The Ultimate Sacrifice',
                    'description' => 'Captain Drake makes the ultimate sacrifice to save humanity, becoming a legend among the stars.',
                    'image_prompt' => 'Heroic captain making sacrifice to save Earth, dramatic space scene, emotional lighting',
                    'image_url' => '/demo/images/image-5.png',
                    'video_url' => '/demo/video/sample5.mp4'
                ]
            ]
        ];
        
        // Default scene set
        $defaultScenes = [
            1 => [
                'title' => 'The Beginning',
                'description' => 'Our hero embarks on an epic journey that will change their life forever.',
                'image_prompt' => 'A hero beginning their journey, dramatic sunrise, cinematic composition',
                'image_url' => '/demo/images/image-1.png',
                'video_url' => '/demo/video/sample1.mp4'
            ],
            2 => [
                'title' => 'The Challenge',
                'description' => 'Obstacles arise that test our hero\'s courage and determination.',
                'image_prompt' => 'Hero facing a difficult challenge, intense moment, dramatic lighting',
                'image_url' => '/demo/images/image-2.png',
                'video_url' => '/demo/video/sample2.mp4'
            ],
            3 => [
                'title' => 'The Turning Point',
                'description' => 'Everything changes in this pivotal moment of the story.',
                'image_prompt' => 'A dramatic turning point moment, emotional scene, cinematic quality',
                'image_url' => '/demo/images/image-3.png',
                'video_url' => '/demo/video/sample3.mp4'
            ],
            4 => [
                'title' => 'The Triumph',
                'description' => 'Our hero overcomes the odds and achieves victory.',
                'image_prompt' => 'Hero achieving victory, triumphant moment, glorious lighting',
                'image_url' => '/demo/images/image-4.png',
                'video_url' => '/demo/video/sample4.mp4'
            ],
            5 => [
                'title' => 'The New Beginning',
                'description' => 'A new chapter begins as the story reaches its conclusion.',
                'image_prompt' => 'A new beginning, hopeful scene, beautiful sunset',
                'image_url' => '/demo/images/image-5.png',
                'video_url' => '/demo/video/sample5.mp4'
            ]
        ];
        
        // Select scene set based on theme
        $selectedScenes = $sceneSets[$theme] ?? $defaultScenes;
        
        $scenes = [];
        $concept = $this->getDemoConcept();
        
        for ($i = 1; $i <= $totalScenes; $i++) {
            $sceneData = $selectedScenes[$i] ?? $selectedScenes[1];
            $content = '<div class="scene-content">
                <h3 class="text-purple-400 text-xl font-bold mb-3">' . htmlspecialchars($sceneData['title']) . '</h3>
                <p><strong>Episode 1 - Scene ' . $i . '</strong></p>
                <p><strong>What happens:</strong> ' . htmlspecialchars($sceneData['description']) . '</p>
                <p><strong>Story Concept:</strong> ' . htmlspecialchars(substr($concept, 0, 200)) . '...</p>
            </div>';
            
            $scenes[] = [
                'episode_id' => $episodeId,
                'web_series_id' => $seriesId,
                'scene_number' => $i,
                'title' => $sceneData['title'],
                'content' => $content,
                'image_prompt' => $sceneData['image_prompt'],
                'generated_image_url' => $sceneData['image_url'],
                'video_url' => $sceneData['video_url'],
                'summary' => substr($sceneData['description'], 0, 150),
                'status' => 'completed'
            ];
        }
        
        return $scenes;
    }

    /**
     * Detect theme from concept text
     */
    private function detectThemeFromConcept($concept)
    {
        if (empty($concept)) {
            return 'default';
        }
        
        $conceptLower = strtolower($concept);
        
        if (strpos($conceptLower, 'neon') !== false || strpos($conceptLower, 'cyber') !== false || strpos($conceptLower, 'neo-tokyo') !== false) {
            return 'cat and dog';
        }
        
        if (strpos($conceptLower, 'dragon') !== false || strpos($conceptLower, 'aethelgard') !== false || strpos($conceptLower, 'magic') !== false) {
            return 'dog and cat';
        }
        
        if (strpos($conceptLower, 'space') !== false || strpos($conceptLower, 'starship') !== false || strpos($conceptLower, 'mars') !== false) {
            return 'space';
        }
        
        return 'default';
    }

    /**
     * Get pre-defined demo image
     */
    public function getDemoImage($sceneId)
    {
        $demoImages = [
            1 => 'https://placehold.co/1024x1024/1a1a1a/8b5cf6?text=Scene+1',
            2 => 'https://placehold.co/1024x1024/1a1a1a/ec4899?text=Scene+2',
            3 => 'https://placehold.co/1024x1024/1a1a1a/06b6d4?text=Scene+3',
            4 => 'https://placehold.co/1024x1024/1a1a1a/10b981?text=Scene+4',
            5 => 'https://placehold.co/1024x1024/1a1a1a/ef4444?text=Scene+5'
        ];
        
        return $demoImages[$sceneId] ?? $demoImages[1];
    }

    /**
     * Get pre-defined demo video for a specific episode from local storage
     */
    public function getDemoVideo($episodeNumber)
{
    $videoPath = 'demo/video/episode_1.mp4';
    $fullPath = public_path($videoPath);
    
    // Check if video exists
    if (!file_exists($fullPath)) {
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Demo video not found.'
            ], 404);
        }
        abort(404, 'Demo video not found');
    }
    
    $videoUrl = asset($videoPath);
    
    // For AJAX requests
    if (request()->wantsJson() || request()->ajax()) {
        return response()->json([
            'success' => true,
            'video_url' => $videoUrl,
            'episode_number' => $episodeNumber
        ]);
    }
    
    // For direct access
    return response()->file($fullPath, [
        'Content-Type' => 'video/mp4',
        'Content-Disposition' => 'inline'
    ]);
}

    /**
     * Create demo series for demo user
     */
    public function createDemoSeries()
    {
        if (!$this->isDemoUser()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $userId = $this->getUserId();
        
        // Delete existing demo series
        WebSeries::where('user_id', $userId)->delete();
        
        // Get or create category
        $category = Category::firstOrCreate(
            ['name' => 'Action'],
            [
                'icon' => 'fa-fist-raised',
                'description' => 'High-octane action sequences',
                'is_active' => true,
                'display_order' => 1
            ]
        );

        // Create demo series with random concept
        $concept = $this->getDemoConcept();
        $title = $this->getConceptTitle();
        
        $series = WebSeries::create([
            'user_id' => $userId,
            'category_id' => $category->id,
            'project_name' => $title,
            'concept' => $concept,
            'status' => 'completed',
            'total_episodes' => 1
        ]);

        // Detect theme from concept
        $theme = $this->detectThemeFromConcept($concept);
        
        // Create episode
        $episode = Episode::create([
            'web_series_id' => $series->id,
            'episode_number' => 1,
            'title' => 'Episode 1: The Beginning',
            'prompt' => $title,
            'concept' => $concept,
            'status' => 'completed',
            'total_scenes' => 5
        ]);

        // Create scenes based on theme
        $scenes = $this->getDemoScenesData($episode->id, $series->id, 5, $theme);
        foreach ($scenes as $sceneData) {
            Scene::create($sceneData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Demo series created successfully!',
            'series_id' => $series->id,
            'redirect_url' => route('web-series.show', $series->id)
        ]);
    }

    /**
     * Generate demo concept (returns JSON for API compatibility)
     */
    public function generateConcept(Request $request, $id)
    {
        if (!$this->isDemoUser()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $userId = $this->getUserId();
        $series = WebSeries::where('user_id', $userId)->findOrFail($id);
        $concept = $this->getDemoConcept($request->prompt);
        $title = $this->getConceptTitle($request->prompt);
        
        $episode = Episode::updateOrCreate(
            [
                'web_series_id' => $series->id,
                'episode_number' => 1
            ],
            [
                'title' => $title,
                'prompt' => $request->prompt,
                'concept' => $concept,
                'status' => 'concept_ready'
            ]
        );
        
        $series->update([
            'project_name' => $title,
            'concept' => $concept,
            'status' => 'concept_generated'
        ]);

        return response()->json([
            'success' => true,
            'concept' => $concept,
            'title' => $title,
            'episode_id' => $episode->id,
            'message' => 'Demo concept generated!'
        ]);
    }

    /**
     * Generate demo scenes (returns JSON for API compatibility)
     */
    public function generateScenes(Request $request, $id)
    {
        if (!$this->isDemoUser()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $userId = $this->getUserId();
        $series = WebSeries::where('user_id', $userId)->findOrFail($id);
        $episode = Episode::where('web_series_id', $series->id)
            ->where('episode_number', 1)
            ->firstOrFail();
        
        // Detect theme from concept
        $theme = $this->detectThemeFromConcept($series->concept);
        
        DB::beginTransaction();
        
        Scene::where('episode_id', $episode->id)->delete();
        
        $scenes = $this->getDemoScenesData($episode->id, $series->id, $request->total_scenes ?? 5, $theme);
        foreach ($scenes as $sceneData) {
            Scene::create($sceneData);
        }
        
        $episode->update([
            'total_scenes' => $request->total_scenes ?? 5,
            'status' => 'completed'
        ]);
        
        $series->update([
            'total_episodes' => 1,
            'status' => 'completed'
        ]);
        
        DB::commit();

        return response()->json([
            'success' => true,
            'message' => ($request->total_scenes ?? 5) . ' demo scenes created!',
            'redirect_url' => route('web-series.show', $series->id)
        ]);
    }

    /**
     * Get demo dashboard data
     */
    public function getDashboardData()
    {
        if (!$this->isDemoUser()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $userId = $this->getUserId();
        
        $stats = [
            'total_series' => WebSeries::where('user_id', $userId)->count(),
            'total_episodes' => Episode::whereHas('webSeries', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count(),
            'total_scenes' => Scene::whereHas('webSeries', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count(),
            'completed_series' => WebSeries::where('user_id', $userId)->where('status', 'completed')->count(),
            'completion_rate' => 100,
            'available_credits' => 999
        ];
        
        $webSeries = WebSeries::where('user_id', $userId)
            ->with('category')
            ->withCount('episodes')
            ->latest()
            ->get();
        
        return response()->json([
            'success' => true,
            'stats' => $stats,
            'series' => $webSeries
        ]);
    }
}