<?php

namespace App\Http\Controllers;

use App\Models\AiCustomGenerator;
use App\Services\AiGenService;
use App\Helpers\AiGen;
use App\Models\UserAiProject;
use App\Models\TknUserCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class AiGenController extends Controller
{
    protected AiGenService $aiGenService;

    public function __construct(AiGenService $aiGenService)
    {
        $this->aiGenService = $aiGenService;
    }
    
    public function index(Request $request, string $slug)
    {
        $generator = AiCustomGenerator::with('aiModel')->where('slug', $slug)->first();
        
        if (!$generator) abort(404);

        if (!$generator->relationLoaded('aiModel')) {
            $generator->load('aiModel');
        }
        
        $projects = UserAiProject::where(['cat_id' => $generator->id, 'user_id' => Auth::id()])
            ->orderBy('id', 'desc')
            ->get();

        $viewData = $this->aiGenService->prepareViewData($generator, $projects);

        return view('user.aigen.index', $viewData);
    }

    // RESERVED METHODS - Keep existing route mappings
    public function sceneGen(Request $request)
    {
        return $this->index($request, 'visual-creator');
    }

    public function clipGen(Request $request)
    {
        return $this->index($request, 'movie-generator');
    }
    
    public function reelMaker(Request $request)
    {
        return $this->index($request, 'reels-maker');
    }


    
    public function avatarGen(Request $request)
    {
        return $this->index($request, 'ai-avatar-creator');
    }

    public function videoTitle(Request $request)
    {
        return $this->index($request, 'video-title-generator');
    }

    public function videoDesc(Request $request)
    {
        return $this->index($request, 'video-description-generator');
    }

    public function videoScript(Request $request)
    {
        return $this->index($request, 'video-script-generator');
    }

    public function videoDialogue(Request $request)
    {
        return $this->index($request, 'video-dialogue-generator');
    }

    public function videoTags(Request $request)
    {
        return $this->index($request, 'video-tags-generator');
    }


    

    public function titleGen(Request $request)
    {
        return $this->index($request, 'title-generator');
    }

    public function descriptionGen(Request $request)
    {
        return $this->index($request, 'description-generator');
    }

    public function voiceoverGen(Request $request)
    {
        return $this->index($request, 'voice-clone');
    }

    public function musicGen(Request $request)
    {
        return $this->index($request, 'music-sound');
    }

    public function soundEffectsGen(Request $request)
    {
        return $this->index($request, 'sfx-generator');
    }

    public function tagsGen(Request $request)
    {
        return $this->index($request, 'tags-generator');
    }

    public function imgClone(Request $request)
    {
        return $this->index($request, 'image-clone');
    }
    
    public function vidClone(Request $request)
    {
        return $this->index($request, 'video-clone');
    }
    
    public function voiceClone(Request $request)
    {
        return $this->index($request, 'voice-clone');
    }
    
    public function musicSound(Request $request)
    {
        return $this->index($request, 'music-sound');
    }
    
    public function contentClone(Request $request)
    {
        return $this->index($request, 'content-clone');
    }
    
    public function imgGen(Request $request)
    {
        return $this->index($request, 'ai-image');
    }
    
    public function vidGen(Request $request)
    {
        return $this->index($request, 'ai-video');
    }

    public function streamReply(Request $request, string $slug)
    {
        try {
            $generator = AiCustomGenerator::with('aiModel')->where('slug', $slug)->first();
            if (!$generator) abort(404);
            
            $userId = Auth::id();
            $postData = $request->all();
            
            // Validate user limits
            $limitError = $this->validateUserLimits($generator, $userId);
            if ($limitError) return $limitError;
            
            // Create hook track ID
            $hookTrackId = $generator->aiModel->name . date('YmdHis');
            
            // Add prompt template if applicable
            if ($generator->prompt_template) {
                $postData['prompt_template'] = $generator->prompt_template;
            }
            
            // Generate the full text using your existing method
            $response = AiGen::generate($generator->aiModel, $postData, $hookTrackId, $generator);
            $responseData = $response->getData(true);

            if (!isset($responseData['text'])) {
                throw new Exception('No text generated');
            }

            $fullText = $responseData['text'];
            
            // Create project record
            $project = UserAiProject::create([
                'user_id' => $userId,
                'response' => $hookTrackId,
                'status' => 2, // Complete
                'cat_id' => $generator->id,
                'input' => $postData['prompt'] ?? 'Generated content',
                'title' => $postData['title'] ?? null,
                'output' => $fullText,
                'payload' => json_encode($postData),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Stream the text word by word with variable timing
            return response()->stream(function() use ($fullText) {
                $words = explode(' ', $fullText);
                $currentText = '';
                
                foreach ($words as $index => $word) {
                    $currentText .= ($index > 0 ? ' ' : '') . $word;
                    
                    echo "data: " . json_encode([
                        'content' => $currentText,
                        'isPartial' => ($index < count($words) - 1)
                    ]) . "\n\n";
                    
                    ob_flush();
                    flush();
                    
                    // Variable delay: longer for punctuation, shorter for short words
                    $delay = 50000; // base 0.05s
                    if (str_ends_with($word, '.') || str_ends_with($word, '!') || str_ends_with($word, '?')) {
                        $delay = 200000; // 0.2s after sentences
                    } elseif (strlen($word) < 4) {
                        $delay = 30000; // 0.03s for short words
                    }
                    usleep($delay);
                }
                
                echo "data: [DONE]\n\n";
                
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no'
            ]);
            
        } catch (Exception $e) {
            Log::error('Stream Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function savePayload(Request $request)
    {
        try {
            $userId = Auth::id();
            $postData = $request->all();
            
            $generator = AiCustomGenerator::with('aiModel')->find($postData['ai_gen_id']);
            
            if (!$generator) {
                return response()->json(['message' => 'AI generator not found'], 404);
            }

            // Validate user limits
            $limitError = $this->validateUserLimits($generator, $userId);
            if ($limitError) {
                return $limitError;
            }

            $hookTrackId = $generator->aiModel->name . date('YmdHis');

            // Create project record BEFORE generation
            $projectData = [
                'user_id' => $userId,
                'response' => $hookTrackId,
                'status' => 0, // Pending
                'cat_id' => $generator->id,
                'input' => $postData['prompt'] ?? $postData['description'] ?? 'Generated content',
                'output' => null,
                'payload' => json_encode($postData),
                'created_at' => now(),
                'updated_at' => now()
            ];

            // Add title if provided
            if (isset($postData['title'])) {
                $projectData['title'] = $postData['title'];
            }

            $project = UserAiProject::create($projectData);
            $projectId = $project->id;

            if (!$projectId) {
                throw new Exception('Failed to create project record');
            }

            // Add prompt template if applicable
            if ($generator->prompt_template) {
                $postData['prompt_template'] = $generator->prompt_template;
            }

            // Use generic generation method
            $response = AiGen::generate($generator->aiModel, $postData, $hookTrackId, $generator);
            
            // Check if response is immediate success
            $responseData = $response->getData(true);
            if (isset($responseData['status']) && $responseData['status'] === 'success' && isset($responseData['output'])) {
                // Update project with output path immediately
                UserAiProject::where('id', $projectId)->update([
                    'output' => $responseData['output'],
                    'status' => 2, // Completed
                    'updated_at' => now()
                ]);
            }
            
            return $response;

        } catch (Exception $e) {
            Log::error('SavePayload Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['message' => 'An error occurred while processing your request'], 500);
        }
    }
    
    // Add this method to AiGenController
    private function handleGenerationException(Exception $e, string $context = ''): JsonResponse
    {
        Log::error("AI Generation Error {$context}: " . $e->getMessage(), [
            'user_id' => Auth::id(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'message' => 'An error occurred while processing your request'
        ], 500);
    }

    private function validateUserLimits(AiCustomGenerator $generator, int $userId): ?\Illuminate\Http\JsonResponse
    {
        $isAdmin = Auth::user()->type === 'admin';
        
        if ($isAdmin) {
            return null;
        }

        $type = $generator->aiModel->name;
        $dailyLimitKey = $type . '_daily_limit_left-' . $userId;
        $tempBlockKey = $type . '_temp_block-' . $userId;
        
        $isTempBlocked = Cache::get($tempBlockKey);

        if ($isTempBlocked) {
            return response()->json([
                'status' => 'block', 
                'message' => $generator->temp_warning
            ], 403);
        }

        // Credit check for specific types
        if ($type === 'img-vid') {
            $userCredit = TknUserCredit::where('user_id', $userId)->first();
            if (!$userCredit || $userCredit->remaining_credits <= 0) {
                return response()->json([
                    'message' => 'Insufficient video credits. Please purchase more credits to continue.'
                ], 402);
            }
        }

        return null;
    }

    // Keep existing webhook methods (saveResponse, saveReplicate, etc.)
    
    public function saveResponse(Request $request)
    {
        $trackId = null;
        
        try {
            if ($request->isMethod('post')) {
                $trackId = $request->track_id;
                
                // Store webhook hit
                DB::table('ml_hits')->insert([
                    'track_id' => $trackId,
                    'status' => ($request->status == 'success' ? 2 : 1),
                    'json' => json_encode($request->all())
                ]);

                // Check if it's a Pro Studio webhook
                if (str_starts_with($trackId, 'prostudio-') || str_starts_with($trackId, 'provoice-') || 
                    str_starts_with($trackId, 'promusic-') || str_starts_with($trackId, 'prosfx-')) {
                    $this->processProStudioWebhook($trackId, $request->output[0] ?? null);
                } else {
                    // Process regular AI generations
                    $this->processWebhookStatus($request->status, $trackId, $request->output[0] ?? null);
                }
            } else {
                $this->processPendingProjects();
            }

            return response()->json(['message' => 'Success'], 200);

        } catch (Exception $e) {
            Log::error("[$trackId] SaveResponse Error: " . $e->getMessage());
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    private function processProStudioWebhook(string $trackId, ?string $outputUrl): void
    {
        try {
            Log::info("Processing Pro Studio webhook: $trackId", ['output_url' => $outputUrl]);
            
            $project = null;
            $field = null;
            $historyKey = null;
            
            // Find project based on track ID pattern
            if (str_starts_with($trackId, 'provoice-')) {
                // Correct JSON query syntax
                $project = ProStudioProject::where('generation_history', 'LIKE', '%"voiceover_track_id":"' . $trackId . '"%')->first();
                $field = 'voiceover_path';
                $historyKey = 'voiceover_completed';
                
            } elseif (str_starts_with($trackId, 'promusic-')) {
                $project = ProStudioProject::where('generation_history', 'LIKE', '%"music_track_id":"' . $trackId . '"%')->first();
                $field = 'music_path';
                $historyKey = 'music_completed';
                
            } elseif (str_starts_with($trackId, 'prosfx-')) {
                $project = ProStudioProject::where('generation_history', 'LIKE', '%"sfx_track_id":"' . $trackId . '"%')->first();
                $field = 'sfx_path';
                $historyKey = 'sfx_completed';
                
            } elseif (str_starts_with($trackId, 'prostudio-')) {
                // For text generation (description/dialogue)
                $baseTrackId = preg_replace('/-(desc|dial)$/', '', $trackId);
                $project = ProStudioProject::where('track_id', $baseTrackId)->first();
                $field = null;
                
            } else {
                Log::warning("Unknown Pro Studio track ID format: $trackId");
                return;
            }
            
            if (!$project) {
                Log::warning("Pro Studio project not found for webhook: $trackId");
                return;
            }
            
            if (!$outputUrl) {
                Log::warning("No output URL for Pro Studio webhook: $trackId");
                return;
            }
            
            // Download the file
            $localPath = $this->downloadProStudioOutput($outputUrl, $project->user_id, $trackId);
            Log::info("Downloaded Pro Studio output: $trackId -> $localPath");
            
            // Update project based on type
            if ($field) {
                $project->update([
                    $field => $localPath,
                    'generation_history' => array_merge(
                        $project->generation_history ?? [],
                        [$historyKey => now()->toDateTimeString(), $field => $localPath]
                    )
                ]);
                
                Log::info("Updated Pro Studio project {$project->id}: $field = $localPath");
            } else {
                // For text generation, just log completion
                $project->update([
                    'generation_history' => array_merge(
                        $project->generation_history ?? [],
                        [str_contains($trackId, '-desc') ? 'description_completed' : 'dialogue_completed' => now()->toDateTimeString()]
                    )
                ]);
            }
            
        } catch (Exception $e) {
            Log::error("Pro Studio webhook processing failed for $trackId: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function downloadProStudioOutput(string $url, int $userId, string $trackId): string
    {
        $path = 'ai-projects/pro-studio/' . $userId;
        return AiGen::convertLinksToLocal($url, $path, $trackId);
    }

    private function processPendingProjects(): void
    {
        $pendingProjects = UserAiProject::whereNull('output')->get();

        foreach ($pendingProjects as $project) {
            $hit = DB::table('ml_hits')
                ->where(['track_id' => $project->response, 'status' => 2])
                ->first();

            if ($hit) {
                $data = json_decode($hit->json, true);
                $path = 'ai-projects/' . $project->user_id;
                
                if (isset($data['output'])) {
                    $outputUrl = is_array($data['output']) ? ($data['output'][0] ?? null) : $data['output'];
                    $this->retryDownloadWithLogging($outputUrl, $path, $project->response);
                }
            }
        }
    }

    private function processWebhookStatus(string $status, string $trackId, ?string $outputUrl): void
    {
        match ($status) {
            'processing' => UserAiProject::where('response', $trackId)
                ->update(['status' => 1]),
                
            'success' => $this->processSuccessfulWebhook($trackId, $outputUrl),
                
            'error' => UserAiProject::where('response', $trackId)
                ->update(['status' => 3]),
                
            default => null,
        };
    }

    private function processSuccessfulWebhook(string $trackId, ?string $outputUrl): void
    {
        $record = UserAiProject::where('response', $trackId)->first();
        
        if (!$record) {
            Log::warning("Project not found for successful webhook: $trackId");
            return;
        }
        
        // Check if this is a text-to-image generation that needs video conversion
        $isTxtImgVid = str_starts_with($trackId, 'txtimgvid');

        if ($isTxtImgVid && $outputUrl) {
            // Check if we already processed this and created a video project
            $existingVideoProject = DB::table('user_ai_projects')
                ->where('user_id', $record->user_id)
                ->where('input', $record->input)
                ->where('title', $record->title . ' - Video')
                ->where('status', 1) // Processing
                ->first();

            if ($existingVideoProject) {
                
                // Just download the image and update the original record
                $path = 'ai-projects/' . $record->user_id;
                $this->retryDownloadWithLogging($outputUrl, $path, $trackId);
                return;
            }

            $this->triggerImageToVideo($record, $outputUrl, $trackId);
        } else {
            $path = 'ai-projects/' . $record->user_id;
            $this->retryDownloadWithLogging($outputUrl, $path, $trackId);
        }
    }

    private function triggerImageToVideo($imageProject, string $imageUrl, string $originalTrackId): void
    {
        try {

            // Download the generated image first
            $path = 'ai-projects/' . $imageProject->user_id;
            $localImagePath = $this->retryDownloadWithLogging($imageUrl, $path, $originalTrackId);

            if (!$localImagePath) {
                throw new Exception("Failed to download generated image from: " . $imageUrl);
            }

            // Wait a moment to ensure file is fully saved
            sleep(2);

            // Verify the file actually exists
            $fullImagePath = public_path($localImagePath);
            if (!file_exists($fullImagePath)) {
                throw new Exception("Downloaded image file not found at: " . $fullImagePath);
            }

            // Create new track ID for video generation
            $videoTrackId = $originalTrackId;//'imgvid' . date('YmdHis') . rand(1000, 9999);
            
            // Convert local path to public URL for the API
            $publicImageUrl = url($localImagePath);

            // Prepare parameters for image-to-video - USE URL NOT LOCAL PATH
            $videoParams = [
                'init_image' => $publicImageUrl, // Use public URL, not local path
                'prompt' => $imageProject->input, // Use the original prompt
                'title' => $imageProject->title ?? 'Generated Video',
                'hook_track_id' => $videoTrackId,
                'endpoint' => 'v6/video/img2video_ultra'
            ];

            // Create new project for video generation
            DB::table('user_ai_projects')->where('response', $videoTrackId)->update([
                'status' => 1, // Processing
                'updated_at' => now(),
            ]);

            // Trigger the image-to-video generation
            $videoResponse = AiGen::generate_imgvideo($videoParams, $videoTrackId, [
                'success' => '🎬 Your video is being generated from the image!',
                'failure' => 'Video generation failed',
                'queue' => 'Video generation queued'
            ]);

        } catch (Exception $e) {
            
            // Mark original project as failed
            DB::table('user_ai_projects')
                ->where('response', $originalTrackId)
                ->update(['status' => 4]); // Special status for chain failure
        }
    }

    private function retryDownloadWithLogging(?string $url, string $path, string $trackId): ?string
    {
        if (!$url) {
            Log::warning("No URL provided for download: $trackId");
            return null;
        }

        try {
            $resultPath = AiGen::convertLinksToLocal($url, $path, $trackId);

            if ($resultPath) {
                UserAiProject::where('response', $trackId)
                    ->update(['status' => 2, 'output' => $resultPath]);
                    
                Log::info("Successfully downloaded and saved: $trackId");
                return $resultPath;
            } else {
                Log::error("Download returned null path for: $trackId");
                return null;
            }
        } catch (Exception $e) {
            Log::error("Download failed for $trackId: " . $e->getMessage(), [
                'url' => $url,
                'path' => $path
            ]);
            return null;
        }
    }


    public function saveReplicate(Request $request)
    {
        $trackId = null;
        
        try {
            Log::info('SaveReplicate called', [
                'method' => $request->method(),
                'has_track_id' => $request->has('track_id')
            ]);

            if ($request->isMethod('post')) {
                $predictionId = $request->id;
                $trackId = $request->input('input.track_id');
                
                Log::info('Replicate webhook received', [
                    'track_id' => $trackId,
                    'prediction_id' => $predictionId,
                    'status' => $request->status
                ]);

                // Store webhook hit
                DB::table('ml_hits')->insert([
                    'track_id' => $trackId,
                    'status' => ($request->status === 'succeeded' ? 2 : ($request->status === 'failed' ? 1 : 0)),
                    'json' => json_encode($request->all())
                ]);

                $this->processReplicateStatus($request->status, $trackId, $request->output);
            } else {
                if ($request->has('track_id')) {
                    $this->processSpecificTrack($request->query('track_id'));
                } else {
                    $this->processPendingReplicateProjects();
                }
            }

            return response()->json(['message' => 'Success'], 200);

        } catch (Exception $e) {
            Log::error('['.($trackId ?? 'unknown').'] SaveReplicate Error: ' . $e->getMessage());
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    private function processReplicateStatus(string $status, string $trackId, $output): void
    {
        match ($status) {
            'starting', 'processing' => DB::table('user_ai_projects')
                ->where('response', $trackId)
                ->update(['status' => 1]),
                
            'succeeded' => $this->processSuccessfulReplicateWebhook($trackId, $output),
            
            'failed', 'canceled' => DB::table('user_ai_projects')
                ->where('response', $trackId)
                ->update(['status' => 3, 'error_message' => 'Processing failed']),
            
            default => null,
        };
    }

    private function processSuccessfulReplicateWebhook(string $trackId, $output): void
    {
        $record = DB::table('user_ai_projects')->where('response', $trackId)->first();
        
        if (!$record) {
            Log::warning("Project not found for successful Replicate webhook: $trackId");
            return;
        }

        $outputUrl = is_array($output) ? ($output[0] ?? null) : $output;
        
        if ($outputUrl) {
            $path = 'ai-projects/' . $record->user_id;
            $this->retryDownloadReplicate($outputUrl, $path, $trackId);
        }
    }

    private function processPendingReplicateProjects(): void
    {
        $pendingProjects = DB::table('user_ai_projects')->whereNull('output')->get();

        foreach ($pendingProjects as $project) {
            $hit = DB::table('ml_hits')
                ->where(['track_id' => $project->response, 'status' => 2])
                ->first();

            if ($hit) {
                $this->processSuccessfulHit($hit, $project->response);
            } else {
                $this->fetchFromReplicateApi($project->response);
            }
        }
    }

    private function processSpecificTrack(string $trackId): void
    {
        $hit = DB::table('ml_hits')->where('track_id', $trackId)->first();
        
        if ($hit) {
            if ($hit->status == 2) {
                $this->processSuccessfulHit($hit, $trackId);
            } else {
                $this->fetchFromReplicateApi($trackId);
            }
        } else {
            $this->fetchFromReplicateApi($trackId);
        }
    }

    private function processSuccessfulHit($hit, string $trackId): void
    {
        $data = json_decode($hit->json, true);
        $record = DB::table('user_ai_projects')->where('response', $trackId)->first();
        
        if ($record && isset($data['output'])) {
            $path = 'ai-projects/' . $record->user_id;
            $outputUrl = is_array($data['output']) ? ($data['output'][0] ?? null) : $data['output'];
            
            if ($outputUrl) {
                $this->retryDownloadReplicate($outputUrl, $path, $trackId);
            }
        }
    }

    private function fetchFromReplicateApi(string $trackId): void
    {
        try {
            $hit = DB::table('ml_hits')->where('track_id', $trackId)->first();
            
            if (!$hit) {
                Log::warning("No ml_hits record found for API fetch: $trackId");
                return;
            }
            
            $jsonData = json_decode($hit->json, true);
            $predictionId = $jsonData['id'] ?? null;
            
            if (!$predictionId) {
                Log::warning("No prediction_id found in ml_hits JSON: $trackId");
                return;
            }
            
            $apiKey = env('RP_KEY');
            if (!$apiKey) {
                Log::error('Replicate API key not configured');
                return;
            }
            
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->get("https://api.replicate.com/v1/predictions/{$predictionId}");
            
            if ($response->successful()) {
                $predictionData = $response->json();
                
                // Store the API response
                DB::table('ml_hits')->insert([
                    'track_id' => $trackId,
                    'status' => ($predictionData['status'] === 'succeeded' ? 2 : ($predictionData['status'] === 'failed' ? 1 : 0)),
                    'json' => json_encode($predictionData)
                ]);
                
                // Process if succeeded
                if ($predictionData['status'] === 'succeeded') {
                    $this->processSuccessfulReplicateWebhook($trackId, $predictionData['output']);
                }
            }
            
        } catch (Exception $e) {
            Log::error("Failed to fetch from Replicate API for $trackId: " . $e->getMessage());
        }
    }

    private function retryDownloadReplicate(string $url, string $path, string $trackId, int $maxRetries = 3): string
    {
        $retryCount = 0;
        
        while ($retryCount < $maxRetries) {
            try {
                $resultPath = AiGen::convertLinksToLocal($url, $path, $trackId);
                return $resultPath;
            } catch (Exception $e) {
                Log::warning("Download attempt failed for $trackId", [
                    'attempt' => $retryCount + 1,
                    'error' => $e->getMessage()
                ]);
            }
            
            $retryCount++;
            if ($retryCount < $maxRetries) {
                sleep(2);
            }
        }
        
        throw new Exception("Failed to download output after $maxRetries attempts for $trackId");
    }

    public function deleteProject(Request $request)
    {
        $projectId = $request->project_id;
        $userId = Auth::id();
        
        $project = UserAiProject::where(['user_id' => $userId, 'id' => $projectId])->first();

        if (!$project) {
            return response()->json(['errors' => ['Record not found']], 404);
        }

        $deleted = UserAiProject::where(['user_id' => $userId, 'id' => $projectId])->delete();

        if ($deleted) {
            return response()->json(['message' => ['Delete was successful']], 200);
        }

        return response()->json(['errors' => ['Delete failed']], 500);
    }
    
    // Add this temporary method to debug the request
    public function debugFileUpload(Request $request)
    {
        Log::debug('Debug file upload', [
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'all_files' => $request->allFiles(),
            'all_input' => $request->all(),
            'content_type' => $request->header('Content-Type'),
            'has_base_image' => $request->hasFile('base_image'),
            'has_target_face' => $request->hasFile('target_face'),
            'has_swap_face' => $request->hasFile('swap_face'),
        ]);

        return response()->json([
            'files_received' => array_keys($request->allFiles()),
            'input_received' => array_keys($request->all())
        ]);
    }
}