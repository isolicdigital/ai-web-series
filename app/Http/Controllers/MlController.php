<?php

namespace App\Http\Controllers;

use App\Models\ComedyVideo;
use App\Models\ComedyJoke;
use App\Helpers\AiGen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class MlController extends Controller
{
    /**
     * Main Webhook Entry Point for Comedy System
     */
    public function saveResponse(Request $request)
    {
        $trackId = null;
        
        try {
            if ($request->isMethod('post')) {
                $trackId = $request->track_id;
                
                // Store webhook hit for history/debugging
                DB::table('ml_hits')->insert([
                    'track_id' => $trackId,
                    'status' => ($request->status == 'success' ? 2 : 1),
                    'json' => json_encode($request->all()),
                    'created_at' => now()
                ]);

                // Process based on track_id prefix
                if (str_starts_with($trackId, 'joke_')) {
                    $this->processJokeWebhook($request->status, $trackId, $request->output[0] ?? null, $request->text ?? null);
                } elseif (str_starts_with($trackId, 'comvid_')) {
                    $this->processVideoWebhook($request->status, $trackId, $request->output[0] ?? null);
                } else {
                    // Legacy or other types
                    $this->processWebhookStatus($request->status, $trackId, $request->output[0] ?? null);
                }
            }

            return response()->json(['message' => 'Success'], 200);

        } catch (Exception $e) {
            Log::error("[$trackId] SaveResponse Error: " . $e->getMessage());
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    /**
     * Process joke generation webhook
     */
    private function processJokeWebhook(string $status, string $trackId, ?string $outputUrl, ?string $generatedText): void
    {
        if ($status === 'success' && $generatedText) {
            // Extract joke_id from track_id (format: joke_{joke_id}_{timestamp})
            $parts = explode('_', $trackId);
            $jokeId = $parts[1] ?? null;
            
            if ($jokeId) {
                ComedyJoke::where('id', $jokeId)->update([
                    'generated_joke' => $generatedText,
                    'status' => 'completed'
                ]);
                
                Log::info("Joke updated successfully", ['joke_id' => $jokeId, 'track_id' => $trackId]);
            }
        } elseif ($status === 'error') {
            $parts = explode('_', $trackId);
            $jokeId = $parts[1] ?? null;
            
            if ($jokeId) {
                ComedyJoke::where('id', $jokeId)->update([
                    'status' => 'failed'
                ]);
            }
        }
    }

    /**
     * Process video generation webhook
     */
    private function processVideoWebhook(string $status, string $trackId, ?string $outputUrl): void
    {
        Log::info("Video webhook received", [
            'track_id' => $trackId, 
            'status' => $status,
            'output_url' => $outputUrl
        ]);
        
        $parts = explode('_', $trackId);
        $videoId = $parts[2] ?? null;
        
        Log::info("Extracted video ID", ['video_id' => $videoId, 'parts' => $parts]);
        
        if (!$videoId) {
            Log::warning("Could not extract video_id from track_id: {$trackId}");
            return;
        }
        
        if ($status === 'success' && $outputUrl) {
            $path = 'comedy-videos/' . $videoId;
            $localPath = $this->downloadFile($outputUrl, $path, $trackId);
            
            if ($localPath) {
                ComedyVideo::where('id', $videoId)->update([
                    'video_url' => $localPath,
                    'processing_status' => 'completed'
                ]);
                
                Log::info("Video updated successfully", ['video_id' => $videoId, 'track_id' => $trackId]);
            }
        } elseif ($status === 'error') {
            ComedyVideo::where('id', $videoId)->update([
                'processing_status' => 'failed'
            ]);
        } elseif ($status === 'processing') {
            ComedyVideo::where('id', $videoId)->update([
                'processing_status' => 'processing'
            ]);
        }
    }

    /**
     * Legacy webhook processing for other types
     */
    private function processWebhookStatus(string $status, string $trackId, ?string $outputUrl): void
    {
        // Legacy handling - can be expanded if needed
        Log::info("Legacy webhook received", ['track_id' => $trackId, 'status' => $status]);
    }

    /**
     * Download remote file to local storage
     */
    private function downloadFile(?string $url, string $path, string $trackId): ?string
    {
        if (!$url) return null;

        try {
            $resultPath = AiGen::convertLinksToLocal($url, $path, $trackId);
            return $resultPath;
        } catch (Exception $e) {
            Log::error("Download failed for $trackId: " . $e->getMessage());
            return null;
        }
    }
}