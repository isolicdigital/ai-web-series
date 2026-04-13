<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateScriptBatch;
use App\Jobs\GenerateFrameImage;
use App\Jobs\GenerateFrameVideo;
use App\Jobs\GenerateVoiceover;
use App\Jobs\GenerateSFX;
use App\Models\AiDirector\ScriptProject;
use App\Models\AiDirector\ScriptScene;
use App\Models\AiDirector\ShootProject;
use App\Models\AiDirector\ShootFrame;
use App\Models\AiDirector\SoundProject;
use App\Models\AiDirector\SFXTrack;
use App\Models\AiDirector\SoundTrack;
use App\Models\AiDirector\MovieProject;
use App\Models\MlVoiceLanguage;
use App\Models\AiCustomGenerator;
use App\Models\TknUserCredit;
use App\Services\AiGenService;
use App\Helpers\AiGen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class AiDirectorController extends Controller
{
    protected AiGenService $aiGenService;

    public function __construct(AiGenService $aiGenService)
    {
        $this->aiGenService = $aiGenService;
    }

    /**
     * ============================================
     * VIEW LOADING METHODS
     * ============================================
     */

    /**
     * Script Director - Blueprint Module
     * UI: Split-pane view with Project Settings and Script Board
     */
    public function script_director(Request $request)
    {
        $userId = Auth::id();
        
        // Get user's existing script projects
        $projects = ScriptProject::with('scenes')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();
        
        // Get available AI models for script generation
        $scriptModel = AiCustomGenerator::with('aiModel')
                        ->where('slug', 'script-director')
                        ->first();
        
        return view('user.aidirector.script', [
            'projects' => $projects,
            'scriptModel' => $scriptModel,
            'styles' => $this->getScriptStyles(),
            'maxScenes' => 10,
            'minScenes' => 3
        ]);
    }

    /**
     * Shoot Director - Virtual Set Module
     * UI: Media Bin layout with status-aware grid
     */
    public function shoot_director(Request $request)
    {
        $userId = Auth::id();
        
        // Get the text-to-image model for storyboard generation
        $imageModel = AiCustomGenerator::with('aiModel')
            ->where('slug', 'storyboard-artist')
            ->first();
        
        // Get the image-to-video model for cinematography
        $videoModel = AiCustomGenerator::with('aiModel')
            ->where('slug', 'cinematographer')
            ->first();
        
        // Get user's completed script projects for the dropdown
        $scriptProjects = ScriptProject::where('user_id', $userId)
            ->where('status', 2) // Only completed scripts
            ->orderBy('id', 'desc')
            ->get();
        
        // Get user's existing shoot projects (if any)
        $projects = ShootProject::with(['frames' => function($query) {
                $query->orderBy('scene_number')->orderBy('frame_number');
            }])
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();
        
        return view('user.aidirector.shoot', [
            'projects' => $projects,
            'scriptProjects' => $scriptProjects,
            'imageModel' => $imageModel,
            'videoModel' => $videoModel
        ]);
    }

    /**
     * Sound Director - Foley & Dub Stage
     * UI: Multi-Track Audio Mixer for each scene
     */
    
    public function sound_director(Request $request)
    {
        $userId = Auth::id();

        // Get user's script projects (completed) - these are needed for the dropdown
        $scriptProjects = ScriptProject::where('user_id', $userId)
            ->where('status', 2)
            ->orderBy('id', 'desc')
            ->get();

        // Get existing sound projects
        $soundProjects = SoundProject::with(['voiceoverScenes', 'sfxTracks'])
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();

        // Get TTS models
        $ttsModel = AiCustomGenerator::where('slug', 'text-to-speech')->first();

        // Get SFX model
        $sfxModel = AiCustomGenerator::where('slug', 'sfx-generator')->first();

        // Get voices and languages for TTS
        $languages = MlVoiceLanguage::with(['voices' => function($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get();

        $defaultLanguage = MlVoiceLanguage::where('code', 'english')->first();
        if (!$defaultLanguage && $languages->count() > 0) {
            $defaultLanguage = $languages->first();
        }

        $defaultLanguageVoices = $defaultLanguage ? $defaultLanguage->voices : collect();

        $voicesByLanguage = [];
        foreach ($languages as $language) {
            $voicesByLanguage[$language->code] = $language->voices;
        }

        return view('user.aidirector.sound', [
            'scriptProjects' => $scriptProjects,
            'soundProjects' => $soundProjects,
            'ttsModel' => $ttsModel,
            'sfxModel' => $sfxModel,
            'languages' => $languages,
            'defaultLanguage' => $defaultLanguage,
            'defaultLanguageVoices' => $defaultLanguageVoices,
            'voicesByLanguage' => $voicesByLanguage
        ]);
    }

    /**
     * Movie Producer - Final Assembly
     * UI: Project Card view with playable carousel
     */
    public function movie_producer(Request $request)
    {
        $userId = Auth::id();
        
        $movies = MovieProject::with(['scenes' => function($query) {
                $query->orderBy('scene_number');
            }])
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();
        
        return view('user.aidirector.movie', [
            'movies' => $movies
        ]);
    }

    /**
     * ============================================
     * SCRIPT DIRECTOR API METHODS
     * ============================================
     */

    /**
     * Generate a complete script with multiple scenes
     */
    public function generateScript(Request $request)
    {
        try {
            $userId = Auth::id();
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'prompt' => 'required|string',
                'style' => 'required|string',
                'scene_count' => 'required|integer|min:3|max:10',
                'model_id' => 'required|exists:ai_custom_generators,id'
            ]);

            // Check for demo mode
            if ($request->has('demo_mode') && $request->demo_mode == 1) {
                Log::info('DEMO GenerateScript RUN');
                return $this->demoGenerateScript($validated);
            }

            DB::beginTransaction();

            // Create script project
            $project = ScriptProject::create([
                'user_id' => $userId,
                'title' => $validated['title'],
                'prompt' => $validated['prompt'],
                'style' => $validated['style'],
                'scene_count' => $validated['scene_count'],
                'model_id' => $validated['model_id'],
                'status' => 0, // Processing
                'payload' => json_encode($validated)
            ]);

            DB::commit();

            // Generate scenes in background (via job or immediate generation)
            $this->dispatchScriptGeneration($project, $validated);

            return response()->json([
                'status' => 'processing',
                'message' => 'Script generation started',
                'project_id' => $project->id
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('GenerateScript Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to generate script'], 500);
        }
    }

    private function demoGenerateScript($validated)
    {
        try {
            $sceneCount = $validated['scene_count'];
            
            // Read demo files
            $scriptsPath = public_path('demo/scripts.txt');
            $promptsPath = public_path('demo/prompts.json');
            
            if (!file_exists($scriptsPath) || !file_exists($promptsPath)) {
                return response()->json(['error' => 'Demo files not found'], 404);
            }
            
            $scriptsContent = file_get_contents($scriptsPath);
            $promptsContent = json_decode(file_get_contents($promptsPath), true);
            
            $scenesText = preg_split('/---/', $scriptsContent);
            $scenes = [];
            
            for ($i = 1; $i <= $sceneCount; $i++) {
                $sceneIndex = $i - 1;
                $voiceover = isset($scenesText[$sceneIndex]) ? trim($scenesText[$sceneIndex]) : "Demo scene $i narration";
                $visualPrompt = isset($promptsContent["scene_$i"]) ? $promptsContent["scene_$i"] : "Demo visual prompt for scene $i";
                
                $scenes[] = [
                    'scene_number' => $i,
                    'voiceover_script' => $voiceover,
                    'visual_prompt' => $visualPrompt,
                    'status' => 2
                ];
            }
            
            DB::beginTransaction();
            
            // Create project
            $project = ScriptProject::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'prompt' => $validated['prompt'],
                'style' => $validated['style'],
                'scene_count' => $sceneCount,
                'model_id' => $validated['model_id'],
                'status' => 1, // Processing
                'payload' => json_encode(['demo_mode' => true])
            ]);
            
            // Create scenes
            foreach ($scenes as $sceneData) {
                ScriptScene::create([
                    'project_id' => $project->id,
                    'scene_number' => $sceneData['scene_number'],
                    'voiceover_script' => $sceneData['voiceover_script'],
                    'visual_prompt' => $sceneData['visual_prompt'],
                    'status' => 2
                ]);
            }
            
            DB::commit();
            
            // Dispatch the job to handle completion and shoot project creation
            // Pass demo data so job doesn't call AI
            $model = AiCustomGenerator::with('aiModel')->find($validated['model_id']);
            $trackId = 'demo-' . $project->id . '-batch-' . uniqid();
            
            GenerateScriptBatch::dispatch($model, ['demo_mode' => true, 'project_id' => $project->id], $trackId, $project->id);
            
            return response()->json([
                'status' => 'processing',
                'message' => 'Demo script generation started',
                'project_id' => $project->id
            ], 200);
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('DemoGenerateScript Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate demo script'], 500);
        }
    }

    /**
     * Regenerate a specific scene
     */
    public function regenerateScene(Request $request, int $sceneId)
    {
        try {
            $userId = Auth::id();
            
            $scene = ScriptScene::where('id', $sceneId)
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$scene) {
                return response()->json(['message' => 'Scene not found'], 404);
            }

            // Check credits (1 credit per regeneration)
            // $creditCheck = $this->checkAndDeductCredits($userId, 'script_scene', 1);
            // if ($creditCheck !== true) {
            //     return $creditCheck;
            // }

            $scene->update(['status' => 1]); // Processing

            // Trigger regeneration
            $this->dispatchSceneRegeneration($scene);

            return response()->json([
                'status' => 'processing',
                'message' => 'Scene regeneration started',
                'scene_id' => $sceneId
            ], 200);

        } catch (Exception $e) {
            Log::error('RegenerateScene Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to regenerate scene'], 500);
        }
    }

    /**
     * Regenerate all scenes in a project
     */
    public function regenerateAllScenes(Request $request, int $projectId)
    {
        try {
            $userId = Auth::id();
            
            $project = ScriptProject::where(['id' => $projectId, 'user_id' => $userId])->first();

            if (!$project) {
                return response()->json(['message' => 'Project not found'], 404);
            }

            // Check credits for all scenes
            $sceneCount = $project->scenes()->count();
            // $creditCheck = $this->checkAndDeductCredits($userId, 'script_scene', $sceneCount);
            // if ($creditCheck !== true) {
            //     return $creditCheck;
            // }

            // Mark all scenes as processing
            $project->scenes()->update(['status' => 1]);
            $project->update(['status' => 1]);

            // Dispatch regeneration for all scenes
            $this->dispatchAllScenesRegeneration($project);

            return response()->json([
                'status' => 'processing',
                'message' => 'Regeneration started for all scenes',
                'project_id' => $projectId
            ], 200);

        } catch (Exception $e) {
            Log::error('RegenerateAllScenes Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to regenerate scenes'], 500);
        }
    }

    /**
     * Export scripts and prompts
     */
    public function exportScriptProject(int $projectId)
    {
        try {
            $userId = Auth::id();
            
            $project = ScriptProject::with('scenes')
                ->where(['id' => $projectId, 'user_id' => $userId])
                ->first();

            if (!$project) {
                return response()->json(['message' => 'Project not found'], 404);
            }

            // Prepare data for export
            $scripts = [];
            $prompts = [];
            
            foreach ($project->scenes->sortBy('scene_number') as $scene) {
                $scripts[] = "SCENE {$scene->scene_number}\n" . $scene->voiceover_script;
                $prompts["scene_{$scene->scene_number}"] = $scene->visual_prompt;
            }

            // Create temporary directory
            $tempDir = storage_path('app/temp/export_' . uniqid());
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Save scripts as .txt
            file_put_contents($tempDir . '/scripts.txt', implode("\n\n---\n\n", $scripts));
            
            // Save prompts as .json
            file_put_contents($tempDir . '/prompts.json', json_encode($prompts, JSON_PRETTY_PRINT));

            // Create zip
            $zipFileName = 'script_project_' . $projectId . '.zip';
            $zipPath = storage_path('app/public/' . $zipFileName);

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                $zip->addFile($tempDir . '/scripts.txt', 'scripts.txt');
                $zip->addFile($tempDir . '/prompts.json', 'prompts.json');
                $zip->close();
            }

            // Clean up
            unlink($tempDir . '/scripts.txt');
            unlink($tempDir . '/prompts.json');
            rmdir($tempDir);

            return response()->download($zipPath)->deleteFileAfterSend(true);

        } catch (Exception $e) {
            Log::error('ExportScriptProject Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to export project'], 500);
        }
    }

    public function getScriptStatus(Request $request, int $projectId)
    {
        try {
            $userId = Auth::id();
            
            $project = ScriptProject::with('scenes')
                ->where(['id' => $projectId, 'user_id' => $userId])
                ->first();
            
            if (!$project) {
                return response()->json(['error' => 'Project not found'], 404);
            }
            
            $completedScenes = $project->scenes->where('status', 2)->count();
            $failedScenes = $project->scenes->where('status', 3)->count();
            $processingScenes = $project->scenes->where('status', 1)->count();
            
            if ($completedScenes >= $project->scene_count) {
                $status = 'complete';
            } elseif ($failedScenes > 0 && $processingScenes == 0) {
                $status = 'failed';
            } elseif ($processingScenes > 0 || $completedScenes > 0) {
                $status = 'processing';
            } else {
                $status = 'pending';
            }
            
            return response()->json([
                'status' => $status,
                'project' => [
                    'id' => $project->id,
                    'title' => $project->title,
                    'scene_count' => $project->scene_count,
                    'scenes' => $project->scenes->map(function($scene) {
                        return [
                            'id' => $scene->id,
                            'scene_number' => $scene->scene_number,
                            'voiceover_script' => $scene->voiceover_script,
                            'visual_prompt' => $scene->visual_prompt,
                            'status' => $scene->status
                        ];
                    })
                ]
            ], 200);
            
        } catch (Exception $e) {
            Log::error('GetScriptStatus Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get status'], 500);
        }
    }

    public function deleteProject(Request $request, int $projectId)
    {
        try {
            $userId = Auth::id();
            
            $project = ScriptProject::where(['id' => $projectId, 'user_id' => $userId])->first();
            
            if (!$project) {
                return response()->json(['error' => 'Project not found'], 404);
            }
            
            // Delete associated scene files (images, videos, voiceovers)
            foreach ($project->scenes as $scene) {
                // Delete image file if exists
                if ($scene->image_path && file_exists(public_path($scene->image_path))) {
                    unlink(public_path($scene->image_path));
                }
                // Delete video file if exists
                if ($scene->video_path && file_exists(public_path($scene->video_path))) {
                    unlink(public_path($scene->video_path));
                }
                // Delete voiceover file if exists
                if ($scene->voiceover_url && file_exists(public_path($scene->voiceover_url))) {
                    unlink(public_path($scene->voiceover_url));
                }
            }
            
            // Delete associated shoot project and its files
            $shootProject = ShootProject::where('source_id', $project->id)
                ->where('source_type', 'script_project')
                ->first();
            
            if ($shootProject) {
                // Delete shoot frames images and videos
                foreach ($shootProject->frames as $frame) {
                    if ($frame->image_path && file_exists(public_path($frame->image_path))) {
                        unlink(public_path($frame->image_path));
                    }
                    if ($frame->video_path && file_exists(public_path($frame->video_path))) {
                        unlink(public_path($frame->video_path));
                    }
                }
                $shootProject->frames()->delete();
                $shootProject->delete();
            }
            
            // Delete associated sound project and its SFX files
            $soundProject = SoundProject::where('script_project_id', $project->id)->first();
            
            if ($soundProject) {
                // Delete SFX files
                foreach ($soundProject->sfxTracks as $sfx) {
                    if ($sfx->url && file_exists(public_path($sfx->url))) {
                        unlink(public_path($sfx->url));
                    }
                }
                $soundProject->sfxTracks()->delete();
                $soundProject->delete();
            }
            
            // Delete scenes first (they will cascade, but we already handled files)
            $project->scenes()->delete();
            
            // Delete the project
            $project->delete();
            
            return response()->json(['message' => 'Project and all associated files deleted successfully'], 200);
            
        } catch (Exception $e) {
            Log::error('DeleteProject Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete project'], 500);
        }
    }

    /**
     * ============================================
     * SHOOT DIRECTOR API METHODS
     * ============================================
     */

    /**
     * Get script scenes with associated frame data if shoot project exists
     */
    public function getScriptScenes(Request $request, int $projectId)
    {
        try {
            $userId = Auth::id();
            
            $scriptProject = ScriptProject::with('scenes')
                ->where(['id' => $projectId, 'user_id' => $userId])
                ->first();
            
            if (!$scriptProject) {
                return response()->json(['error' => 'Script project not found'], 404);
            }

            // Check if a shoot project already exists - use source_id and source_type
            $shootProject = ShootProject::with('frames')
                ->where('source_id', $scriptProject->id)
                ->where('source_type', 'script_project')
                ->first();
            
            $scenes = $scriptProject->scenes->sortBy('scene_number')->map(function($scene) use ($shootProject) {
                $frame = $shootProject ? $shootProject->frames->where('scene_number', $scene->scene_number)->first() : null;
                
                return [
                    'id' => $scene->id,
                    'scene_number' => $scene->scene_number,
                    'voiceover_script' => $scene->voiceover_script,
                    'visual_prompt' => $scene->visual_prompt,
                    'frame_id' => $frame ? $frame->id : null,
                    'image_status' => $frame ? $frame->status_image : 0,
                    'image_url' => $frame && $frame->image_path ? asset($frame->image_path) : null,
                    'video_status' => $frame ? $frame->status_video : 0,
                    'video_url' => $frame && $frame->video_path ? asset($frame->video_path) : null
                ];
            })->values();
            
            return response()->json([
                'script_project_id' => $scriptProject->id,
                'shoot_project_id' => $shootProject ? $shootProject->id : null,
                'title' => $scriptProject->title,
                'scenes' => $scenes
            ], 200);
            
        } catch (Exception $e) {
            Log::error('GetScriptScenes Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch scenes'], 500);
        }
    }

    /**
     * Generate image for a specific frame
     */
    public function generateImage(Request $request)
    {
        try {
            $userId = Auth::id();
            $validated = $request->validate([
                'frame_id' => 'required|integer',
                'prompt' => 'required|string'
            ]);

            $frame = ShootFrame::with('project')
                ->where('id', $validated['frame_id'])
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$frame) {
                return response()->json(['error' => 'Frame not found'], 404);
            }

            // Get the script project to access style_base and character_bible
            $scriptProject = ScriptProject::where('id', $frame->project->source_id)
                ->where('user_id', $userId)
                ->first();

            $isDemo = $request->has('demo_mode') && $request->demo_mode == 1;
            $trackId = 'shoot-' . $frame->project_id . '-s' . $frame->scene_number . '-f' . $frame->frame_number . '-' . uniqid();

            // Get the image model
            $imageModel = AiCustomGenerator::with('aiModel')
                ->where('slug', 'storyboard-artist')
                ->first();

            if (!$imageModel) {
                return response()->json(['error' => 'Image model not configured'], 500);
            }

            // Build enhanced prompt with style_base and character_bible
            $enhancedPrompt = $validated['prompt'];
            
            if ($scriptProject) {
                $styleBase = $scriptProject->style_base;
                $characterBible = $scriptProject->character_bible;
                
                if ($styleBase || $characterBible) {
                    $parts = [];
                    if ($styleBase) {
                        $parts[] = $styleBase;
                    }
                    if ($characterBible) {
                        $parts[] = $characterBible;
                    }
                    $parts[] = $validated['prompt'];
                    $enhancedPrompt = implode(' ', $parts);
                    
                    Log::info('Enhanced prompt with style/character', [
                        'frame_id' => $frame->id,
                        'has_style' => !empty($styleBase),
                        'has_character' => !empty($characterBible),
                        'original_length' => strlen($validated['prompt']),
                        'enhanced_length' => strlen($enhancedPrompt)
                    ]);
                }
            }

            // Prepare generation data
            $genData = [
                'prompt' => $enhancedPrompt,
                'hook_track_id' => $trackId,
                'skip_ultra' => true,  // Add this
                'frame_id' => $frame->id,
                'project_id' => $frame->project_id
            ];
            
            // Use project seed for consistency across all scenes
            if ($frame->project->seed) {
                $genData['seed'] = $frame->project->seed;
                Log::info('Using project seed', [
                    'project_id' => $frame->project_id,
                    'seed' => $frame->project->seed
                ]);
            }

            // Add filters from project payload if any
            $payload = json_decode($frame->project->payload, true);
            if (isset($payload['filters']) && !empty($payload['filters'])) {
                $genData['filters'] = $payload['filters'];
            }

            // Save the request payload
            $frame->update([
                'status_image' => 1,
                'track_id' => $trackId,
                'prompt' => $enhancedPrompt,
                'payload_image' => json_encode($genData)
            ]);

            if ($isDemo) {
                return $this->demoGenerateImage($frame);
            }

            // Queue the generation job
            GenerateFrameImage::dispatch($imageModel, $genData, $trackId, $frame->id);

            Log::info('Image generation queued', [
                'frame_id' => $frame->id,
                'track_id' => $trackId,
                'generation_type' => 'txt2img with seed',
                'project_seed' => $frame->project->seed ?? 'not set',
                'has_style_base' => !empty($scriptProject?->style_base),
                'has_character_bible' => !empty($scriptProject?->character_bible)
            ]);

            return response()->json([
                'status' => 'processing',
                'message' => 'Image generation started',
                'frame_id' => $frame->id
            ], 200);

        } catch (Exception $e) {
            Log::error('GenerateImage Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to start generation'], 500);
        }
    }
    public function generateImage33(Request $request)
    {
        try {
            $userId = Auth::id();
            $validated = $request->validate([
                'frame_id' => 'required|integer',
                'prompt' => 'required|string'
            ]);

            $frame = ShootFrame::with('project')
                ->where('id', $validated['frame_id'])
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$frame) {
                return response()->json(['error' => 'Frame not found'], 404);
            }

            $projectPayload = json_decode($frame->project->payload, true);
            $isDemo = isset($projectPayload['demo_mode']) && $projectPayload['demo_mode'] == true;
            $trackId = 'shoot-' . $frame->project_id . '-s' . $frame->scene_number . '-f' . $frame->frame_number . '-' . uniqid();

            // Get the image model
            $imageModel = AiCustomGenerator::with('aiModel')
                ->where('slug', 'storyboard-artist')
                ->first();

            if (!$imageModel) {
                return response()->json(['error' => 'Image model not configured'], 500);
            }

            // Prepare generation data
            $genData = [
                'prompt' => $validated['prompt'],
                'hook_track_id' => $trackId,
                'frame_id' => $frame->id,
                'project_id' => $frame->project_id,
                'art_style' => 'storyboard'
            ];
            
            // Use project seed if available
            // if ($frame->project->seed) {
            //     $genData['seed'] = $frame->project->seed;
            // }

            // Get frame 1 image (the first scene)
            $firstFrame = ShootFrame::where('project_id', $frame->project_id)
                ->where('scene_number', 1)
                ->where('status_image', 2)
                ->first();
            
            // For scene 2 and beyond, use frame 1 as source image
            if ($frame->scene_number > 1 && $firstFrame && $firstFrame->image_path && file_exists(public_path($firstFrame->image_path))) {
                $genData['init_image'] = url($firstFrame->image_path);
                $genData['strength'] = 0.65;
                
                Log::info('Using frame 1 as source image', [
                    'current_scene' => $frame->scene_number,
                    'source_scene' => 1,
                    'source_image' => $firstFrame->image_path
                ]);
            } else {
                Log::info('No source image available (scene 1 or frame 1 not ready)', [
                    'current_scene' => $frame->scene_number,
                    'frame1_ready' => $firstFrame ? true : false
                ]);
            }

            // Add filters from project payload
            $payload = json_decode($frame->project->payload, true);
            if (isset($payload['filters']) && !empty($payload['filters'])) {
                $genData['filters'] = $payload['filters'];
            }

            // Save the request payload
            $frame->update([
                'status_image' => 1,
                'track_id' => $trackId,
                'prompt' => $validated['prompt'],
                'payload_image' => json_encode($genData)
            ]);

            if ($isDemo) {
                return $this->demoGenerateImage($frame);
            }

            // Queue the generation job
            GenerateFrameImage::dispatch($imageModel, $genData, $trackId, $frame->id);

            Log::info('Image generation queued', [
                'frame_id' => $frame->id,
                'track_id' => $trackId,
                'generation_type' => isset($genData['init_image']) ? 'img2img (using frame 1)' : 'txt2img',
                'has_source_image' => isset($genData['init_image'])
            ]);

            return response()->json([
                'status' => 'processing',
                'message' => 'Image generation started',
                'frame_id' => $frame->id
            ], 200);

        } catch (Exception $e) {
            Log::error('GenerateImage Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to start generation'], 500);
        }
    }
    public function generateImage2(Request $request)
    {
        try {
            $userId = Auth::id();
            $validated = $request->validate([
                'frame_id' => 'required|integer',
                'prompt' => 'required|string'
            ]);

            $frame = ShootFrame::with('project')
                ->where('id', $validated['frame_id'])
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$frame) {
                return response()->json(['error' => 'Frame not found'], 404);
            }

            $isDemo = $request->has('demo_mode') && $request->demo_mode == 1;
            $trackId = 'shoot-' . $frame->project_id . '-s' . $frame->scene_number . '-f' . $frame->frame_number . '-' . uniqid();

            // Get the image model
            $imageModel = AiCustomGenerator::with('aiModel')
                ->where('slug', 'storyboard-artist')
                ->first();

            if (!$imageModel) {
                return response()->json(['error' => 'Image model not configured'], 500);
            }

            // Prepare generation data
            $genData = [
                'prompt' => $validated['prompt'],
                'hook_track_id' => $trackId,
                'frame_id' => $frame->id,
                'project_id' => $frame->project_id
            ];
            
            // Determine if this is the first frame (scene_number == 1)
            $isFirstFrame = ($frame->scene_number == 1);
            
            if (!$isFirstFrame) {
                // For subsequent frames, use previous frame as source image
                $prevFrame = ShootFrame::where('project_id', $frame->project_id)
                    ->where('scene_number', $frame->scene_number - 1)
                    ->where('status_image', 2)
                    ->first();
                
                if ($prevFrame && $prevFrame->image_path && file_exists(public_path($prevFrame->image_path))) {
                    // Use img2img for subsequent frames
                    $genData['init_image'] = url($prevFrame->image_path);
                    $genData['strength'] = 0.65; // Balance between source and new prompt
                    
                    Log::info('Using img2img for scene', [
                        'current_scene' => $frame->scene_number,
                        'source_scene' => $prevFrame->scene_number,
                        'source_image' => $prevFrame->image_path
                    ]);
                }
            } else {
                // First frame uses txt2img - use project seed for consistency across regenerations
                if ($frame->project->seed) {
                    $genData['seed'] = $frame->project->seed;
                    Log::info('Using project seed for first frame', [
                        'project_id' => $frame->project_id,
                        'seed' => $frame->project->seed
                    ]);
                }
            }
            
            // Add filters from project payload
            $payload = json_decode($frame->project->payload, true);
            if (isset($payload['filters']) && !empty($payload['filters'])) {
                $genData['filters'] = $payload['filters'];
            }

            // Save the request payload
            $frame->update([
                'status_image' => 1,
                'track_id' => $trackId,
                'prompt' => $validated['prompt'],
                'payload_image' => json_encode($genData)
            ]);

            if ($isDemo) {
                return $this->demoGenerateImage($frame);
            }

            // Queue the generation job
            GenerateFrameImage::dispatch($imageModel, $genData, $trackId, $frame->id);

            Log::info('Image generation queued', [
                'frame_id' => $frame->id,
                'track_id' => $trackId,
                'generation_type' => $isFirstFrame ? 'txt2img' : 'img2img',
                'has_source_image' => isset($genData['init_image'])
            ]);

            return response()->json([
                'status' => 'processing',
                'message' => 'Image generation started',
                'frame_id' => $frame->id
            ], 200);

        } catch (Exception $e) {
            Log::error('GenerateImage Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to start generation'], 500);
        }
    }

    public function generateImage1(Request $request)
    {
        try {
            $userId = Auth::id();
            $validated = $request->validate([
                'frame_id' => 'required|integer',
                'prompt' => 'required|string'
            ]);

            $frame = ShootFrame::with('project')
                ->where('id', $validated['frame_id'])
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$frame) {
                return response()->json(['error' => 'Frame not found'], 404);
            }

            // Check for demo mode
            $isDemo = $request->has('demo_mode') && $request->demo_mode == 1;

            $trackId = 'shoot-' . $frame->project_id . '-s' . $frame->scene_number . '-f' . $frame->frame_number . '-' . uniqid();

            // Get the image model
            $imageModel = AiCustomGenerator::with('aiModel')
                ->where('slug', 'storyboard-artist')
                ->first();

            if (!$imageModel) {
                return response()->json(['error' => 'Image model not configured'], 500);
            }

            // Prepare generation data (this is what gets sent to ModelsLab)
            $genData = [
                'prompt' => $validated['prompt'],
                'hook_track_id' => $trackId,
                'frame_id' => $frame->id,
                'project_id' => $frame->project_id
            ];
            
            // Use project seed if available
            if ($frame->project->seed) {
                $genData['seed'] = $frame->project->seed;
            }

            // Add filters from project payload if any
            $payload = json_decode($frame->project->payload, true);
            if (isset($payload['filters']) && !empty($payload['filters'])) {
                $genData['filters'] = $payload['filters'];
            }

            // Save the request payload BEFORE dispatching the job
            $frame->update([
                'status_image' => 1,
                'track_id' => $trackId,
                'prompt' => $validated['prompt'],
                'payload_image' => json_encode($genData)  // Save the request payload
            ]);

            if ($isDemo) {
                return $this->demoGenerateImage($frame);
            }

            // Queue the generation job
            GenerateFrameImage::dispatch($imageModel, $genData, $trackId, $frame->id);

            Log::info('Image generation queued', [
                'frame_id' => $frame->id,
                'track_id' => $trackId,
                'project_seed' => $frame->project->seed ?? 'not set'
            ]);

            return response()->json([
                'status' => 'processing',
                'message' => 'Image generation started',
                'frame_id' => $frame->id
            ], 200);

        } catch (Exception $e) {
            Log::error('GenerateImage Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to start generation'], 500);
        }
    }

    private function demoGenerateImage($frame)
    {
        try {
            $sceneNumber = $frame->scene_number;
            $imagePath = public_path("demo/scene{$sceneNumber}.png");
            
            if (!file_exists($imagePath)) {
                // Fallback to scene1.png if specific scene not found
                $imagePath = public_path('demo/scene1.png');
            }
            
            if (!file_exists($imagePath)) {
                return response()->json(['error' => 'Demo image not found'], 404);
            }
            
            // Copy to user's project directory
            $userPath = "ai-projects/shoot/{$frame->project->user_id}";
            $localPath = $userPath . '/scene_' . $sceneNumber . '_' . uniqid() . '.png';
            $fullPath = public_path($localPath);
            
            if (!is_dir(public_path($userPath))) {
                mkdir(public_path($userPath), 0755, true);
            }
            
            copy($imagePath, $fullPath);
            
            // Update frame
            $frame->update([
                'image_path' => '/' . $localPath,
                'status_image' => 2 // Success
            ]);
            
            return response()->json([
                'status' => 'processing',
                'message' => 'Image generation started',
                'frame_id' => $frame->id
            ], 200);
            
        } catch (Exception $e) {
            Log::error('DemoGenerateImage Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate demo image'], 500);
        }
    }

    /**
     * Check image generation status
     */
    public function getImageStatus(Request $request, int $frameId)
    {
        try {
            $userId = Auth::id();
            
            $frame = ShootFrame::with('project')
                ->where('id', $frameId)
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$frame) {
                return response()->json(['error' => 'Frame not found'], 404);
            }

            // If image already exists, return success
            if ($frame->status_image == 2 && $frame->image_path) {
                return response()->json([
                    'status' => 'success',
                    'image_url' => asset($frame->image_path),
                    'prompt' => $frame->prompt,
                    'frame_id' => $frame->id
                ], 200);
            }

            // Check if still processing (demo mode may still be copying file)
            if ($frame->status_image == 1) {
                // Wait a moment and check again
                sleep(1);
                
                $frame->refresh();
                if ($frame->status_image == 2 && $frame->image_path) {
                    return response()->json([
                        'status' => 'success',
                        'image_url' => asset($frame->image_path),
                        'prompt' => $frame->prompt,
                        'frame_id' => $frame->id
                    ], 200);
                }
            }

            return response()->json([
                'status' => 'processing',
                'frame_id' => $frame->id
            ], 200);

        } catch (Exception $e) {
            Log::error('GetImageStatus Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get status'], 500);
        }
    }
    public function getImageStatus1(Request $request, int $frameId)
    {
        try {
            $userId = Auth::id();
            
            $frame = ShootFrame::with('project')
                ->where('id', $frameId)
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$frame) {
                return response()->json(['error' => 'Frame not found'], 404);
            }

            $statusMap = [
                0 => 'idle',
                1 => 'processing',
                2 => 'success',
                3 => 'failed'
            ];

            return response()->json([
                'frame_id' => $frame->id,
                'status' => $statusMap[$frame->status_image] ?? 'idle',
                'image_url' => $frame->image_path ? asset($frame->image_path) : null,
                'prompt' => $frame->prompt
            ], 200);

        } catch (Exception $e) {
            Log::error('GetImageStatus Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get status'], 500);
        }
    }

    /**
     * Update frame prompt
     */
    public function updateFramePrompt(Request $request, int $frameId)
    {
        try {
            $userId = Auth::id();
            $validated = $request->validate([
                'prompt' => 'required|string'
            ]);

            $frame = ShootFrame::where('id', $frameId)
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$frame) {
                return response()->json(['error' => 'Frame not found'], 404);
            }

            $frame->update(['prompt' => $validated['prompt']]);

            return response()->json([
                'success' => true,
                'message' => 'Prompt updated successfully'
            ], 200);

        } catch (Exception $e) {
            Log::error('UpdateFramePrompt Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update prompt'], 500);
        }
    }

    /**
     * Delete a frame (scene)
     */
    public function deleteFrame(Request $request, int $frameId)
    {
        try {
            $userId = Auth::id();
            
            $frame = ShootFrame::where('id', $frameId)
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$frame) {
                return response()->json(['error' => 'Frame not found'], 404);
            }

            // Delete associated image file if exists
            if ($frame->image_path && file_exists(public_path($frame->image_path))) {
                unlink(public_path($frame->image_path));
            }

            // Delete associated video file if exists
            if ($frame->video_path && file_exists(public_path($frame->video_path))) {
                unlink(public_path($frame->video_path));
            }

            $frame->delete();

            return response()->json([
                'success' => true,
                'message' => 'Frame deleted successfully'
            ], 200);

        } catch (Exception $e) {
            Log::error('DeleteFrame Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete frame'], 500);
        }
    }

    /**
     * Get ready images for video generation
     */
    public function getReadyImages(Request $request)
    {
        try {
            $userId = Auth::id();
            
            $frames = ShootFrame::with('project')
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->where('status_image', 2) // Success
                ->where('status_video', '!=', 2) // Not already have video
                ->get();

            $scenes = $frames->map(function($frame) {
                return [
                    'id' => $frame->id,
                    'scene_number' => $frame->scene_number,
                    'image_url' => asset($frame->image_path),
                    'project_id' => $frame->project_id
                ];
            });

            return response()->json([
                'has_images' => $frames->count() > 0,
                'scenes' => $scenes
            ], 200);

        } catch (Exception $e) {
            Log::error('GetReadyImages Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch ready images'], 500);
        }
    }

    /**
     * Generate video from frame
     */
    public function generateVideo(Request $request)
    {
        try {
            $userId = Auth::id();
            $validated = $request->validate([
                'frame_id' => 'required|integer',
            ]);

            $frame = ShootFrame::with('project')
                ->where('id', $validated['frame_id'])
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$frame) {
                return response()->json(['error' => 'Frame not found'], 404);
            }

            if ($frame->status_image !== 2) {
                return response()->json(['error' => 'Image not ready'], 400);
            }

            // Get the script project to access original visual prompt
            $scriptProject = ScriptProject::where('id', $frame->project->source_id)
                ->where('user_id', $userId)
                ->first();

            // Use the original visual prompt (without style/character) for video
            $videoPrompt = $frame->prompt; // This is the stored prompt from frame creation
            
            // If we have the original script scene, use its visual_prompt (clean version)
            if ($scriptProject) {
                $scene = $scriptProject->scenes->where('scene_number', $frame->scene_number)->first();
                if ($scene && $scene->visual_prompt) {
                    $videoPrompt = $scene->visual_prompt;
                    Log::info('Using original visual prompt for video', [
                        'frame_id' => $frame->id,
                        'scene_number' => $frame->scene_number,
                        'prompt_length' => strlen($videoPrompt)
                    ]);
                }
            }

            // Check for demo mode
            $project = $frame->project;
            $payload = json_decode($project->payload, true);
            
            if (isset($payload['demo_mode']) && $payload['demo_mode'] == true) {
                return $this->demoGenerateVideo($frame);
            }

            $trackId = 'video-' . $frame->id . '-' . uniqid();

            // Prepare generation data - only motion description, no style/character
            $genData = [
                'init_image' => asset($frame->image_path),
                'prompt' => $videoPrompt,
                'hook_track_id' => $trackId,
                'frame_id' => $frame->id,
                'negative_prompt' => 'blurry, low quality, distorted, extra limbs, missing limbs, broken fingers, deformed, glitch, artifacts, unrealistic, low resolution, bad anatomy, duplicate, cropped, watermark, text, logo, jpeg artifacts, noisy, oversaturated, underexposed, overexposed, flicker, unstable motion, motion blur, stretched, mutated, out of frame, bad proportions',
                'output_type' => 'mp4',
                'resolution' => '16:9'
            ];

            $frame->update([
                'status_video' => 1, // Processing
                'video_track_id' => $trackId,
                'payload_video' => json_encode($genData)
            ]);

            // Get the video model
            $videoModel = AiCustomGenerator::with('aiModel')
                ->where('slug', 'cinematographer')
                ->first();

            if (!$videoModel) {
                return response()->json(['error' => 'Video model not configured'], 500);
            }

            // Queue video generation job
            GenerateFrameVideo::dispatch($videoModel, $genData, $trackId, $frame->id);

            Log::info('Video generation queued', [
                'frame_id' => $frame->id,
                'track_id' => $trackId,
                'prompt_preview' => substr($videoPrompt, 0, 100),
                'using_original_prompt' => true
            ]);

            return response()->json([
                'status' => 'processing',
                'message' => 'Video generation started',
                'frame_id' => $frame->id
            ], 200);

        } catch (Exception $e) {
            Log::error('GenerateVideo Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to start video generation'], 500);
        }
    }

    private function demoGenerateVideo($frame)
    {
        try {
            $sceneNumber = $frame->scene_number;
            $videoPath = public_path("demo/clip{$sceneNumber}.mp4");
            
            if (!file_exists($videoPath)) {
                // Fallback to clip1.mp4 if specific scene not found
                $videoPath = public_path('demo/clip1.mp4');
            }
            
            if (!file_exists($videoPath)) {
                return response()->json(['error' => 'Demo video not found'], 404);
            }
            
            // Copy to user's project directory
            $userPath = "ai-projects/shoot/{$frame->project->user_id}";
            $localPath = $userPath . '/video_' . $sceneNumber . '_' . uniqid() . '.mp4';
            $fullPath = public_path($localPath);
            
            if (!is_dir(public_path($userPath))) {
                mkdir(public_path($userPath), 0755, true);
            }
            
            copy($videoPath, $fullPath);
            
            // Update frame
            $frame->update([
                'video_path' => '/' . $localPath,
                'status_video' => 2 // Success
            ]);
            
            return response()->json([
                'status' => 'processing',
                'message' => 'Video generation started',
                'frame_id' => $frame->id
            ], 200);
            
        } catch (Exception $e) {
            Log::error('DemoGenerateVideo Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate demo video'], 500);
        }
    }

    /**
     * Check video generation status
     */
    public function getVideoStatus(Request $request, int $frameId)
    {
        try {
            $userId = Auth::id();
            
            $frame = ShootFrame::with('project')
                ->where('id', $frameId)
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$frame) {
                return response()->json(['error' => 'Frame not found'], 404);
            }

            // If video already exists, return success
            if ($frame->status_video == 2 && $frame->video_path) {
                return response()->json([
                    'status' => 'success',
                    'video_url' => asset($frame->video_path),
                    'frame_id' => $frame->id
                ], 200);
            }

            // Check if still processing (demo mode may still be copying file)
            if ($frame->status_video == 1) {
                sleep(1);
                
                $frame->refresh();
                if ($frame->status_video == 2 && $frame->video_path) {
                    return response()->json([
                        'status' => 'success',
                        'video_url' => asset($frame->video_path),
                        'frame_id' => $frame->id
                    ], 200);
                }
            }

            return response()->json([
                'status' => 'processing',
                'frame_id' => $frame->id
            ], 200);

        } catch (Exception $e) {
            Log::error('GetVideoStatus Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get status'], 500);
        }
    }
    public function getVideoStatus1(Request $request, int $frameId)
    {
        try {
            $userId = Auth::id();
            
            $frame = ShootFrame::with('project')
                ->where('id', $frameId)
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$frame) {
                return response()->json(['error' => 'Frame not found'], 404);
            }

            $statusMap = [
                0 => 'idle',
                1 => 'processing',
                2 => 'success',
                3 => 'failed'
            ];

            return response()->json([
                'frame_id' => $frame->id,
                'status' => $statusMap[$frame->status_video] ?? 'idle',
                'video_url' => $frame->video_path ? asset($frame->video_path) : null
            ], 200);

        } catch (Exception $e) {
            Log::error('GetVideoStatus Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get status'], 500);
        }
    }

    /**
     * Download all images as zip
     */
    public function downloadAllImages(Request $request, int $projectId)
    {
        try {
            $userId = Auth::id();
            
            $project = ShootProject::with('frames')
                ->where(['id' => $projectId, 'user_id' => $userId])
                ->first();

            if (!$project) {
                abort(404, 'Project not found');
            }

            $frames = $project->frames->where('status_image', 2);

            if ($frames->isEmpty()) {
                abort(404, 'No images available');
            }

            // Create temporary directory
            $tempDir = storage_path('app/temp/shoot_' . uniqid());
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $projectTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $project->title);
            if (empty($projectTitle)) $projectTitle = 'project';

            // Copy all image files
            foreach ($frames as $frame) {
                if ($frame->image_path && file_exists(public_path($frame->image_path))) {
                    $ext = pathinfo($frame->image_path, PATHINFO_EXTENSION);
                    $filename = $projectTitle . '_scene' . $frame->scene_number . '.' . $ext;
                    copy(public_path($frame->image_path), $tempDir . '/' . $filename);
                }
            }

            // Create zip
            $zipFileName = $projectTitle . '_images.zip';
            $zipPath = storage_path('app/public/' . $zipFileName);

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                $files = glob($tempDir . '/*');
                foreach ($files as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();
            }

            // Clean up
            array_map('unlink', glob("$tempDir/*.*"));
            rmdir($tempDir);

            return response()->download($zipPath)->deleteFileAfterSend(true);

        } catch (Exception $e) {
            Log::error('DownloadAllImages Error: ' . $e->getMessage());
            abort(403, 'Failed to create download');
        }
    }

    /**
     * Download all videos as zip
     */
    public function downloadAllVideos(Request $request, int $projectId)
    {
        try {
            $userId = Auth::id();
            
            $project = ShootProject::with('frames')
                ->where(['id' => $projectId, 'user_id' => $userId])
                ->first();

            if (!$project) {
                abort(404, 'Project not found');
            }

            $frames = $project->frames->where('status_video', 2);

            if ($frames->isEmpty()) {
                abort(404, 'No videos available');
            }

            // Create temporary directory
            $tempDir = storage_path('app/temp/shoot_' . uniqid());
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $projectTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $project->title);
            if (empty($projectTitle)) $projectTitle = 'project';

            // Copy all video files
            foreach ($frames as $frame) {
                if ($frame->video_path && file_exists(public_path($frame->video_path))) {
                    $ext = pathinfo($frame->video_path, PATHINFO_EXTENSION);
                    $filename = $projectTitle . '_scene' . $frame->scene_number . '.' . $ext;
                    copy(public_path($frame->video_path), $tempDir . '/' . $filename);
                }
            }

            // Create zip
            $zipFileName = $projectTitle . '_videos.zip';
            $zipPath = storage_path('app/public/' . $zipFileName);

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                $files = glob($tempDir . '/*');
                foreach ($files as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();
            }

            // Clean up
            array_map('unlink', glob("$tempDir/*.*"));
            rmdir($tempDir);

            return response()->download($zipPath)->deleteFileAfterSend(true);

        } catch (Exception $e) {
            Log::error('DownloadAllVideos Error: ' . $e->getMessage());
            abort(403, 'Failed to create download');
        }
    }

    /**
     * ============================================
     * SOUND DIRECTOR API METHODS
     * ============================================
     */

    /**
     * Get scenes with voiceover data for sound project
     */
    public function getSoundScenes(Request $request, int $scriptProjectId)
    {
        try {
            $userId = Auth::id();

            $scriptProject = ScriptProject::with('scenes')
                ->where(['id' => $scriptProjectId, 'user_id' => $userId])
                ->first();

            if (!$scriptProject) {
                return response()->json(['error' => 'Script project not found'], 404);
            }

            // First, find the shoot project that was auto-created
            $shootProject = ShootProject::where('source_id', $scriptProject->id)
                ->where('source_type', 'script_project')
                ->first();

            if (!$shootProject) {
                // If no shoot project exists, the script project might not be fully ready
                return response()->json([
                    'error' => 'Shoot project not found. Please ensure the script project is fully processed.',
                    'scenes' => [] // Return empty scenes
                ], 404);
            }

            // Now get or create sound project linked to this shoot project
            $soundProject = SoundProject::firstOrCreate(
                [
                    'user_id' => $userId,
                    'shoot_project_id' => $shootProject->id  // Link to shoot project
                ],
                [
                    'title' => $scriptProject->title . ' (Audio)',
                    'script_project_id' => $scriptProject->id,
                    'scene_count' => $scriptProject->scene_count,
                    'status' => 0,
                    'payload' => json_encode(['auto_created' => true])
                ]
            );

            $scenes = $scriptProject->scenes->sortBy('scene_number')->map(function($scene) {
                $settings = $scene->voiceover_settings ?? [];
                
                return [
                    'id' => $scene->id,
                    'scene_number' => $scene->scene_number,
                    'voiceover_script' => $scene->voiceover_script,
                    'voiceover_status' => $scene->voiceover_status ?? 0,
                    'voiceover_url' => $scene->voiceover_url ? asset($scene->voiceover_url) : null,
                    'voiceover_settings' => [
                        'voice_id' => $settings['voice_id'] ?? null,
                        'language' => $settings['language'] ?? 'english',
                        'emotion' => $settings['emotion'] ?? 'happy',
                        'speed' => $settings['speed'] ?? 1.0,
                        'format' => $settings['format'] ?? 'mp3',
                        'bitrate' => $settings['bitrate'] ?? 192
                    ]
                ];
            })->values();

            return response()->json([
                'script_project_id' => $scriptProject->id,
                'shoot_project_id' => $shootProject->id,
                'sound_project_id' => $soundProject->id,
                'title' => $scriptProject->title,
                'scenes' => $scenes
            ], 200);

        } catch (Exception $e) {
            Log::error('GetSoundScenes Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch scenes'], 500);
        }
    }

    /**
     * Generate voiceover for a scene
     */
    public function generateVoiceover(Request $request)
    {
        try {
            $userId = Auth::id();
            $validated = $request->validate([
                'scene_id' => 'required|integer',
                'text' => 'required|string|max:2500',
                'voice_id' => 'required|string',
                'language' => 'required|string',
                'emotion' => 'nullable|string',
                'speed' => 'required|numeric|min:0.5|max:1.5',
                'format' => 'required|in:wav,mp3,ogg',
                'bitrate' => 'required|integer|in:64,128,192,256,320'
            ]);

            $scene = ScriptScene::where('id', $validated['scene_id'])
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$scene) {
                return response()->json(['error' => 'Scene not found'], 404);
            }

            // Get sound project to check demo mode
            $soundProject = SoundProject::where('script_project_id', $scene->project_id)->first();

            // Or check the script project's payload directly
            $scriptPayload = json_decode($scene->project->payload, true);
            $isDemo = isset($scriptPayload['demo_mode']) && $scriptPayload['demo_mode'] == true;

            // Generate numeric track ID (matches pattern from old TTS: '775' . date('YmdHis'))
            $trackId = '775' . $userId. date('Ymd'). $scene->id;

            // Update scene status
            $scene->update([
                'voiceover_status' => 1, // Processing
                'voiceover_track_id' => $trackId,
                'voiceover_settings' => [
                    'voice_id' => $validated['voice_id'],
                    'language' => $validated['language'],
                    'emotion' => $validated['emotion'] ?? 'happy',
                    'speed' => $validated['speed'],
                    'format' => $validated['format'],
                    'bitrate' => $validated['bitrate']
                ]
            ]);

            if ($isDemo) {
                // Demo mode - return after 3 second delay
                return $this->demoGenerateVoiceover($scene, $trackId);
            }

            // Get TTS model
            $ttsModel = AiCustomGenerator::with('aiModel')
                ->where('slug', 'text-to-speech')
                ->first();

            if (!$ttsModel) {
                return response()->json(['error' => 'TTS model not configured'], 500);
            }

            // Prepare generation data
            $genData = [
                'prompt' => $validated['text'],
                'voice_id' => $validated['voice_id'],
                'language' => $validated['language'],
                'emotion' => $validated['emotion'] ?? 'happy',
                'speed' => $validated['speed'],
                'format' => $validated['format'],
                'bitrate' => $validated['bitrate'],
                'hook_track_id' => $trackId,
                'scene_id' => $scene->id
            ];

            // Queue generation job
            GenerateVoiceover::dispatch($ttsModel, $genData, $trackId, $scene->id);

            Log::info('Voiceover generation queued', [
                'scene_id' => $scene->id,
                'track_id' => $trackId
            ]);

            return response()->json([
                'status' => 'processing',
                'message' => 'Voiceover generation started',
                'scene_id' => $scene->id
            ], 200);

        } catch (Exception $e) {
            Log::error('GenerateVoiceover Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to start generation'], 500);
        }
    }

    /**
     * Demo mode voiceover generation
     */
    private function demoGenerateVoiceover($scene, $trackId)
    {
        Log::info('DemoGenerateVoiceover: Started', [
            'scene_id' => $scene->id,
            'track_id' => $trackId
        ]);
        
        // Simulate processing delay
        sleep(3);
        
        Log::info('DemoGenerateVoiceover: Delay completed', ['scene_id' => $scene->id]);
        
        // Use a demo MP3 URL (online accessible)
        $demoUrl = 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3';
        
        try {
            DB::beginTransaction();
            
            $updateResult = ScriptScene::where('id', $scene->id)->update([
                'voiceover_url' => $demoUrl,
                'voiceover_status' => 2,
                'voiceover_track_id' => $trackId
            ]);
            
            Log::info('DemoGenerateVoiceover: Update result', [
                'scene_id' => $scene->id,
                'update_success' => $updateResult,
                'affected_rows' => $updateResult
            ]);
            
            DB::commit();
            
            // Verify the update with a fresh query
            $updatedScene = ScriptScene::find($scene->id);
            Log::info('DemoGenerateVoiceover: Verification', [
                'scene_id' => $scene->id,
                'current_voiceover_status' => $updatedScene->voiceover_status,
                'current_voiceover_url' => $updatedScene->voiceover_url,
                'current_voiceover_track_id' => $updatedScene->voiceover_track_id
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('DemoGenerateVoiceover: Update failed', [
                'scene_id' => $scene->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return response()->json([
            'status' => 'processing',
            'message' => 'Demo voiceover generated',
            'scene_id' => $scene->id
        ], 200);
    }

    /**
     * Check voiceover generation status
     */
    public function getVoiceoverStatus(Request $request, int $sceneId)
    {
        try {
            $userId = Auth::id();

            $scene = ScriptScene::where('id', $sceneId)
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$scene) {
                Log::warning('GetVoiceoverStatus: Scene not found', ['scene_id' => $sceneId]);
                return response()->json(['error' => 'Scene not found'], 404);
            }

            Log::info('GetVoiceoverStatus: Checking scene', [
                'scene_id' => $scene->id,
                'voiceover_status' => $scene->voiceover_status,
                'voiceover_url' => $scene->voiceover_url,
                'voiceover_track_id' => $scene->voiceover_track_id
            ]);

            if ($scene->voiceover_status == 2 && $scene->voiceover_url) {
                Log::info('GetVoiceoverStatus: Success', [
                    'scene_id' => $scene->id,
                    'url' => $scene->voiceover_url
                ]);
                return response()->json([
                    'status' => 'success',
                    'url' => $scene->voiceover_url,
                    'scene_id' => $scene->id
                ], 200);
            }

            if ($scene->voiceover_status == 3) {
                Log::warning('GetVoiceoverStatus: Failed', ['scene_id' => $scene->id]);
                return response()->json([
                    'status' => 'failed',
                    'scene_id' => $scene->id
                ], 200);
            }

            Log::info('GetVoiceoverStatus: Still processing', ['scene_id' => $scene->id]);
            return response()->json([
                'status' => 'processing',
                'scene_id' => $scene->id
            ], 200);

        } catch (Exception $e) {
            Log::error('GetVoiceoverStatus Error: ' . $e->getMessage(), [
                'scene_id' => $sceneId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to get status'], 500);
        }
    }

    /**
     * Generate SFX
     */
    public function generateSFX(Request $request)
    {
        try {
            $userId = Auth::id();
            $validated = $request->validate([
                'prompt' => 'required|string|max:500',
                'script_project_id' => 'required|integer'
            ]);

            $scriptProject = ScriptProject::where('id', $validated['script_project_id'])
                ->where('user_id', $userId)
                ->first();

            if (!$scriptProject) {
                return response()->json(['error' => 'Script project not found'], 404);
            }

            // Get or create sound project
            $soundProject = SoundProject::firstOrCreate(
                [
                    'user_id' => $userId,
                    'script_project_id' => $scriptProject->id
                ],
                [
                    'title' => $scriptProject->title . ' (Audio)',
                    'shoot_project_id' => null,
                    'scene_count' => $scriptProject->scene_count,
                    'status' => 0,
                    'payload' => json_encode(['auto_created' => true])
                ]
            );

            // Or check the script project's payload directly
            $scriptPayload = json_decode($scriptProject->payload, true);
            $isDemo = isset($scriptPayload['demo_mode']) && $scriptPayload['demo_mode'] == true;

            $trackId = 'sfx-' . $soundProject->id . '-' . uniqid();

            // Create SFX track record
            $sfxTrack = SFXTrack::create([
                'project_id' => $soundProject->id,
                'script_project_id' => $scriptProject->id,
                'prompt' => $validated['prompt'],
                'status' => 1, // Processing
                'track_id' => $trackId,
                'settings' => $request->all()
            ]);

            if ($isDemo) {
                // Demo mode - return after 3 second delay
                return $this->demoGenerateSFX($sfxTrack, $trackId);
            }

            // Get SFX model
            $sfxModel = AiCustomGenerator::with('aiModel')
                ->where('slug', 'sfx-generator')
                ->first();

            if (!$sfxModel) {
                return response()->json(['error' => 'SFX model not configured'], 500);
            }

            // Prepare generation data
            $genData = [
                'prompt' => $validated['prompt'],
                'hook_track_id' => $trackId,
                'sfx_id' => $sfxTrack->id
            ];

            // Queue generation job
            GenerateSFX::dispatch($sfxModel, $genData, $trackId, $sfxTrack->id);

            Log::info('SFX generation queued', [
                'sfx_id' => $sfxTrack->id,
                'track_id' => $trackId
            ]);

            return response()->json([
                'status' => 'processing',
                'message' => 'SFX generation started',
                'sfx_id' => $sfxTrack->id
            ], 200);

        } catch (Exception $e) {
            Log::error('GenerateSFX Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to start generation'], 500);
        }
    }

    /**
     * Demo mode SFX generation
     */
    private function demoGenerateSFX($sfxTrack, $trackId)
    {
        // Simulate processing delay
        sleep(3);

        // Demo SFX URLs (online accessible)
        $demoSfxUrls = [
            'https://www.soundjay.com/misc/sounds/bell-ringing-01.mp3',
            'https://www.soundjay.com/misc/sounds/button-click-01.mp3',
            'https://www.soundjay.com/nature/sounds/rain-01.mp3',
            'https://www.soundjay.com/mechanical/sounds/car-horn-01.mp3',
            'https://www.soundjay.com/mechanical/sounds/door-slam-01.mp3'
        ];

        $demoUrl = $demoSfxUrls[array_rand($demoSfxUrls)];

        $sfxTrack->update([
            'url' => $demoUrl,
            'status' => 2,
            'track_id' => $trackId
        ]);

        return response()->json([
            'status' => 'processing',
            'message' => 'Demo SFX generated',
            'sfx_id' => $sfxTrack->id
        ], 200);
    }

    /**
     * Get SFX tracks for a sound project
     */
    public function getSFXTracks(Request $request, int $scriptProjectId)
    {
        try {
            $userId = Auth::id();

            $scriptProject = ScriptProject::where('id', $scriptProjectId)
                ->where('user_id', $userId)
                ->first();

            if (!$scriptProject) {
                return response()->json(['error' => 'Script project not found'], 404);
            }

            $soundProject = SoundProject::where('script_project_id', $scriptProjectId)
                ->where('user_id', $userId)
                ->first();

            if (!$soundProject) {
                return response()->json(['sfx_tracks' => []], 200);
            }

            $sfxTracks = SFXTrack::where('project_id', $soundProject->id)
                ->orderBy('id', 'desc')
                ->get()
                ->map(function($track) {
                    return [
                        'id' => $track->id,
                        'prompt' => $track->prompt,
                        'status' => $track->status,
                        'url' => $track->url ? asset($track->url) : null,
                        'created_at' => $track->created_at->format('M d, Y H:i')
                    ];
                });

            return response()->json([
                'sfx_tracks' => $sfxTracks
            ], 200);

        } catch (Exception $e) {
            Log::error('GetSFXTracks Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch SFX tracks'], 500);
        }
    }

    /**
     * Check SFX generation status
     */
    public function getSFXStatus(Request $request, int $sfxId)
    {
        try {
            $userId = Auth::id();

            $sfxTrack = SFXTrack::with('soundProject')
                ->where('id', $sfxId)
                ->whereHas('soundProject', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$sfxTrack) {
                return response()->json(['error' => 'SFX track not found'], 404);
            }

            if ($sfxTrack->status == 2 && $sfxTrack->url) {
                return response()->json([
                    'status' => 'success',
                    'url' => $sfxTrack->url,
                    'sfx_id' => $sfxTrack->id
                ], 200);
            }

            if ($sfxTrack->status == 3) {
                return response()->json([
                    'status' => 'failed',
                    'sfx_id' => $sfxTrack->id
                ], 200);
            }

            // Check ml_hits for updates
            if ($sfxTrack->track_id) {
                $hit = DB::table('ml_hits')
                    ->where('track_id', $sfxTrack->track_id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($hit && $hit->status == 2) {
                    $data = json_decode($hit->json, true);
                    if (isset($data['output'][0])) {
                        $sfxTrack->update([
                            'url' => $data['output'][0],
                            'status' => 2
                        ]);
                        return response()->json([
                            'status' => 'success',
                            'url' => $data['output'][0],
                            'sfx_id' => $sfxTrack->id
                        ], 200);
                    }
                }
            }

            return response()->json([
                'status' => 'processing',
                'sfx_id' => $sfxTrack->id
            ], 200);

        } catch (Exception $e) {
            Log::error('GetSFXStatus Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get status'], 500);
        }
    }

    /**
     * Delete SFX track
     */
    public function deleteSFX(Request $request, int $sfxId)
    {
        try {
            $userId = Auth::id();

            $sfxTrack = SFXTrack::with('soundProject')
                ->where('id', $sfxId)
                ->whereHas('soundProject', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$sfxTrack) {
                return response()->json(['error' => 'SFX track not found'], 404);
            }

            $sfxTrack->delete();

            return response()->json([
                'success' => true,
                'message' => 'SFX deleted successfully'
            ], 200);

        } catch (Exception $e) {
            Log::error('DeleteSFX Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete SFX'], 500);
        }
    }
    

    /**
     * Generate ambient music from prompt
     */
    public function generateAmbient(Request $request, int $trackId)
    {
        try {
            $userId = Auth::id();
            $validated = $request->validate([
                'prompt' => 'required|string|max:500',
                'mood' => 'nullable|string'
            ]);

            $track = SoundTrack::with('project')
                ->where('id', $trackId)
                ->whereHas('project', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->first();

            if (!$track || $track->track_type !== 'ambient') {
                return response()->json(['message' => 'Ambient track not found'], 404);
            }

            // Check credits
            // $creditCheck = $this->checkAndDeductCredits($userId, 'music', 2);
            // if ($creditCheck !== true) {
            //     return $creditCheck;
            // }

            $fullPrompt = $validated['prompt'];
            if (!empty($validated['mood'])) {
                $fullPrompt = $validated['mood'] . ' mood: ' . $validated['prompt'];
            }

            $track->update([
                'prompt' => $fullPrompt,
                'status' => 1, // Processing
                'track_id' => 'music-' . $track->id . '-' . Str::random(8)
            ]);

            $this->dispatchMusicGeneration($track);

            return response()->json([
                'status' => 'processing',
                'message' => 'Ambient music generation started'
            ], 200);

        } catch (Exception $e) {
            Log::error('GenerateAmbient Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to generate ambient music'], 500);
        }
    }
    

    /**
     * ============================================
     * MOVIE PRODUCER API METHODS
     * ============================================
     */

    /**
     * Create movie from shoot and sound projects
     */
    public function createMovie(Request $request)
    {
        try {
            $userId = Auth::id();
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'shoot_project_id' => 'required|exists:ai_director_shoot_projects,id',
                'sound_project_id' => 'required|exists:ai_director_sound_projects,id'
            ]);

            // Verify ownership
            $shootProject = ShootProject::where(['id' => $validated['shoot_project_id'], 'user_id' => $userId])->first();
            $soundProject = SoundProject::where(['id' => $validated['sound_project_id'], 'user_id' => $userId])->first();

            if (!$shootProject || !$soundProject) {
                return response()->json(['message' => 'Projects not found'], 404);
            }

            DB::beginTransaction();

            $movie = MovieProject::create([
                'user_id' => $userId,
                'title' => $validated['title'],
                'shoot_project_id' => $validated['shoot_project_id'],
                'sound_project_id' => $validated['sound_project_id'],
                'scene_count' => $shootProject->scene_count,
                'status' => 1, // Rendering
                'payload' => json_encode($validated)
            ]);

            // Queue final rendering
            $this->dispatchMovieRendering($movie);

            DB::commit();

            return response()->json([
                'status' => 'rendering',
                'message' => 'Movie rendering started',
                'movie_id' => $movie->id
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('CreateMovie Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create movie'], 500);
        }
    }

    /**
     * Get movie scenes with playable URLs
     */
    public function getMovieScenes(int $movieId)
    {
        try {
            $userId = Auth::id();
            
            $movie = MovieProject::with('scenes')
                ->where(['id' => $movieId, 'user_id' => $userId])
                ->first();

            if (!$movie) {
                return response()->json(['message' => 'Movie not found'], 404);
            }

            $scenes = $movie->scenes->map(function($scene) {
                return [
                    'scene_number' => $scene->scene_number,
                    'video_url' => $scene->video_path ? url($scene->video_path) : null,
                    'thumbnail' => $scene->thumbnail ? url($scene->thumbnail) : null,
                    'duration' => $scene->duration,
                    'status' => $scene->status
                ];
            });

            return response()->json([
                'movie' => [
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'status' => $movie->status,
                    'scenes' => $scenes
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('GetMovieScenes Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to get movie scenes'], 500);
        }
    }

    /**
     * Download final movie
     */
    public function downloadMovie(int $movieId)
    {
        try {
            $userId = Auth::id();
            
            $movie = MovieProject::where(['id' => $movieId, 'user_id' => $userId])->first();

            if (!$movie) {
                return response()->json(['message' => 'Movie not found'], 404);
            }

            if ($movie->status !== 2) {
                return response()->json(['message' => 'Movie not ready yet'], 400);
            }

            if (!$movie->final_path || !file_exists(public_path($movie->final_path))) {
                return response()->json(['message' => 'Movie file not found'], 404);
            }

            $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $movie->title) . '.mp4';
            
            return response()->download(public_path($movie->final_path), $filename);

        } catch (Exception $e) {
            Log::error('DownloadMovie Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to download movie'], 500);
        }
    }

    /**
     * ============================================
     * WEBHOOK HANDLERS
     * ============================================
     */

    /**
     * Handle script generation webhook
     */
    public function scriptWebhook(Request $request)
    {
        try {
            $trackId = $request->track_id;
            
            Log::info('Script webhook received', [
                'track_id' => $trackId,
                'status' => $request->status
            ]);

            if ($request->status === 'success' && isset($request->output)) {
                // Parse track ID to find project and scene
                preg_match('/script-(\d+)-s(\d+)/', $trackId, $matches);
                
                if (count($matches) >= 3) {
                    $projectId = $matches[1];
                    $sceneNumber = $matches[2];
                    
                    $scene = ScriptScene::where([
                        'project_id' => $projectId,
                        'scene_number' => $sceneNumber
                    ])->first();
                    
                    if ($scene) {
                        $output = $request->output;
                        
                        // Parse output based on format (expecting JSON with voiceover and visual)
                        if (is_string($output)) {
                            $output = json_decode($output, true);
                        }
                        
                        $scene->update([
                            'voiceover_script' => $output['voiceover'] ?? $output,
                            'visual_prompt' => $output['visual'] ?? $output,
                            'status' => 2 // Success
                        ]);
                        
                        // Check if project is complete
                        $this->checkScriptProjectCompletion($projectId);
                    }
                }
            }

            return response()->json(['message' => 'Success'], 200);

        } catch (Exception $e) {
            Log::error('ScriptWebhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    /**
     * Handle image generation webhook
     */
    public function imageWebhook(Request $request)
    {
        try {
            $trackId = $request->track_id;
            
            Log::info('Image webhook received', [
                'track_id' => $trackId,
                'status' => $request->status
            ]);

            if ($request->status === 'success' && isset($request->output[0])) {
                preg_match('/shoot-(\d+)-s(\d+)-f(\d+)/', $trackId, $matches);
                
                if (count($matches) >= 4) {
                    $projectId = $matches[1];
                    $sceneNumber = $matches[2];
                    $frameNumber = $matches[3];
                    
                    $frame = ShootFrame::where([
                        'project_id' => $projectId,
                        'scene_number' => $sceneNumber,
                        'frame_number' => $frameNumber
                    ])->first();
                    
                    if ($frame) {
                        $path = 'ai-projects/shoot/' . $frame->project->user_id;
                        $localPath = AiGen::convertLinksToLocal($request->output[0], $path, $trackId);
                        
                        $frame->update([
                            'image_path' => $localPath,
                            'status_image' => 2 // Success
                        ]);
                        
                        // Check if all images are ready
                        $this->checkShootProjectImageCompletion($projectId);
                    }
                }
            } elseif ($request->status === 'error') {
                // Mark as failed
                preg_match('/shoot-(\d+)-s(\d+)-f(\d+)/', $trackId, $matches);
                if (count($matches) >= 4) {
                    ShootFrame::where([
                        'project_id' => $matches[1],
                        'scene_number' => $matches[2],
                        'frame_number' => $matches[3]
                    ])->update(['status_image' => 3]); // Failed
                }
            }

            return response()->json(['message' => 'Success'], 200);

        } catch (Exception $e) {
            Log::error('ImageWebhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    /**
     * Handle video generation webhook
     */
    public function videoWebhook(Request $request)
    {
        try {
            $trackId = $request->track_id;
            
            Log::info('Video webhook received', [
                'track_id' => $trackId,
                'status' => $request->status
            ]);

            if ($request->status === 'success' && isset($request->output[0])) {
                preg_match('/video-(\d+)/', $trackId, $matches);
                
                if (count($matches) >= 2) {
                    $frameId = $matches[1];
                    
                    $frame = ShootFrame::with('project')->find($frameId);
                    
                    if ($frame) {
                        $path = 'ai-projects/shoot/' . $frame->project->user_id;
                        $localPath = AiGen::convertLinksToLocal($request->output[0], $path, $trackId);
                        
                        $frame->update([
                            'video_path' => $localPath,
                            'status_video' => 2 // Success
                        ]);
                        
                        // Check if all videos are ready
                        $this->checkShootProjectVideoCompletion($frame->project_id);
                    }
                }
            } elseif ($request->status === 'error') {
                preg_match('/video-(\d+)/', $trackId, $matches);
                if (count($matches) >= 2) {
                    ShootFrame::find($matches[1])->update(['status_video' => 3]); // Failed
                }
            }

            return response()->json(['message' => 'Success'], 200);

        } catch (Exception $e) {
            Log::error('VideoWebhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    /**
     * Handle TTS webhook
     */
    public function ttsWebhook(Request $request)
    {
        try {
            $trackId = $request->track_id;
            
            Log::info('TTS webhook received', [
                'track_id' => $trackId,
                'status' => $request->status
            ]);

            if ($request->status === 'success' && isset($request->output[0])) {
                preg_match('/tts-(\d+)/', $trackId, $matches);
                
                if (count($matches) >= 2) {
                    $trackId = $matches[1];
                    
                    $track = SoundTrack::find($trackId);
                    
                    if ($track) {
                        $path = 'ai-projects/sound/' . $track->project->user_id;
                        $localPath = AiGen::convertLinksToLocal($request->output[0], $path, $trackId);
                        
                        $track->update([
                            'audio_path' => $localPath,
                            'status' => 2 // Success
                        ]);
                    }
                }
            }

            return response()->json(['message' => 'Success'], 200);

        } catch (Exception $e) {
            Log::error('TTSWebhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    /**
     * Handle SFX webhook
     */
    public function sfxWebhook(Request $request)
    {
        try {
            $trackId = $request->track_id;
            
            Log::info('SFX webhook received', [
                'track_id' => $trackId,
                'status' => $request->status
            ]);

            if ($request->status === 'success' && isset($request->output[0])) {
                preg_match('/sfx-(\d+)/', $trackId, $matches);
                
                if (count($matches) >= 2) {
                    $trackId = $matches[1];
                    
                    $track = SoundTrack::find($trackId);
                    
                    if ($track) {
                        $path = 'ai-projects/sound/' . $track->project->user_id;
                        $localPath = AiGen::convertLinksToLocal($request->output[0], $path, $trackId);
                        
                        $track->update([
                            'audio_path' => $localPath,
                            'status' => 2 // Success
                        ]);
                    }
                }
            }

            return response()->json(['message' => 'Success'], 200);

        } catch (Exception $e) {
            Log::error('SFXWebhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    /**
     * Handle music webhook
     */
    public function musicWebhook(Request $request)
    {
        try {
            $trackId = $request->track_id;
            
            Log::info('Music webhook received', [
                'track_id' => $trackId,
                'status' => $request->status
            ]);

            if ($request->status === 'success' && isset($request->output[0])) {
                preg_match('/music-(\d+)/', $trackId, $matches);
                
                if (count($matches) >= 2) {
                    $trackId = $matches[1];
                    
                    $track = SoundTrack::find($trackId);
                    
                    if ($track) {
                        $path = 'ai-projects/sound/' . $track->project->user_id;
                        $localPath = AiGen::convertLinksToLocal($request->output[0], $path, $trackId);
                        
                        $track->update([
                            'audio_path' => $localPath,
                            'status' => 2 // Success
                        ]);
                    }
                }
            }

            return response()->json(['message' => 'Success'], 200);

        } catch (Exception $e) {
            Log::error('MusicWebhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    /**
     * ============================================
     * HELPER METHODS
     * ============================================
     */

    private function checkScriptProjectCompletion($projectId)
    {
        $project = ScriptProject::with('scenes')->find($projectId);
        
        $completedScenes = $project->scenes->where('status', 2)->count();
        
        if ($completedScenes >= $project->scene_count) {
            $project->update(['status' => 2]); // Complete
            Log::info('Script project completed', ['project_id' => $projectId]);
        }
    }

    private function checkShootProjectImageCompletion($projectId)
    {
        $project = ShootProject::with('frames')->find($projectId);
        
        $completedImages = $project->frames->where('status_image', 2)->count();
        $totalImages = $project->frames->count();
        
        if ($completedImages >= $totalImages) {
            $project->update(['status' => 2]); // Images ready, waiting for videos
            Log::info('Shoot project images completed', ['project_id' => $projectId]);
        }
    }

    private function checkShootProjectVideoCompletion($projectId)
    {
        $project = ShootProject::with('frames')->find($projectId);
        
        $completedVideos = $project->frames->where('status_video', 2)->count();
        $totalVideos = $project->frames->count();
        
        if ($completedVideos >= $totalVideos) {
            $project->update(['status' => 4]); // All videos complete
            Log::info('Shoot project videos completed', ['project_id' => $projectId]);
        }
    }

    private function checkAndDeductCredits($userId, $type, $amount)
    {
        $creditCosts = [
            'script' => 1,
            'script_scene' => 1,
            'image' => 2,
            'video' => 5,
            'tts' => 1,
            'sfx' => 1,
            'music' => 2
        ];

        $costPerUnit = $creditCosts[$type] ?? 1;
        $totalCreditsNeeded = $amount * $costPerUnit;

        $userCredit = TknUserCredit::where('user_id', $userId)->lockForUpdate()->first();
        
        if (!$userCredit || $userCredit->remaining_credits < $totalCreditsNeeded) {
            return response()->json([
                'message' => 'Insufficient credits. Need ' . $totalCreditsNeeded . ' credits.'
            ], 402);
        }

        $userCredit->used_credits += $totalCreditsNeeded;
        $userCredit->save();

        return true;
    }

    private function getScriptStyles()
    {
        return [
            'noir' => 'Noir',
            'cinematic' => 'Cinematic',
            'anime' => 'Anime',
            'documentary' => 'Documentary',
            'comedy' => 'Comedy',
            'drama' => 'Drama',
            'action' => 'Action',
            'sci-fi' => 'Sci-Fi',
            'fantasy' => 'Fantasy',
            'horror' => 'Horror'
        ];
    }

    private function getMotionPresets()
    {
        return [
            ['value' => 1, 'label' => 'Very Subtle'],
            ['value' => 3, 'label' => 'Subtle'],
            ['value' => 5, 'label' => 'Medium'],
            ['value' => 7, 'label' => 'Dynamic'],
            ['value' => 10, 'label' => 'Very Dynamic']
        ];
    }

    private function getVoiceProfiles()
    {
        return [
            ['id' => 'male_deep', 'name' => 'Male - Deep', 'gender' => 'male', 'tone' => 'deep'],
            ['id' => 'male_warm', 'name' => 'Male - Warm', 'gender' => 'male', 'tone' => 'warm'],
            ['id' => 'female_soft', 'name' => 'Female - Soft', 'gender' => 'female', 'tone' => 'soft'],
            ['id' => 'female_warm', 'name' => 'Female - Warm', 'gender' => 'female', 'tone' => 'warm'],
            ['id' => 'female_energetic', 'name' => 'Female - Energetic', 'gender' => 'female', 'tone' => 'energetic'],
            ['id' => 'narrator_calm', 'name' => 'Narrator - Calm', 'gender' => 'neutral', 'tone' => 'calm'],
            ['id' => 'narrator_authoritative', 'name' => 'Narrator - Authoritative', 'gender' => 'neutral', 'tone' => 'authoritative']
        ];
    }

    private function getSoundModels()
    {
        return [
            'tts' => [
                'name' => 'Text to Speech',
                'models' => ['elevenlabs', 'openai-tts']
            ],
            'sfx' => [
                'name' => 'Sound Effects',
                'models' => ['audiocraft', 'riffusion']
            ],
            'music' => [
                'name' => 'Music Generation',
                'models' => ['musicgen', 'museformer']
            ]
        ];
    }

    /**
     * ============================================
     * DISPATCH METHODS (To be implemented with jobs)
     * ============================================
     */

    private function dispatchScriptGeneration($project, $validated)
    {
        try {
            $sceneCount = $validated['scene_count'];
            $model = $project->model;
            
            // Create all scene records first (pending)
            $scenes = [];
            for ($i = 1; $i <= $sceneCount; $i++) {
                $scene = ScriptScene::create([
                    'project_id' => $project->id,
                    'scene_number' => $i,
                    'status' => 1, // Processing
                    'track_id' => null
                ]);
                $scenes[] = $scene;
            }
            
            // Generate dynamic JSON structure for the exact number of scenes
            $scenePlaceholders = [];
            for ($i = 1; $i <= $sceneCount; $i++) {
                $scenePlaceholders[] = [
                    'scene' => $i,
                    'voiceover' => "narration here",
                    'image_prompt' => "visual here"
                ];
            }
            $scenesJson = json_encode($scenePlaceholders); // No pretty print

            // Replace placeholders
            $processedPrompt = str_replace(
                ['#SCENE_COUNT', '#PROMPT', '#STYLE', '#JSON_TEMPLATE'],
                [$sceneCount, $validated['prompt'], $validated['style'], $scenesJson],
                $model->prompt_template
            );
            
            // Single track ID for batch generation
            $trackId = 'script-' . $project->id . '-batch-' . uniqid();
            
            // Prepare generation data
            $genData = [
                'prompt' => $processedPrompt,
                'style' => $validated['style'],
                'scene_count' => $sceneCount,
                'hook_track_id' => $trackId,
                'project_id' => $project->id,
                // 'prompt_template' => $processedPrompt  // Use the fully processed prompt
            ];

            // Only update status after job is successfully queued
            $project->update(['status' => 1, 'payload' => json_encode($genData)]);
            
            // Queue single batch job
            GenerateScriptBatch::dispatch($model, $genData, $trackId, $project->id);

            Log::info('Script batch generation queued', [
                'project_id' => $project->id,
                'scene_count' => $sceneCount,
                'track_id' => $trackId
            ]);
            
        } catch (\Exception $e) {
            Log::error('DispatchScriptGeneration Error: ' . $e->getMessage(), [
                'project_id' => $project->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            $project->update(['status' => 3]); // Failed
        }
    }

    private function dispatchSceneRegeneration($scene)
    {
        Log::info('Scene regeneration queued', ['scene_id' => $scene->id]);
    }

    private function dispatchAllScenesRegeneration($project)
    {
        Log::info('All scenes regeneration queued', ['project_id' => $project->id]);
    }

    private function dispatchImageGeneration($frame, $modelId)
    {
        Log::info('Image generation queued', ['frame_id' => $frame->id, 'model_id' => $modelId]);
    }

    private function dispatchVideoGeneration($frame)
    {
        Log::info('Video generation queued', ['frame_id' => $frame->id]);
    }

    private function dispatchTTSGeneration($track)
    {
        Log::info('TTS generation queued', ['track_id' => $track->id]);
    }

    private function dispatchSFXGeneration($track)
    {
        Log::info('SFX generation queued', ['track_id' => $track->id]);
    }

    private function dispatchMusicGeneration($track)
    {
        Log::info('Music generation queued', ['track_id' => $track->id]);
    }

    private function dispatchMovieRendering($movie)
    {
        Log::info('Movie rendering queued', ['movie_id' => $movie->id]);
    }

    public function checkGenerationStatus(Request $request, string $trackId)
    {
        try {
            // Find which frame this track belongs to (could be image or video)
            $frame = ShootFrame::where('track_id', $trackId)
                ->orWhere('video_track_id', $trackId)
                ->with('project')
                ->first();

            if (!$frame) {
                return response()->json(['error' => 'Frame not found'], 404);
            }

            // Determine if this is image or video track
            $isVideo = ($frame->video_track_id === $trackId);
            $statusField = $isVideo ? 'status_video' : 'status_image';
            $pathField = $isVideo ? 'video_path' : 'image_path';

            // If already success, return immediately
            if ($frame->$statusField == 2 && $frame->$pathField) {
                return response()->json([
                    'type' => $isVideo ? 'video' : 'image',
                    'status' => 'success',
                    'url' => asset($frame->$pathField),
                    'frame_id' => $frame->id
                ], 200);
            }

            // If failed
            if ($frame->$statusField == 3) {
                return response()->json([
                    'type' => $isVideo ? 'video' : 'image',
                    'status' => 'failed',
                    'frame_id' => $frame->id
                ], 200);
            }

            // Check ml_hits for this track_id
            $hit = DB::table('ml_hits')
                ->where('track_id', $trackId)
                ->orderBy('id', 'desc')
                ->first();

            if (!$hit) {
                return response()->json([
                    'type' => $isVideo ? 'video' : 'image',
                    'status' => 'pending',
                    'frame_id' => $frame->id
                ], 200);
            }

            $data = json_decode($hit->json, true);

            // Case 1: Success with immediate output
            if (isset($data['status']) && $data['status'] === 'success' && isset($data['output'][0])) {
                $fileUrl = $data['output'][0];
                
                // Download and save locally
                $path = 'ai-projects/shoot/' . $frame->project->user_id;
                $localPath = AiGen::convertLinksToLocal($fileUrl, $path, $trackId);
                
                $updateData = $isVideo 
                    ? ['video_path' => $localPath, 'status_video' => 2]
                    : ['image_path' => $localPath, 'status_image' => 2];
                
                $frame->update($updateData);

                return response()->json([
                    'type' => $isVideo ? 'video' : 'image',
                    'status' => 'success',
                    'url' => asset($localPath),
                    'frame_id' => $frame->id
                ], 200);
            }

            // Case 2: Processing with fetch URL (both image and video have this)
            if (isset($data['status']) && $data['status'] === 'processing' && isset($data['fetch_result'])) {
                return response()->json([
                    'type' => $isVideo ? 'video' : 'image',
                    'status' => 'processing',
                    'fetch_url' => $data['fetch_result'],
                    'request_id' => $data['id'] ?? null,
                    'eta' => $data['eta'] ?? null,
                    'frame_id' => $frame->id
                ], 200);
            }

            // Case 3: Future links available (some APIs provide this)
            if (isset($data['future_links']) && !empty($data['future_links'])) {
                $fileUrl = $data['future_links'][0];
                
                $path = 'ai-projects/shoot/' . $frame->project->user_id;
                $localPath = AiGen::convertLinksToLocal($fileUrl, $path, $trackId);
                
                $updateData = $isVideo 
                    ? ['video_path' => $localPath, 'status_video' => 2]
                    : ['image_path' => $localPath, 'status_image' => 2];
                
                $frame->update($updateData);

                return response()->json([
                    'type' => $isVideo ? 'video' : 'image',
                    'status' => 'success',
                    'url' => asset($localPath),
                    'frame_id' => $frame->id
                ], 200);
            }

            // Default: still processing
            return response()->json([
                'type' => $isVideo ? 'video' : 'image',
                'status' => 'processing',
                'frame_id' => $frame->id
            ], 200);

        } catch (Exception $e) {
            Log::error('CheckGenerationStatus Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to check status'], 500);
        }
    }

    public function updatePendingGenerations()
    {
        try {
            $updated = 0;
            
            // 1. Check Shoot frames - image generation
            $processingImages = ShootFrame::with('project')
                ->where('status_image', 1)
                ->whereNotNull('track_id')
                ->get();
            
            foreach ($processingImages as $frame) {
                $status = $this->checkTrackStatus($frame->track_id, $frame, false);
                if ($status) $updated++;
            }
            
            // 2. Check Shoot frames - video generation
            $processingVideos = ShootFrame::with('project')
                ->where('status_video', 1)
                ->whereNotNull('video_track_id')
                ->get();
            
            foreach ($processingVideos as $frame) {
                $status = $this->checkTrackStatus($frame->video_track_id, $frame, true);
                if ($status) $updated++;
            }
            
            // 3. Check Script scenes - voiceover generation
            $processingVoiceovers = ScriptScene::with('project')
                ->where('voiceover_status', 1)
                ->whereNotNull('voiceover_track_id')
                ->get();
            
            foreach ($processingVoiceovers as $scene) {
                $status = $this->checkVoiceoverTrackStatus($scene->voiceover_track_id, $scene);
                if ($status) $updated++;
            }
            
            // 4. Check SFX tracks processing
            $processingSFX = SFXTrack::with('soundProject')
                ->where('status', 1)
                ->whereNotNull('track_id')
                ->get();
            
            foreach ($processingSFX as $sfx) {
                $status = $this->checkSFXTrackStatus($sfx->track_id, $sfx);
                if ($status) $updated++;
            }
            
            Log::info('Updated pending generations', ['updated' => $updated]);
            
            return response()->json([
                'success' => true,
                'updated' => $updated
            ], 200);
            
        } catch (Exception $e) {
            Log::error('UpdatePendingGenerations Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update'], 500);
        }
    }

    private function checkTrackStatus($trackId, $frame, $isVideo = false)
    {
        try {
            $hit = DB::table('ml_hits')
                ->where('track_id', $trackId)
                ->orderBy('id', 'desc')
                ->first();
            
            if (!$hit) return false;
            
            $data = json_decode($hit->json, true);
            
            // Check for success with output
            if (isset($data['status']) && $data['status'] === 'success' && isset($data['output'][0])) {
                $fileUrl = $data['output'][0];
                $path = 'ai-projects/shoot/' . $frame->project->user_id;
                $localPath = AiGen::convertLinksToLocal($fileUrl, $path, $trackId);
                
                if ($isVideo) {
                    $frame->update([
                        'video_path' => $localPath,
                        'status_video' => 2
                    ]);
                } else {
                    $frame->update([
                        'image_path' => $localPath,
                        'status_image' => 2
                    ]);
                }
                return true;
            }
            
            // Check for future_links (alternative success format)
            if (isset($data['future_links']) && !empty($data['future_links'])) {
                $fileUrl = $data['future_links'][0];
                $path = 'ai-projects/shoot/' . $frame->project->user_id;
                $localPath = AiGen::convertLinksToLocal($fileUrl, $path, $trackId);
                
                if ($isVideo) {
                    $frame->update([
                        'video_path' => $localPath,
                        'status_video' => 2
                    ]);
                } else {
                    $frame->update([
                        'image_path' => $localPath,
                        'status_image' => 2
                    ]);
                }
                return true;
            }
            
            // Check for failure
            if (isset($data['status']) && $data['status'] === 'error') {
                if ($isVideo) {
                    $frame->update(['status_video' => 3]);
                } else {
                    $frame->update(['status_image' => 3]);
                }
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            Log::error('CheckTrackStatus Error: ' . $e->getMessage());
            return false;
        }
    }

    private function checkVoiceoverTrackStatus($trackId, $scene)
    {
        try {
            $hit = DB::table('ml_hits')
                ->where('track_id', $trackId)
                ->orderBy('id', 'desc')
                ->first();
            
            if (!$hit) return false;
            
            $data = json_decode($hit->json, true);
            
            // Check for success with output
            if (isset($data['status']) && $data['status'] === 'success' && isset($data['output'][0])) {
                $audioUrl = $data['output'][0];
                $path = 'ai-projects/voiceover/' . $scene->project->user_id;
                $localPath = AiGen::convertLinksToLocal($audioUrl, $path, $trackId);
                
                $scene->update([
                    'voiceover_url' => $localPath,
                    'voiceover_status' => 2
                ]);
                return true;
            }
            
            // Check for future_links
            if (isset($data['future_links']) && !empty($data['future_links'])) {
                $audioUrl = $data['future_links'][0];
                $path = 'ai-projects/voiceover/' . $scene->project->user_id;
                $localPath = AiGen::convertLinksToLocal($audioUrl, $path, $trackId);
                
                $scene->update([
                    'voiceover_url' => $localPath,
                    'voiceover_status' => 2
                ]);
                return true;
            }
            
            // Check for failure
            if (isset($data['status']) && $data['status'] === 'error') {
                $scene->update(['voiceover_status' => 3]);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            Log::error('CheckVoiceoverTrackStatus Error: ' . $e->getMessage());
            return false;
        }
    }

    private function checkSFXTrackStatus($trackId, $sfx)
    {
        try {
            $hit = DB::table('ml_hits')
                ->where('track_id', $trackId)
                ->orderBy('id', 'desc')
                ->first();
            
            if (!$hit) return false;
            
            $data = json_decode($hit->json, true);
            
            // Check for success with output
            if (isset($data['status']) && $data['status'] === 'success' && isset($data['output'][0])) {
                $audioUrl = $data['output'][0];
                $path = 'ai-projects/sfx/' . $sfx->soundProject->user_id;
                $localPath = AiGen::convertLinksToLocal($audioUrl, $path, $trackId);
                
                $sfx->update([
                    'url' => $localPath,
                    'status' => 2
                ]);
                return true;
            }
            
            // Check for future_links
            if (isset($data['future_links']) && !empty($data['future_links'])) {
                $audioUrl = $data['future_links'][0];
                $path = 'ai-projects/sfx/' . $sfx->soundProject->user_id;
                $localPath = AiGen::convertLinksToLocal($audioUrl, $path, $trackId);
                
                $sfx->update([
                    'url' => $localPath,
                    'status' => 2
                ]);
                return true;
            }
            
            // Check for failure
            if (isset($data['status']) && $data['status'] === 'error') {
                $sfx->update(['status' => 3]);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            Log::error('CheckSFXTrackStatus Error: ' . $e->getMessage());
            return false;
        }
    }



    // ML API WEBHOOK

    /**
     * Webhook endpoint for API responses
     * Handles image, video, voiceover, and SFX generation callbacks
     */
    public function webhook(Request $request)
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
                
                Log::info('Webhook received', [
                    'track_id' => $trackId,
                    'status' => $request->status,
                    'has_output' => isset($request->output)
                ]);
                
                // Extract seed and full payload from meta
                $seed = null;
                $payload = null;
                if ($request->has('meta')) {
                    $meta = $request->meta;
                    $seed = $meta['seed'] ?? null;
                    $payload = $meta; // Save full meta as payload
                }
                
                // Also include the full request data
                $fullPayload = $request->all();
                
                // Process based on track_id prefix
                if (str_starts_with($trackId, 'prostudio-') || str_starts_with($trackId, 'provoice-') || 
                    str_starts_with($trackId, 'promusic-') || str_starts_with($trackId, 'prosfx-')) {
                    $this->processProStudioWebhook($trackId, $request->output[0] ?? null);
                } 
                // Shoot director - image generation
                elseif (str_starts_with($trackId, 'shoot-')) {
                    $this->processShootImageWebhook($trackId, $request->status, $request->output[0] ?? null, $seed, $fullPayload);
                }
                // Shoot director - video generation
                elseif (str_starts_with($trackId, 'video-')) {
                    $this->processShootVideoWebhook($trackId, $request->status, $request->output[0] ?? null);
                }
                // Sound director - voiceover generation
                elseif (str_starts_with($trackId, '775')) {
                    $this->processVoiceoverWebhook($trackId, $request->status, $request->output[0] ?? null);
                }
                // Sound director - SFX generation
                elseif (str_starts_with($trackId, 'sfx-')) {
                    $this->processSFXWebhook($trackId, $request->status, $request->output[0] ?? null);
                }
                // Script director - batch generation
                elseif (str_starts_with($trackId, 'script-')) {
                    $this->processScriptWebhook($trackId, $request->status, $request->output[0] ?? null);
                }
                // Default processing
                else {
                    $this->processWebhookStatus($request->status, $trackId, $request->output[0] ?? null);
                }
            } else {
                $this->processPendingProjects();
            }
            
            return response()->json(['message' => 'Success'], 200);
            
        } catch (Exception $e) {
            Log::error("[$trackId] Webhook Error: " . $e->getMessage());
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    /**
     * Process shoot director image webhook
     */
    private function processShootImageWebhook($trackId, $status, $outputUrl, $seed = null)
    {
        try {
            $frame = ShootFrame::where('track_id', $trackId)->first();
            
            if (!$frame) {
                Log::warning('Shoot frame not found for webhook', ['track_id' => $trackId]);
                return;
            }
            
            // If already success, skip processing
            if ($frame->status_image == 2 && $frame->image_path) {
                Log::info('Frame already has image, skipping webhook', [
                    'frame_id' => $frame->id,
                    'track_id' => $trackId
                ]);
                return;
            }
            
            if ($status == 'success' && $outputUrl) {
                // DO NOT save webhook payload here - only download the file
                
                $path = 'ai-projects/shoot/' . $frame->project->user_id;
                $localPath = null;
                $maxRetries = 5;
                $retryDelay = 2;
                
                for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                    try {
                        $localPath = AiGen::convertLinksToLocal($outputUrl, $path, $trackId);
                        
                        if ($localPath) {
                            Log::info('File downloaded successfully', [
                                'track_id' => $trackId,
                                'attempt' => $attempt,
                                'local_path' => $localPath
                            ]);
                            break;
                        }
                    } catch (Exception $e) {
                        Log::warning("Download attempt $attempt failed", [
                            'track_id' => $trackId,
                            'error' => $e->getMessage()
                        ]);
                        
                        if ($attempt < $maxRetries) {
                            sleep($retryDelay);
                        }
                    }
                }
                
                if (!$localPath) {
                    Log::error('Failed to download file after multiple attempts', [
                        'track_id' => $trackId,
                        'url' => $outputUrl
                    ]);
                    $frame->update(['status_image' => 3]);
                    return;
                }
                
                $updateData = [
                    'image_path' => $localPath,
                    'status_image' => 2
                ];
                
                // Set project seed on the first successful generation
                if ($seed !== null && !$frame->project->seed) {
                    $frame->project->update(['seed' => $seed]);
                    Log::info('Project seed set from first image', [
                        'project_id' => $frame->project_id,
                        'seed' => $seed
                    ]);
                }
                
                $frame->update($updateData);
                
                Log::info('Shoot image webhook processed', [
                    'frame_id' => $frame->id,
                    'track_id' => $trackId,
                    'local_path' => $localPath
                ]);
                
                $this->checkShootProjectImageCompletion($frame->project_id);
                
            } elseif ($status == 'error' || $status == 'failed') {
                $frame->update(['status_image' => 3]);
                Log::warning('Shoot image generation failed', ['track_id' => $trackId]);
            }
            
        } catch (Exception $e) {
            Log::error('ProcessShootImageWebhook Error: ' . $e->getMessage(), [
                'track_id' => $trackId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Process shoot director video webhook
     */
    private function processShootVideoWebhook($trackId, $status, $outputUrl)
    {
        try {
            $frame = ShootFrame::where('video_track_id', $trackId)->first();
            
            if (!$frame) {
                Log::warning('Shoot frame not found for video webhook', ['track_id' => $trackId]);
                return;
            }
            
            if ($status == 'success' && $outputUrl) {
                $path = 'ai-projects/shoot/' . $frame->project->user_id;
                $localPath = AiGen::convertLinksToLocal($outputUrl, $path, $trackId);
                
                $frame->update([
                    'video_path' => $localPath,
                    'status_video' => 2
                ]);
                
                Log::info('Shoot video webhook processed', [
                    'frame_id' => $frame->id,
                    'track_id' => $trackId,
                    'local_path' => $localPath
                ]);
                
                // Check if all videos are complete
                $this->checkShootProjectVideoCompletion($frame->project_id);
            } elseif ($status == 'error' || $status == 'failed') {
                $frame->update(['status_video' => 3]);
                Log::warning('Shoot video generation failed', ['track_id' => $trackId]);
            }
            
        } catch (Exception $e) {
            Log::error('ProcessShootVideoWebhook Error: ' . $e->getMessage(), [
                'track_id' => $trackId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Process voiceover webhook
     */
    private function processVoiceoverWebhook($trackId, $status, $outputUrl)
    {
        try {
            $scene = ScriptScene::where('voiceover_track_id', $trackId)->first();
            
            if (!$scene) {
                Log::warning('Scene not found for voiceover webhook', ['track_id' => $trackId]);
                return;
            }
            
            if ($status == 'success' && $outputUrl) {
                $path = 'ai-projects/voiceover/' . $scene->project->user_id;
                $localPath = AiGen::convertLinksToLocal($outputUrl, $path, $trackId);
                
                // Verify file exists
                if (file_exists(public_path($localPath))) {
                    $scene->update([
                        'voiceover_url' => $localPath,
                        'voiceover_status' => 2
                    ]);
                    
                    ScriptScene::where('id', $scene->id)
                        ->update([
                            'voiceover_url' => $localPath,
                            'voiceover_status' => 2
                        ]);
                    
                    $scene->refresh();
                    
                    Log::info('Voiceover webhook processed', [
                        'scene_id' => $scene->id,
                        'track_id' => $trackId,
                        'local_path' => $localPath,
                        'new_status' => $scene->voiceover_status,
                        'new_url' => $scene->voiceover_url
                    ]);
                }
            } elseif ($status == 'error' || $status == 'failed') {
                $scene->update(['voiceover_status' => 3]);
                Log::warning('Voiceover generation failed', ['track_id' => $trackId]);
            }
            
        } catch (Exception $e) {
            Log::error('ProcessVoiceoverWebhook Error: ' . $e->getMessage(), [
                'track_id' => $trackId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Process SFX webhook
     */
    private function processSFXWebhook($trackId, $status, $outputUrl)
    {
        try {
            $sfxTrack = SFXTrack::where('track_id', $trackId)->first();
            
            if (!$sfxTrack) {
                Log::warning('SFX track not found for webhook', ['track_id' => $trackId]);
                return;
            }
            
            if ($status == 'success' && $outputUrl) {
                $path = 'ai-projects/sfx/' . $sfxTrack->soundProject->user_id;
                $localPath = AiGen::convertLinksToLocal($outputUrl, $path, $trackId);
                
                // Verify file exists
                if (file_exists(public_path($localPath))) {
                    // Use DB facade directly to bypass transaction
                    DB::table('ai_director_sfx_tracks')
                        ->where('id', $sfxTrack->id)
                        ->update([
                            'url' => $localPath,
                            'status' => 2
                        ]);
                    
                    $sfxTrack->refresh();
                    
                    Log::info('SFX webhook processed', [
                        'sfx_id' => $sfxTrack->id,
                        'track_id' => $trackId,
                        'local_path' => $localPath,
                        'new_status' => $sfxTrack->status,
                        'new_url' => $sfxTrack->url
                    ]);
                } else {
                    Log::error('SFX file not found after conversion', [
                        'track_id' => $trackId,
                        'local_path' => $localPath
                    ]);
                }
            } elseif ($status == 'error' || $status == 'failed') {
                SFXTrack::where('id', $sfxTrack->id)->update(['status' => 3]);
                Log::warning('SFX generation failed', ['track_id' => $trackId]);
            }
            
        } catch (Exception $e) {
            Log::error('ProcessSFXWebhook Error: ' . $e->getMessage(), [
                'track_id' => $trackId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Process script batch webhook
     */
    private function processScriptWebhook($trackId, $status, $output)
    {
        try {
            // Extract project ID from track_id
            preg_match('/script-(\d+)-batch/', $trackId, $matches);
            if (count($matches) < 2) {
                Log::warning('Could not extract project ID from script track', ['track_id' => $trackId]);
                return;
            }
            
            $projectId = $matches[1];
            
            if ($status == 'success' && $output) {
                // Parse the JSON output and update scenes
                $this->processScriptBatchOutput($projectId, $output);
            } elseif ($status == 'error' || $status == 'failed') {
                ScriptProject::where('id', $projectId)->update(['status' => 3]);
                Log::warning('Script generation failed', ['project_id' => $projectId]);
            }
            
        } catch (Exception $e) {
            Log::error('ProcessScriptWebhook Error: ' . $e->getMessage(), [
                'track_id' => $trackId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Process script batch output and update scenes
     */
    private function processScriptBatchOutput($projectId, $output)
    {
        try {
            $text = is_string($output) ? trim($output) : '';
            
            if (empty($text)) {
                throw new Exception('Empty output received');
            }
            
            // Extract JSON from response
            $jsonData = json_decode($text, true);
            
            if (!$jsonData && preg_match('/\{[\s\S]*\}/', $text, $matches)) {
                $jsonData = json_decode($matches[0], true);
            }
            
            if (!$jsonData || !isset($jsonData['scenes'])) {
                throw new Exception('Could not extract valid JSON from response');
            }
            
            DB::beginTransaction();
            
            $updatedCount = 0;
            
            foreach ($jsonData['scenes'] as $sceneData) {
                if (!isset($sceneData['scene'], $sceneData['voiceover'], $sceneData['image_prompt'])) {
                    continue;
                }
                
                $sceneNumber = (int)$sceneData['scene'];
                
                $updated = ScriptScene::where('project_id', $projectId)
                    ->where('scene_number', $sceneNumber)
                    ->update([
                        'voiceover_script' => trim($sceneData['voiceover']),
                        'visual_prompt' => trim($sceneData['image_prompt']),
                        'status' => 2
                    ]);
                
                if ($updated) {
                    $updatedCount++;
                }
            }
            
            $totalScenes = ScriptScene::where('project_id', $projectId)->count();
            
            if ($updatedCount >= $totalScenes && $totalScenes > 0) {
                ScriptProject::where('id', $projectId)->update(['status' => 2]);
                $this->autoCreateShootProject($projectId);
                Log::info('Script project completed via webhook', ['project_id' => $projectId]);
            }
            
            DB::commit();
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ProcessScriptBatchOutput Error: ' . $e->getMessage(), [
                'project_id' => $projectId
            ]);
        }
    }

    /**
     * Auto-create shoot project after script completion
     */
    private function autoCreateShootProject($projectId)
    {
        $project = ScriptProject::with('scenes')->find($projectId);
        if (!$project) return;
        
        try {
            $userId = $project->user_id;
            
            $existingProject = ShootProject::where('source_id', $project->id)
                ->where('source_type', 'script_project')
                ->first();
            
            if ($existingProject) {
                return;
            }
            
            $imageModel = AiCustomGenerator::where('slug', 'storyboard-artist')->first();
            $videoModel = AiCustomGenerator::where('slug', 'cinematographer')->first();
            
            DB::beginTransaction();
            
            $shootProject = ShootProject::create([
                'user_id' => $userId,
                'title' => $project->title . ' (Storyboard)',
                'source_id' => $project->id,
                'source_type' => 'script_project',
                'image_model_id' => $imageModel ? $imageModel->id : null,
                'video_model_id' => $videoModel ? $videoModel->id : null,
                'scene_count' => $project->scene_count,
                'status' => 0,
                'payload' => json_encode(['auto_created' => true])
            ]);
            
            foreach ($project->scenes->sortBy('scene_number') as $scene) {
                ShootFrame::create([
                    'project_id' => $shootProject->id,
                    'scene_number' => $scene->scene_number,
                    'frame_number' => 1,
                    'prompt' => $scene->visual_prompt,
                    'status_image' => 0,
                    'status_video' => 0
                ]);
            }
            
            DB::commit();
            
            Log::info('Auto-created shoot project', [
                'script_project_id' => $project->id,
                'shoot_project_id' => $shootProject->id
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('AutoCreateShootProject Error: ' . $e->getMessage(), [
                'script_project_id' => $project->id
            ]);
        }
    }
}