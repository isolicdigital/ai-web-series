<?php
// app/Services/ModelsLabService.php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ModelsLabService
{
    protected $client;
    protected $chatApiKey;
    protected $imageApiKey;
    protected $chatApiUrl;
    protected $imageApiUrl;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 300,
            'verify' => false,
            'curl' => [
                CURLOPT_DNS_USE_GLOBAL_CACHE => false,
                CURLOPT_DNS_CACHE_TIMEOUT => 0,
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ]
        ]);
        
        // Chat API Key
        $this->chatApiKey = '1TnJavtjQ8pitGj0ah16oZGWW6eGegeCwLSLLpKZ3V1JNy1fRiASh8jg7EN5';
        
        // Image API Key
        $this->imageApiKey = '1TnJavtjQ8pitGj0ah16oZGWW6eGegeCwLSLLpKZ3V1JNy1fRiASh8jg7EN5';
        
        $this->chatApiUrl = 'https://modelslab.com/api/v6/llm/uncensored_chat';
        $this->imageApiUrl = 'https://modelslab.com/api/v6/images/text2img';
    }

    /**
     * Generate episode concept using category template from database
     */
    public function generateConcept($prompt, $categoryId, $projectName)
{
    Log::info('=== GENERATING CONCEPT FROM DATABASE TEMPLATE ===');
    
    try {
        // Get category template from database
        $template = \App\Models\CategoryTemplate::where('category_id', $categoryId)
            ->where('is_active', true)
            ->first();
        
        if (!$template) {
            throw new \Exception("No active category template found for category ID: {$categoryId}");
        }
        
        if (!$template->category_prompt || !isset($template->category_prompt['concept_generator'])) {
            throw new \Exception("Category template has no concept_generator for category ID: {$categoryId}");
        }
        
        // Get the full prompt directly from database
        $userPrompt = $template->category_prompt['concept_generator'];
        
        // Replace placeholders
        $userPrompt = str_replace('{user_prompt}', $prompt, $userPrompt);
        $userPrompt = str_replace('{series_name}', $projectName, $userPrompt);
        $userPrompt = str_replace('{category}', $template->category->name ?? 'Web Series', $userPrompt);
        
        Log::info('Using database category template for concept generation');
        Log::info('Full prompt: ' . $userPrompt);
        
        $systemPrompt = "You are an award-winning screenwriter. Write engaging, complete episode concepts. Always finish your sentences and complete your thoughts. Write detailed, vivid descriptions with rich imagery and emotional depth.";
        
        $response = $this->callChatApi($systemPrompt, $userPrompt, 1200);
        
        $concept = trim($response);
        
        // Remove any technical labels
        $concept = preg_replace('/^(Concept:|Episode Concept:|Story:|Summary:|Pitch:|Logline:)/i', '', $concept);
        $concept = trim($concept);
        $concept = preg_replace('/\s+/', ' ', $concept);
        
        if (!empty($concept) && !preg_match('/[.!?]$/', $concept)) {
            $concept .= '.';
        }
        
        // Smart truncate to 1100 characters (minimum 800, maximum 1100)
        $conceptLength = strlen($concept);
        
        if ($conceptLength > 1100) {
            // Truncate to 1097 characters to allow for punctuation
            $truncated = substr($concept, 0, 1097);
            
            // Find the last sentence boundary (., !, ?)
            $lastPeriod = strrpos($truncated, '.');
            $lastExclamation = strrpos($truncated, '!');
            $lastQuestion = strrpos($truncated, '?');
            $lastPunctuation = max($lastPeriod, $lastExclamation, $lastQuestion);
            
            // Only truncate at sentence boundary if it's within a reasonable range
            if ($lastPunctuation > 600) {
                $concept = substr($concept, 0, $lastPunctuation + 1);
            } else {
                $concept = substr($concept, 0, 1097) . '...';
            }
        } elseif ($conceptLength < 800) {
            // If concept is too short, add more detail
            Log::info('Concept too short (' . $conceptLength . ' chars), regenerating with more detail...');
            
            $enhancePrompt = "The following concept is too short (" . $conceptLength . " characters). Please expand it to 800-1100 characters by adding more details, emotional depth, and vivid descriptions while maintaining the same core story:\n\n" . $concept;
            
            $enhancedResponse = $this->callChatApi($systemPrompt, $enhancePrompt, 1200);
            $concept = trim($enhancedResponse);
            
            // Clean up the enhanced concept
            $concept = preg_replace('/^(Concept:|Episode Concept:|Story:|Summary:|Pitch:|Logline:)/i', '', $concept);
            $concept = trim($concept);
            $concept = preg_replace('/\s+/', ' ', $concept);
            
            if (!empty($concept) && !preg_match('/[.!?]$/', $concept)) {
                $concept .= '.';
            }
            
            // Final check - if still too short, append additional content
            if (strlen($concept) < 800) {
                $appendText = " This gripping narrative builds tension throughout, leading to a powerful climax that will leave audiences eager for the next episode.";
                if (strlen($concept) + strlen($appendText) <= 1100) {
                    $concept .= $appendText;
                }
            }
        }
        
        // Final length validation
        $finalLength = strlen($concept);
        Log::info('Final concept length: ' . $finalLength . ' characters');
        Log::info('Generated concept: ' . $concept);
        
        // Ensure we're within bounds (800-1100)
        if ($finalLength < 600) {
            Log::warning('Concept still below 800 characters (' . $finalLength . '), using fallback');
            $category = \App\Models\Category::find($categoryId);
            $categoryName = $category ? $category->name : 'Web Series';
            return $this->getFallbackConcept($categoryName, $prompt);
        }
        
        return $concept;

    } catch (\Exception $e) {
        Log::error('Generate concept error: ' . $e->getMessage());
        
        // Fallback concept
        $category = \App\Models\Category::find($categoryId);
        $categoryName = $category ? $category->name : 'Web Series';
        return $this->getFallbackConcept($categoryName, $prompt);
    }
}



    /**
     * Generate scene prompts based on concept
     */
    public function generateScenePrompts($concept, $totalScenes, $episodeNumber)
    {
        Log::info('=== GENERATING SCENE PROMPTS ===');
        Log::info('Concept: ' . substr($concept, 0, 200) . '...');
        Log::info('Total Scenes: ' . $totalScenes);
        Log::info('Episode Number: ' . $episodeNumber);
        
        try {
            $systemPrompt = "You are a professional screenwriter. Create very concise scene descriptions.";
            
            $userPrompt = "Based on this concept, create {$totalScenes} SHORT scenes.

CONCEPT: {$concept}

For each scene, provide:
SCENE_[number]_TITLE: [Short title - 3-5 words]
SCENE_[number]_DESC: [1 short sentence - under 100 characters]

Keep descriptions VERY SHORT and punchy.";

            Log::info('Calling API to generate scenes...');
            
            $response = $this->callChatApi($systemPrompt, $userPrompt, 2000);
            
            Log::info('API Response received');
            Log::info('Raw Response: ' . $response);
            
            $scenes = $this->parseScenesFromResponse($response, $totalScenes);
            
            // Log each generated scene
            Log::info('=== GENERATED SCENES ===');
            foreach ($scenes as $index => $scene) {
                Log::info('Scene ' . ($index + 1) . ':');
                Log::info('  Title: ' . $scene['title']);
                Log::info('  Description: ' . $scene['description']);
                Log::info('  Description Length: ' . strlen($scene['description']) . ' chars');
            }
            
            Log::info('Total scenes generated: ' . count($scenes));
            
            return $scenes;
            
        } catch (\Exception $e) {
            Log::error('Generate scene prompts error: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());
            
            $scenes = [];
            $defaultTitles = ["The Opening", "The Conflict", "The Turning Point", "The Climax", "The Resolution"];
            
            for ($i = 1; $i <= $totalScenes; $i++) {
                $scenes[] = [
                    'title' => $defaultTitles[$i-1] ?? "Scene {$i}",
                    'description' => "The story continues with exciting new developments."
                ];
            }
            
            Log::info('Using fallback scenes due to error');
            foreach ($scenes as $index => $scene) {
                Log::info('Fallback Scene ' . ($index + 1) . ': ' . $scene['title']);
            }
            
            return $scenes;
        }
    }
    
    /**
     * Parse scenes from API response
     */
    private function parseScenesFromResponse($response, $expectedCount)
    {
        $scenes = [];
        
        preg_match_all('/SCENE_(\d+)_TITLE:\s*(.+?)(?:\n|$)/i', $response, $titleMatches);
        preg_match_all('/SCENE_(\d+)_DESC:\s*(.+?)(?=\nSCENE_\d+_TITLE:|\n*$)/is', $response, $descMatches);
        
        if (!empty($titleMatches[2])) {
            $descMap = [];
            foreach ($descMatches[1] as $idx => $sceneNum) {
                $descMap[$sceneNum] = trim($descMatches[2][$idx]);
            }
            
            foreach ($titleMatches[2] as $idx => $title) {
                $sceneNum = $titleMatches[1][$idx];
                $description = $descMap[$sceneNum] ?? "The scene continues the story.";
                
                if (!empty($description) && !preg_match('/[.!?]$/', $description)) {
                    $description .= '.';
                }
                
                if (strlen($description) > 150) {
                    $description = substr($description, 0, 147) . '...';
                }
                
                $scenes[] = [
                    'title' => trim($title),
                    'description' => trim($description)
                ];
            }
        }
        
        while (count($scenes) < $expectedCount) {
            $scenes[] = [
                'title' => "Scene " . (count($scenes) + 1),
                'description' => "The story continues with exciting developments."
            ];
        }
        
        return array_slice($scenes, 0, $expectedCount);
    }

    /**
     * Generate image prompt using category template from database
     */
    public function generateImagePrompt($concept, $sceneTitle, $sceneDescription, $sceneNumber, $episodeNumber, $categoryId)
    {
        Log::info('=== GENERATING IMAGE PROMPT FROM DATABASE TEMPLATE ===');
        Log::info('Scene: ' . $sceneTitle);
        Log::info('Category ID: ' . $categoryId);
        
        try {
            // Get category template from database
            $template = \App\Models\CategoryTemplate::where('category_id', $categoryId)
                ->where('is_active', true)
                ->first();
            
            if (!$template) {
                throw new \Exception("No active category template found for category ID: {$categoryId}");
            }
            
            if (!$template->category_prompt || !isset($template->category_prompt['image_generator'])) {
                throw new \Exception("Category template has no image_generator for category ID: {$categoryId}");
            }
            
            // Get the full image prompt from database
            $imagePromptTemplate = $template->category_prompt['image_generator'];
            
            // Replace placeholders
            $imagePromptTemplate = str_replace('{scene_title}', $sceneTitle, $imagePromptTemplate);
            $imagePromptTemplate = str_replace('{scene_description}', $sceneDescription, $imagePromptTemplate);
            $imagePromptTemplate = str_replace('{concept}', $concept, $imagePromptTemplate);
            $imagePromptTemplate = str_replace('{category}', $template->category->name ?? 'Web Series', $imagePromptTemplate);
            
            Log::info('Using database category template for image prompt generation');
            Log::info('Full image prompt length: ' . strlen($imagePromptTemplate));
            
            return $imagePromptTemplate;
            
        } catch (\Exception $e) {
            Log::error('Generate image prompt error: ' . $e->getMessage());
            
            // Fallback image prompt
            $category = \App\Models\Category::find($categoryId);
            $categoryName = $category ? $category->name : 'Web Series';
            return "Cinematic {$categoryName} scene, {$sceneTitle}. {$sceneDescription} Professional cinematography, dramatic lighting, 8K resolution, movie still.";
        }
    }

    /**
 * Generate image using ModelsLab API
 */
// public function generateImage($prompt, $width = 1024, $height = 1024, $samples = 1)
// {
//     Log::info('=== GENERATING IMAGE VIA MODELSLAB API ===');
//     Log::info('Prompt length: ' . strlen($prompt));
    
//     try {
//         $payload = [
//             'key' => $this->imageApiKey,
//             'model_id' => 'flux-2-dev',
//             'prompt' => $prompt,
//             'width' => (string)$width,
//             'height' => (string)$height,
//             'num_inference_steps' => '30',
//             'samples' => (string)$samples,
//             'safety_checker' => false,
//             'enhance_prompt' => true,
//             'guidance_scale' => 7.5,
//         ];
        
//         Log::info('Sending request to ModelsLab API');
        
//         $response = $this->client->post($this->imageApiUrl, [
//             'json' => $payload,
//             'headers' => ['Content-Type' => 'application/json']
//         ]);
        
//         $body = $response->getBody()->getContents();
//         $result = json_decode($body, true);
        
//         Log::info('API Response received', ['response' => json_encode($result)]);
        
//         // Check for immediate output
//         if (isset($result['output']) && !empty($result['output'])) {
//             return [
//                 'success' => true,
//                 'images' => $result['output'],
//                 'status' => 'completed'
//             ];
//         }
        
//         // Check for future_links
//         if (isset($result['future_links']) && !empty($result['future_links'])) {
//             return [
//                 'success' => true,
//                 'images' => $result['future_links'],
//                 'status' => 'completed'
//             ];
//         }
        
//         // Check if processing
//         if (isset($result['status']) && $result['status'] === 'processing') {
//             return [
//                 'success' => true,
//                 'status' => 'processing',
//                 'request_id' => $result['id'] ?? null,
//                 'message' => 'Image generation started'
//             ];
//         }
        
//         return [
//             'success' => false,
//             'message' => $result['message'] ?? 'Unknown error occurred'
//         ];
        
//     } catch (\Exception $e) {
//         Log::error('Generate image error: ' . $e->getMessage());
//         return [
//             'success' => false,
//             'message' => $e->getMessage()
//         ];
//     }
// }


public function generateImage($prompt, $width = 1024, $height = 1024, $samples = 1)
{
    Log::info('=== GENERATING IMAGE VIA REPLICATE FLUX SCHNELL API ===');
    Log::info('Prompt: ' . substr($prompt, 0, 200));
    
    $apiToken = env('REPLICATE_API_TOKEN');
    
    if (!$apiToken) {
        Log::error('REPLICATE_API_TOKEN not set');
        return [
            'success' => false,
            'message' => 'REPLICATE_API_TOKEN not configured'
        ];
    }
    
    try {
        // Determine aspect ratio from dimensions
        $aspectRatio = $this->getAspectRatio($width, $height);
        
        $payload = [
            'input' => [
                'prompt' => $prompt,
                'go_fast' => true,
                'megapixels' => '1',
                'num_outputs' => $samples,
                'aspect_ratio' => $aspectRatio,
                'output_format' => 'webp',
                'output_quality' => 80,
                'num_inference_steps' => 4
            ]
        ];
        
        Log::info('Sending request to Replicate Flux Schnell API');
        Log::info('Payload: ' . json_encode($payload));
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.replicate.com/v1/models/black-forest-labs/flux-schnell/predictions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiToken,
            'Content-Type: application/json',
            'Prefer: wait'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        Log::info("Replicate API response code: {$httpCode}");
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            Log::error("Replicate API error: " . $response);
            return [
                'success' => false,
                'message' => "API returned status code: {$httpCode}"
            ];
        }
        
        $result = json_decode($response, true);
        Log::info('API Response received', ['response' => json_encode($result)]);
        
        // Check for immediate output (with Prefer: wait header)
        if (isset($result['output']) && !empty($result['output'])) {
            // Flux Schnell returns output as array of URLs
            $images = is_array($result['output']) ? $result['output'] : [$result['output']];
            return [
                'success' => true,
                'images' => $images,
                'status' => 'completed'
            ];
        }
        
        // Check if we have URLs in the output
        if (isset($result['urls']) && isset($result['urls']['get'])) {
            // Need to poll for result
            return $this->pollReplicatePrediction($result['id'], $apiToken);
        }
        
        // Check if processing
        if (isset($result['status']) && $result['status'] === 'processing') {
            return [
                'success' => true,
                'status' => 'processing',
                'prediction_id' => $result['id'] ?? null,
                'message' => 'Image generation started'
            ];
        }
        
        return [
            'success' => false,
            'message' => $result['error'] ?? 'Unknown error occurred'
        ];
        
    } catch (\Exception $e) {
        Log::error('Generate image error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Convert width/height to aspect ratio string for Replicate API
 */
private function getAspectRatio($width, $height)
{
    $ratios = [
        '1:1' => ['min' => 0.95, 'max' => 1.05],
        '3:2' => ['min' => 1.45, 'max' => 1.55],
        '2:3' => ['min' => 0.64, 'max' => 0.68],
        '4:3' => ['min' => 1.32, 'max' => 1.35],
        '3:4' => ['min' => 0.74, 'max' => 0.76],
        '16:9' => ['min' => 1.77, 'max' => 1.78],
        '9:16' => ['min' => 0.56, 'max' => 0.57],
    ];
    
    $ratio = $width / $height;
    
    foreach ($ratios as $aspect => $range) {
        if ($ratio >= $range['min'] && $ratio <= $range['max']) {
            return $aspect;
        }
    }
    
    return '1:1'; // Default
}

/**
 * Poll Replicate prediction for result
 */
private function pollReplicatePrediction($predictionId, $apiToken, $maxAttempts = 30, $delaySeconds = 2)
{
    Log::info("Polling Replicate prediction: {$predictionId}");
    
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
                Log::warning("Polling HTTP error: {$httpCode}");
                continue;
            }
            
            $result = json_decode($response, true);
            $status = $result['status'] ?? 'processing';
            
            Log::info("Prediction status: {$status} (attempt {$attempt})");
            
            if ($status === 'succeeded') {
                $output = $result['output'] ?? null;
                
                if ($output) {
                    $images = is_array($output) ? $output : [$output];
                    return [
                        'success' => true,
                        'images' => $images,
                        'status' => 'completed'
                    ];
                }
            }
            
            if ($status === 'failed') {
                $error = $result['error'] ?? 'Unknown error';
                Log::error("Replicate prediction failed: " . $error);
                return [
                    'success' => false,
                    'message' => $error
                ];
            }
            
        } catch (\Exception $e) {
            Log::error("Error polling Replicate: " . $e->getMessage());
        }
    }
    
    Log::error("Polling timeout for prediction {$predictionId}");
    return [
        'success' => false,
        'message' => 'Image generation timed out'
    ];
}

    /**
     * Get negative prompt from category template
     */
    public function getNegativePrompt($categoryId)
    {
        try {
            $template = \App\Models\CategoryTemplate::where('category_id', $categoryId)
                ->where('is_active', true)
                ->first();
            
            if ($template && $template->category_prompt && isset($template->category_prompt['negative_prompt'])) {
                return $template->category_prompt['negative_prompt'];
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Get negative prompt error: ' . $e->getMessage());
            return null;
        }
    }

    /**
 * Get a fallback concept when generation fails
 */
private function getFallbackConcept($categoryName, $prompt)
{
    $fallbacks = [
        "A compelling {$categoryName} story about an extraordinary journey. The hero faces impossible challenges, discovers hidden truths about their past, and transforms in unexpected ways. Along the way, they form unlikely alliances and confront their deepest fears. The episode builds to a breathtaking climax where everything changes, ending with a revelation that will haunt audiences until the next installment.",
        
        "In this gripping {$categoryName} tale, ordinary people are thrust into extraordinary circumstances. As secrets unravel and tensions rise, each character must choose between what is easy and what is right. The narrative weaves together multiple storylines, creating a rich tapestry of emotion, action, and suspense. Just when you think you know what happens next, a shocking twist changes everything, setting up an epic continuation.",
        
        "A powerful {$categoryName} narrative exploring themes of redemption, courage, and sacrifice. When a mysterious threat emerges, our protagonist must rally unlikely allies and overcome personal demons to save everything they hold dear. The episode masterfully balances heart-pounding action with quiet, character-driven moments, building toward an emotionally devastating conclusion that redefines what's possible in storytelling.",
        
        "An epic {$categoryName} adventure that pushes the boundaries of imagination. The story follows a unlikely hero who discovers they have the power to change their world. Through trials and tribulations, they learn that true strength comes from within. The episode ends with a cliffhanger that will leave audiences desperate for more, setting up an exciting season ahead.",
        
        "A heart-wrenching {$categoryName} drama that explores the depths of human emotion. When tragedy strikes, our protagonist must find the courage to carry on while helping others do the same. The episode weaves together multiple storylines, each exploring different aspects of love, loss, and redemption. The powerful conclusion will stay with viewers long after the credits roll."
    ];
    
    // Add the user prompt context to the fallback
    $fallback = $fallbacks[array_rand($fallbacks)];
    if (!empty($prompt) && strlen($prompt) > 10) {
        $fallback = "Based on the idea: \"{$prompt}\". " . $fallback;
    }
    
    // Ensure fallback length is between 900-1000 characters
    if (strlen($fallback) > 1000) {
        $fallback = substr($fallback, 0, 997) . '.';
    } elseif (strlen($fallback) < 900) {
        $fallback .= " Every moment builds toward an unforgettable finale that redefines the {$categoryName} genre.";
    }
    
    return $fallback;
}

    /**
     * Get joke prompt for comedy category
     */
    public function getJokePrompt($categoryId, $userPrompt)
    {
        try {
            $template = \App\Models\CategoryTemplate::where('category_id', $categoryId)
                ->where('is_active', true)
                ->first();
            
            if ($template && $template->category_prompt && isset($template->category_prompt['joke'])) {
                $jokePrompt = $template->category_prompt['joke'];
                $result = str_replace('{user_prompt}', $userPrompt, $jokePrompt);
                Log::info('Using joke prompt from database', ['prompt' => $result]);
                return $result;
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Get joke prompt error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate unique tracking ID
     */
    public function generateTrackingId()
    {
        return 'img_' . uniqid() . '_' . time() . '_' . bin2hex(random_bytes(8));
    }

    /**
     * Generate image with webhook and logging (includes negative prompt in API call only)
     */
    public function generateImageWithWebhook($prompt, $sceneId, $seriesId, $userId, $width = 1024, $height = 1024, $samples = 1)
{
    Log::info('=== GENERATING IMAGE VIA REPLICATE FLUX SCHNELL WITH WEBHOOK ===');
    Log::info('Scene ID: ' . $sceneId);
    
    $apiToken = env('REPLICATE_API_TOKEN');
    
    if (!$apiToken) {
        Log::error('REPLICATE_API_TOKEN not set');
        return [
            'success' => false,
            'message' => 'REPLICATE_API_TOKEN not configured'
        ];
    }
    
    try {
        // Get category for tracking
        $scene = \App\Models\Scene::find($sceneId);
        $webhookUrl = url("/api/replicate-webhook?scene_id={$sceneId}&series_id={$seriesId}&user_id={$userId}");
        
        $aspectRatio = $this->getAspectRatio($width, $height);
        
        $payload = [
            'input' => [
                'prompt' => $prompt,
                'go_fast' => true,
                'megapixels' => '1',
                'num_outputs' => $samples,
                'aspect_ratio' => $aspectRatio,
                'output_format' => 'webp',
                'output_quality' => 80,
                'num_inference_steps' => 4
            ],
            'webhook' => $webhookUrl,
            'webhook_events_filter' => ['completed']
        ];
        
        Log::info('Sending request to Replicate API with webhook');
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.replicate.com/v1/models/black-forest-labs/flux-schnell/predictions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        Log::info("Replicate API response code: {$httpCode}");
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            return [
                'success' => false,
                'message' => "API returned status code: {$httpCode}"
            ];
        }
        
        $result = json_decode($response, true);
        
        return [
            'success' => true,
            'status' => 'processing',
            'prediction_id' => $result['id'] ?? null,
            'message' => 'Image generation started'
        ];
        
    } catch (\Exception $e) {
        Log::error('Generate image error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

    /**
     * Call Chat API
     */
    private function callChatApi($systemPrompt, $userPrompt, $maxTokens = 2000)
    {
        $payload = [
            'key' => $this->chatApiKey,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'max_tokens' => $maxTokens,
            'temperature' => 0.7,
            'top_p' => 0.9,
            'presence_penalty' => 0.3,
            'frequency_penalty' => 0.3
        ];
        
        Log::info('Calling Chat API with max_tokens: ' . $maxTokens);
        
        $response = $this->client->post($this->chatApiUrl, [
            'json' => $payload,
            'headers' => ['Content-Type' => 'application/json']
        ]);
        
        $body = $response->getBody()->getContents();
        $decoded = json_decode($body, true);
        
        if (isset($decoded['status']) && $decoded['status'] === 'success') {
            $message = $decoded['message'] ?? $decoded['response'] ?? '';
            Log::info('Chat API response length: ' . strlen($message) . ' chars');
            return $message;
        }
        
        Log::error('Chat API invalid response', ['body' => substr($body, 0, 500)]);
        throw new \Exception('Invalid API response: ' . substr($body, 0, 200));
    }
}