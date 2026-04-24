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
use Illuminate\Support\Facades\Auth;

class WebSeriesController extends Controller
{
    protected $modelsLabService;
    
    public function __construct(ModelsLabService $modelsLabService)
    {
        $this->modelsLabService = $modelsLabService;
    }
    
    private function isDemoUser()
    {
        if (!Auth::check()) {
            return false;
        }
        
        $user = Auth::user();
        $demoMode = $user->demo_mode;
        $isDemo = in_array($demoMode, [true, 1, '1', 'true', 'yes'], true);
        
        Log::info('isDemoUser check', [
            'user_id' => $user->id,
            'demo_mode_raw' => $demoMode,
            'result' => $isDemo
        ]);
        
        return $isDemo;
    }
    
    private function getDemoController()
    {
        return app(DemoController::class);
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
    
    public function generateEpisodeConcept(Request $request, $id)
{
    Log::info('generateEpisodeConcept called', [
        'series_id' => $id,
        'user_id' => auth()->id(),
        'is_demo_user' => $this->isDemoUser(),
        'request_data' => $request->all()
    ]);
    
    if ($this->isDemoUser()) {
        Log::info('Demo user detected, delegating to DemoController', ['series_id' => $id]);
        $demoController = $this->getDemoController();
        return $demoController->generateConcept($request, $id);
    }
    
    try {
        $request->validate([
            'prompt' => 'required|string|min:10|max:500',
            'episode_number' => 'required|integer|min:1'  // Make episode_number required
        ]);

        $series = WebSeries::where('user_id', auth()->id())->with('category')->findOrFail($id);
        $episodeNumber = $request->episode_number;
        
        $concept = $this->modelsLabService->generateConcept(
            $request->prompt, 
            $series->category_id,
            $series->project_name
        );
        
        // Find or create episode by number
        $episode = Episode::where('web_series_id', $series->id)
            ->where('episode_number', $episodeNumber)
            ->first();
        
        if ($episode) {
            $episode->update([
                'title' => 'Episode ' . $episodeNumber,
                'prompt' => $request->prompt,
                'concept' => $concept,
                'status' => 'concept_ready'
            ]);
        } else {
            $episode = Episode::create([
                'web_series_id' => $series->id,
                'user_id' => auth()->id(),
                'episode_number' => $episodeNumber,
                'title' => 'Episode ' . $episodeNumber,
                'prompt' => $request->prompt,
                'concept' => $concept,
                'status' => 'concept_ready',
                'total_scenes' => 0
            ]);
        }
        
        $series->update([
            'concept' => $concept,
            'status' => 'concept_generated'
        ]);

        return response()->json([
            'success' => true,
            'concept' => $concept,
            'episode_id' => $episode->id,
            'episode_number' => $episodeNumber,
            'message' => 'Concept generated for Episode ' . $episodeNumber . '!'
        ]);

    } catch (\Exception $e) {
        Log::error('Unexpected error in generateEpisodeConcept', [
            'series_id' => $id,
            'error_message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to generate concept: ' . $e->getMessage()
        ], 500);
    }
}
    
    public function updateEpisodeConcept(Request $request, $id)
{
    try {
        $request->validate([
            'concept' => 'required|string|min:10',
            'episode_number' => 'required|integer|min:1'
        ]);

        $series = WebSeries::where('user_id', auth()->id())->findOrFail($id);
        $episode = Episode::where('web_series_id', $series->id)
            ->where('episode_number', $request->episode_number)
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
    
    public function generateEpisodeScenes(Request $request, $id)
{
    if ($this->isDemoUser()) {
        $demoController = $this->getDemoController();
        return $demoController->generateScenes($request, $id);
    }
    
    try {
        $request->validate([
            'total_scenes' => 'required|integer|min:5|max:10',
            'episode_number' => 'required|integer|min:1'
        ]);

        $series = WebSeries::where('user_id', auth()->id())->with('category')->findOrFail($id);
        $episode = Episode::where('web_series_id', $series->id)
            ->where('episode_number', $request->episode_number)
            ->firstOrFail();
        
        $scenePrompts = $this->modelsLabService->generateScenePrompts(
            $episode->concept, 
            $request->total_scenes, 
            $request->episode_number
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
                <p><strong>Episode ' . $request->episode_number . ' - Scene ' . $sceneNumber . '</strong></p>
                <p><strong>What happens:</strong> ' . htmlspecialchars($description) . '</p>
                <p><strong>Story Concept:</strong> ' . htmlspecialchars(substr($episode->concept, 0, 200)) . '...</p>
            </div>';
            
            $imagePrompt = $this->modelsLabService->generateImagePrompt(
                $episode->concept, 
                $title, 
                $description, 
                $sceneNumber, 
                $request->episode_number, 
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
            'total_episodes' => $series->episodes()->count(),
            'status' => 'scenes_created'
        ]);
        
        DB::commit();
        
        if (!empty($createdScenes)) {
            $firstScene = $createdScenes[0];
            $this->startAsyncImageGenerationForFirstScene($firstScene->id);
            
            return response()->json([
                'success' => true,
                'message' => $request->total_scenes . ' scenes created! Image generation started.',
                'redirect_after_first_image' => true,
                'series_id' => $series->id,
                'first_scene_id' => $firstScene->id
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $request->total_scenes . ' scenes created!',
            'redirect_after_first_image' => false
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

    private function startAsyncImageGenerationForFirstScene($sceneId)
    {
        Log::info("Starting async image generation for first scene: {$sceneId}");
        
        $url = url("/api/generate-scene-image-async/{$sceneId}");
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Requested-With: XMLHttpRequest'
        ]);
        
        curl_exec($ch);
        curl_close($ch);
        
        Log::info("Async request dispatched for scene: {$sceneId}");
    }

    public function generateSceneImageAsyncBackground($sceneId, $retryCount = 0)
    {
        ignore_user_abort(true);
        set_time_limit(300);
        
        $maxRetries = 3;
        
        Log::info("Starting background image generation for scene: {$sceneId}, retry: {$retryCount}");
        
        try {
            $scene = Scene::find($sceneId);
            if (!$scene) {
                Log::error("Scene {$sceneId} not found for background generation");
                return response()->json(['success' => false]);
            }
            
            if ($scene->generated_image_url) {
                Log::info("Scene {$sceneId} already has image");
                $this->generateNextSceneImageAsync($sceneId);
                return response()->json(['success' => true]);
            }
            
            $scene->update(['status' => 'generating']);
            
            $result = $this->modelsLabService->generateImage(
                $scene->image_prompt,
                1024, 1024, 1
            );
            
            // Check for rate limit (429)
            if (!$result['success'] && isset($result['message'])) {
                $errorMsg = strtolower($result['message']);
                if (str_contains($errorMsg, '429') || str_contains($errorMsg, 'rate limit') || str_contains($errorMsg, 'throttled')) {
                    if ($retryCount < $maxRetries) {
                        $waitTime = pow(2, $retryCount) * 5; // 5s, 10s, 20s
                        Log::warning("Rate limit hit for scene {$sceneId}. Retry {$retryCount}/{$maxRetries} after {$waitTime}s");
                        sleep($waitTime);
                        return $this->generateSceneImageAsyncBackground($sceneId, $retryCount + 1);
                    } else {
                        $scene->update([
                            'status' => 'failed',
                            'error_message' => 'Rate limit exceeded after ' . $maxRetries . ' retries'
                        ]);
                        return response()->json(['success' => false]);
                    }
                }
            }
            
            if ($result['success'] && !empty($result['images'])) {
                $imageUrl = $result['images'][0];
                $validatedUrl = $this->waitForAndValidateImage($imageUrl, $sceneId, $scene->web_series_id);
                
                if ($validatedUrl) {
                    $scene->update([
                        'generated_image_url' => $validatedUrl,
                        'status' => 'completed'
                    ]);
                    Log::info("Image saved for scene {$sceneId}");
                    $this->generateNextSceneImageAsync($sceneId);
                    return response()->json(['success' => true]);
                } else {
                    // Image validation failed - retry
                    if ($retryCount < $maxRetries) {
                        $waitTime = pow(2, $retryCount) * 3;
                        Log::warning("Image validation failed for scene {$sceneId}. Retry {$retryCount}/{$maxRetries} after {$waitTime}s");
                        sleep($waitTime);
                        return $this->generateSceneImageAsyncBackground($sceneId, $retryCount + 1);
                    } else {
                        $scene->update([
                            'status' => 'failed',
                            'error_message' => 'Image validation failed after ' . $maxRetries . ' retries'
                        ]);
                    }
                }
            } else {
                // API error - retry
                if ($retryCount < $maxRetries) {
                    $waitTime = pow(2, $retryCount) * 3;
                    Log::warning("API error for scene {$sceneId}: " . ($result['message'] ?? 'Unknown') . ". Retry {$retryCount}/{$maxRetries} after {$waitTime}s");
                    sleep($waitTime);
                    return $this->generateSceneImageAsyncBackground($sceneId, $retryCount + 1);
                } else {
                    $scene->update([
                        'status' => 'failed',
                        'error_message' => $result['message'] ?? 'Unknown error after ' . $maxRetries . ' retries'
                    ]);
                    Log::error("Failed to generate image for scene {$sceneId} after {$maxRetries} retries");
                }
            }
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error("Background image generation error for scene {$sceneId}: " . $e->getMessage());
            
            // Exception - retry
            if ($retryCount < $maxRetries) {
                $waitTime = pow(2, $retryCount) * 3;
                Log::warning("Exception for scene {$sceneId}. Retry {$retryCount}/{$maxRetries} after {$waitTime}s");
                sleep($waitTime);
                return $this->generateSceneImageAsyncBackground($sceneId, $retryCount + 1);
            }
            
            if (isset($scene)) {
                $scene->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
            return response()->json(['success' => false]);
        }
    }

    private function generateNextSceneImageAsync($currentSceneId)
    {
        try {
            $currentScene = Scene::find($currentSceneId);
            if (!$currentScene) return;
            
            $nextScene = Scene::where('episode_id', $currentScene->episode_id)
                ->where('scene_number', $currentScene->scene_number + 1)
                ->first();
            
            if ($nextScene && !$nextScene->generated_image_url) {
                Log::info("Triggering async generation for next scene: {$nextScene->id}");
                
                $url = url("/api/generate-scene-image-async/{$nextScene->id}");
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                curl_exec($ch);
                curl_close($ch);
            } else {
                Log::info("All images generated for episode {$currentScene->episode_id}");
                Episode::where('id', $currentScene->episode_id)->update(['status' => 'images_completed']);
            }
            
        } catch (\Exception $e) {
            Log::error("Error triggering next scene: " . $e->getMessage());
        }
    }
    
    public function getSceneImageStatus($sceneId)
    {
        try {
            $scene = Scene::find($sceneId);
            
            if (!$scene) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scene not found',
                    'image_url' => null
                ]);
            }
            
            $imageUrl = $scene->generated_image_url ? asset($scene->generated_image_url) : null;
            
            return response()->json([
                'success' => true,
                'image_url' => $imageUrl,
                'status' => $scene->status,
                'scene_id' => $sceneId
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'image_url' => null
            ]);
        }
    }
    
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
    
    public function show($id, $episodeNumber = null)
{
    $series = WebSeries::with('episodes.scenes', 'category')
        ->where('user_id', auth()->id())
        ->findOrFail($id);
    
    // If episode number is provided, load that specific episode's scenes
    if ($episodeNumber) {
        $episode = Episode::where('web_series_id', $id)
            ->where('episode_number', $episodeNumber)
            ->with('scenes')
            ->firstOrFail();
        
        $totalScenes = $episode->scenes->count();
        $completedVideos = $episode->scenes->whereNotNull('video_url')->count();
        $allVideosCompleted = $totalScenes > 0 && $completedVideos == $totalScenes;
        
        // Pass the episode's scenes to the same show.blade.php
        return view('web-series.show', compact('series', 'episode', 'totalScenes', 'completedVideos', 'allVideosCompleted'));
    }
    
    // If no episode number, show all episodes (default view)
    $episodes = $series->episodes()->with('scenes')->orderBy('episode_number')->paginate(9);
    
    return view('web-series.show', compact('series', 'episodes'));
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
    
    public function showEpisodes($seriesId)
    {
        $series = WebSeries::where('user_id', auth()->id())
            ->with('episodes.scenes')
            ->findOrFail($seriesId);
        
        $episodes = $series->episodes()->with('scenes')->orderBy('episode_number')->paginate(9);
        
        return view('web-series.episodes', compact('series', 'episodes'));
    }
    
    public function createEpisode($seriesId)
    {
        $series = WebSeries::where('user_id', auth()->id())->findOrFail($seriesId);
        $nextEpisodeNumber = $series->episodes()->max('episode_number') + 1;
        
        return view('episodes.create', compact('series', 'nextEpisodeNumber'));
    }
    
    public function storeEpisode(Request $request, $seriesId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'concept' => 'nullable|string',
            'episode_number' => 'required|integer|min:1'
        ]);

        $series = WebSeries::where('user_id', auth()->id())->findOrFail($seriesId);
        
        $exists = $series->episodes()->where('episode_number', $request->episode_number)->exists();
        if ($exists) {
            return redirect()->route('web-series.show', $seriesId)
                ->withErrors(['episode_number' => 'Episode number already exists for this series']);
        }
        
        $episode = $series->episodes()->create([
            'web_series_id' => $series->id,
            'user_id' => auth()->id(),
            'episode_number' => $request->episode_number,
            'title' => $request->title,
            'concept' => $request->concept,
            'status' => 'draft',
            'total_scenes' => 0
        ]);
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'episode_id' => $episode->id,
                'message' => "Episode {$episode->episode_number} created successfully!"
            ]);
        }
        
        return redirect()->route('web-series.show', $seriesId)
            ->with('success', "Episode {$episode->episode_number} created successfully!");
    }
    
    public function editEpisode($seriesId, $episodeNumber)
    {
        $series = WebSeries::where('user_id', auth()->id())->findOrFail($seriesId);
        $episode = $series->episodes()->where('episode_number', $episodeNumber)->firstOrFail();
        
        return view('episodes.edit', compact('series', 'episode'));
    }
    
    public function updateEpisode(Request $request, $seriesId, $episodeNumber)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'concept' => 'nullable|string'
        ]);

        $series = WebSeries::where('user_id', auth()->id())->findOrFail($seriesId);
        $episode = $series->episodes()->where('episode_number', $episodeNumber)->firstOrFail();
        
        $episode->update([
            'title' => $request->title,
            'concept' => $request->concept
        ]);
        
        return redirect()->route('web-series.show', $seriesId)
            ->with('success', 'Episode updated successfully!');
    }
    
    public function destroyEpisode($seriesId, $episodeNumber)
{
    try {
        $series = WebSeries::where('user_id', auth()->id())->findOrFail($seriesId);
        $episode = $series->episodes()->where('episode_number', $episodeNumber)->firstOrFail();
        
        // Delete all scenes and their files first
        foreach ($episode->scenes as $scene) {
            // Delete video file if exists
            if ($scene->video_url && Storage::disk('public')->exists($scene->video_url)) {
                Storage::disk('public')->delete($scene->video_url);
            }
            // Delete image file if exists
            if ($scene->generated_image_url && Storage::disk('public')->exists($scene->generated_image_url)) {
                Storage::disk('public')->delete($scene->generated_image_url);
            }
            // Delete the scene record
            $scene->delete();
        }
        
        // Delete episode's final video if exists
        if ($episode->final_video_url && Storage::disk('public')->exists($episode->final_video_url)) {
            Storage::disk('public')->delete($episode->final_video_url);
        }
        
        // Delete the episode
        $episode->delete();
        
        // Re-order remaining episodes (but DON'T do this if it causes issues)
        // Instead of renumbering, just keep the episode numbers as they are
        // Or implement properly
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Episode deleted successfully!'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Destroy episode error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
    
    public function dashboard()
    {
        if ($this->isDemoUser()) {
            $demoController = $this->getDemoController();
            return $demoController->getDashboardView();
        }
        
        try {
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
            $availableCredits = auth()->user()->credits ?? 0;
            
            $stats = [
                'total_series' => $mySeriesCount,
                'total_episodes' => $myEpisodesCount,
                'total_scenes' => $myScenesCount,
                'completed_series' => $completedSeries,
                'available_credits' => $availableCredits
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
            
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            
            $stats = [
                'total_series' => 0,
                'total_episodes' => 0,
                'total_scenes' => 0,
                'completed_series' => 0,
                'available_credits' => 0
            ];
            
            $webSeries = collect();
            $trendingCategories = collect();
            $recommendedSeries = collect();
            $completionRate = 0;
            $mySeriesCount = 0;
            $myEpisodesCount = 0;
            $myScenesCount = 0;
            $completedSeries = 0;
            $totalUsers = 1;
            $avgRating = 4.5;
            
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
    
    // ==================== VIDEO GENERATION METHODS ====================
    
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
            
            if ($scene->video_url) {
                $fullUrl = asset(ltrim($scene->video_url, '/'));
                return response()->json([
                    'success' => true,
                    'message' => 'Video already exists',
                    'video_url' => $fullUrl
                ]);
            }
            
            $scene->update([
                'video_status' => 'generating',
                'video_generation_started_at' => now()
            ]);
            
            $videoUrl = $this->generateVideoWithReplicate($imageUrl, $sceneId, $scene->title);
            
            if ($videoUrl) {
                $localUrl = $this->storeVideoLocally($videoUrl, $sceneId, $scene->web_series_id);
                
                $scene->update([
                    'video_url' => $localUrl,
                    'video_status' => 'completed',
                    'video_generation_completed_at' => now()
                ]);
                
                $fullUrl = asset($localUrl);
                
                return response()->json([
                    'success' => true,
                    'video_url' => $fullUrl,
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
            
            $videoUrl = null;
            if ($scene->video_url) {
                if (filter_var($scene->video_url, FILTER_VALIDATE_URL)) {
                    $videoUrl = $scene->video_url;
                } 
                elseif (str_starts_with($scene->video_url, 'videos/')) {
                    $videoUrl = asset($scene->video_url);
                }
                elseif (str_starts_with($scene->video_url, 'storage/')) {
                    $videoUrl = asset($scene->video_url);
                }
                else {
                    $videoUrl = asset(ltrim($scene->video_url, '/'));
                }
            }
            
            Log::info("Video status check for scene {$sceneId}: video_url = " . ($videoUrl ?? 'null'));
            
            return response()->json([
                'success' => true,
                'status' => $scene->video_status ?? 'pending',
                'video_url' => $videoUrl,
                'error_message' => $scene->video_error_message ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error('Check video status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    private function generateVideoWithReplicate($imageUrl, $sceneId, $sceneTitle)
    {
        Log::info("Generating video for scene: {$sceneId}");
        Log::info("Input image URL: {$imageUrl}");
        
        $apiToken = env('REPLICATE_API_TOKEN');
        
        if (!$apiToken) {
            Log::error('REPLICATE_API_TOKEN not set');
            return null;
        }
        
        $scene = Scene::find($sceneId);
        if (!$scene) {
            Log::error("Scene {$sceneId} not found");
            return null;
        }
        
        $series = WebSeries::find($scene->web_series_id);
        if (!$series) {
            Log::error("Series not found for scene {$sceneId}");
            return null;
        }
        
        $videoPrompt = $this->getVideoPromptFromTemplate($scene, $series);
        Log::info("Video prompt: " . substr($videoPrompt, 0, 500));
        
        $imageContent = $this->getImageContentFromLocalPath($imageUrl);
        
        if (!$imageContent) {
            Log::error("Failed to get image content");
            return null;
        }
        
        $base64Image = base64_encode($imageContent);
        $dataUri = 'data:image/png;base64,' . $base64Image;
        
        Log::info("Image converted to base64, length: " . strlen($base64Image));
        
        $payload = [
            'version' => 'stability-ai/stable-video-diffusion:3f0457e4619daac51203dedb472816fd4af51f3149fa7a9e0b5ffcf1b8172438',
            'input' => [
                'input_image' => $dataUri,
                'video_length' => '14_frames_with_svd',
                'sizing_strategy' => 'maintain_aspect_ratio',
                'frames_per_second' => 6,
            ]
        ];
        
        Log::info("Calling Replicate API for image-to-video");
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.replicate.com/v1/predictions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiToken,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            Log::info("Replicate API response code: {$httpCode}");
            
            if ($httpCode !== 201) {
                Log::error("Replicate API error: " . $response);
                return null;
            }
            
            $result = json_decode($response, true);
            $predictionId = $result['id'] ?? null;
            
            if (!$predictionId) {
                Log::error("No prediction ID received");
                return null;
            }
            
            Log::info("Prediction ID: {$predictionId}. Polling for video...");
            
            return $this->pollReplicatePrediction($predictionId, $apiToken, $sceneId, 60, 3);
            
        } catch (\Exception $e) {
            Log::error('Replicate error: ' . $e->getMessage());
            return null;
        }
    }
    
    private function getVideoPromptFromTemplate($scene, $series)
    {
        $template = \App\Models\CategoryTemplate::where('category_id', $series->category_id)
            ->where('is_active', true)
            ->first();
        
        $videoPrompt = null;
        
        if ($template && isset($template->category_prompt['video_generator'])) {
            $videoPrompt = $template->category_prompt['video_generator'];
            $videoPrompt = str_replace('{scene_title}', $scene->title, $videoPrompt);
            $videoPrompt = str_replace('{scene_description}', $scene->summary ?? $scene->title, $videoPrompt);
            $videoPrompt = str_replace('{category}', $series->category->name ?? 'Web Series', $videoPrompt);
            Log::info("Using video prompt from database template");
        } else {
            $videoPrompt = "A cinematic video of {$scene->title}. Smooth camera movement, dramatic lighting, professional cinematography. High quality MP4, natural motion, atmospheric.";
            Log::info("Using default video prompt");
        }
        
        return $videoPrompt;
    }
    
    private function getImageContentFromLocalPath($url)
    {
        try {
            $path = parse_url($url, PHP_URL_PATH);
            
            $possiblePaths = [
                public_path($path),
                public_path('storage/' . ltrim($path, '/storage/')),
                base_path(ltrim($path, '/')),
            ];
            
            foreach ($possiblePaths as $fullPath) {
                if (file_exists($fullPath)) {
                    Log::info("Found image at: {$fullPath}");
                    return file_get_contents($fullPath);
                }
            }
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $content = curl_exec($ch);
            curl_close($ch);
            
            if ($content) {
                Log::info("Downloaded image from URL");
                return $content;
            }
            
            Log::error("Image not found at any location: {$url}");
            return null;
            
        } catch (\Exception $e) {
            Log::error("Error getting image content: " . $e->getMessage());
            return null;
        }
    }
    
    private function pollReplicatePrediction($predictionId, $apiToken, $sceneId, $maxAttempts = 60, $delaySeconds = 3)
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            Log::info("Polling attempt {$attempt} for scene {$sceneId}");
            
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
                    Log::warning("Polling HTTP error: {$httpCode}");
                    continue;
                }
                
                $result = json_decode($response, true);
                $status = $result['status'] ?? 'processing';
                
                Log::info("Prediction status: {$status}");
                
                if ($status === 'succeeded') {
                    $output = $result['output'] ?? null;
                    
                    Log::info("=== VIDEO GENERATION SUCCESS ===");
                    Log::info("Prediction ID: {$predictionId}");
                    Log::info("Scene ID: {$sceneId}");
                    
                    $videoUrl = null;
                    
                    if (is_array($output)) {
                        if (isset($output['video'])) {
                            $videoUrl = $output['video'];
                        } elseif (isset($output['mp4'])) {
                            $videoUrl = $output['mp4'];
                        } elseif (isset($output[0]) && is_string($output[0])) {
                            $videoUrl = $output[0];
                        } elseif (is_string($output)) {
                            $videoUrl = $output;
                        }
                    } elseif (is_string($output)) {
                        $videoUrl = $output;
                    }
                    
                    if ($videoUrl) {
                        Log::info("Video URL extracted: {$videoUrl}");
                        return $videoUrl;
                    } else {
                        Log::error("No video URL found in output", ['output' => $output]);
                        return null;
                    }
                }
                
                if ($status === 'failed') {
                    $error = $result['error'] ?? 'Unknown error';
                    Log::error("Replicate prediction failed: " . $error);
                    Log::error("Full response: " . json_encode($result, JSON_PRETTY_PRINT));
                    return null;
                }
                
            } catch (\Exception $e) {
                Log::error("Error polling Replicate: " . $e->getMessage());
            }
        }
        
        Log::error("Polling timeout for scene {$sceneId}");
        return null;
    }
    
    private function storeVideoLocally($videoUrl, $sceneId, $seriesId)
    {
        try {
            $directory = public_path("videos/series/{$seriesId}/scenes/{$sceneId}");
            
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
                return "videos/series/{$seriesId}/scenes/{$sceneId}/{$filename}";
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
            Log::error('Get dashboard stats error: ' . $e->getMessage());
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
    
    // ==================== RETRY METHODS ====================
    
    public function retryFailedImages()
    {
        Log::info('=== RETRYING FAILED IMAGE GENERATIONS ===');
        
        $failedScenes = Scene::where('status', 'failed')
            ->whereNull('generated_image_url')
            ->whereNull('video_url')
            ->take(10)
            ->get();
        
        $retriedCount = 0;
        $stillFailedCount = 0;
        
        foreach ($failedScenes as $scene) {
            Log::info("Retrying image generation for scene {$scene->id}");
            
            $scene->update([
                'status' => 'pending',
                'error_message' => null
            ]);
            
            $this->generateSceneImageAsyncBackground($scene->id);
            $retriedCount++;
            
            sleep(2);
        }
        
        Log::info("Retry complete. Retried: {$retriedCount}, Still failed: {$stillFailedCount}");
        
        return response()->json([
            'success' => true,
            'retried_count' => $retriedCount,
            'message' => "Successfully retried {$retriedCount} scenes"
        ]);
    }
    
    public function retrySingleScene($sceneId)
    {
        $scene = Scene::where('id', $sceneId)
            ->whereHas('webSeries', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();
        
        $scene->update([
            'status' => 'pending',
            'error_message' => null
        ]);
        
        $this->generateSceneImageAsyncBackground($sceneId);
        
        return response()->json([
            'success' => true,
            'message' => 'Retry started for scene ' . $sceneId
        ]);
    }
}