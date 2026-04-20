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
     * Demo user ID
     */
    const DEMO_USER_ID = 141;

    /**
     * Check if current user is demo user
     */
    private function isDemoUser()
    {
        return Auth::check() && Auth::id() == self::DEMO_USER_ID;
    }

    /**
     * Get pre-defined demo concept (no API call)
     */
    public function getDemoConcept($prompt = null)
    {
        return "In the neon-drenched sprawl of Neo-Tokyo, a rogue cybernetic wolfhound named K9-7 and a sly feline hacker known as Whisk3r must unite to stop a rogue AI from deleting all organic life. Their unlikely alliance is tested as they navigate through digital landscapes, dodging corporate security drones and uncovering a conspiracy that threatens to erase the boundary between man and machine.";
    }

    /**
     * Get pre-defined demo scenes data
     */
    public function getDemoScenesData($episodeId, $seriesId, $totalScenes = 5)
    {
        $demoScenes = [
            1 => [
                'title' => 'The Neon Marketplace',
                'description' => 'K9-7 patrols the bustling neon marketplace, his cybernetic eyes scanning for threats. Holographic advertisements flicker above as drones zip through the crowded streets.',
                'image_prompt' => 'A futuristic neon-lit marketplace in Neo-Tokyo with holographic advertisements and flying drones, cyberpunk aesthetic, purple and pink neon lights',
                'image_url' => '/demo/images/image-1.png',
                'video_url' => '/demo/video/video.mp4'
            ],
            2 => [
                'title' => 'The Rooftop Chase',
                'description' => 'A thrilling chase across the skyline as K9-7 pursues Whisk3r. They leap between buildings, dodging holographic billboards and security drones.',
                'image_prompt' => 'A cybernetic wolfhound chasing a feline hacker across neon-lit rooftops with city lights below, dynamic action pose, cinematic lighting',
                'image_url' => '/demo/images/image-1.png',
                'video_url' => '/demo/video/video.mp4'
            ],
            3 => [
                'title' => 'The Underground Lair',
                'description' => 'Whisk3r reveals her hidden underground lair filled with advanced hacking equipment. Monitors display surveillance feeds from across the city.',
                'image_prompt' => 'A secret underground hacker lair filled with monitors and advanced technology, cyberpunk aesthetic, blue neon lighting',
                'image_url' => '/demo/images/image-1.png',
                'video_url' => '/demo/video/video.mp4'
            ],
            4 => [
                'title' => 'The Alliance',
                'description' => 'Unlikely allies join forces. K9-7 and Whisk3r shake hands, agreeing to work together to stop the rogue AI threatening their city.',
                'image_prompt' => 'A cybernetic wolfhound and a feline hacker shaking hands in a neon-lit room, partnership moment, warm lighting',
                'image_url' => '/demo/images/image-1.png',
                'video_url' => '/demo/video/video.mp4'
            ],
            5 => [
                'title' => 'The Final Confrontation',
                'description' => 'The ultimate battle against the rogue AI. K9-7 and Whisk3r combine their skills to upload the virus and save Neo-Tokyo from digital destruction.',
                'image_prompt' => 'Epic battle scene with neon lights and digital effects against a rogue AI, heroic pose, dramatic lighting',
                'image_url' => '/demo/images/image-1.png',
                'video_url' => '/demo/video/video.mp4'
            ]
        ];
        
        $scenes = [];
        for ($i = 1; $i <= $totalScenes; $i++) {
            $sceneData = $demoScenes[$i] ?? $demoScenes[1];
            $content = '<div class="scene-content">
                <h3 class="text-purple-400 text-xl font-bold mb-3">' . htmlspecialchars($sceneData['title']) . '</h3>
                <p><strong>Episode 1 - Scene ' . $i . '</strong></p>
                <p><strong>What happens:</strong> ' . htmlspecialchars($sceneData['description']) . '</p>
                <p><strong>Story Concept:</strong> ' . htmlspecialchars(substr($this->getDemoConcept(), 0, 200)) . '...</p>
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
     * Get pre-defined demo image
     */
    public function getDemoImage($sceneId)
    {
        $demoImages = [
            1 => 'https://placehold.co/1024x1024/1a1a1a/8b5cf6?text=Neon+Marketplace',
            2 => 'https://placehold.co/1024x1024/1a1a1a/ec4899?text=Rooftop+Chase',
            3 => 'https://placehold.co/1024x1024/1a1a1a/06b6d4?text=Underground+Lair',
            4 => 'https://placehold.co/1024x1024/1a1a1a/10b981?text=The+Alliance',
            5 => 'https://placehold.co/1024x1024/1a1a1a/ef4444?text=Final+Confrontation'
        ];
        
        return $demoImages[$sceneId] ?? $demoImages[1];
    }

    /**
     * Get pre-defined demo video
     */
    public function getDemoVideo($sceneId)
    {
        $demoVideos = [
            1 => 'https://sample-videos.com/video123/mp4/720/big_buck_bunny_720p_1mb.mp4',
            2 => 'https://sample-videos.com/video123/mp4/720/sample_1280x720_surfing.mp4',
            3 => 'https://sample-videos.com/video123/mp4/720/sample_1280x720_surfing_with_audio.mp4',
            4 => 'https://sample-videos.com/video123/mp4/720/big_buck_bunny_720p_1mb.mp4',
            5 => 'https://sample-videos.com/video123/mp4/720/sample_1280x720_surfing.mp4'
        ];
        
        return $demoVideos[$sceneId] ?? $demoVideos[1];
    }

    /**
     * Create demo series for demo user
     */
    public function createDemoSeries()
    {
        if (!$this->isDemoUser()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $userId = self::DEMO_USER_ID;
        
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

        // Create demo series
        $series = WebSeries::create([
            'user_id' => $userId,
            'category_id' => $category->id,
            'project_name' => 'Neo-Tokyo Chronicles (Demo)',
            'concept' => $this->getDemoConcept(),
            'status' => 'completed',
            'total_episodes' => 1
        ]);

        // Create episode
        $episode = Episode::create([
            'web_series_id' => $series->id,
            'episode_number' => 1,
            'title' => 'Episode 1: The Awakening',
            'prompt' => 'A cybernetic wolfhound and a feline hacker team up to stop a rogue AI',
            'concept' => $this->getDemoConcept(),
            'status' => 'completed',
            'total_scenes' => 5
        ]);

        // Create scenes
        $scenes = $this->getDemoScenesData($episode->id, $series->id, 5);
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

        $series = WebSeries::where('user_id', self::DEMO_USER_ID)->findOrFail($id);
        $concept = $this->getDemoConcept($request->prompt);
        
        $episode = Episode::updateOrCreate(
            [
                'web_series_id' => $series->id,
                'episode_number' => 1
            ],
            [
                'title' => 'Episode 1',
                'prompt' => $request->prompt,
                'concept' => $concept,
                'status' => 'concept_ready'
            ]
        );
        
        $series->update([
            'concept' => $concept,
            'status' => 'concept_generated'
        ]);

        return response()->json([
            'success' => true,
            'concept' => $concept,
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

        $series = WebSeries::where('user_id', self::DEMO_USER_ID)->findOrFail($id);
        $episode = Episode::where('web_series_id', $series->id)
            ->where('episode_number', 1)
            ->firstOrFail();
        
        DB::beginTransaction();
        
        Scene::where('episode_id', $episode->id)->delete();
        
        $scenes = $this->getDemoScenesData($episode->id, $series->id, $request->total_scenes ?? 5);
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

        $userId = self::DEMO_USER_ID;
        
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