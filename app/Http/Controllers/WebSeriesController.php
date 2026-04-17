<?php
// app/Http/Controllers/WebSeriesController.php

namespace App\Http\Controllers;

use App\Models\WebSeries;
use App\Models\Episode;
use App\Models\Scene;
use App\Models\Category;
use App\Models\CategoryTemplate;
use App\Models\ImageGenerationLog;
use App\Models\VideoGenerationLog;
use App\Services\ModelsLabService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class WebSeriesController extends Controller
{
    protected $modelsLabService;
    
    public function __construct(ModelsLabService $modelsLabService)
    {
        $this->modelsLabService = $modelsLabService;
    }
    
    public function create()
    {
        $categories = Category::active()->ordered()->get();
        return view('web-series.create', compact('categories'));
    }
    
    public function saveProject(Request $request)
    {
        try {
            $validated = $request->validate([
                'project_name' => 'required|string|min:3|max:100',
                'category_id' => 'required|exists:categories,id'
            ]);

            $series = WebSeries::create([
                'user_id' => auth()->id(),
                'category_id' => $validated['category_id'],
                'project_name' => $validated['project_name'],
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
    
    public function generateEpisode1Concept(Request $request, $id)
    {
        try {
            $request->validate([
                'prompt' => 'required|string|min:10|max:500'
            ]);

            $series = WebSeries::where('user_id', auth()->id())->with('category')->findOrFail($id);
            
            $concept = $this->modelsLabService->generateConcept(
                $request->prompt, 
                $series->category_id,
                $series->project_name
            );
            
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
    
    public function updateEpisode1Concept(Request $request, $id)
    {
        try {
            $request->validate([
                'concept' => 'required|string|min:10'
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
    
    public function generateEpisode1Scenes(Request $request, $id)
    {
        try {
            $request->validate([
                'total_scenes' => 'required|integer|min:5|max:10'
            ]);

            $series = WebSeries::where('user_id', auth()->id())->with('category')->findOrFail($id);
            $episode = Episode::where('web_series_id', $series->id)
                ->where('episode_number', 1)
                ->firstOrFail();
            
            $scenePrompts = $this->modelsLabService->generateScenePrompts(
                $episode->concept, 
                $request->total_scenes, 
                1
            );
            
            DB::beginTransaction();
            
            Scene::where('episode_id', $episode->id)->delete();
            
            foreach ($scenePrompts as $index => $scenePrompt) {
                $sceneNumber = $index + 1;
                $title = $scenePrompt['title'];
                $description = $scenePrompt['description'];
                
                $content = '<div class="scene-content">
                    <h3 class="text-purple-400 text-xl font-bold mb-3">' . htmlspecialchars($title) . '</h3>
                    <p><strong>Episode 1 - Scene ' . $sceneNumber . '</strong></p>
                    <p><strong>What happens:</strong> ' . htmlspecialchars($description) . '</p>
                    <p><strong>Story Concept:</strong> ' . htmlspecialchars(substr($episode->concept, 0, 200)) . '...</p>
                </div>';
                
                $imagePrompt = $this->modelsLabService->generateImagePrompt(
                    $episode->concept, 
                    $title, 
                    $description, 
                    $sceneNumber, 
                    1, 
                    $series->category_id
                );
                
                Scene::create([
                    'episode_id' => $episode->id,
                    'web_series_id' => $series->id,
                    'scene_number' => $sceneNumber,
                    'title' => $title,
                    'content' => $content,
                    'image_prompt' => $imagePrompt,
                    'summary' => substr($description, 0, 150),
                    'status' => 'pending'
                ]);
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
                'redirect_url' => route('web-series.show', $series->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Generate scenes error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create scenes: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function showEpisode1($id)
    {
        $series = WebSeries::where('user_id', auth()->id())->with('category')->findOrFail($id);
        $episode = Episode::with('scenes')
            ->where('web_series_id', $series->id)
            ->where('episode_number', 1)
            ->firstOrFail();
        
        return view('web-series.episode-complete', compact('series', 'episode'));
    }
    
    public function showScene($seriesId, $sceneId)
    {
        try {
            $series = WebSeries::where('user_id', auth()->id())
                ->with('episodes.scenes', 'category')
                ->findOrFail($seriesId);
            
            $scene = Scene::where('web_series_id', $seriesId)
                ->where('id', $sceneId)
                ->firstOrFail();
            
            $episode = Episode::where('id', $scene->episode_id)->firstOrFail();
            
            return view('web-series.scene', compact('series', 'scene', 'episode'));
            
        } catch (\Exception $e) {
            Log::error('Show scene error: ' . $e->getMessage());
            return back()->with('error', 'Scene not found');
        }
    }
    
   public function show(Request $request, $id)
{
    $series = WebSeries::with('episodes.scenes', 'category')
        ->where('user_id', auth()->id())
        ->findOrFail($id);
    
    // If episode parameter is provided, pass it to the view
    $selectedEpisodeId = $request->query('episode');
    
    return view('web-series.show', compact('series', 'selectedEpisodeId'));
}
    
    /**
     * Get scenes status for polling (used in create.blade.php)
     */
    public function getScenesStatus($seriesId)
    {
        try {
            $series = WebSeries::where('user_id', auth()->id())->findOrFail($seriesId);
            $episode = Episode::where('web_series_id', $seriesId)->where('episode_number', 1)->first();
            
            if (!$episode) {
                return response()->json([
                    'success' => true,
                    'completed_count' => 0,
                    'total_count' => 5,
                    'all_completed' => false,
                    'scenes' => []
                ]);
            }
            
            $scenes = Scene::where('episode_id', $episode->id)->orderBy('scene_number')->get();
            $completedCount = $scenes->whereNotNull('generated_image_url')->count();
            $totalCount = $scenes->count();
            
            $scenesData = $scenes->map(function($scene) {
                return [
                    'id' => $scene->id,
                    'scene_number' => $scene->scene_number,
                    'title' => $scene->title,
                    'generated_image_url' => $scene->generated_image_url
                ];
            });
            
            return response()->json([
                'success' => true,
                'completed_count' => $completedCount,
                'total_count' => $totalCount > 0 ? $totalCount : 5,
                'all_completed' => $completedCount == $totalCount && $totalCount > 0,
                'scenes' => $scenesData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get scenes status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check if image URL is valid and accessible
     */
    private function isImageUrlValid($url, $maxAttempts = 3, $delaySeconds = 2)
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                Log::info("Checking image URL - Attempt {$attempt}/{$maxAttempts}: " . substr($url, 0, 100));
                
                $response = Http::timeout(10)->head($url);
                
                if ($response->successful()) {
                    $contentType = $response->header('Content-Type');
                    if (str_contains($contentType, 'image')) {
                        Log::info("Image URL is valid via HEAD");
                        return true;
                    }
                }
                
                $response = Http::timeout(10)->get($url);
                
                if ($response->successful()) {
                    $contentType = $response->header('Content-Type');
                    if (str_contains($contentType, 'image')) {
                        Log::info("Image URL is valid via GET");
                        return true;
                    }
                }
                
                if ($attempt < $maxAttempts) {
                    Log::warning("Image URL not ready yet, waiting {$delaySeconds} seconds...");
                    sleep($delaySeconds);
                }
                
            } catch (\Exception $e) {
                Log::warning("Error checking image URL (Attempt {$attempt}): " . $e->getMessage());
                if ($attempt < $maxAttempts) {
                    sleep($delaySeconds);
                }
            }
        }
        
        Log::error("Image URL validation failed after {$maxAttempts} attempts");
        return false;
    }
    
    /**
     * Store image locally
     */
    private function storeImageLocally($url, $sceneId, $seriesId)
    {
        try {
            $directory = public_path("images/series/{$seriesId}/scenes/{$sceneId}");
            
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            
            $filename = 'scene_' . $sceneId . '_' . time() . '.png';
            $fullPath = $directory . '/' . $filename;
            
            Log::info("Downloading image from: " . substr($url, 0, 100));
            
            $imageContent = Http::timeout(60)->get($url)->body();
            
            if (empty($imageContent)) {
                throw new \Exception("Downloaded image content is empty");
            }
            
            file_put_contents($fullPath, $imageContent);
            
            if (file_exists($fullPath) && filesize($fullPath) > 0) {
                Log::info("Image stored locally: {$fullPath}, Size: " . filesize($fullPath) . " bytes");
                return asset("images/series/{$seriesId}/scenes/{$sceneId}/{$filename}");
            }
            
            throw new \Exception("Failed to save file");
            
        } catch (\Exception $e) {
            Log::error('Failed to store image: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Wait for and validate image before saving
     */
    private function waitForAndValidateImage($imageUrl, $sceneId, $seriesId, $maxAttempts = 60, $delaySeconds = 3)
    {
        Log::info("Waiting for image to be ready: " . substr($imageUrl, 0, 100));
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            Log::info("Validating image - Attempt {$attempt}/{$maxAttempts}");
            
            if ($this->isImageUrlValid($imageUrl, 2, 1)) {
                Log::info("Image is valid! Attempt {$attempt}");
                
                $localUrl = $this->storeImageLocally($imageUrl, $sceneId, $seriesId);
                
                if ($localUrl) {
                    Log::info("Image stored locally: {$localUrl}");
                    return $localUrl;
                }
                
                Log::warning("Failed to store locally, using original URL");
                return $imageUrl;
            }
            
            if ($attempt < $maxAttempts) {
                sleep($delaySeconds);
            }
        }
        
        Log::error("Image validation failed after {$maxAttempts} attempts");
        return null;
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
            
            $scene = Scene::find($sceneId);
            if (!$scene) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scene not found'
                ], 404);
            }
            
            $seriesId = $scene->web_series_id;
            $userId = auth()->id();
            
            $scene->update(['status' => 'generating']);
            
            $result = $this->modelsLabService->generateImageWithWebhook(
                $prompt, $sceneId, $seriesId, $userId, 1024, 1024, 1
            );
            
            if ($result['success']) {
                if (isset($result['images']) && !empty($result['images'])) {
                    $imageUrl = $result['images'][0];
                    
                    $validatedUrl = $this->waitForAndValidateImage($imageUrl, $sceneId, $seriesId, 60, 3);
                    
                    if ($validatedUrl) {
                        $scene->update([
                            'generated_image_url' => $validatedUrl,
                            'status' => 'completed'
                        ]);
                        
                        return response()->json([
                            'success' => true,
                            'image_url' => $validatedUrl,
                            'tracking_id' => $result['tracking_id'] ?? null,
                            'message' => 'Image generated and validated successfully!'
                        ]);
                    } else {
                        $scene->update(['status' => 'failed']);
                        return response()->json([
                            'success' => false,
                            'message' => 'Image generated but validation failed - please try again',
                            'tracking_id' => $result['tracking_id'] ?? null
                        ], 500);
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'processing' => true,
                    'scene_id' => $sceneId,
                    'tracking_id' => $result['tracking_id'] ?? null,
                    'message' => 'Image generation started. The image will appear when ready.'
                ]);
            }
            
            $scene->update(['status' => 'failed']);
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to start generation',
                'tracking_id' => $result['tracking_id'] ?? null
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Generate image error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle webhook from image generation API
     */
    public function handleImageWebhook(Request $request)
    {
        Log::info('=== WEBHOOK RECEIVED ===');
        Log::info('Webhook data:', $request->all());
        
        try {
            $trackingId = $request->query('tracking_id');
            $sceneId = $request->query('scene_id');
            $seriesId = $request->query('series_id');
            $data = $request->all();
            
            $log = ImageGenerationLog::where('tracking_id', $trackingId)->first();
            
            if ($log) {
                $log->update([
                    'webhook_received_at' => now(),
                    'webhook_payload' => $data
                ]);
            }
            
            if (isset($data['status']) && $data['status'] === 'success') {
                $imageUrls = $data['output'] ?? $data['future_links'] ?? [];
                
                if (!empty($imageUrls)) {
                    $validatedUrl = $this->waitForAndValidateImage($imageUrls[0], $sceneId, $seriesId, 60, 3);
                    
                    if ($validatedUrl) {
                        $scene = Scene::find($sceneId);
                        if ($scene) {
                            $scene->update([
                                'generated_image_url' => $validatedUrl,
                                'status' => 'completed'
                            ]);
                        }
                        
                        if ($log) {
                            $log->update([
                                'status' => 'completed',
                                'image_urls' => $imageUrls,
                                'image_paths' => [$validatedUrl],
                                'completed_at' => now()
                            ]);
                        }
                        
                        Log::info("Image saved for scene {$sceneId}", ['tracking_id' => $trackingId, 'url' => $validatedUrl]);
                    } else {
                        if ($log) {
                            $log->update([
                                'status' => 'failed',
                                'error_message' => 'Image validation failed',
                                'completed_at' => now()
                            ]);
                        }
                        Log::error("Image validation failed for scene {$sceneId}");
                    }
                }
            } else {
                if ($log) {
                    $log->update([
                        'status' => 'failed',
                        'error_message' => $data['message'] ?? 'Webhook received with error status',
                        'completed_at' => now()
                    ]);
                }
            }
            
            return response()->json(['status' => 'success']);
            
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Check image generation status
     */
    public function checkImageStatus(Request $request)
    {
        try {
            $scene = Scene::find($request->scene_id);
            
            if (!$scene) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scene not found'
                ]);
            }
            
            $imageUrl = $scene->generated_image_url ? asset($scene->generated_image_url) : null;
            
            return response()->json([
                'success' => true,
                'status' => $scene->status,
                'image_url' => $imageUrl
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function mySeries()
    {
        $webSeries = WebSeries::where('user_id', auth()->id())
            ->with('category')
            ->withCount('episodes')
            ->latest()
            ->paginate(20);
        
        return view('web-series.my-series', compact('webSeries'));
    }
    
    public function dashboard()
    {
        $userId = auth()->id();
        
        // Get counts
        $mySeriesCount = WebSeries::where('user_id', $userId)->count();
        $myEpisodesCount = Episode::whereHas('webSeries', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->count();
        $myScenesCount = Scene::whereHas('webSeries', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->count();
        $completedSeries = WebSeries::where('user_id', $userId)
            ->where('status', 'completed')->count();
        
        // Calculate completion rate
        $completionRate = $mySeriesCount > 0 ? round(($completedSeries / $mySeriesCount) * 100) : 0;
        
        $stats = [
            'total_series' => $mySeriesCount,
            'total_episodes' => $myEpisodesCount,
            'total_scenes' => $myScenesCount,
            'completed_series' => $completedSeries
        ];
        
        // Get user's web series
        $webSeries = WebSeries::where('user_id', $userId)
            ->with('category')
            ->withCount('episodes')
            ->latest()
            ->paginate(12);
        
        // Get trending categories (categories with most series)
        $trendingCategories = Category::with('template')
            ->where('is_active', true)
            ->withCount('webSeries')
            ->orderBy('web_series_count', 'desc')
            ->take(10)
            ->get();
        
        // If no categories have series, show all active categories with mock data
        if ($trendingCategories->count() == 0) {
            $trendingCategories = Category::with('template')
                ->where('is_active', true)
                ->orderBy('display_order', 'asc')
                ->take(10)
                ->get();
            
            // Add mock series count for visual appeal
            foreach ($trendingCategories as $index => $category) {
                $category->series_count = rand(5, 50);
            }
        } else {
            // Add series count to each category
            foreach ($trendingCategories as $category) {
                $category->series_count = $category->web_series_count;
            }
        }
        
        // ==============================================
        // RECOMMENDED FOR YOU - PERSONALIZED SERIES
        // ==============================================
        $recommendedSeries = collect();
        
        // Get user's category preferences from their existing series
        $userCategoryIds = WebSeries::where('user_id', $userId)
            ->whereNotNull('category_id')
            ->pluck('category_id')
            ->unique()
            ->toArray();
        
        // Also get categories from their completed scenes (based on prompts)
        $userSceneCategories = Scene::whereHas('webSeries', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->whereNotNull('image_prompt')
            ->get()
            ->map(function($scene) {
                // Simple keyword-based category detection from prompts
                $prompt = strtolower($scene->image_prompt);
                $categories = Category::where('is_active', true)->get();
                
                foreach ($categories as $category) {
                    if (str_contains($prompt, strtolower($category->name)) ||
                        str_contains($prompt, strtolower($category->keywords ?? ''))) {
                        return $category->id;
                    }
                }
                return null;
            })
            ->filter()
            ->unique()
            ->toArray();
        
        // Merge user category preferences
        $preferredCategoryIds = array_unique(array_merge($userCategoryIds, $userSceneCategories));
        
        // Get recommended series from same categories, excluding user's own series
        if (!empty($preferredCategoryIds)) {
            $recommendedSeries = WebSeries::with(['user', 'category', 'episodes'])
                ->where('user_id', '!=', $userId)
                ->whereIn('category_id', $preferredCategoryIds)
                ->where('status', 'completed')
                ->withCount('episodes')
                ->latest()
                ->take(10)
                ->get();
        }
        
        // If not enough recommendations, fill with popular series from trending categories
        if ($recommendedSeries->count() < 6) {
            $needed = 10 - $recommendedSeries->count();
            $existingIds = $recommendedSeries->pluck('id')->toArray();
            
            $popularSeries = WebSeries::with(['user', 'category', 'episodes'])
                ->where('user_id', '!=', $userId)
                ->whereNotIn('id', $existingIds)
                ->where('status', 'completed')
                ->withCount('episodes')
                ->orderBy('created_at', 'desc')
                ->take($needed)
                ->get();
            
            $recommendedSeries = $recommendedSeries->merge($popularSeries);
        }
        
        // Add mock views_count and rating for each series (for display purposes)
        foreach ($recommendedSeries as $series) {
            $series->views_count = rand(100, 5000);
            $series->rating = rand(40, 50) / 10;
        }
        
        // Get total users count
        $totalUsers = \App\Models\User::count();
        
        // Get average rating
        $avgRating = 4.9;
        
        return view('web-series.dashboard', compact(
            'webSeries', 
            'stats', 
            'trendingCategories',
            'mySeriesCount',
            'myEpisodesCount',
            'myScenesCount',
            'completedSeries',
            'completionRate',
            'totalUsers',
            'avgRating',
            'recommendedSeries'
        ));
    }
    
    public function destroy($id)
    {
        try {
            $series = WebSeries::where('user_id', auth()->id())->findOrFail($id);
            
            DB::beginTransaction();
            
            foreach ($series->episodes as $episode) {
                $episode->scenes()->delete();
            }
            $series->episodes()->delete();
            $series->delete();
            
            DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Series deleted successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function generateVideoPage($id)
    {
        $series = WebSeries::with('scenes', 'category')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        return view('web-series.generate-video', compact('series'));
    }
    
    /**
     * Generate video for a single scene using Replicate LTX-Video
     */
    public function generateSceneVideo(Request $request)
    {
        try {
            $request->validate([
                'scene_id' => 'required|integer|exists:scenes,id',
                'image_url' => 'required|string|url'
            ]);
            
            $sceneId = $request->scene_id;
            $imageUrl = $request->image_url;
            
            // Verify scene belongs to authenticated user
            $scene = Scene::where('id', $sceneId)
                ->whereHas('webSeries', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->firstOrFail();
            
            // Create video generation prompt
            $prompt = $this->generateVideoPrompt($scene->title, $imageUrl);
            
            // Create log entry
            $log = $this->createVideoGenerationLog($sceneId, $scene->web_series_id, $imageUrl, $prompt);
            
            // Update scene status to generating
            $scene->update([
                'video_status' => 'generating',
                'video_generation_started_at' => now()
            ]);
            
            // Update log status
            $log->update(['status' => 'processing', 'started_at' => now()]);
            
            Log::info('Video generation requested via Replicate', [
                'scene_id' => $sceneId,
                'user_id' => auth()->id(),
                'image_url' => $imageUrl,
                'log_id' => $log->id
            ]);
            
            // Generate video using Replicate API
            $videoUrl = $this->generateVideoWithReplicate($imageUrl, $sceneId, $scene->title, $log);
            
            if ($videoUrl) {
                // Store video locally
                $localUrl = $this->storeVideoLocally($videoUrl, $sceneId, $scene->web_series_id);
                
                // Update scene with generated video URL
                $scene->update([
                    'video_url' => $localUrl ?? $videoUrl,
                    'video_status' => 'completed',
                    'video_generation_completed_at' => now()
                ]);
                
                // Update log entry
                $log->update([
                    'status' => 'completed',
                    'video_url' => $localUrl ?? $videoUrl,
                    'completed_at' => now(),
                    'output_data' => ['video_url' => $localUrl ?? $videoUrl]
                ]);
                
                return response()->json([
                    'success' => true,
                    'video_url' => $localUrl ?? $videoUrl,
                    'message' => 'Video generated successfully!'
                ]);
            } else {
                throw new \Exception('Failed to generate video');
            }
            
        } catch (\Exception $e) {
            Log::error('Generate scene video error: ' . $e->getMessage(), [
                'scene_id' => $request->scene_id ?? null,
                'user_id' => auth()->id()
            ]);
            
            // Update scene status to failed
            if (isset($scene)) {
                $scene->update([
                    'video_status' => 'failed',
                    'video_error_message' => $e->getMessage()
                ]);
            }
            
            // Update log entry if exists
            if (isset($log)) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now()
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate video: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create video generation log entry
     */
    private function createVideoGenerationLog($sceneId, $seriesId, $imageUrl, $prompt)
    {
        return VideoGenerationLog::create([
            'user_id' => auth()->id(),
            'scene_id' => $sceneId,
            'series_id' => $seriesId,
            'status' => 'pending',
            'image_url' => $imageUrl,
            'prompt' => $prompt,
            'input_params' => [
                'width' => 704,
                'height' => 480,
                'frame_rate' => 25,
                'num_frames' => 121,
                'guidance_scale' => 3,
                'num_inference_steps' => 40,
                'negative_prompt' => 'worst quality, inconsistent motion, blurry, jittery, distorted, low resolution, bad anatomy, ugly'
            ],
            'started_at' => now(),
            'attempts' => 0
        ]);
    }
    
    /**
     * Generate a video prompt based on the scene
     */
    private function generateVideoPrompt($sceneTitle, $imageUrl)
    {
        $prompts = [
            "A cinematic video of {$sceneTitle} with smooth camera movement, natural motion, and atmospheric lighting. The scene should feel alive with subtle movements and depth.",
            "Bring this scene to life with gentle motion, a subtle camera pan, and dramatic atmospheric lighting. Create a cinematic feel with smooth transitions.",
            "Transform this static image into a dynamic cinematic shot. Add fluid motion, depth of field, and natural movement that makes the scene feel immersive and realistic.",
            "Create a captivating video from this image with smooth motion, cinematic camera work, and atmospheric effects. The video should feel professional and engaging.",
            "Animate this scene with realistic motion, adding cinematic depth, smooth camera movement, and natural lighting effects. Make the video feel like a movie scene."
        ];
        
        return $prompts[array_rand($prompts)];
    }
    
    /**
     * Generate video using Replicate LTX-Video API
     */
    private function generateVideoWithReplicate($imageUrl, $sceneId, $sceneTitle, $log = null)
    {
        $apiToken = env('REPLICATE_API_TOKEN');
        
        if (!$apiToken) {
            Log::error('REPLICATE_API_TOKEN not set in .env file');
            if ($log) $log->update(['error_message' => 'REPLICATE_API_TOKEN not set', 'status' => 'failed']);
            return null;
        }
        
        $prompt = $log ? $log->prompt : $this->generateVideoPrompt($sceneTitle, $imageUrl);
        
        $payload = [
            'version' => '69599cebad125acfd3d5c682c187702ea7a84d537603e46b468a44fe94c5fd13',
            'input' => [
                'width' => 704,
                'height' => 480,
                'prompt' => $prompt,
                'frame_rate' => 25,
                'num_frames' => 121,
                'guidance_scale' => 3,
                'negative_prompt' => 'worst quality, inconsistent motion, blurry, jittery, distorted, low resolution, bad anatomy, ugly',
                'num_inference_steps' => 40
            ]
        ];
        
        if ($log) {
            $payload['webhook'] = route('replicate.webhook') . '?log_id=' . $log->id . '&scene_id=' . $sceneId;
            $payload['webhook_events_filter'] = ['completed'];
        }
        
        Log::info('Calling Replicate API', [
            'scene_id' => $sceneId,
            'image_url' => $imageUrl,
            'prompt' => $prompt,
            'log_id' => $log?->id
        ]);
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.replicate.com/v1/predictions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiToken,
                'Content-Type: application/json',
                'Prefer: wait'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            Log::info('Replicate API response', [
                'http_code' => $httpCode,
                'response' => substr($response, 0, 500)
            ]);
            
            if ($httpCode !== 201 && $httpCode !== 200) {
                if ($log) $log->update(['error_message' => 'Replicate API error: HTTP ' . $httpCode, 'status' => 'failed']);
                return null;
            }
            
            $result = json_decode($response, true);
            
            if ($log && isset($result['id'])) {
                $log->update(['prediction_id' => $result['id']]);
            }
            
            if (isset($result['status']) && $result['status'] === 'succeeded') {
                $output = $result['output'] ?? null;
                if (is_array($output) && isset($output[0])) {
                    if ($log) $log->update(['output_data' => $result]);
                    return $output[0];
                }
                if (is_string($output)) {
                    if ($log) $log->update(['output_data' => $result]);
                    return $output;
                }
                return null;
            }
            
            if (isset($result['id']) && ($result['status'] === 'processing' || $result['status'] === 'starting')) {
                return $this->pollReplicatePrediction($result['id'], $apiToken, $sceneId, $log);
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Replicate cURL error: ' . $e->getMessage());
            if ($log) $log->update(['error_message' => $e->getMessage(), 'status' => 'failed']);
            return null;
        }
    }
    
    /**
     * Poll Replicate for prediction status
     */
    private function pollReplicatePrediction($predictionId, $apiToken, $sceneId, $log = null, $maxAttempts = 60, $delaySeconds = 3)
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            sleep($delaySeconds);
            
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.replicate.com/v1/predictions/' . $predictionId);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $apiToken,
                    'Content-Type: application/json'
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode !== 200) {
                    Log::warning('Failed to get prediction status', ['prediction_id' => $predictionId]);
                    continue;
                }
                
                $result = json_decode($response, true);
                
                Log::info('Replicate prediction status', [
                    'attempt' => $attempt,
                    'prediction_id' => $predictionId,
                    'status' => $result['status'] ?? 'unknown'
                ]);
                
                if ($result['status'] === 'succeeded') {
                    $output = $result['output'] ?? null;
                    if (is_array($output) && isset($output[0])) {
                        if ($log) $log->update(['output_data' => $result]);
                        return $output[0];
                    }
                    if (is_string($output)) {
                        if ($log) $log->update(['output_data' => $result]);
                        return $output;
                    }
                    return null;
                }
                
                if ($result['status'] === 'failed') {
                    Log::error('Replicate prediction failed', [
                        'prediction_id' => $predictionId,
                        'error' => $result['error'] ?? 'Unknown error'
                    ]);
                    if ($log) $log->update(['error_message' => $result['error'] ?? 'Unknown error', 'status' => 'failed']);
                    return null;
                }
                
            } catch (\Exception $e) {
                Log::error('Error polling Replicate: ' . $e->getMessage());
            }
        }
        
        Log::error('Replicate prediction timeout', ['prediction_id' => $predictionId]);
        if ($log) $log->update(['error_message' => 'Prediction timeout', 'status' => 'failed']);
        return null;
    }
    
    /**
     * Store video locally
     */
    private function storeVideoLocally($videoUrl, $sceneId, $seriesId)
    {
        try {
            $directory = storage_path("app/public/videos/series/{$seriesId}/scenes/{$sceneId}");
            
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            
            $filename = 'video_' . $sceneId . '_' . time() . '.mp4';
            $fullPath = $directory . '/' . $filename;
            
            Log::info("Downloading video from: " . substr($videoUrl, 0, 100));
            
            $ch = curl_init($videoUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            $videoContent = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200 || empty($videoContent)) {
                throw new \Exception("Failed to download video: HTTP {$httpCode}");
            }
            
            file_put_contents($fullPath, $videoContent);
            
            if (file_exists($fullPath) && filesize($fullPath) > 0) {
                Log::info("Video stored locally: {$fullPath}, Size: " . filesize($fullPath) . " bytes");
                return Storage::url("videos/series/{$seriesId}/scenes/{$sceneId}/{$filename}");
            }
            
            throw new \Exception("Failed to save file");
            
        } catch (\Exception $e) {
            Log::error('Failed to store video: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check video generation status
     */
    public function checkVideoStatus($sceneId)
    {
        try {
            $scene = Scene::where('id', $sceneId)
                ->whereHas('webSeries', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->firstOrFail();
            
            $videoUrl = $scene->video_url ? asset($scene->video_url) : null;
            
            return response()->json([
                'success' => true,
                'status' => $scene->video_status ?? 'pending',
                'video_url' => $videoUrl,
                'error_message' => $scene->video_error_message ?? null
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle Replicate webhook (for async processing)
     */
    public function handleReplicateWebhook(Request $request)
    {
        Log::info('Replicate webhook received', $request->all());
        
        try {
            $logId = $request->query('log_id');
            $sceneId = $request->query('scene_id');
            $data = $request->all();
            
            $log = VideoGenerationLog::find($logId);
            $scene = Scene::find($sceneId);
            
            if (!$scene) {
                Log::error('Scene not found for webhook', ['scene_id' => $sceneId]);
                return response()->json(['error' => 'Scene not found'], 404);
            }
            
            if (isset($data['status']) && $data['status'] === 'succeeded') {
                $videoUrl = $data['output'][0] ?? $data['output'] ?? null;
                
                if ($videoUrl) {
                    $localUrl = $this->storeVideoLocally($videoUrl, $sceneId, $scene->web_series_id);
                    
                    $scene->update([
                        'video_url' => $localUrl ?? $videoUrl,
                        'video_status' => 'completed',
                        'video_generation_completed_at' => now()
                    ]);
                    
                    if ($log) {
                        $log->update([
                            'status' => 'completed',
                            'video_url' => $localUrl ?? $videoUrl,
                            'output_data' => $data,
                            'completed_at' => now()
                        ]);
                    }
                    
                    Log::info('Video saved from webhook', ['scene_id' => $sceneId]);
                }
            } elseif (isset($data['status']) && $data['status'] === 'failed') {
                $scene->update([
                    'video_status' => 'failed',
                    'video_error_message' => $data['error'] ?? 'Unknown error'
                ]);
                
                if ($log) {
                    $log->update([
                        'status' => 'failed',
                        'error_message' => $data['error'] ?? 'Unknown error',
                        'completed_at' => now()
                    ]);
                }
                
                Log::error('Replicate prediction failed', [
                    'scene_id' => $sceneId,
                    'error' => $data['error']
                ]);
            }
            
            return response()->json(['status' => 'success']);
            
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
    
    /**
     * Get dashboard stats as JSON for AJAX updates
     */
    public function getDashboardStats()
    {
        try {
            $userId = auth()->id();
            
            $totalSeries = WebSeries::where('user_id', $userId)->count();
            $totalEpisodes = Episode::whereHas('webSeries', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count();
            $completedSeries = WebSeries::where('user_id', $userId)
                ->where('status', 'completed')->count();
            
            $completionRate = $totalSeries > 0 ? round(($completedSeries / $totalSeries) * 100) : 0;
            $availableCredits = auth()->user()->credits ?? 0;
            
            return response()->json([
                'success' => true,
                'stats' => [
                    'total_series' => $totalSeries,
                    'total_episodes' => $totalEpisodes,
                    'available_credits' => $availableCredits,
                    'completion_rate' => $completionRate,
                    'completed_series' => $completedSeries
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get recent series for dashboard refresh
     */
    public function getRecentSeries()
    {
        try {
            $webSeries = WebSeries::where('user_id', auth()->id())
                ->with('category')
                ->withCount('episodes')
                ->latest()
                ->take(5)
                ->get();
            
            $html = view('web-series.partials.series-grid', compact('webSeries'))->render();
            
            return response()->json([
                'success' => true,
                'series_html' => $html,
                'series_count' => $webSeries->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function adminIndex()
    {
        $webSeries = WebSeries::with('user', 'episodes', 'category')->latest()->paginate(20);
        return view('admin.web-series.index', compact('webSeries'));
    }
    
    public function adminShow($id)
    {
        $series = WebSeries::with(['user', 'episodes.scenes', 'category'])->findOrFail($id);
        return view('admin.web-series.show', compact('series'));
    }
    
    public function adminDestroy($id)
    {
        try {
            WebSeries::findOrFail($id)->delete();
            return redirect()->route('admin.web-series.index')->with('success', 'Series deleted!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete series');
        }
    }
}