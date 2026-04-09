<?php

namespace App\Http\Controllers;

use App\Models\StandUpComedian;
use App\Models\StandUpComedianTemplate;
use App\Models\StandUpFaceSwapJob;
use App\Models\StandUpScript;
use App\Models\StandUpVideo;
use App\Helpers\AiGen;
use App\Models\AiModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiStandUpController extends Controller
{
    // ========== VIEWS ==========
    
    public function index()
    {
        return view('standup.index');
    }
    
    public function templates()
    {
        $templates = StandUpComedianTemplate::where('active', true)->get();
        $customComedians = StandUpComedian::where('user_id', Auth::id())
            ->where('type', 'custom')
            ->where('status', 'ready')
            ->get();
        $systemComedians = StandUpComedian::where('type', 'system')
            ->where('status', 'ready')
            ->get();

        return view('standup.templates', compact('templates', 'customComedians', 'systemComedians'));
    }
    
    public function scriptPage()
    {
        $comedians = StandUpComedian::where('user_id', Auth::id())
            ->where('status', 'ready')
            ->get();
        
        return view('standup.script-page', compact('comedians'));
    }
    
    public function videoGenerator(Request $request)
    {
        $comedianId = $request->comedian_id;
        $scriptId = $request->script_id;
        
        $comedian = StandUpComedian::where('user_id', Auth::id())->find($comedianId);
        $script = StandUpScript::where('user_id', Auth::id())->find($scriptId);
        
        if (!$comedian || !$script) {
            return redirect()->route('standup.script.page')->with('error', 'Please select a comedian and script first');
        }
        
        return view('standup.video-generator', compact('comedianId', 'scriptId', 'comedian', 'script'));
    }
    
    public function myVideos()
    {
        $videos = StandUpVideo::where('user_id', Auth::id())
            ->with(['comedian', 'script'])
            ->orderBy('id', 'desc')
            ->get();
        
        return view('standup.my-videos', compact('videos'));
    }
    
    // ========== COMEDIAN CREATION ==========
    
    public function createComedian(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:stand_up_comedian_templates,id',
            'target_image' => 'required|image|max:10240'
        ]);

        $template = StandUpComedianTemplate::findOrFail($request->template_id);
        $targetImagePath = $request->file('target_image')->store('temp/faces', 'public');

        $job = StandUpFaceSwapJob::create([
            'user_id' => Auth::id(),
            'template_id' => $template->id,
            'target_image' => $targetImagePath,
            'status' => 'pending',
            'track_id' => 'faceswap-' . Str::random(16) . time()
        ]);

        $this->dispatchFaceSwap($job, $template);

        return response()->json([
            'message' => 'Comedian creation started',
            'job_id' => $job->id,
            'track_id' => $job->track_id
        ]);
    }
    
    public function selectComedian(Request $request)
    {
        session(['selected_comedian_id' => $request->comedian_id]);
        return response()->json(['success' => true]);
    }
    
    public function comedianStatus($trackId)
    {
        $job = StandUpFaceSwapJob::where('track_id', $trackId)->first();
        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }

        $comedian = null;
        if ($job->status === 'completed') {
            $comedian = StandUpComedian::where('user_id', Auth::id())
                ->where('template_id', $job->template_id)
                ->latest()
                ->first();
        }

        return response()->json([
            'status' => $job->status,
            'comedian_id' => $comedian?->id
        ]);
    }
    
    private function dispatchFaceSwap(StandUpFaceSwapJob $job, StandUpComedianTemplate $template): void
    {
        try {
            $aiModel = AiModel::where('name', 'face-swap')->first();
            $targetUrl = asset('storage/' . $job->target_image);
            $initImageUrl = asset($template->init_image);

            $payload = [
                'init_image' => $initImageUrl,
                'target_image' => $targetUrl,
                'reference_image' => $initImageUrl,
                'track_id' => $job->track_id,
                'webhook' => route('standup.face-swap-webhook')
            ];

            $response = AiGen::generate($aiModel, $payload, $job->track_id, null);
            $responseData = $response->getData(true);

            if (isset($responseData['status']) && $responseData['status'] === 'queue') {
                $job->update(['status' => 'processing']);
            } else {
                $job->update(['status' => 'failed', 'api_response' => json_encode($responseData)]);
            }
        } catch (\Exception $e) {
            Log::error('Face swap dispatch failed: ' . $e->getMessage());
            $job->update(['status' => 'failed', 'api_response' => $e->getMessage()]);
        }
    }
    
    // ========== SCRIPT GENERATION ==========
    
    public function generateScript(Request $request)
    {
        $request->validate([
            'comedian_id' => 'required|exists:stand_up_comedians,id',
            'topic' => 'required|string|max:500',
            'tone' => 'nullable|string',
            'duration' => 'nullable|integer|min:1|max:10'
        ]);

        $comedian = StandUpComedian::findOrFail($request->comedian_id);
        
        $script = StandUpScript::create([
            'user_id' => Auth::id(),
            'comedian_id' => $comedian->id,
            'input_text' => $request->topic,
            'tone' => $request->tone,
            'duration' => $request->duration,
            'status' => 'pending'
        ]);

        $this->dispatchScriptGeneration($script, $comedian);

        return response()->json([
            'message' => 'Script generation started',
            'script_id' => $script->id
        ]);
    }
    
    public function getScript($id)
    {
        $script = StandUpScript::with('comedian')->where('user_id', Auth::id())->findOrFail($id);
        return response()->json($script);
    }
    
    public function updateScript(Request $request, $id)
    {
        $script = StandUpScript::where('user_id', Auth::id())->findOrFail($id);
        $script->update(['generated_script' => $request->script_content]);
        return response()->json(['message' => 'Script updated']);
    }
    
    public function regenerateScript($id)
    {
        $script = StandUpScript::where('user_id', Auth::id())->findOrFail($id);
        $script->update(['status' => 'pending', 'generated_script' => null]);
        
        $comedian = StandUpComedian::find($script->comedian_id);
        $this->dispatchScriptGeneration($script, $comedian);
        
        return response()->json(['message' => 'Regeneration started']);
    }
    
    private function dispatchScriptGeneration(StandUpScript $script, StandUpComedian $comedian): void
    {
        try {
            $aiModel = AiModel::where('name', 'text-gen')->first();
            $prompt = $this->buildScriptPrompt($script, $comedian);
            
            $payload = [
                'prompt' => $prompt,
                'max_tokens' => 2000,
                'temperature' => 0.8,
                'track_id' => 'standup-script-' . $script->id . '-' . time(),
                'webhook' => route('standup.script-webhook')
            ];

            $response = AiGen::generate($aiModel, $payload, $payload['track_id'], null);
            $responseData = $response->getData(true);

            if (isset($responseData['status']) && $responseData['status'] === 'queue') {
                $script->update(['status' => 'processing']);
            } else {
                $script->update(['status' => 'failed']);
            }
        } catch (\Exception $e) {
            Log::error('Script generation dispatch failed: ' . $e->getMessage());
            $script->update(['status' => 'failed']);
        }
    }
    
    private function buildScriptPrompt(StandUpScript $script, StandUpComedian $comedian): string
    {
        $template = StandUpComedianTemplate::find($comedian->template_id);
        $personality = $template->personality ?? 'observational, witty, and relatable';
        
        $durationMinutes = $script->duration ?? 3;
        $targetLength = $durationMinutes * 150;
        $tone = $script->tone ?? 'comedic, engaging';
        
        return "Write a stand-up comedy script about: {$script->input_text}
            
    Comedian personality: {$personality}
    Tone: {$tone}
    Target length: {$targetLength} words (~{$durationMinutes} minutes)

    Format the script with:
    - [OPENING HOOK] - Strong opening line
    - [SETUP] - Context building
    - [PUNCHLINES] - Jokes with beats
    - [DELIVERY CUES] - Timing, pauses, emphasis
    - [CLOSING] - Strong ending

    Write in first person as the comedian speaking directly to the audience.";
    }
    
    // ========== VIDEO GENERATION ==========
    
    public function generateStandUpVideo(Request $request)
    {
        $request->validate([
            'comedian_id' => 'required|exists:stand_up_comedians,id',
            'script_id' => 'required|exists:stand_up_scripts,id'
        ]);

        $comedian = StandUpComedian::findOrFail($request->comedian_id);
        $script = StandUpScript::where('user_id', Auth::id())->findOrFail($request->script_id);

        $standUpVideo = StandUpVideo::create([
            'user_id' => Auth::id(),
            'comedian_id' => $comedian->id,
            'script_id' => $script->id,
            'status' => 'pending'
        ]);

        $this->dispatchVideoGeneration($standUpVideo, $comedian, $script);

        return response()->json([
            'message' => 'Video generation started',
            'video_id' => $standUpVideo->id
        ]);
    }
    
    private function dispatchVideoGeneration(StandUpVideo $standUpVideo, StandUpComedian $comedian, StandUpScript $script): void
    {
        try {
            $aiModel = AiModel::where('name', 'img-vid')->first();
            $trackId = 'standup-video-' . $standUpVideo->id . '-' . time();

            $payload = [
                'init_image' => asset($comedian->final_image),
                'prompt' => $this->extractPromptFromScript($script->generated_script),
                'duration' => $script->duration ?? 8,
                'generate_audio' => true,
                'fps' => 25,
                'model_id' => 'ltx-2-3-pro-i2v',
                'track_id' => $trackId,
                'webhook' => route('standup.video-webhook')
            ];

            $response = AiGen::generate($aiModel, $payload, $trackId, null);
            $responseData = $response->getData(true);

            if (isset($responseData['status']) && $responseData['status'] === 'queue') {
                $standUpVideo->update(['status' => 'processing', 'api_response' => json_encode($responseData)]);
            } else {
                $standUpVideo->update(['status' => 'failed', 'api_response' => json_encode($responseData)]);
            }
        } catch (\Exception $e) {
            Log::error('Video generation dispatch failed: ' . $e->getMessage());
            $standUpVideo->update(['status' => 'failed', 'api_response' => $e->getMessage()]);
        }
    }
    
    private function extractPromptFromScript(string $script): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $script);
        $relevantLines = array_slice($lines, 0, 50);
        $cleanText = preg_replace('/\[.*?\]/', '', implode(' ', $relevantLines));
        return substr(trim($cleanText), 0, 500);
    }
    
    // ========== WEBHOOKS ==========
    
    public function faceSwapWebhook(Request $request)
    {
        $trackId = $request->track_id;
        $outputUrl = $request->output[0] ?? null;

        $job = StandUpFaceSwapJob::where('track_id', $trackId)->first();
        if (!$job) {
            Log::warning('Face swap webhook: job not found', ['track_id' => $trackId]);
            return response()->json(['message' => 'Job not found'], 404);
        }

        if ($request->status === 'success' && $outputUrl) {
            $localPath = AiGen::convertLinksToLocal($outputUrl, 'ai-projects/standup-comedians/' . $job->user_id, $trackId);
            
            StandUpComedian::create([
                'user_id' => $job->user_id,
                'template_id' => $job->template_id,
                'final_image' => $localPath,
                'type' => 'custom',
                'status' => 'ready',
                'name' => 'Custom Comedian'
            ]);

            $job->update(['status' => 'completed', 'output_image' => $localPath]);
        } elseif ($request->status === 'error') {
            $job->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'Webhook processed']);
    }
    
    public function scriptWebhook(Request $request)
    {
        $trackId = $request->track_id;
        $scriptId = str_replace('standup-script-', '', explode('-', $trackId)[1] ?? null);
        
        $script = StandUpScript::find($scriptId);
        if (!$script) {
            Log::warning('Script webhook: script not found', ['track_id' => $trackId]);
            return response()->json(['message' => 'Script not found'], 404);
        }

        if ($request->status === 'success' && isset($request->text)) {
            $script->update([
                'generated_script' => $request->text,
                'status' => 'completed'
            ]);
        } elseif ($request->status === 'error') {
            $script->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'Webhook processed']);
    }
    
    public function videoWebhook(Request $request)
    {
        $trackId = $request->track_id;
        $videoId = str_replace('standup-video-', '', explode('-', $trackId)[1] ?? null);
        
        $standUpVideo = StandUpVideo::find($videoId);
        if (!$standUpVideo) {
            Log::warning('Video webhook: video not found', ['track_id' => $trackId]);
            return response()->json(['message' => 'Video not found'], 404);
        }

        if ($request->status === 'success' && isset($request->output)) {
            $outputUrl = is_array($request->output) ? $request->output[0] : $request->output;
            $localPath = AiGen::convertLinksToLocal($outputUrl, 'ai-projects/standup-videos/' . $standUpVideo->user_id, $trackId);
            
            $standUpVideo->update([
                'video_url' => $localPath,
                'status' => 'completed'
            ]);
        } elseif ($request->status === 'error') {
            $standUpVideo->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'Webhook processed']);
    }
}