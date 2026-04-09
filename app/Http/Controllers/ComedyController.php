<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ComedyCategory;
use App\Models\ComedyTemplate;
use App\Models\ComedyVideo;
use App\Models\ComedyJoke;
use App\Models\AiModel;
use App\Models\AiCustomGenerator;
use App\Models\UserVideoCredit;
use App\Helpers\AiGen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ComedyController extends Controller
{
    public function index()
    {
        $categories = ComedyCategory::where('is_active', true)
            ->with(['templates' => function($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('display_order')
            ->get();

        $recentVideos = ComedyVideo::where('user_id', Auth::id())
            ->with('template')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return view('comedy.index', compact('categories', 'recentVideos'));
    }

    public function jokes()
    {
        $jokes = ComedyJoke::where('user_id', Auth::id())
            ->with(['template.category'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('comedy.jokes', compact('jokes'));
    }

    public function myVideos()
    {
        $videos = ComedyVideo::where('user_id', Auth::id())
            ->with('template')
            ->orderBy('created_at', 'desc')
            ->paginate(24);
        
        return view('comedy.my-videos', compact('videos'));
    }

    public function getJokes(Request $request)
    {
        $templateId = $request->template_id;
        
        $jokes = ComedyJoke::where('user_id', Auth::id())
            ->when($templateId, function($query) use ($templateId) {
                $query->where('template_id', $templateId);
            })
            ->orderBy('created_at', 'desc')
            ->get(['id', 'generated_joke', 'prompt']);
        
        return response()->json([
            'success' => true,
            'jokes' => $jokes
        ]);
    }

    public function deleteJoke($id)
    {
        $joke = ComedyJoke::where('user_id', Auth::id())->findOrFail($id);
        $joke->delete();
        
        return response()->json(['success' => true]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:500',
            'template_id' => 'nullable|exists:comedy_templates,id'
        ]);

        $userId = Auth::id();
        $userPrompt = $request->prompt;
        $templateId = $request->template_id;

        try {
            $systemPrompt = "You are a professional stand-up comedian. Generate a short, funny joke.";
            
            if ($templateId) {
                $template = ComedyTemplate::find($templateId);
                Log::info("Template found", [
                    'id' => $template->id,
                    'name' => $template->name,
                    'json_encode' => json_encode($template->joke_template),
                    'joke_template_type' => gettype($template->joke_template),
                    'joke_template_value' => $template->joke_template
                ]);
                
                if ($template) {
                    Log::info("Raw joke_template", ['template_id' => $templateId, 'raw' => $template->joke_template]);
                    
                    $jokeTemplate = is_string($template->joke_template) ? json_decode($template->joke_template, true) : $template->joke_template;
                    
                    Log::info("Decoded joke_template", ['decoded' => $jokeTemplate]);
                    
                    if ($jokeTemplate && !empty($jokeTemplate['joke'])) {
                        $jokeTemplateText = $jokeTemplate['joke'];
                        $systemPrompt = str_replace('{input}', $userPrompt, $jokeTemplateText);
                        Log::info("Template applied", ['systemPrompt' => $systemPrompt]);
                    } else {
                        Log::warning("Template structure invalid", ['jokeTemplate' => $jokeTemplate]);
                        $systemPrompt = "Generate a stand-up comedy joke about: " . $userPrompt;
                    }
                }
            } else {
                $systemPrompt = "Generate a stand-up comedy joke about: " . $userPrompt;
            }

            $generator = AiCustomGenerator::where('slug', 'joke-generator')->with('aiModel')->firstOrFail();
            $aiModel = $generator->aiModel;
            $hookTrackId = 'joke_u' . $userId . '_' . date('YmdHis');

            Log::info("Joke Generator: Dispatching to AI", [
                'user_id' => $userId,
                'track_id' => $hookTrackId,
                'model' => $aiModel->name,
                'final_prompt' => $systemPrompt
            ]);

            $response = AiGen::generate($aiModel, [
                'prompt' => $systemPrompt,
                'max_tokens' => 5000,
                'temperature' => 0.9,
                'hook_track_id' => $hookTrackId
            ], $hookTrackId, $generator, null);

            $responseData = $response->getData();

            if (isset($responseData->status) && $responseData->status === 'success' && isset($responseData->text)) {
                $joke = $responseData->text;

                $savedJoke = ComedyJoke::create([
                    'user_id' => $userId,
                    'template_id' => $templateId,
                    'prompt' => $userPrompt,
                    'generated_joke' => $joke,
                    'status' => 'active'
                ]);

                Log::info("Joke Generator: Success", [
                    'user_id' => $userId,
                    'joke_id' => $savedJoke->id,
                    'track_id' => $hookTrackId
                ]);

                $joke = preg_replace([
                    '/\*\*Setup:\*\*/i', 
                    '/\*\*Punchline:\*\*/i', 
                    '/Setup:/i', 
                    '/Punchline:/i', 
                    '/\\*/'
                ], '', $joke);
                
                $joke = trim($joke);

                return response()->json([
                    'success' => true,
                    'joke' => $joke,
                    'joke_id' => $savedJoke->id
                ]);
            }

            Log::error("Joke Generator: API returned non-success", [
                'user_id' => $userId,
                'track_id' => $hookTrackId,
                'api_response' => $responseData
            ]);

            return response()->json([
                'success' => false,
                'message' => $responseData->message ?? 'Failed to generate joke'
            ]);

        } catch (\Exception $e) {
            Log::critical("Joke Generator: Execution Exception", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something unexpected occurred. Please try again in a minute.'
            ]);
        }
    }

/**
 * Set temporary block for user video generation
 */
private function setTempBlock($userId, $minutes = 10)
{
    $cacheKey = 'video_gen_block_' . $userId;
    $seconds = $minutes * 60;
    Cache::put($cacheKey, $seconds, $seconds);
    
    Log::info("Temp block set for user", [
        'user_id' => $userId,
        'minutes' => $minutes,
        'expires_in_seconds' => $seconds
    ]);
}

/**
 * Check if user has active temp block
 */
private function hasTempBlock($userId)
{
    $cacheKey = 'video_gen_block_' . $userId;
    return Cache::has($cacheKey);
}

/**
 * Get remaining block time in minutes
 */
private function getRemainingBlockMinutes($userId)
{
    $cacheKey = 'video_gen_block_' . $userId;
    $seconds = Cache::get($cacheKey);
    return $seconds ? ceil($seconds / 60) : 0;
}

/**
 * Clear temp block for user
 */
private function clearTempBlock($userId)
{
    $cacheKey = 'video_gen_block_' . $userId;
    Cache::forget($cacheKey);
    
    Log::info("Temp block cleared for user", ['user_id' => $userId]);
}

/**
 * Generate video from template and joke
 */
public function generateVideo(Request $request)
{
    $request->validate([
        'template_id' => 'required|exists:comedy_templates,id',
        'joke' => 'required|string',
        'joke_id' => 'nullable|exists:comedy_jokes,id'
    ]);

    $userId = Auth::id();
    $template = ComedyTemplate::findOrFail($request->template_id);
    $joke = $request->joke;
    
    // Check if user has Enterprise plan (15,000+ paid credits)
    $userCredits = UserVideoCredit::where('user_id', $userId)->first();
    $hasEnterprisePlan = $userCredits && $userCredits->paid_credits >= 15000;
    
    // Check temp block using cache (only if not Enterprise plan)
    if (!$hasEnterprisePlan && $this->hasTempBlock($userId)) {
        $minutesRemaining = $this->getRemainingBlockMinutes($userId);
        
        return response()->json([
            'success' => false,
            'message' => "{$minutesRemaining} minute(s) before generating another video.",
            'temp_block' => true,
            'wait_minutes' => $minutesRemaining
        ], 429);
    }
    
    // Determine credit cost based on category
    $categoryId = $template->category_id;
    $freeCreditCost = 500;
    $paidCreditCost = 200;
    
    Log::info("Generate Video: Request received", [
        'user_id' => $userId,
        'template_id' => $template->id,
        'template_name' => $template->name,
        'category_id' => $categoryId,
        'free_credit_cost' => $freeCreditCost,
        'paid_credit_cost' => $paidCreditCost,
        'has_enterprise_plan' => $hasEnterprisePlan,
        'joke_length' => strlen($joke)
    ]);

    // Check and deduct credits
    if (!$userCredits) {
        return response()->json([
            'success' => false,
            'message' => 'No credit account found. Please contact support.'
        ], 400);
    }
    
    $freeRemaining = $userCredits->free_credits - $userCredits->free_credits_used;
    $paidRemaining = $userCredits->paid_credits - $userCredits->paid_credits_used;
    
    // Try to use free credits first
    if ($freeRemaining >= $freeCreditCost) {
        $userCredits->free_credits_used += $freeCreditCost;
        $userCredits->used_credits += $freeCreditCost;
        $userCredits->save();
        
        Log::info("Generate Video: Free credits used", [
            'user_id' => $userId,
            'cost' => $freeCreditCost,
            'free_remaining_after' => $userCredits->free_credits - $userCredits->free_credits_used
        ]);
    } 
    // Use paid credits (flat 100)
    elseif ($paidRemaining >= $paidCreditCost) {
        $userCredits->paid_credits_used += $paidCreditCost;
        $userCredits->used_credits += $paidCreditCost;
        $userCredits->save();
        
        Log::info("Generate Video: Paid credits used", [
            'user_id' => $userId,
            'cost' => $paidCreditCost,
            'paid_remaining_after' => $userCredits->paid_credits - $userCredits->paid_credits_used
        ]);
    }
    else {
        $totalRemaining = $freeRemaining + $paidRemaining;
        $required = min($freeCreditCost, $paidCreditCost);
        
        return response()->json([
            'success' => false,
            'message' => "Insufficient credits. Please purchase more credits.",
            'credits_available' => $totalRemaining
        ], 400);
    }

    // Set temp block cache (only if not Enterprise plan)
    if (!$hasEnterprisePlan) {
        $this->setTempBlock($userId, 10);
    }

    // Build video prompt from template
    $jokeTemplate = is_string($template->joke_template) ? json_decode($template->joke_template, true) : $template->joke_template;
    $videoPrompt = $joke;
    if ($jokeTemplate && isset($jokeTemplate['prompt'])) {
        $videoPrompt = str_replace('{input}', $joke, $jokeTemplate['prompt']);
    }
    
    // Create video record
    $video = ComedyVideo::create([
        'user_id' => $userId,
        'template_id' => $template->id,
        'category_id' => $template->category_id,
        'title' => 'Comedy Video - ' . now()->format('Y-m-d H:i'),
        'joke' => $joke,
        'video_url' => '',
        'thumbnail_url' => $template->preview_image,
        'processing_status' => 'pending',
        'view_count' => 0
    ]);
    
    Log::info("Generate Video: Database record created", ['video_id' => $video->id]);
    
    // Dispatch to AI
    $generator = AiCustomGenerator::where('slug', 'comedy-video-generator')->with('aiModel')->firstOrFail();
    $aiModel = $generator->aiModel;
    $hookTrackId = 'comvid_u' . $userId . '_' . $video->id . '_' . date('Ymd');
    $initImageUrl = asset($template->init_image);
    
    $response = AiGen::generate($aiModel, [
        'init_image' => $initImageUrl,
        'prompt' => $videoPrompt,
        'hook_track_id' => $hookTrackId
    ], $hookTrackId, $generator, null);

    $responseData = $response->getData();

    if ($responseData->status === 'queue' || $responseData->status === 'success') {
        return response()->json([
            'success' => true,
            'message' => 'Video generation started',
            'video_id' => $video->id
        ]);
    }

    if ($response->status() === 200 && !isset($responseData->status)) {
        return response()->json([
            'success' => true,
            'message' => 'Video generation queued',
            'video_id' => $video->id
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => $responseData->message ?? 'Your video is under processing. It may take a few minutes.'
    ]);
}

    public function deleteVideo($id)
    {
        $video = ComedyVideo::where('user_id', Auth::id())->findOrFail($id);
        $video->delete();
        
        return response()->json(['success' => true]);
    }

    public function templates()
    {
        $templates = ComedyTemplate::where('is_active', true)
            ->with('category')
            ->paginate(24);
        
        return view('comedy.templates', compact('templates'));
    }

    public function videos()
    {
        $videos = ComedyVideo::where('user_id', Auth::id())
            ->with('template')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('comedy.videos', compact('videos'));
    }
}