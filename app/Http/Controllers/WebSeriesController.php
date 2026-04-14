<?php
// app/Http/Controllers/WebSeriesController.php

namespace App\Http\Controllers;

use App\Models\WebSeries;
use App\Models\Episode;
use App\Models\Scene;
use App\Models\Category;
use App\Models\ImageGenerationLog;
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
            
            // Use category_id from the series
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
                
                // Use the new image prompt method with category_id
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
    
    public function show($id)
    {
        $series = WebSeries::with('episodes.scenes', 'category')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        return view('web-series.show', compact('series'));
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
        $stats = [
            'total_series' => WebSeries::where('user_id', auth()->id())->count(),
            'total_episodes' => Episode::whereHas('webSeries', function($q) {
                $q->where('user_id', auth()->id());
            })->count(),
            'total_scenes' => Scene::whereHas('webSeries', function($q) {
                $q->where('user_id', auth()->id());
            })->count(),
            'completed_series' => WebSeries::where('user_id', auth()->id())
                ->where('status', 'completed')->count()
        ];
        
        $webSeries = WebSeries::where('user_id', auth()->id())
            ->with('category')
            ->withCount('episodes')
            ->latest()
            ->paginate(12);
        
        return view('web-series.dashboard', compact('webSeries', 'stats'));
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
    
    public function generateVideo(Request $request)
    {
        try {
            $request->validate([
                'series_id' => 'required|integer',
                'image_urls' => 'required|array',
                'transition' => 'string',
                'duration_per_image' => 'numeric',
                'resolution' => 'string',
                'background_music' => 'string'
            ]);
            
            // Here you would implement actual video generation logic
            // For now, return a placeholder response
            
            return response()->json([
                'success' => true,
                'video_url' => 'https://sample-videos.com/video123/mp4/720/big_buck_bunny_720p_1mb.mp4',
                'message' => 'Video generated successfully'
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