<?php
// app/Http/Controllers/WebSeriesController.php

namespace App\Http\Controllers;

use App\Models\WebSeries;
use App\Models\Episode;
use App\Models\Scene;
use App\Services\ModelsLabService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebSeriesController extends Controller
{
    protected $modelsLabService;
    
    public function __construct(ModelsLabService $modelsLabService)
    {
        $this->modelsLabService = $modelsLabService;
    }
    
    public function create()
    {
        return view('web-series.create');
    }
    
    // Step 1: Create series
    public function saveProject(Request $request)
    {
        try {
            $validated = $request->validate([
                'project_name' => 'required|string|min:3|max:100',
                'category' => 'required|string|in:Action,Drama,Comedy,Sci-Fi,Fantasy,Thriller,Romance,Mystery,Horror,Adventure'
            ]);

            $series = WebSeries::create([
                'user_id' => auth()->id(),
                'project_name' => $validated['project_name'],
                'category' => $validated['category'],
                'status' => 'series_created'
            ]);

            return response()->json([
                'success' => true,
                'series_id' => $series->id,
                'message' => 'Project created successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Save project error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save project: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Step 2: Generate concept for Episode 1
    public function generateEpisode1Concept(Request $request, $id)
    {
        try {
            $request->validate([
                'prompt' => 'required|string|min:10|max:500'
            ]);

            $series = WebSeries::where('user_id', auth()->id())->findOrFail($id);
            
            // Generate concept using AI
            $concept = $this->modelsLabService->generateConcept(
                $request->prompt, 
                $series->category, 
                $series->project_name
            );
            
            // Create Episode 1
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
                'message' => 'Concept generated for Episode 1!'
            ]);

        } catch (\Exception $e) {
            Log::error('Generate concept error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate concept: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Step 3: Update concept
    public function updateEpisode1Concept(Request $request, $id)
    {
        try {
            $request->validate([
                'concept' => 'required|string|min:10|max:600'
            ]);

            $series = WebSeries::where('user_id', auth()->id())->findOrFail($id);
            $episode = Episode::where('web_series_id', $series->id)
                ->where('episode_number', 1)
                ->firstOrFail();
            
            $episode->update([
                'concept' => $request->concept,
                'status' => 'concept_saved'
            ]);
            
            $series->update([
                'concept' => $request->concept,
                'status' => 'concept_saved'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Concept saved successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save concept: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Step 4: Generate scenes for Episode 1
    public function generateEpisode1Scenes(Request $request, $id)
    {
        try {
            $request->validate([
                'total_scenes' => 'required|integer|min:5|max:10'
            ]);

            $series = WebSeries::where('user_id', auth()->id())->findOrFail($id);
            $episode = Episode::where('web_series_id', $series->id)
                ->where('episode_number', 1)
                ->firstOrFail();
            
            // Get series category
            $category = $series->category;
            
            // Generate scenes from concept
            $scenes = $this->createScenesFromConcept(
                $episode->concept, 
                $request->total_scenes, 
                1,
                $category,
                $series->id
            );
            
            DB::beginTransaction();
            
            // Delete existing scenes
            Scene::where('episode_id', $episode->id)->delete();
            
            // Create new scenes
            $createdScenes = [];
            foreach ($scenes as $index => $sceneData) {
                $scene = Scene::create([
                    'episode_id' => $episode->id,
                    'web_series_id' => $series->id,
                    'scene_number' => $index + 1,
                    'title' => $sceneData['title'],
                    'content' => $sceneData['content'],
                    'image_prompt' => $sceneData['image_prompt'] ?? null,
                    'summary' => $sceneData['summary'],
                    'status' => 'completed'
                ]);
                $createdScenes[] = $scene;
            }
            
            $episode->update([
                'total_scenes' => $request->total_scenes,
                'status' => 'completed'
            ]);
            
            $series->update([
                'total_episodes' => 1,
                'status' => 'completed'
            ]);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->total_scenes . ' scenes created successfully!',
                'redirect_url' => route('web-series.episode1', ['id' => $series->id]),
                'episode_id' => $episode->id,
                'scenes_count' => count($createdScenes)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Generate scenes error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create scenes: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function createScenesFromConcept($concept, $totalScenes, $episodeNumber, $category, $seriesId = null)
    {
        $scenes = [];
        
        $sceneTemplates = [
            [
                'title' => 'Opening Hook',
                'content' => '<div class="scene-content">
                    <h3 class="text-purple-400 text-xl font-bold mb-3">Opening Hook</h3>
                    <p><strong>Scene 1 - Episode ' . $episodeNumber . '</strong></p>
                    <p><strong>Location:</strong> A dramatic and atmospheric setting</p>
                    <p><strong>What happens:</strong> The episode opens with an intense moment that grabs attention.</p>
                    <p><strong>Key moment:</strong> An incident occurs that sets the entire episode in motion.</p>
                </div>'
            ],
            [
                'title' => 'Rising Action',
                'content' => '<div class="scene-content">
                    <h3 class="text-purple-400 text-xl font-bold mb-3">Rising Action</h3>
                    <p><strong>Scene 2 - Episode ' . $episodeNumber . '</strong></p>
                    <p><strong>Location:</strong> Where the conflict begins to unfold</p>
                    <p><strong>What happens:</strong> The stakes are raised as the protagonist faces challenges.</p>
                    <p><strong>Key moment:</strong> An important discovery changes everything.</p>
                </div>'
            ],
            [
                'title' => 'Conflict Emerges',
                'content' => '<div class="scene-content">
                    <h3 class="text-purple-400 text-xl font-bold mb-3">Conflict Emerges</h3>
                    <p><strong>Scene 3 - Episode ' . $episodeNumber . '</strong></p>
                    <p><strong>Location:</strong> Where opposing forces meet</p>
                    <p><strong>What happens:</strong> The main conflict comes to the forefront.</p>
                    <p><strong>Key moment:</strong> A confrontation reveals the true nature of the challenge.</p>
                </div>'
            ],
            [
                'title' => 'Turning Point',
                'content' => '<div class="scene-content">
                    <h3 class="text-purple-400 text-xl font-bold mb-3">Turning Point</h3>
                    <p><strong>Scene 4 - Episode ' . $episodeNumber . '</strong></p>
                    <p><strong>Location:</strong> Where everything changes</p>
                    <p><strong>What happens:</strong> A major revelation changes the protagonist\'s understanding.</p>
                    <p><strong>Key moment:</strong> The protagonist learns a crucial truth.</p>
                </div>'
            ],
            [
                'title' => 'Climax',
                'content' => '<div class="scene-content">
                    <h3 class="text-purple-400 text-xl font-bold mb-3">Climax</h3>
                    <p><strong>Scene 5 - Episode ' . $episodeNumber . '</strong></p>
                    <p><strong>Location:</strong> Where the main action peaks</p>
                    <p><strong>What happens:</strong> The central conflict reaches its peak.</p>
                    <p><strong>Key moment:</strong> The protagonist faces the ultimate test.</p>
                </div>'
            ]
        ];
        
        $extraTitles = ['Aftermath', 'Resolution', 'Setup for Next Episode', 'Character Growth', 'The Twist'];
        
        for ($i = 1; $i <= $totalScenes; $i++) {
            if ($i <= count($sceneTemplates)) {
                $template = $sceneTemplates[$i - 1];
                $title = $template['title'];
                $content = $template['content'];
            } else {
                $title = $extraTitles[$i - 6] ?? "Scene {$i}: Continuing the Story";
                $content = '<div class="scene-content">
                    <h3 class="text-purple-400 text-xl font-bold mb-3">' . $title . '</h3>
                    <p><strong>Scene ' . $i . ' - Episode ' . $episodeNumber . '</strong></p>
                    <p>This scene continues the action, revealing more about the characters and their journey.</p>
                </div>';
            }
            
            // Generate image prompt using AI
            $imagePrompt = null;
            try {
                $imagePrompt = $this->modelsLabService->generateImagePrompts(
                    $concept,
                    $title,
                    strip_tags($content),
                    $i,
                    $episodeNumber,
                    $category
                );
            } catch (\Exception $e) {
                Log::error('Image prompt generation failed for scene ' . $i . ': ' . $e->getMessage());
                $imagePrompt = "Cinematic {$category} scene, {$title}, dramatic lighting, professional cinematography, 8K resolution, movie still";
            }
            
            $scenes[] = [
                'title' => $title,
                'content' => $content,
                'image_prompt' => $imagePrompt,
                'summary' => substr(strip_tags($content), 0, 150) . '...'
            ];
        }
        
        return $scenes;
    }
    
    // Show Episode 1 complete view
    public function showEpisode1($id)
    {
        $series = WebSeries::where('user_id', auth()->id())->findOrFail($id);
        $episode = Episode::with('scenes')
            ->where('web_series_id', $series->id)
            ->where('episode_number', 1)
            ->firstOrFail();
        
        return view('web-series.episode-complete', compact('series', 'episode'));
    }
    
    // Show single scene
    public function showScene($seriesId, $sceneId)
{
    try {
        // Load series with episodes and scenes
        $series = WebSeries::where('user_id', auth()->id())
            ->with(['episodes.scenes'])  // Eager load episodes and scenes
            ->findOrFail($seriesId);
        
        // Find the specific scene
        $scene = Scene::where('web_series_id', $seriesId)
            ->where('id', $sceneId)
            ->firstOrFail();
        
        // Find the episode that contains this scene
        $episode = Episode::with('scenes')
            ->where('web_series_id', $seriesId)
            ->where('id', $scene->episode_id)
            ->firstOrFail();
        
        // Also attach scenes to series for navigation
        $series->scenes = Scene::where('web_series_id', $seriesId)
            ->orderBy('scene_number')
            ->get();
        
        return view('web-series.scene', compact('series', 'scene', 'episode'));
        
    } catch (\Exception $e) {
        Log::error('Show scene error: ' . $e->getMessage());
        return back()->with('error', 'Scene not found: ' . $e->getMessage());
    }
}
    
    // Show series details
    public function show($id)
    {
        $series = WebSeries::with('episodes.scenes')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        return view('web-series.show', compact('series'));
    }
    
    // List user's series
    public function mySeries()
    {
        $webSeries = WebSeries::where('user_id', auth()->id())
            ->withCount('episodes')
            ->latest()
            ->paginate(20);
        
        return view('web-series.my-series', compact('webSeries'));
    }
    
    // Dashboard
    public function dashboard()
    {
        $stats = [
            'total_series' => WebSeries::where('user_id', auth()->id())->count(),
            'total_episodes' => Episode::whereHas('webSeries', function($q) {
                $q->where('user_id', auth()->id());
            })->count(),
            'total_scenes' => Scene::whereHas('webSeries', function($q) {
                $q->where('user_id', auth()->id());
            })->count(),
            'completed_series' => WebSeries::where('user_id', auth()->id())
                ->where('status', 'completed')
                ->count()
        ];
        
        $webSeries = WebSeries::where('user_id', auth()->id())
            ->withCount('episodes')
            ->latest()
            ->paginate(12);
        
        return view('web-series.dashboard', compact('webSeries', 'stats'));
    }
    
    // Delete series
    public function destroy($id)
    {
        try {
            $series = WebSeries::where('user_id', auth()->id())->findOrFail($id);
            $series->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Series deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete series: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Admin methods
    public function adminIndex()
    {
        try {
            $webSeries = WebSeries::with('user', 'episodes')
                ->latest()
                ->paginate(20);
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $webSeries
                ]);
            }
            
            return view('admin.web-series.index', compact('webSeries'));
            
        } catch (\Exception $e) {
            Log::error('Admin index error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load series: ' . $e->getMessage());
        }
    }
    
    public function adminShow($id)
    {
        try {
            $series = WebSeries::with(['user', 'episodes.scenes'])
                ->findOrFail($id);
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $series
                ]);
            }
            
            return view('admin.web-series.show', compact('series'));
            
        } catch (\Exception $e) {
            Log::error('Admin show error: ' . $e->getMessage());
            return back()->with('error', 'Series not found');
        }
    }
    
    public function adminDestroy($id)
    {
        try {
            $series = WebSeries::findOrFail($id);
            $series->delete();
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Series deleted successfully!'
                ]);
            }
            
            return redirect()->route('admin.web-series.index')
                ->with('success', 'Series deleted successfully!');
            
        } catch (\Exception $e) {
            Log::error('Admin destroy error: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete series: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to delete series: ' . $e->getMessage());
        }
    }
    /**
 * Generate image for a scene
 */
public function generateImage(Request $request)
{
    try {
        $request->validate([
            'prompt' => 'required|string|min:10',
            'scene_id' => 'required|integer'
        ]);
        
        $prompt = $request->prompt;
        $sceneId = $request->scene_id;
        
        Log::info('Generating image for scene: ' . $sceneId);
        Log::info('Prompt: ' . $prompt);
        
        // Generate images using Flux model
        $imageUrls = $this->modelsLabService->generateImage($prompt, 1024, 1024, 1);
        
        // Get the first image URL
        $imageUrl = is_array($imageUrls) ? $imageUrls[0] : $imageUrls;
        
        // Save to scene if needed
        $scene = Scene::find($sceneId);
        if ($scene) {
            $scene->generated_image_url = $imageUrl;
            $scene->save();
        }
        
        return response()->json([
            'success' => true,
            'image_url' => $imageUrl,
            'message' => 'Image generated successfully!'
        ]);
        
    } catch (\Exception $e) {
        Log::error('Generate image error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to generate image: ' . $e->getMessage()
        ], 500);
    }
}
}