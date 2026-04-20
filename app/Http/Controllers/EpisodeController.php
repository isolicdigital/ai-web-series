<?php

namespace App\Http\Controllers;

use App\Models\WebSeries;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EpisodeController extends Controller
{
    /**
     * Display a listing of episodes for a series.
     */
    public function index($seriesId)
    {
        $series = WebSeries::where('user_id', auth()->id())->findOrFail($seriesId);
        $episodes = $series->episodes()->with('scenes')->orderBy('episode_number')->paginate(10);
        
        return view('episodes.index', compact('series', 'episodes'));
    }

    /**
     * Show the form for creating a new episode.
     */
    public function create($seriesId)
    {
        $series = WebSeries::where('user_id', auth()->id())->findOrFail($seriesId);
        return view('episodes.create', compact('series'));
    }

    /**
     * Store a newly created episode in storage.
     */
    public function store(Request $request, $seriesId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'concept' => 'nullable|string',
            'episode_number' => 'required|integer|min:1'
        ]);

        $series = WebSeries::where('user_id', auth()->id())->findOrFail($seriesId);
        
        // Check if episode number already exists
        $exists = $series->episodes()->where('episode_number', $request->episode_number)->exists();
        if ($exists) {
            return back()->withErrors(['episode_number' => 'Episode number already exists for this series'])->withInput();
        }
        
        $episode = $series->episodes()->create([
            'title' => $request->title,
            'concept' => $request->concept,
            'episode_number' => $request->episode_number,
            'status' => 'draft',
            'user_id' => auth()->id()
        ]);
        
        return redirect()->route('web-series.show', $seriesId)
            ->with('success', 'Episode created successfully!');
    }

    /**
     * Display the specified episode.
     */
    public function show($seriesId, $episodeId)
    {
        $series = WebSeries::where('user_id', auth()->id())->findOrFail($seriesId);
        $episode = $series->episodes()->with('scenes')->findOrFail($episodeId);
        
        return view('episodes.show', compact('series', 'episode'));
    }

    /**
     * Show the form for editing the specified episode.
     */
    public function edit($seriesId, $episodeId)
    {
        $series = WebSeries::where('user_id', auth()->id())->findOrFail($seriesId);
        $episode = $series->episodes()->findOrFail($episodeId);
        
        return view('episodes.edit', compact('series', 'episode'));
    }

    /**
     * Update the specified episode in storage.
     */
    public function update(Request $request, $seriesId, $episodeId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'concept' => 'nullable|string'
        ]);

        $series = WebSeries::where('user_id', auth()->id())->findOrFail($seriesId);
        $episode = $series->episodes()->findOrFail($episodeId);
        
        $episode->update([
            'title' => $request->title,
            'concept' => $request->concept
        ]);
        
        return redirect()->route('web-series.show', $seriesId)
            ->with('success', 'Episode updated successfully!');
    }

    /**
     * Remove the specified episode from storage.
     */
    public function destroy($seriesId, $episodeId)
    {
        $series = WebSeries::where('user_id', auth()->id())->findOrFail($seriesId);
        $episode = $series->episodes()->findOrFail($episodeId);
        
        // Delete associated scenes first
        foreach ($episode->scenes as $scene) {
            // Delete video files if they exist
            if ($scene->video_url && Storage::disk('public')->exists($scene->video_url)) {
                Storage::disk('public')->delete($scene->video_url);
            }
            // Delete image files if they exist
            if ($scene->generated_image_url && Storage::disk('public')->exists($scene->generated_image_url)) {
                Storage::disk('public')->delete($scene->generated_image_url);
            }
            $scene->delete();
        }
        
        // Delete final episode video if exists
        if ($episode->final_video_url && Storage::disk('public')->exists($episode->final_video_url)) {
            Storage::disk('public')->delete($episode->final_video_url);
        }
        
        $episode->delete();
        
        return redirect()->route('web-series.show', $seriesId)
            ->with('success', 'Episode deleted successfully!');
    }

    /**
     * Merge all scenes of an episode into a single video.
     */
    public function merge(Request $request)
    {
        try {
            $request->validate([
                'series_id' => 'required|exists:web_series,id',
                'music_file' => 'nullable|file|mimes:mp3,wav,ogg|max:10240'
            ]);

            $series = WebSeries::where('user_id', auth()->id())->findOrFail($request->series_id);
            
            // Get the current episode (the one being viewed)
            $episode = $series->episodes()->latest()->first();
            
            if (!$episode) {
                return response()->json([
                    'success' => false,
                    'message' => 'No episode found for this series'
                ], 404);
            }

            // Check if all scenes have videos
            $allScenesHaveVideo = $episode->scenes->every(function ($scene) {
                return !is_null($scene->video_url);
            });

            if (!$allScenesHaveVideo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not all scenes have generated videos yet'
                ], 400);
            }

            // For demo user (ID 141), return a simulated response
            if (auth()->check() && auth()->user()->demo_mode == true) {
                // Simulate merge for demo
                $demoVideoUrl = asset('demo/merged_episode_' . $episode->id . '.mp4');
                
                // Update episode status for demo
                $episode->update([
                    'demo_final_video_url' => $demoVideoUrl,
                    'status' => 'completed'
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Episode merged successfully (Demo)',
                    'video_url' => $demoVideoUrl
                ]);
            }

            // For real users, process the merge
            // Get all video paths in order
            $videoPaths = $episode->scenes
                ->sortBy('scene_number')
                ->pluck('video_url')
                ->map(function($path) {
                    return Storage::disk('public')->path($path);
                })
                ->toArray();
            
            // Process music file if provided
            $musicPath = null;
            if ($request->hasFile('music_file')) {
                $musicFile = $request->file('music_file');
                $musicFileName = 'episode_music_' . $episode->id . '_' . time() . '.' . $musicFile->getClientOriginalExtension();
                $musicPath = $musicFile->storeAs('episode_music', $musicFileName, 'public');
                $musicPath = Storage::disk('public')->path($musicPath);
            }
            
            // Implement actual video merging logic
            $finalVideoPath = $this->mergeVideosWithFFmpeg($videoPaths, $musicPath, $episode->id);
            
            if ($finalVideoPath) {
                // Update episode with final video URL
                $episode->update([
                    'final_video_url' => $finalVideoPath,
                    'status' => 'completed'
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Episode merged successfully',
                    'video_url' => Storage::url($finalVideoPath)
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to merge videos'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Episode merge error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Merge videos using FFmpeg.
     */
    private function mergeVideosWithFFmpeg(array $videoPaths, $musicPath = null, $episodeId = null)
    {
        // Create a temporary file for the concatenation list
        $concatFile = tempnam(sys_get_temp_dir(), 'concat_');
        $fileList = [];
        
        foreach ($videoPaths as $index => $videoPath) {
            if (file_exists($videoPath)) {
                $fileList[] = "file '" . addslashes($videoPath) . "'";
            }
        }
        
        if (empty($fileList)) {
            Log::error('No valid video files found for merging');
            return null;
        }
        
        file_put_contents($concatFile, implode("\n", $fileList));
        
        // Output path for merged video
        $outputFileName = 'merged_episodes/merged_episode_' . $episodeId . '_' . time() . '.mp4';
        $outputPath = Storage::disk('public')->path($outputFileName);
        
        // Create directory if it doesn't exist
        $outputDir = dirname($outputPath);
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        // Build FFmpeg command
        $ffmpegCommand = 'ffmpeg -f concat -safe 0 -i ' . escapeshellarg($concatFile) . ' -c copy ';
        
        // Add audio if provided
        if ($musicPath && file_exists($musicPath)) {
            $ffmpegCommand .= '-i ' . escapeshellarg($musicPath) . ' -c:v copy -c:a aac -map 0:v:0 -map 1:a:0 -shortest ';
        }
        
        $ffmpegCommand .= '-y ' . escapeshellarg($outputPath) . ' 2>&1';
        
        // Execute FFmpeg command
        exec($ffmpegCommand, $output, $returnCode);
        
        // Clean up temporary file
        unlink($concatFile);
        
        if ($returnCode === 0 && file_exists($outputPath)) {
            return $outputFileName;
        }
        
        Log::error('FFmpeg merge failed: ' . implode("\n", $output));
        return null;
    }

    /**
     * Create full episode (alternative method).
     */
    public function createFullEpisode(Request $request)
    {
        return $this->merge($request);
    }

    /**
     * Get full video for an episode.
     */
    public function getFullVideo($seriesId, $episodeId)
    {
        try {
            $series = WebSeries::where('user_id', auth()->id())->findOrFail($seriesId);
            $episode = $series->episodes()->findOrFail($episodeId);
            
            // Check if episode is completed and merged
            if ($episode->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Episode is not ready yet'
                ], 400);
            }
            
            // Get video URL
            $videoUrl = null;
            if (auth()->check() && auth()->user()->demo_mode == true && $episode->demo_final_video_url) {
                $videoUrl = $episode->demo_final_video_url;
            } elseif ($episode->final_video_url) {
                $videoUrl = Storage::url($episode->final_video_url);
            }
            
            if (!$videoUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'video_url' => $videoUrl,
                'title' => $episode->title,
                'episode_number' => $episode->episode_number,
                'scenes_count' => $episode->scenes->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get full video error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Episode not found'
            ], 404);
        }
    }
}