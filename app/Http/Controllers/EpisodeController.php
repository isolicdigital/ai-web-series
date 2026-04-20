<?php
// app/Http/Controllers/EpisodeController.php

namespace App\Http\Controllers;

use App\Models\WebSeries;
use App\Models\Episode;
use App\Models\Scene;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;

class EpisodeController extends Controller
{
    public function createFullEpisode(Request $request)
    {
        try {
            $request->validate([
                'series_id' => 'required|integer|exists:web_series,id',
                'music_url' => 'nullable|string'
            ]);

            $seriesId = $request->series_id;
            $musicUrl = $request->music_url;
            
            // Get all scenes with videos
            $scenes = Scene::where('web_series_id', $seriesId)
                ->whereNotNull('video_url')
                ->orderBy('scene_number')
                ->get();
            
            if ($scenes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No videos found to merge'
                ], 400);
            }
            
            // Update episode status
            $episode = Episode::where('web_series_id', $seriesId)
                ->where('episode_number', 1)
                ->first();
            
            if ($episode) {
                $episode->update([
                    'status' => 'processing',
                    'merged_video_status' => 'processing'
                ]);
            }
            
            // Dispatch job to merge videos (this will run in background)
            dispatch(new \App\Jobs\MergeEpisodeVideos($seriesId, $scenes->pluck('id')->toArray(), $musicUrl));
            
            return response()->json([
                'success' => true,
                'message' => 'Episode creation started! You will be notified when ready.',
                'processing' => true
            ]);
            
        } catch (\Exception $e) {
            Log::error('Create full episode error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create episode: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function checkEpisodeStatus($seriesId)
    {
        try {
            $episode = Episode::where('web_series_id', $seriesId)
                ->where('episode_number', 1)
                ->first();
            
            if (!$episode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Episode not found'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'status' => $episode->merged_video_status ?? 'pending',
                'merged_video_url' => $episode->merged_video_url ? asset($episode->merged_video_url) : null,
                'message' => $episode->merged_video_error ?? null
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function downloadMergedEpisode($seriesId)
    {
        try {
            $episode = Episode::where('web_series_id', $seriesId)
                ->where('episode_number', 1)
                ->first();
            
            if (!$episode || !$episode->merged_video_url) {
                return response()->json([
                    'success' => false,
                    'message' => 'Episode not found'
                ], 404);
            }
            
            $filePath = storage_path('app/public/' . $episode->merged_video_url);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }
            
            return response()->download($filePath, 'episode_' . $seriesId . '.mp4');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}