<?php

namespace App\Helpers;

use App\Models\AiModel;
use App\Models\AiCustomGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Http\JsonResponse;

class AiGen
{
    private const ML_API_KEY = '1TnJavtjQ8pitGj0ah16oZGWW6eGegeCwLSLLpKZ3V1JNy1fRiASh8jg7EN5';
    private const ML_BASE_URL = 'https://modelslab.com/api/';
    private const REPLICATE_BASE_URL = 'https://api.replicate.com/v1/models/';
    private const SUCCESS_HTTP_CODES = [200, 201, 204];
    private const CURL_TIMEOUT = 60;
    private const FILE_UPLOAD_PATH = 'ai-assets';
    private const USER_PROJECTS_PATH = 'ai-projects';


    public static function chatbot_qa($prompts)
    {
        try {
            $endpoint = 'v6/llm/uncensored_chat';
            $track_id = 'unc' . date('YmdHis');
            $post_array = (object)[
                'messages' => $prompts,
                'track_id' => $track_id,
                'max_tokens' => 10000,
                'key' => env('ML_KEY')
            ];

            Log::info("Chatbot QA initiated", ['track_id' => $track_id]);

            $response = self::ml($endpoint, $post_array);

            if (is_object($response) && isset($response->message)) {
                $resp_text = trim($response->message);
                Log::info("Chatbot QA response", ['track_id' => $track_id, 'message' => $resp_text]);
                return ['text' => $resp_text];
            }

            Log::error("Invalid response from chatbot", ['track_id' => $track_id, 'response' => $response]);
            return ['error' => 'Invalid response from chatbot.'];

        } catch (Exception $e) {
            $err_msg = $e->getMessage();

            if (strpos($err_msg, '400') !== false) {
                $err_msg = 'Server is unable to understand the request. Please try again later!';
            } elseif (strpos($err_msg, '401') !== false) {
                $err_msg = 'Server is Busy. Please try again later!';
            } elseif (strpos($err_msg, '429') !== false) {
                $err_msg = 'Server is unable to process so many requests. Please try again later!';
            }

            Log::error("Exception in chatbot_qa", ['error' => $err_msg]);
            return ['error' => $err_msg];
        }
    }

    public static function generate(AiModel $model, array $param, string $hookTrackId, AiCustomGenerator $generator, $demoMode = null): JsonResponse
    {
        try {
            $payload = self::buildPayload($model, $param, $hookTrackId, $generator);
            
            $respMsg = [
                'success' => $generator->success_msg,
                'queue' => str_replace('successfully generated', 'queued', $generator->success_msg),
                'failure' => str_replace('successfully generated', 'queued. Wait time is longer than expected', $generator->success_msg),
            ];

            // For video models, return queue immediately without waiting for API response
            if (in_array($model->name, ['img-vid-fusion', 'txt-vid', 'img-vid'])) {
                self::dispatchAsyncRequest($model->endpoint, $payload, $hookTrackId);

                sleep(3);
                
                return response()->json([
                    'status' => 'queue',
                    'message' => 'Video generation queued',
                    'payload' => json_encode($payload),
                    'hook_track_id' => $hookTrackId
                ]);
            }

            return self::handleGenerationRequest($model->name, $param, $hookTrackId, $respMsg, function($param) use ($payload) {
                return $payload;
            }, $model->endpoint, $demoMode);

        } catch (Exception $e) {
            Log::error("AiGen Generation Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage(),
                'payload' => null
            ], 500);
        }
    }

    private static function dispatchAsyncRequest(string $endpoint, array $payload, string $hookTrackId): void
    {
        try {
            $payload['key'] = self::ML_API_KEY;
            $postData = json_encode($payload);
            $url = self::ML_BASE_URL . $endpoint;
            
            // Log the hit before dispatching
            self::logHit($hookTrackId, $postData);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($postData)
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $response = curl_exec($ch);
            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Update the log with response and status
            DB::table('ml_hits')
                ->where('track_id', $hookTrackId)
                ->update([
                    'json' => $response,
                    'status' => $httpStatus >= 200 && $httpStatus < 300 ? 2 : 1,
                    'updated_at' => now()
                ]);
            
            Log::info("Async request dispatched", [
                'hook_track_id' => $hookTrackId, 
                'endpoint' => $endpoint,
                'http_status' => $httpStatus
            ]);
            
        } catch (Exception $e) {
            Log::error("Async dispatch failed", ['hook_track_id' => $hookTrackId, 'error' => $e->getMessage()]);
        }
    }

    public static function generate1(AiModel $model, array $param, string $hookTrackId, AiCustomGenerator $generator, $demoMode = null): JsonResponse
    {
        try {
            $payload = self::buildPayload($model, $param, $hookTrackId, $generator);
            
            $respMsg = [
                'success' => $generator->success_msg,
                'queue' => str_replace('successfully generated', 'queued', $generator->success_msg),
                'failure' => str_replace('successfully generated', 'queued. Wait time is longer than expected', $generator->success_msg),
            ];

            return self::handleGenerationRequest($model->name, $param, $hookTrackId, $respMsg, function($param) use ($payload) {
                return $payload;
            }, $model->endpoint, $demoMode);

        } catch (Exception $e) {
            Log::error("AiGen Generation Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage(),
                'payload' => null
            ], 500);
        }
    }

    private static function buildPayload(AiModel $model, array $param, string $hookTrackId, AiCustomGenerator $generator): array
    {
        $payload = [
            'track_id' => $hookTrackId,
            'webhook' => route('aigen.saveresponse')
        ];

        switch($model->name) {
            case 'txt-img':
                $payload['prompt'] = self::buildUltraImagePrompt($param);
                break;
            case 'txt-vid':
                $payload['negative_prompt'] = self::getNegativePrompt('video');
                break;
            case 'img-vid':
                $payload['prompt'] = self::buildPrompt($param);
                $payload['negative_prompt'] = self::getNegativePrompt('video');
                break;
            case 'img-vid-fusion':
                $payload['prompt'] = $param['prompt'];
                $payload['negative_prompt'] = self::getNegativePrompt('video');
                break;
            case 'text-gen-v6':
                $payload['messages'] = [
                    ['role' => 'user', 'content' => $param['prompt']]
                ];
                $payload['max_tokens'] = $param['max_tokens'] ?? 10000;
                break;
            case 'text-gen-gemma':
                $payload['messages'] = [
                    ['role' => 'user', 'content' => $param['prompt']]
                ];
                $payload['max_tokens'] = $param['max_tokens'] ?? 500;
                $payload['temperature'] = $param['temperature'] ?? 0.7;
                break;
            default:
                $payload['prompt'] = self::buildPrompt($param);
        }

        // Merge default parameters from database
        if ($model->default_parameters) {
            $payload = array_merge($payload, $model->default_parameters);
        }

        // Map input fields from parameters
        $inputFields = $model->input_fields ?? [];
        foreach ($inputFields as $field) {
            if (isset($param[$field])) {
                if (in_array($field, ['init_image', 'init_audio', 'init_video', 'base_image', 'target_face', 'swap_face', 'reference_image', 'mask_image', 'cloth_image'])) {
                    $value = $param[$field];
                    
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $payload[$field] = self::uploadFile($value, self::FILE_UPLOAD_PATH);
                    } else {
                        $payload[$field] = $value;
                    }
                } else {
                    $payload[$field] = $param[$field];
                }
            }
        }

        self::applyCustomProcessing($model->name, $param, $payload, $generator);

        return $payload;
    }

    private static function buildPayload1(AiModel $model, array $param, string $hookTrackId, AiCustomGenerator $generator): array
    {
        $payload = [
            'track_id' => $hookTrackId,
            'webhook' => route('aigen.saveresponse')
        ];

        switch($model->name) {
            case 'txt-img':
                $payload['prompt'] = self::buildUltraImagePrompt($param);
                break;
            case 'txt-vid':
                // Add negative prompt for video models
                $payload['negative_prompt'] = self::getNegativePrompt('video');
                break;
            case 'img-vid':
                $payload['prompt'] = self::buildPrompt($param);
                // Add negative prompt for video models
                $payload['negative_prompt'] = self::getNegativePrompt('video');
                break;
            case 'img-vid-fusion':
                $payload['prompt'] = $param['prompt'];
                // Add negative prompt for video models
                $payload['negative_prompt'] = self::getNegativePrompt('video');
                break;
            default:
                $payload['prompt'] = self::buildPrompt($param);
        }

        // Merge default parameters from database
        if ($model->default_parameters) {
            $payload = array_merge($payload, $model->default_parameters);
        }

        // Map input fields from parameters
        $inputFields = $model->input_fields ?? [];
        foreach ($inputFields as $field) {
            if (isset($param[$field])) {
                // Handle file uploads
                if (in_array($field, ['init_image', 'init_audio', 'init_video', 'base_image', 'target_face', 'swap_face', 'reference_image', 'mask_image', 'cloth_image'])) {
                    $value = $param[$field];
                    
                    // Check if it's a file upload (instance of UploadedFile)
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $payload[$field] = self::uploadFile($value, self::FILE_UPLOAD_PATH);
                    } else {
                        // It's already a URL or local path, use as is
                        $payload[$field] = $value;
                    }
                } else {
                    $payload[$field] = $param[$field];
                }
            }
        }

        // Apply custom processing based on model type
        self::applyCustomProcessing($model->name, $param, $payload, $generator);

        return $payload;
    }


    /**
     * Build prompt from parameters
     */
    private static function buildPrompt(array $param): string
    {
        $initialPrompt = $param['prompt'] ?? $param['description'] ?? '';
        $promptTemplate = $param['prompt_template'] ?? null;
        
        return $promptTemplate 
            ? str_replace('#PROMPT', $initialPrompt, $promptTemplate) 
            : $initialPrompt;
    }

    /**
     * Build image prompt
     */
    private static function buildImagePrompt(array $param): string
    {
        $prompt = self::buildPrompt($param);

        $keywords = ["high-resolution", "realistic", "color-grading", "smooth", "dynamic-lighting", "camera-angles", "cinematic-effects", "professional"];
        shuffle($keywords);

        return $prompt . ' [' . implode(',', array_slice($keywords, 0, 4)) . '] ';
    }

    /**
     * Build ultra image prompt with art style and other parameters
     */
    private static function buildUltraImagePrompt(array $param): string
    {
        $initialPrompt = self::buildPrompt($param);
        
        // Get art style from parameters (default to digital-art)
        $artStyle = $param['art_style'] ?? 'digital-art';
        
        // Define art style templates that completely transform the prompt
        $artStyleTemplates = [
            'cartoon' => [
                'prefix' => 'Cartoon style illustration of ',
                'keywords' => ['cartoon', 'animated style', '2D animation', 'character design', 'vibrant colors', 'bold outlines', 'comic book art', 'fun illustration'],
                'remove_words' => ['realistic', 'photographic', 'photorealistic', 'cinematic', 'film', 'TV']
            ],
            'anime' => [
                'prefix' => 'Anime artwork of ',
                'keywords' => ['anime style', 'japanese animation', 'manga art', 'cel shading', 'anime artwork', 'character design', 'expressive eyes', 'detailed hair'],
                'remove_words' => ['realistic', 'photographic', 'photorealistic', 'cinematic']
            ],
            '3d-render' => [
                'prefix' => '3D render of ',
                'keywords' => ['3D render', 'CGI', 'blender', '3D artwork', 'digital render', 'smooth lighting', 'subsurface scattering', 'PBR materials'],
                'remove_words' => ['photographic', 'painting', 'drawing']
            ],
            'digital-art' => [
                'prefix' => 'Digital art of ',
                'keywords' => ['digital art', 'concept art', 'artstation', 'trending', 'highly detailed', 'fantasy art', 'dynamic composition'],
                'remove_words' => ['realistic', 'photographic']
            ],
            'photorealistic' => [
                'prefix' => 'Photorealistic image of ',
                'keywords' => ['photorealistic', 'photographic', 'realistic', 'ultra detailed', 'cinematic lighting', 'professional photography', 'sharp focus'],
                'remove_words' => ['cartoon', 'anime', 'illustration']
            ],
            'fantasy' => [
                'prefix' => 'Fantasy art of ',
                'keywords' => ['fantasy art', 'magical', 'epic', 'mythical', 'concept art', 'dreamlike', 'ethereal', 'enchanted'],
                'remove_words' => ['realistic', 'photographic']
            ],
            'cyberpunk' => [
                'prefix' => 'Cyberpunk style of ',
                'keywords' => ['cyberpunk', 'futuristic', 'neon', 'sci-fi', 'dystopian', 'neon lighting', 'high tech low life', 'futuristic city'],
                'remove_words' => ['natural', 'organic', 'historical']
            ],
            'watercolor' => [
                'prefix' => 'Watercolor painting of ',
                'keywords' => ['watercolor painting', 'watercolor art', 'painterly', 'brush strokes', 'soft edges', 'translucent colors', 'artistic'],
                'remove_words' => ['digital', '3D', 'photographic']
            ],
            'oil-painting' => [
                'prefix' => 'Oil painting of ',
                'keywords' => ['oil painting', 'classical art', 'masterpiece', 'brushwork', 'impasto', 'old masters', 'art museum quality'],
                'remove_words' => ['digital', '3D', 'photographic']
            ],
            'pixel-art' => [
                'prefix' => 'Pixel art of ',
                'keywords' => ['pixel art', '8-bit', 'retro gaming', 'pixelated', 'low resolution', 'NES style', 'arcade game'],
                'remove_words' => ['high resolution', '4K', '8K', 'detailed', 'realistic']
            ],
            'minimalist' => [
                'prefix' => 'Minimalist illustration of ',
                'keywords' => ['minimalist', 'simple', 'clean lines', 'elegant', 'modern', 'flat design', 'geometric', 'stylized'],
                'remove_words' => ['detailed', 'complex', 'highly detailed', 'ultra detailed']
            ]
        ];
        
        // Get template for selected art style, fallback to digital-art
        $template = $artStyleTemplates[$artStyle] ?? $artStyleTemplates['digital-art'];
        
        // Clean the base prompt by removing conflicting words
        $cleanedPrompt = $initialPrompt;
        foreach ($template['remove_words'] as $word) {
            $cleanedPrompt = str_ireplace($word, '', $cleanedPrompt);
        }
        
        // Remove extra spaces
        $cleanedPrompt = preg_replace('/\s+/', ' ', trim($cleanedPrompt));
        
        // Add art style prefix
        $styledPrompt = $template['prefix'] . $cleanedPrompt;
        
        // Add quality and other enhancement parameters
        $quality = $param['quality'] ?? 85;
        $steps = $param['steps'] ?? 30;
        
        // Quality-based enhancements
        $qualityKeywords = [];
        if ($quality >= 90) {
            $qualityKeywords = ['masterpiece', 'best quality', 'ultra detailed', 'insane detail'];
        } elseif ($quality >= 80) {
            $qualityKeywords = ['high quality', 'detailed', 'sharp focus'];
        } else {
            $qualityKeywords = ['good quality', 'clear'];
        }
        
        // Steps-based enhancements
        $stepKeywords = [];
        if ($steps >= 40) {
            $stepKeywords = ['highly refined', 'perfect composition', 'exquisite details'];
        } elseif ($steps >= 25) {
            $stepKeywords = ['well refined', 'good composition', 'fine details'];
        }
        
        // Combine all keywords (art style keywords first)
        $allKeywords = array_merge($template['keywords'], $qualityKeywords, $stepKeywords);
        $allKeywords = array_unique($allKeywords);
        shuffle($allKeywords);
        
        // Build final prompt
        $finalPrompt = $styledPrompt . ' [' . implode(', ', array_slice($allKeywords, 0, 8)) . ']';
        
        // Log the prompt construction
        \Log::debug('Ultra Image Prompt Construction', [
            'hook_track_id' => $param['hook_track_id'] ?? 'unknown',
            'art_style' => $artStyle,
            'original_prompt' => $initialPrompt,
            'cleaned_prompt' => $cleanedPrompt,
            'styled_prompt' => $styledPrompt,
            'final_prompt_length' => strlen($finalPrompt),
            'selected_keywords' => array_slice($allKeywords, 0, 8)
        ]);
        
        return $finalPrompt;
    }

    /**
     * Get negative prompt by type
     */
    private static function getNegativePrompt(string $type): string
    {
        $prompts = [
            'image' => '(worst quality:2), (low quality:2), (normal quality:2), (jpeg artifacts), (blurry), (duplicate), (morbid), (mutilated), (out of frame), (extra limbs), (bad anatomy), (disfigured), (deformed), (cross-eye), (glitch), (oversaturated), (overexposed), (underexposed), (bad proportions), (bad hands), (bad feet), (cloned face), (long neck), (missing arms), (missing legs), (extra fingers), (fused fingers), (poorly drawn hands), (poorly drawn face), (mutation), (deformed eyes), watermark, text, logo, signature, grainy, tiling, censored, nsfw, ugly, blurry eyes, noisy image, bad lighting, unnatural skin, asymmetry',
            'video' => 'blurry, low quality, distorted, extra limbs, missing limbs, broken fingers, deformed, glitch, artifacts, unrealistic, low resolution, bad anatomy, duplicate, cropped, watermark, text, logo, jpeg artifacts, noisy, oversaturated, underexposed, overexposed, flicker, unstable motion, motion blur, stretched, mutated, out of frame, bad proportions'
        ];

        return $prompts[$type] ?? 'low quality';
    }

    private static function applyCustomProcessing(string $modelType, array $param, array &$payload, AiCustomGenerator $generator): void
    {
        switch ($modelType) {
            case 'txt-img':
                if (isset($param['resolution'])) {
                    $resolution = explode('x', $param['resolution']);
                    $payload['width'] = (int) $resolution[0];
                    $payload['height'] = (int) $resolution[1];
                }
                break;

            case 'text-gen-gemma':
                $payload['max_tokens'] = self::calculateMaxTokens($param['prompt'] ?? '', $param['length'] ?? 3);
                break;

            case 'text-gen-v6':
                $payload['max_tokens'] = self::calculateMaxTokens($param['prompt'] ?? '', $param['length'] ?? 3);
                break;

            case 'video':
                $payload['portrait'] = ($param['aspect_ratio'] ?? 'landscape') === 'portrait';
                break;

            case 'scene-gen':
                if (isset($param['scene_images'])) {
                    $payload['scenes'] = self::processSceneImages($param['scene_images'], $hookTrackId);
                }
                break;
        }
    }

    private static function handleGenerationRequest(
        string $type, 
        array $param, 
        string $hookTrackId, 
        array $respMsg, 
        callable $payloadBuilder, 
        $endpointOrCallback,
        $demoMode = null
    ): JsonResponse {
        try {
            // Remove this check since endpoint comes from the model
            // if (!isset($param['endpoint']) && is_string($endpointOrCallback)) {
            //     throw new Exception("Endpoint parameter is required");
            // }
    
            $payload = $payloadBuilder($param);
            $mlData = is_callable($endpointOrCallback) 
                ? $endpointOrCallback($param, $payload)
                : self::ml($endpointOrCallback, (object) $payload);
    
            return self::processApiResponse($type, $mlData, $payload, $hookTrackId, $respMsg);
    
        } catch (Exception $e) {
            Log::error("{$type}Output Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage(),
                'payload' => null
            ], 500);
        }
    }

    // Keep existing API methods
    public static function modelslab(string $endpoint, object $postArray): mixed
    {
        $url = self::ML_BASE_URL . $endpoint;
        $trackId = $postArray->track_id;
        $postData = json_encode($postArray);

        $headers = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ];

        if ($endpoint === 'uncensored-chat/v1/completions') {
            $headers[] = 'Authorization: Bearer ' . self::ML_API_KEY;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::CURL_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Log::error("CURL Error: " . $curlError, ['track_id' => $trackId]);
        }

        if (empty($response)) {
            Log::error("Empty response from API", ['track_id' => $trackId, 'http_status' => $httpStatus]);
            return 'failed';
        }

        self::logHit($trackId, $response);

        return self::handleApiResponse($response, $httpStatus);
    }

    public static function ml(string $endpoint, object $payload): mixed
    {
        $payload->key = self::ML_API_KEY;
        return self::modelslab($endpoint, $payload);
    }

    // Keep existing utility methods
    private static function uploadFile($file, $uploadPath)
    {
        if (!$file) return null;

        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $nameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);
        $safeName = Str::slug($nameWithoutExtension);
        $fileName = $safeName . '_' . time() . '.' . $extension;

        $file->move(public_path($uploadPath), $fileName);

        return URL::to('/') . "/" . $uploadPath . "/" . $fileName;
    }

    private static function calculateMaxTokens(string $prompt, int $length): int
    {
        $promptTokens = (int) ceil(strlen($prompt) / 4);
        $lengthMultipliers = [1 => 500, 2 => 1000, 3 => 2000, 4 => 4000, 5 => 8000];
        return min($lengthMultipliers[$length] ?? 2000, 4000 - $promptTokens);
    }

    private static function processApiResponse($type, $mlData, array $payload, string $hookTrackId, array $respMsg): JsonResponse
    {
        \Log::debug('=== PROCESS API RESPONSE DEBUG ===', [
            'type' => $type,
            'hook_track_id' => $hookTrackId,
            'mlData_status' => $mlData->status ?? 'NO_STATUS',
            'mlData_type' => gettype($mlData)
        ]);

        Log::info("[$hookTrackId] Processing API response", [
            'response_type' => gettype($mlData),
            'has_object' => isset($mlData->object) ? $mlData->object : 'none',
            'has_status' => isset($mlData->status) ? $mlData->status : 'none',
            'has_choices' => isset($mlData->choices) ? count($mlData->choices) : 0
        ]);

        // Build input data based on payload content
        $inputData = self::buildInputData($payload, $hookTrackId);

        if ($mlData === 'failed') {
            Log::error("[$hookTrackId] API response marked as failed", [
                'payload' => $payload,
                'response_message' => $respMsg['failure']
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $respMsg['failure'],
                'payload' => json_encode($payload),
                'input' => $inputData
            ], 200);
        }

        if ($type === 'text-gen-gemma') {
            if (isset($mlData->choices[0]->message->content)) {
                $generatedText = $mlData->choices[0]->message->content;
                
                $path = self::USER_PROJECTS_PATH . '/' . Auth::id();
                $fileName = 'text_result_' . $hookTrackId . '.txt';
                $filePath = $path . '/' . $fileName;
                
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                
                file_put_contents($filePath, $generatedText);
                self::updateHitStatus($hookTrackId, 2);
                
                return response()->json([
                    'status' => 'success',
                    'message' => $respMsg['success'],
                    'text' => $generatedText,
                    'output' => '/' . $filePath,
                    'input' => $inputData
                ], 200);
            }
        }
        // Handle text completion response format for v6
        if ($type === 'text-gen-v6') {
            Log::info("[$hookTrackId] Processing text completion response", [
                'model' => $mlData->model ?? 'unknown',
                'status' => $mlData->status ?? 'unknown',
                'has_message' => isset($mlData->message),
                'has_choices' => isset($mlData->choices),
                'has_text' => isset($mlData->text)
            ]);

            // Try multiple possible response formats
            $generatedText = null;
            
            // Format 1: Direct message field (from your response)
            if (isset($mlData->message) && !empty($mlData->message)) {
                $generatedText = $mlData->message;
                Log::info("[$hookTrackId] Using message field", ['text_length' => strlen($generatedText)]);
            }
            // Format 2: Choices array format (legacy)
            elseif (!empty($mlData->choices) && isset($mlData->choices[0]->text)) {
                $generatedText = $mlData->choices[0]->text;
                Log::info("[$hookTrackId] Using choices[0]->text field", ['text_length' => strlen($generatedText)]);
            }
            // Format 3: Direct text field
            elseif (isset($mlData->text)) {
                $generatedText = $mlData->text;
                Log::info("[$hookTrackId] Using text field", ['text_length' => strlen($generatedText)]);
            }
            // Format 4: Response with content field
            elseif (isset($mlData->content)) {
                $generatedText = $mlData->content;
                Log::info("[$hookTrackId] Using content field", ['text_length' => strlen($generatedText)]);
            }

            if ($generatedText) {
                $textLength = strlen($generatedText);
                
                Log::info("[$hookTrackId] Text completion successful", [
                    'text_length' => $textLength,
                    'text_preview' => substr($generatedText, 0, 100) . '...'
                ]);
                
                // Save the generated text to a file
                $path = self::USER_PROJECTS_PATH . '/' . Auth::id();
                $fileName = 'text_result_' . $hookTrackId . '.txt';
                $filePath = $path . '/' . $fileName;
                
                // Ensure directory exists
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                    Log::info("[$hookTrackId] Created user directory", ['path' => $path]);
                }
                
                // Save text to file
                $saveResult = file_put_contents($filePath, $generatedText);
                
                if ($saveResult === false) {
                    Log::error("[$hookTrackId] Failed to save text to file", [
                        'file_path' => $filePath,
                        'text_length' => $textLength
                    ]);
                    
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to save generated text',
                        'payload' => json_encode($payload),
                        'input' => $inputData
                    ], 200);
                }
                
                Log::info("[$hookTrackId] Text saved successfully", [
                    'file_path' => $filePath,
                    'file_size' => $saveResult
                ]);
                
                self::updateHitStatus($hookTrackId, 2);
                
                return response()->json([
                    'status' => 'success',
                    'message' => $respMsg['success'],
                    'payload' => json_encode($payload),
                    'output' => '/' . $filePath,
                    'text' => $generatedText,
                    'input' => $inputData
                ], 200);
            } else {
                Log::warning("[$hookTrackId] Text completion response missing all expected fields", [
                    'response_structure' => json_encode($mlData),
                    'available_keys' => array_keys((array)$mlData)
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid response format from API - no text content found',
                    'payload' => json_encode($payload),
                    'input' => $inputData
                ], 200);
            }
        }

        // Handle text completion response format for code generation
        if ($type === 'code-gen') {
            Log::info("[$hookTrackId] Processing code completion response", [
                'model' => $mlData->model ?? 'unknown',
                'choices_count' => count($mlData->choices ?? []),
                'usage_tokens' => $mlData->usage->total_tokens ?? 0,
                'finish_reason' => $mlData->choices[0]->finish_reason ?? 'unknown'
            ]);

            if (!empty($mlData->choices) && isset($mlData->choices[0]->text)) {
                $generatedCode = $mlData->choices[0]->text;
                $codeLength = strlen($generatedCode);
                
                Log::info("[$hookTrackId] Code completion successful", [
                    'code_length' => $codeLength,
                    'finish_reason' => $mlData->choices[0]->finish_reason ?? 'unknown',
                    'code_preview' => substr($generatedCode, 0, 100) . '...'
                ]);
                
                // Determine file extension based on language
                $language = $param['language'] ?? 'python';
                $fileExtensions = [
                    'python' => 'py',
                    'javascript' => 'js',
                    'typescript' => 'ts',
                    'java' => 'java',
                    'csharp' => 'cs',
                    'cpp' => 'cpp',
                    'go' => 'go',
                    'rust' => 'rs',
                    'php' => 'php',
                    'ruby' => 'rb',
                    'swift' => 'swift',
                    'kotlin' => 'kt',
                    'r' => 'r',
                    'sql' => 'sql',
                    'html' => 'html',
                    'shell' => 'sh'
                ];
                
                $extension = $fileExtensions[$language] ?? 'txt';
                $fileName = 'code_result_' . $hookTrackId . '.' . $extension;
                
                // Save the generated code to a file
                $path = self::USER_PROJECTS_PATH . '/' . Auth::id();
                $filePath = $path . '/' . $fileName;
                
                // Ensure directory exists
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                    Log::info("[$hookTrackId] Created user directory", ['path' => $path]);
                }
                
                // Save code to file
                $saveResult = file_put_contents($filePath, $generatedCode);
                
                if ($saveResult === false) {
                    Log::error("[$hookTrackId] Failed to save code to file", [
                        'file_path' => $filePath,
                        'code_length' => $codeLength
                    ]);
                    
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to save generated code',
                        'payload' => json_encode($payload),
                        'input' => $inputData
                    ], 200);
                }
                
                Log::info("[$hookTrackId] Code saved successfully", [
                    'file_path' => $filePath,
                    'file_size' => $saveResult,
                    'language' => $language,
                    'extension' => $extension
                ]);
                
                self::updateHitStatus($hookTrackId, 2);
                
                return response()->json([
                    'status' => 'success',
                    'message' => $respMsg['success'],
                    'payload' => json_encode($payload),
                    'output' => '/'.$filePath,
                    'text' => $generatedCode,
                    'input' => $inputData
                ], 200);
            } else {
                Log::warning("[$hookTrackId] Code completion response missing choices or text", [
                    'choices_structure' => json_encode($mlData->choices ?? []),
                    'response_object' => json_encode($mlData)
                ]);
            }
        }

        // Handle legacy success format (for other AI tools)
        if (isset($mlData->status) && $mlData->status === 'success') {
            Log::info("[$hookTrackId] Processing legacy success response", [
                'output_type' => gettype($mlData->output),
                'output_value' => is_array($mlData->output) ? 'array[' . count($mlData->output) . ']' : $mlData->output
            ]);
            
            $path = self::USER_PROJECTS_PATH . '/' . Auth::id();
            $outputUrl = is_array($mlData->output) ? $mlData->output[0] : $mlData->output;
            
            Log::info("[$hookTrackId] Validating remote file", ['output_url' => $outputUrl]);
            
            $validated = self::checkRemoteFile($outputUrl, $hookTrackId);
            if ($validated) {
                Log::info("[$hookTrackId] Remote file validated successfully", [
                    'file_size' => $validated['file_size'] ?? 'unknown',
                    'file_type' => $validated['file_type'] ?? 'unknown'
                ]);
                
                $resultPath = self::convertLinksToLocal($outputUrl, $path, $hookTrackId, $validated);
                
                Log::info("[$hookTrackId] File converted to local path", [
                    'local_path' => $resultPath,
                    'original_url' => $outputUrl
                ]);
                
                self::updateHitStatus($hookTrackId, 2);
                
                return response()->json([
                    'status' => 'success',
                    'message' => $respMsg['success'],
                    'payload' => json_encode($payload),
                    'output' => $resultPath,
                    'input' => $inputData
                ], 200);
            } else {
                Log::error("[$hookTrackId] Remote file validation failed", [
                    'output_url' => $outputUrl,
                    'hook_track_id' => $hookTrackId
                ]);
            }
        }

        // Log unknown response format
        Log::warning("[$hookTrackId] Unknown API response format - treating as queued", [
            'response_data' => json_encode($mlData),
            'response_type' => gettype($mlData),
            'object_property' => isset($mlData->object) ? $mlData->object : 'not_set',
            'status_property' => isset($mlData->status) ? $mlData->status : 'not_set'
        ]);

        self::updateHitStatus($hookTrackId, 1);
        return response()->json([
            'status' => 'queue',
            'message' => $respMsg['queue'],
            'payload' => json_encode($payload),
            'input' => $inputData
        ], 200);
    }

    /**
     * Build input data based on payload content
     * For text-only: returns the text string
     * For file uploads: returns JSON object with prompt and file URLs
     */
    private static function buildInputData(array $payload, string $hookTrackId): string|null
    {
        // Check if this is a file upload request by looking for file-related fields
        $hasFiles = false;
        $fileFields = ['init_image', 'init_audio', 'init_video', 'base_image', 'target_face', 'swap_face', 'reference_image', 'mask_image', 'cloth_image'];
        
        foreach ($fileFields as $field) {
            if (isset($payload[$field]) && !empty($payload[$field])) {
                $hasFiles = true;
                break;
            }
        }
        
        // If it's a file upload, create structured JSON
        if ($hasFiles) {
            $structuredData = [];
            
            // Add text prompt if available
            if (isset($payload['prompt']) && !empty($payload['prompt'])) {
                $structuredData['prompt'] = $payload['prompt'];
            }
            
            // Add file URLs
            foreach ($fileFields as $field) {
                if (isset($payload[$field]) && !empty($payload[$field])) {
                    $structuredData[$field] = $payload[$field];
                }
            }
            
            // Add other relevant text fields
            $textFields = ['description', 'language', 'speed', 'title', 'aspect_ratio', 'resolution', 'writing_style', 'tone_intensity', 'output_length', 'target_audience'];
            foreach ($textFields as $field) {
                if (isset($payload[$field]) && !empty($payload[$field])) {
                    $structuredData[$field] = $payload[$field];
                }
            }
            
            Log::info("[$hookTrackId] Built structured input data", [
                'has_files' => true,
                'file_count' => count($structuredData) - (isset($structuredData['prompt']) ? 1 : 0),
                'has_prompt' => isset($structuredData['prompt'])
            ]);
            
            return json_encode($structuredData);
        }
        
        // // For text-only requests, return the prompt/description directly
        // $textInput = $payload['prompt'] ?? $payload['description'] ?? '';
        
        // Log::info("[$hookTrackId] Built text-only input data", [
        //     'has_files' => false,
        //     'text_length' => strlen($textInput)
        // ]);
        
        return NULL;
    }

    private static function handleApiResponse($response, int $httpStatus): mixed
    {
        if ($response === 'error code: 524') {
            return 'failed';
        }

        $responseContent = json_decode($response);
        
        if (isset($responseContent->status) && $responseContent->status === 'error') {
            return 'failed';
        }

        if (in_array($httpStatus, self::SUCCESS_HTTP_CODES)) {
            return $response ? $responseContent : $httpStatus;
        }

        return $httpStatus;
    }

    private static function logHit(string $trackId, string $response): void
    {
        DB::table('ml_hits')->insert([
            'track_id' => $trackId,
            'json' => $response
        ]);
        
        Log::info("ModelsLab API Hit [{$trackId}]: " . substr($response, 0, 200));
    }

    
    /**
     * Check remote file availability
     */
    public static function checkRemoteFile(string $url, ?string $trackId = null): array|bool
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_HTTPHEADER => [
                'Accept: */*',
                'Referer: ' . env('APP_ORIGIN', 'https://localhost') . '/',
                'Origin: ' . env('APP_ORIGIN', 'https://localhost'),
            ]
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        $isValid = $httpCode === 200 && (
            str_starts_with($contentType, 'video/') ||
            str_starts_with($contentType, 'audio/') ||
            str_starts_with($contentType, 'image/')
        );

        if (!$isValid && $trackId) {
            Log::info("[$trackId] File not ready. HTTP: $httpCode, Type: $contentType, URL: $url");
            return false;
        }

        return $isValid ? ['contentType' => $contentType] : false;
    }

    /**
     * Convert remote links to local storage
     */
    public static function convertLinksToLocal(string $link, string $path, string $hookTrackId, array $validated = []): string
    {
        if (empty($validated)) {
            $validated = self::checkRemoteFile($link, $hookTrackId);
            if (!$validated) {
                throw new Exception("[$hookTrackId] Remote fetch failed for: $link");
            }
        }

        $contentType = $validated['contentType'] ?? 'image/jpeg';
        $ext = explode('/', $contentType)[1] ?? 'jpg';

        $ch = curl_init($link);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $fileContent = curl_exec($ch);
        curl_close($ch);

        if (!$fileContent) {
            throw new Exception("[$hookTrackId] Failed to download file from: $link");
        }

        $directory = public_path($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $fileName = $path . '/' . Str::random(10) . '.' . $ext;
        $fullPath = public_path($fileName);
        
        if (file_put_contents($fullPath, $fileContent) === false) {
            throw new Exception("[$hookTrackId] Failed to save file: $fullPath");
        }

        return '/' . $fileName;
    }

    /**
     * Call Replicate API
     */
    private static function callReplicateApi(string $model, array $payload): object
    {
        $url = self::REPLICATE_BASE_URL . $model . '/predictions';
        $trackId = $payload['track_id'];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['input' => $payload]),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . env('RP_KEY'),
                "Content-Type: application/json"
            ]
        ]);

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        self::logHit($trackId, $response);
        Log::info("Replicate API [{$httpStatus}]: " . substr($response, 0, 200));

        $responseContent = json_decode($response);

        if ($httpStatus >= 200 && $httpStatus < 300 && isset($responseContent->id)) {
            return (object)['status' => 'queued', 'id' => $responseContent->id];
        }

        return (object)['status' => 'error'];
    }

    public static function processSceneImages(array $sceneImages, string $hookTrackId): array
    {
        $scenes = [];
        $sceneCount = count($sceneImages);
        $framesPerScene = floor(200 / $sceneCount);
        
        foreach ($sceneImages as $index => $image) {
            $imageUrl = self::uploadFile($image, self::FILE_UPLOAD_PATH);
            $startFrame = $index * $framesPerScene;
            
            $scenes[] = [
                'img_url' => $imageUrl,
                'start_frame_index' => (int)$startFrame
            ];
            
            Log::debug('Processed scene', [
                'hook_track_id' => $hookTrackId,
                'scene_index' => $index,
                'start_frame' => $startFrame
            ]);
        }

        return $scenes;
    }

    
    /**
     * Update hit status
     */
    private static function updateHitStatus(string $trackId, int $status): void
    {
        DB::table('ml_hits')
            ->where('track_id', $trackId)
            ->update(['status' => $status, 'updated_at' => now()]);
    }

    /**
     * Get demo response
     */
    private static function getDemoResponse(): object
    {
        sleep(2);
        return (object) [
            'status' => 'success',
            'output' => ['https://aicgivfxstudio.softprohub.com/ai-projects/1/demo_output.png']
        ];
    }

    /**
     * Generate AI content for Pro Studio (no custom generator required)
     */
    public static function generateForProStudio(AiModel $model, array $param, string $hookTrackId, string $successMessage = null): JsonResponse
    {
        try {
            $respMsg = [
                'success' => $successMessage ?? 'Generation completed successfully',
                'queue' => 'Generation queued. Processing may take a moment.',
                'failure' => 'Generation failed. Please try again.'
            ];

            $payload = [
                'track_id' => $hookTrackId,
                'webhook' => route('aigen.saveresponse')
            ];

            // Add prompt
            if (isset($param['prompt'])) {
                $payload['prompt'] = $param['prompt'];
            }

            // Add description if present
            if (isset($param['description']) && !isset($payload['prompt'])) {
                $payload['prompt'] = $param['description'];
            }

            // Add other parameters from input
            $allowedParams = ['max_tokens', 'temperature', 'language', 'speed', 'ai_model', 'model', 'width', 'height', 'samples', 'negative_prompt', 'fps', 'portrait'];
            foreach ($allowedParams as $key) {
                if (isset($param[$key])) {
                    $payload[$key] = $param[$key];
                }
            }

            // Merge default parameters from model
            if ($model->default_parameters) {
                $payload = array_merge($payload, $model->default_parameters);
            }

            // Handle file uploads for voice-clone
            if ($model->name === 'voice-clone' && isset($param['init_audio'])) {
                $payload['init_audio'] = self::uploadFile($param['init_audio'], self::FILE_UPLOAD_PATH);
            }

            // In AiGen.php, add to generateForProStudio() method:
            if ($model->name === 'txt-music') {
                $payload['sampling_rate'] = '48000';
                $payload['base64'] = false;
                $payload['temp'] = false;
            }

            if ($model->name === 'txt-sfx') {
                $payload['temp'] = false;
            }

            return self::handleGenerationRequest(
                $model->name,
                $param,
                $hookTrackId,
                $respMsg,
                function($param) use ($payload) {
                    return $payload;
                },
                $model->endpoint
            );

        } catch (Exception $e) {
            Log::error("Pro Studio Generation Error [{$hookTrackId}]: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage(),
                'payload' => null
            ], 500);
        }
    }
}