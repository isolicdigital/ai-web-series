<?php
// app/Services/ModelsLabService.php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ModelsLabService
{
    protected $client;
    protected $apiKey;
    protected $chatApiUrl;
    protected $imageApiUrl;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 120,
            'verify' => false
        ]);
        // Chat API Key
        $this->apiKey = '1TnJavtjQ8pitGj0ah16oZGWW6eGegeCwLSLLpKZ3V1JNy1fRiASh8jg7EN5';
        $this->chatApiUrl = 'https://modelslab.com/api/v6/llm/uncensored_chat';
        // Image API URL
        $this->imageApiUrl = 'https://modelslab.com/api/v6/images/text2img';
    }

    /**
     * Generate a concept based on user prompt
     */
    public function generateConcept($prompt, $category, $projectName)
    {
        try {
            $systemPrompt = "You are a creative story concept generator. Create a compelling, unique concept for a {$category} web series titled '{$projectName}'.";
            
            $userPrompt = "Based on this idea: '{$prompt}'\n\nGenerate a 250-300 character concept for the first episode. Be concise, engaging, and vivid. Output ONLY the concept text, no explanations or labels.";

            $response = $this->callChatApi($systemPrompt, $userPrompt);
            
            // Extract the concept from response
            $concept = $this->extractConceptFromResponse($response);
            
            // Ensure concept is around 300 characters
            if (strlen($concept) > 350) {
                $concept = substr($concept, 0, 300) . '...';
            }
            
            return $concept;

        } catch (\Exception $e) {
            Log::error('Generate concept error: ' . $e->getMessage());
            // Return a fallback concept
            return "A thrilling {$category} series about: {$prompt}. Follow the journey of our hero as they face incredible challenges and discover their true destiny.";
        }
    }

    /**
     * Generate an episode based on concept
     */
    public function generateEpisode($concept, $episodeNumber, $totalEpisodes, $category, $projectName)
    {
        try {
            $position = $episodeNumber == 1 ? "OPENING" : ($episodeNumber == $totalEpisodes ? "FINAL" : "MIDDLE");
            
            $systemPrompt = "You are a professional screenwriter. Write engaging, vivid episodes for a {$category} web series.";
            
            $userPrompt = "Concept: {$concept}\n\n";
            $userPrompt .= "Write Episode {$episodeNumber} of {$totalEpisodes} ({$position} episode) for the web series '{$projectName}'.\n\n";
            $userPrompt .= "Format:\n";
            $userPrompt .= "TITLE: [Episode title - max 10 words]\n";
            $userPrompt .= "CONTENT: [Episode content - 400-600 words including dialogue and action]\n\n";
            $userPrompt .= "Make it dramatic, engaging, and consistent with the concept.";

            $response = $this->callChatApi($systemPrompt, $userPrompt);
            
            return $this->parseEpisodeResponse($response, $episodeNumber);

        } catch (\Exception $e) {
            Log::error('Generate episode error: ' . $e->getMessage());
            // Return a fallback episode
            return [
                'title' => "Episode {$episodeNumber}: The Journey Continues",
                'content' => "<p>This is episode {$episodeNumber} of {$totalEpisodes}. The story continues with exciting developments and unexpected twists.</p><p>Based on the concept: {$concept}</p>",
                'summary' => "Episode {$episodeNumber} continues the adventure"
            ];
        }
    }

    /**
     * Call the ModelsLab Chat API
     */
    private function callChatApi($systemPrompt, $userPrompt)
    {
        try {
            $payload = [
                'key' => $this->apiKey,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt
                    ],
                    [
                        'role' => 'user',
                        'content' => $userPrompt
                    ]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.8
            ];
            
            Log::info('ModelsLab API Request', ['payload' => $payload]);
            
            $response = $this->client->post($this->chatApiUrl, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
            
            $body = $response->getBody()->getContents();
            Log::info('ModelsLab API Raw Response', ['body' => $body]);
            
            $decoded = json_decode($body, true);
            
            // Check for different response formats
            if (isset($decoded['status']) && $decoded['status'] === 'success') {
                if (isset($decoded['message'])) {
                    return $decoded['message'];
                }
                if (isset($decoded['response'])) {
                    return $decoded['response'];
                }
                if (isset($decoded['text'])) {
                    return $decoded['text'];
                }
            }
            
            // Check for OpenAI-style response
            if (isset($decoded['choices'][0]['message']['content'])) {
                return $decoded['choices'][0]['message']['content'];
            }
            
            // If we get here, log the full response and throw exception
            Log::error('Unknown API response format', ['response' => $decoded]);
            throw new \Exception('Invalid API response format');
            
        } catch (\Exception $e) {
            Log::error('ModelsLab API Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract concept from API response
     */
    private function extractConceptFromResponse($response)
    {
        // Remove any markdown or extra formatting
        $concept = strip_tags($response);
        $concept = preg_replace('/\*\*(.*?)\*\*/', '$1', $concept);
        $concept = preg_replace('/\*(.*?)\*/', '$1', $concept);
        $concept = trim($concept);
        
        // Remove any common prefixes
        $prefixes = ['Concept:', 'Story Concept:', 'Episode Concept:', 'Here is the concept:', 'Concept:', 'CONCEPT:'];
        foreach ($prefixes as $prefix) {
            if (str_starts_with($concept, $prefix)) {
                $concept = trim(substr($concept, strlen($prefix)));
            }
        }
        
        return $concept;
    }

    /**
     * Parse episode response from API
     */
    private function parseEpisodeResponse($response, $episodeNumber)
    {
        // Extract title
        preg_match('/TITLE:\s*(.+?)(?:\n|$)/i', $response, $titleMatch);
        $title = trim($titleMatch[1] ?? "Episode {$episodeNumber}");
        
        // Extract content
        preg_match('/CONTENT:\s*(.*)/is', $response, $contentMatch);
        $content = trim($contentMatch[1] ?? $response);
        
        // Remove any markdown and format as HTML
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
        
        // Convert to paragraphs
        $paragraphs = explode("\n\n", $content);
        $formatted = '';
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (!empty($paragraph)) {
                // Check if it's already HTML
                if (strpos($paragraph, '<') !== false && strpos($paragraph, '>') !== false) {
                    $formatted .= $paragraph;
                } else {
                    $formatted .= '<p class="mb-4">' . nl2br(htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8')) . '</p>';
                }
            }
        }
        
        if (empty($formatted)) {
            $formatted = '<p>' . nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8')) . '</p>';
        }
        
        $summary = substr(strip_tags($formatted), 0, 150) . '...';
        
        return [
            'title' => $title,
            'content' => $formatted,
            'summary' => $summary
        ];
    }

    /**
     * Generate image prompts for each scene
     */
    public function generateImagePrompts($concept, $sceneTitle, $sceneContent, $sceneNumber, $episodeNumber, $category)
    {
        try {
            $systemPrompt = "You are an expert at creating detailed prompts for AI image generation (Midjourney, DALL-E, Stable Diffusion). Create vivid, descriptive prompts that will generate cinematic images.";
            
            $userPrompt = "Based on this web series scene, create a detailed image generation prompt.

Series Category: {$category}
Episode: {$episodeNumber}
Scene {$sceneNumber}: {$sceneTitle}

Scene Content:
{$sceneContent}

Create a PROMPT for AI image generation that includes:
1. Main subject and action
2. Setting and atmosphere  
3. Lighting and mood
4. Camera angle and composition
5. Style (cinematic, movie still)
6. Key visual elements

Output ONLY the prompt text, no explanations. Make it 100-200 characters, highly descriptive.";

            $response = $this->callChatApi($systemPrompt, $userPrompt);
            
            // Clean and format the prompt
            $imagePrompt = trim($response);
            $imagePrompt = str_replace(['"', "'"], '', $imagePrompt);
            
            return $imagePrompt;
            
        } catch (\Exception $e) {
            Log::error('Generate image prompt error: ' . $e->getMessage());
            return $this->generateFallbackImagePrompt($sceneTitle, $sceneNumber, $category);
        }
    }

    private function generateFallbackImagePrompt($sceneTitle, $sceneNumber, $category)
    {
        return "Cinematic {$category} scene, {$sceneTitle}, dramatic lighting, professional cinematography, 8K resolution, movie still, epic atmosphere, detailed characters, emotional moment, wide angle shot, rich colors, film grain, ultra HD.";
    }

    /**
     * Generate image using Flux 2 Dev model
     */
    public function generateImage($prompt, $width = 1024, $height = 1024, $samples = 1)
    {
        try {
            // Use the image API key (different from chat API key)
            $imageApiKey = '1TnJavtjQ8pitGj0ah16oZGWW6eGegeCwLSLLpKZ3V1JNy1fRiASh8jg7EN5';
            
            $payload = [
                'key' => $imageApiKey,
                'model_id' => 'flux-2-dev',
                'prompt' => $prompt,
                'width' => (string)$width,
                'height' => (string)$height,
                'num_inference_steps' => '30',
                'samples' => (string)$samples,
                'safety_checker' => false,
                'enhance_prompt' => true,
                'guidance_scale' => 7.5,
                'seed' => null
            ];
            
            Log::info('Image Generation API Request', ['payload' => $payload]);
            
            $response = $this->client->post($this->imageApiUrl, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
            
            $body = $response->getBody()->getContents();
            Log::info('Image Generation API Response', ['body' => substr($body, 0, 500)]);
            
            $result = json_decode($body, true);
            
            if (isset($result['status']) && $result['status'] === 'success') {
                // Check for output URLs in different response formats
                if (isset($result['output']) && is_array($result['output'])) {
                    return $result['output'];
                }
                if (isset($result['image_url'])) {
                    return [$result['image_url']];
                }
                if (isset($result['images']) && is_array($result['images'])) {
                    return $result['images'];
                }
            }
            
            // Check for error message
            if (isset($result['message'])) {
                throw new \Exception($result['message']);
            }
            
            Log::error('Unexpected API response format', ['response' => $result]);
            
            // Return placeholder images if API fails
            $placeholders = [];
            for ($i = 0; $i < $samples; $i++) {
                $placeholders[] = "https://placehold.co/{$width}x{$height}/7c3aed/ffffff?text=" . urlencode(substr($prompt, 0, 50));
            }
            return $placeholders;
            
        } catch (\Exception $e) {
            Log::error('Generate image API error: ' . $e->getMessage());
            // Return placeholder on error
            return ["https://placehold.co/{$width}x{$height}/7c3aed/ffffff?text=Image+Generation+Failed"];
        }
    }

    /**
     * Test API connection
     */
    public function testConnection()
    {
        try {
            $response = $this->callChatApi(
                "You are a helpful assistant.",
                "Say 'API is working'"
            );
            return ['success' => true, 'response' => $response];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}