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
    
    private function isDemoUser()
    {
        return auth()->check() && auth()->id() == 141;
    }
    
    private function getDemoController()
    {
        return new DemoController();
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
        if ($this->isDemoUser()) {
            $demoController = $this->getDemoController();
            return $demoController->generateConcept($request, $id);
        }
        
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
        if ($this->isDemoUser()) {
            $demoController = $this->getDemoController();
            return $demoController->generateScenes($request, $id);
        }
        
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
            
            $createdScenes = [];
            
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
                
                $scene = Scene::create([
                    'episode_id' => $episode->id,
                    'web_series_id' => $series->id,
                    'scene_number' => $sceneNumber,
                    'title' => $title,
                    'content' => $content,
                    'image_prompt' => $imagePrompt,
                    'summary' => substr($description, 0, 150),
                    'status' => 'pending'
                ]);
                
                $createdScenes[] = $scene;
            }
            
            $episode->update([
                'total_scenes' => $request->total_scenes,
                'status' => 'scenes_created'
            ]);
            
            $series->update([
                'total_episodes' => 1,
                'status' => 'scenes_created'
            ]);
            
            DB::commit();
            
            // Start sequential image generation for first scene
            if (!empty($createdScenes)) {
                $this->generateImageForScene($createdScenes[0]->id);
            }

            return response()->json([
                'success' => true,
                'message' => $request->total_scenes . ' scenes created successfully! Images are being generated in the background.',
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
    
    /**
     * Generate image for a specific scene (called from backend)
     */
    public function generateImageForScene($sceneId)
    {
        try {
            $scene = Scene::find($sceneId);
            if (!$scene) {
                Log::error("Scene {$sceneId} not found for image generation");
                return;
            }
            
            if ($scene->generated_image_url) {
                Log::info("Scene {$sceneId} already has image");
                $this->generateNextSceneImage($sceneId);
                return;
            }
            
            $prompt = $scene->image_prompt;
            $seriesId = $scene->web_series_id;
            $userId = $scene->webSeries->user_id ?? auth()->id();
            
            Log::info("Generating image for scene {$sceneId}");
            
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
                        Log::info("Image saved for scene {$sceneId}");
                        $this->generateNextSceneImage($sceneId);
                    }
                } else {
                    Log::info("Image generation for scene {$sceneId} is processing asynchronously");
                }
            } else {
                Log::error("Failed to generate image for scene {$sceneId}");
                $scene->update(['status' => 'failed']);
            }
            
        } catch (\Exception $e) {
            Log::error("Error generating image for scene {$sceneId}: " . $e->getMessage());
            if (isset($scene)) {
                $scene->update(['status' => 'failed']);
            }
        }
    }
    
    /**
     * Generate image for the next scene
     */
    private function generateNextSceneImage($currentSceneId)
    {
        try {
            $currentScene = Scene::find($currentSceneId);
            if (!$currentScene) return;
            
            $nextScene = Scene::where('episode_id', $currentScene->episode_id)
                ->where('scene_number', $currentScene->scene_number + 1)
                ->first();
            
            if ($nextScene && !$nextScene->generated_image_url) {
                Log::info("Moving to next scene: {$nextScene->id}");
                $this->generateImageForScene($nextScene->id);
            } else {
                Log::info("All images generated for episode {$currentScene->episode_id}");
                Episode::where('id', $currentScene->episode_id)->update(['status' => 'images_completed']);
            }
            
        } catch (\Exception $e) {
            Log::error("Error generating next scene image: " . $e->getMessage());
        }
    }
    
    private function isImageUrlValid($url, $maxAttempts = 3, $delaySeconds = 2)
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = Http::timeout(10)->head($url);
                
                if ($response->successful()) {
                    $contentType = $response->header('Content-Type');
                    if (str_contains($contentType, 'image')) {
                        return true;
                    }
                }
                
                $response = Http::timeout(10)->get($url);
                
                if ($response->successful()) {
                    $contentType = $response->header('Content-Type');
                    if (str_contains($contentType, 'image')) {
                        return true;
                    }
                }
                
                if ($attempt < $maxAttempts) {
                    sleep($delaySeconds);
                }
                
            } catch (\Exception $e) {
                Log::warning("Error checking image URL (Attempt {$attempt}): " . $e->getMessage());
                if ($attempt < $maxAttempts) {
                    sleep($delaySeconds);
                }
            }
        }
        
        return false;
    }
    
    private function storeImageLocally($url, $sceneId, $seriesId)
    {
        try {
            $directory = public_path("images/series/{$seriesId}/scenes/{$sceneId}");
            
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            
            $filename = 'scene_' . $sceneId . '_' . time() . '.png';
            $fullPath = $directory . '/' . $filename;
            
            $imageContent = Http::timeout(60)->get($url)->body();
            
            if (empty($imageContent)) {
                throw new \Exception("Downloaded image content is empty");
            }
            
            file_put_contents($fullPath, $imageContent);
            
            if (file_exists($fullPath) && filesize($fullPath) > 0) {
                return asset("images/series/{$seriesId}/scenes/{$sceneId}/{$filename}");
            }
            
            throw new \Exception("Failed to save file");
            
        } catch (\Exception $e) {
            Log::error('Failed to store image: ' . $e->getMessage());
            return null;
        }
    }
    
    private function waitForAndValidateImage($imageUrl, $sceneId, $seriesId, $maxAttempts = 60, $delaySeconds = 3)
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            if ($this->isImageUrlValid($imageUrl, 2, 1)) {
                $localUrl = $this->storeImageLocally($imageUrl, $sceneId, $seriesId);
                if ($localUrl) {
                    return $localUrl;
                }
                return $imageUrl;
            }
            
            if ($attempt < $maxAttempts) {
                sleep($delaySeconds);
            }
        }
        
        return null;
    }
    
    public function handleImageWebhook(Request $request)
    {
        Log::info('=== WEBHOOK RECEIVED ===');
        
        try {
            $trackingId = $request->query('tracking_id');
            $sceneId = $request->query('scene_id');
            $seriesId = $request->query('series_id');
            $data = $request->all();
            
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
                            $this->generateNextSceneImage($sceneId);
                        }
                        
                        Log::info("Image saved for scene {$sceneId}");
                    }
                }
            }
            
            return response()->json(['status' => 'success']);
            
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
    
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
    
    public function show($id)
    {
        $series = WebSeries::with('episodes.scenes', 'category')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        return view('web-series.show', compact('series'));
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
        if ($this->isDemoUser()) {
            $demoController = $this->getDemoController();
            return $demoController->getDashboardView();
        }
        
        $userId = auth()->id();
        
        $mySeriesCount = WebSeries::where('user_id', $userId)->count();
        $myEpisodesCount = Episode::whereHas('webSeries', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->count();
        $myScenesCount = Scene::whereHas('webSeries', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->count();
        $completedSeries = WebSeries::where('user_id', $userId)
            ->where('status', 'completed')->count();
        
        $completionRate = $mySeriesCount > 0 ? round(($completedSeries / $mySeriesCount) * 100) : 0;
        
        $stats = [
            'total_series' => $mySeriesCount,
            'total_episodes' => $myEpisodesCount,
            'total_scenes' => $myScenesCount,
            'completed_series' => $completedSeries
        ];
        
        $webSeries = WebSeries::where('user_id', $userId)
            ->with('category')
            ->withCount('episodes')
            ->latest()
            ->paginate(12);
        
        $trendingCategories = Category::with('template')
            ->where('is_active', true)
            ->withCount('webSeries')
            ->orderBy('web_series_count', 'desc')
            ->take(10)
            ->get();
        
        if ($trendingCategories->count() == 0) {
            $trendingCategories = Category::with('template')
                ->where('is_active', true)
                ->orderBy('display_order', 'asc')
                ->take(10)
                ->get();
            
            foreach ($trendingCategories as $index => $category) {
                $category->series_count = rand(5, 50);
            }
        } else {
            foreach ($trendingCategories as $category) {
                $category->series_count = $category->web_series_count;
            }
        }
        
        $recommendedSeries = collect();
        
        $userCategoryIds = WebSeries::where('user_id', $userId)
            ->whereNotNull('category_id')
            ->pluck('category_id')
            ->unique()
            ->toArray();
        
        if (!empty($userCategoryIds)) {
            $recommendedSeries = WebSeries::with(['user', 'category', 'episodes'])
                ->where('user_id', '!=', $userId)
                ->whereIn('category_id', $userCategoryIds)
                ->where('status', 'completed')
                ->withCount('episodes')
                ->latest()
                ->take(10)
                ->get();
        }
        
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
        
        foreach ($recommendedSeries as $series) {
            $series->views_count = rand(100, 5000);
            $series->rating = rand(40, 50) / 10;
        }
        
        $totalUsers = \App\Models\User::count();
        $avgRating = 4.9;
        
        return view('web-series.dashboard', compact(
            'webSeries', 'stats', 'trendingCategories',
            'mySeriesCount', 'myEpisodesCount', 'myScenesCount',
            'completedSeries', 'completionRate', 'totalUsers', 'avgRating', 'recommendedSeries'
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
    
    public function generateSceneVideo(Request $request)
    {
        try {
            $request->validate([
                'scene_id' => 'required|integer|exists:scenes,id',
                'image_url' => 'required|string|url'
            ]);
            
            $sceneId = $request->scene_id;
            $imageUrl = $request->image_url;
            
            $scene = Scene::where('id', $sceneId)
                ->whereHas('webSeries', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->firstOrFail();
            
            $scene->update([
                'video_status' => 'generating',
                'video_generation_started_at' => now()
            ]);
            
            $videoUrl = $this->generateVideoWithReplicate($imageUrl, $sceneId, $scene->title);
            
            if ($videoUrl) {
                $localUrl = $this->storeVideoLocally($videoUrl, $sceneId, $scene->web_series_id);
                
                $scene->update([
                    'video_url' => $localUrl ?? $videoUrl,
                    'video_status' => 'completed',
                    'video_generation_completed_at' => now()
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
            Log::error('Generate scene video error: ' . $e->getMessage());
            
            if (isset($scene)) {
                $scene->update([
                    'video_status' => 'failed',
                    'video_error_message' => $e->getMessage()
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate video: ' . $e->getMessage()
            ], 500);
        }
    }
    
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
    
    private function generateVideoWithReplicate($imageUrl, $sceneId, $sceneTitle)
    {
        $apiToken = env('REPLICATE_API_TOKEN');
        
        if (!$apiToken) {
            Log::error('REPLICATE_API_TOKEN not set');
            return null;
        }
        
        $prompts = [
            "A cinematic video of {$sceneTitle} with smooth camera movement, natural motion, and atmospheric lighting.",
            "Bring this scene to life with gentle motion, a subtle camera pan, and dramatic atmospheric lighting.",
            "Transform this static image into a dynamic cinematic shot with fluid motion and depth of field."
        ];
        
        $prompt = $prompts[array_rand($prompts)];
        
        $payload = [
            'version' => '69599cebad125acfd3d5c682c187702ea7a84d537603e46b468a44fe94c5fd13',
            'input' => [
                'width' => 704,
                'height' => 480,
                'prompt' => $prompt,
                'frame_rate' => 25,
                'num_frames' => 121,
                'guidance_scale' => 3,
                'negative_prompt' => 'worst quality, inconsistent motion, blurry, jittery, distorted',
                'num_inference_steps' => 40
            ]
        ];
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.replicate.com/v1/predictions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiToken,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 201) {
                return null;
            }
            
            $result = json_decode($response, true);
            $predictionId = $result['id'] ?? null;
            
            if (!$predictionId) {
                return null;
            }
            
            return $this->pollReplicatePrediction($predictionId, $apiToken, $sceneId);
            
        } catch (\Exception $e) {
            Log::error('Replicate error: ' . $e->getMessage());
            return null;
        }
    }
    
    private function pollReplicatePrediction($predictionId, $apiToken, $sceneId, $maxAttempts = 60, $delaySeconds = 3)
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
                    continue;
                }
                
                $result = json_decode($response, true);
                
                if ($result['status'] === 'succeeded') {
                    $output = $result['output'] ?? null;
                    if (is_array($output) && isset($output[0])) {
                        return $output[0];
                    }
                    if (is_string($output)) {
                        return $output;
                    }
                    return null;
                }
                
                if ($result['status'] === 'failed') {
                    return null;
                }
                
            } catch (\Exception $e) {
                Log::error('Error polling Replicate: ' . $e->getMessage());
            }
        }
        
        return null;
    }
    
    private function storeVideoLocally($videoUrl, $sceneId, $seriesId)
    {
        try {
            $directory = storage_path("app/public/videos/series/{$seriesId}/scenes/{$sceneId}");
            
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            
            $filename = 'video_' . $sceneId . '_' . time() . '.mp4';
            $fullPath = $directory . '/' . $filename;
            
            $ch = curl_init($videoUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            $videoContent = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200 || empty($videoContent)) {
                throw new \Exception("Failed to download video");
            }
            
            file_put_contents($fullPath, $videoContent);
            
            if (file_exists($fullPath) && filesize($fullPath) > 0) {
                return Storage::url("videos/series/{$seriesId}/scenes/{$sceneId}/{$filename}");
            }
            
            throw new \Exception("Failed to save file");
            
        } catch (\Exception $e) {
            Log::error('Failed to store video: ' . $e->getMessage());
            return null;
        }
    }
    
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
    
    public function getDashboardStats()
    {
        if ($this->isDemoUser()) {
            $demoController = $this->getDemoController();
            return $demoController->getDashboardStats();
        }
        
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