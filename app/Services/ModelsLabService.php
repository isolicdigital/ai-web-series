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
            
            $systemPrompt = "You are an award-winning screenwriter. Write engaging, complete episode concepts. Always finish your sentences and complete your thoughts.";
            
            $response = $this->callChatApi($systemPrompt, $userPrompt, 1000);
            
            $concept = trim($response);
            
            // Remove any technical labels
            $concept = preg_replace('/^(Concept:|Episode Concept:|Story:|Summary:|Pitch:|Logline:)/i', '', $concept);
            $concept = trim($concept);
            $concept = preg_replace('/\s+/', ' ', $concept);
            
            if (!empty($concept) && !preg_match('/[.!?]$/', $concept)) {
                $concept .= '.';
            }
            
            // Smart truncate to 800 characters
            if (strlen($concept) > 800) {
                $truncated = substr($concept, 0, 797);
                $lastPeriod = strrpos($truncated, '.');
                $lastExclamation = strrpos($truncated, '!');
                $lastQuestion = strrpos($truncated, '?');
                $lastPunctuation = max($lastPeriod, $lastExclamation, $lastQuestion);
                
                if ($lastPunctuation > 500) {
                    $concept = substr($concept, 0, $lastPunctuation + 1);
                } else {
                    $concept = substr($concept, 0, 797) . '...';
                }
            }
            
            Log::info('Concept length: ' . strlen($concept));
            Log::info('Generated concept: ' . $concept);
            
            return $concept;

        } catch (\Exception $e) {
            Log::error('Generate concept error: ' . $e->getMessage());
            
            // Fallback concept
            $category = \App\Models\Category::find($categoryId);
            $categoryName = $category ? $category->name : 'Web Series';
            return "A compelling {$categoryName} story about an extraordinary journey. The hero faces challenges, discovers hidden truths, and transforms in unexpected ways. The episode ends with a revelation that changes everything.";
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
     * Generate image with webhook and logging
     */
    public function generateImageWithWebhook($prompt, $sceneId, $seriesId, $userId, $width = 1024, $height = 1024, $samples = 1)
    {
        Log::info('=== GENERATING IMAGE WITH WEBHOOK ===');
        Log::info('Scene ID: ' . $sceneId);
        Log::info('Series ID: ' . $seriesId);
        Log::info('User ID: ' . $userId);
        
        try {
            $trackingId = $this->generateTrackingId();
            $webhookUrl = url("/webhook/image-generation?tracking_id={$trackingId}&scene_id={$sceneId}&series_id={$seriesId}");
            
            // Create log entry if model exists
            if (class_exists(\App\Models\ImageGenerationLog::class)) {
                $log = \App\Models\ImageGenerationLog::create([
                    'tracking_id' => $trackingId,
                    'scene_id' => $sceneId,
                    'web_series_id' => $seriesId,
                    'user_id' => $userId,
                    'prompt' => $prompt,
                    'model_id' => 'flux-2-dev',
                    'samples' => $samples,
                    'num_inference_steps' => 30,
                    'guidance_scale' => 7.5,
                    'webhook_url' => $webhookUrl,
                    'status' => 'pending',
                    'api_called_at' => now()
                ]);
                Log::info('Created log entry with tracking ID: ' . $trackingId);
            }
            
            $payload = [
                'key' => $this->imageApiKey,
                'model_id' => 'flux-2-dev',
                'prompt' => $prompt,
                'width' => (string)$width,
                'height' => (string)$height,
                'num_inference_steps' => '30',
                'samples' => (string)$samples,
                'safety_checker' => false,
                'enhance_prompt' => true,
                'guidance_scale' => 7.5,
                'seed' => null,
                'webhook' => $webhookUrl
            ];
            
            Log::info('Sending request to ModelsLab API');
            
            $response = $this->client->post($this->imageApiUrl, [
                'json' => $payload,
                'headers' => ['Content-Type' => 'application/json']
            ]);
            
            $body = $response->getBody()->getContents();
            $result = json_decode($body, true);
            
            Log::info('Image API Response', ['response' => $result]);
            
            // Update log with API response
            if (isset($log)) {
                $log->update([
                    'full_api_response' => $result,
                    'api_request_id' => $result['id'] ?? null
                ]);
            }
            
            // Check if future_links already available
            if (isset($result['future_links']) && !empty($result['future_links'])) {
                if (isset($log)) {
                    $log->update([
                        'status' => 'completed',
                        'image_urls' => $result['future_links'],
                        'completed_at' => now()
                    ]);
                }
                return [
                    'success' => true,
                    'images' => $result['future_links'],
                    'status' => 'completed',
                    'tracking_id' => $trackingId
                ];
            }
            
            if (isset($result['output']) && !empty($result['output'])) {
                if (isset($log)) {
                    $log->update([
                        'status' => 'completed',
                        'image_urls' => $result['output'],
                        'completed_at' => now()
                    ]);
                }
                return [
                    'success' => true,
                    'images' => $result['output'],
                    'status' => 'completed',
                    'tracking_id' => $trackingId
                ];
            }
            
            if (isset($result['status']) && $result['status'] === 'processing') {
                if (isset($log)) {
                    $log->update(['status' => 'processing']);
                }
                return [
                    'success' => true,
                    'status' => 'processing',
                    'request_id' => $result['id'] ?? null,
                    'tracking_id' => $trackingId,
                    'message' => 'Image generation started'
                ];
            }
            
            if (isset($log)) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $result['message'] ?? 'Failed to start generation',
                    'completed_at' => now()
                ]);
            }
            
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Failed to start generation',
                'tracking_id' => $trackingId
            ];
            
        } catch (\Exception $e) {
            Log::error('Generate image error: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());
            
            // Create failed log entry
            if (class_exists(\App\Models\ImageGenerationLog::class)) {
                \App\Models\ImageGenerationLog::create([
                    'tracking_id' => $this->generateTrackingId(),
                    'scene_id' => $sceneId,
                    'web_series_id' => $seriesId,
                    'user_id' => $userId,
                    'prompt' => $prompt,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'api_called_at' => now(),
                    'completed_at' => now()
                ]);
            }
            
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