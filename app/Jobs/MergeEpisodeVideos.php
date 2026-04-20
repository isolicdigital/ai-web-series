<?php
// app/Jobs/MergeEpisodeVideos.php

namespace App\Jobs;

use App\Models\WebSeries;
use App\Models\Episode;
use App\Models\Scene;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;

class MergeEpisodeVideos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $seriesId;
    protected $sceneIds;
    protected $musicUrl;
    
    public function __construct($seriesId, $sceneIds, $musicUrl = null)
    {
        $this->seriesId = $seriesId;
        $this->sceneIds = $sceneIds;
        $this->musicUrl = $musicUrl;
    }
    
    public function handle()
    {
        try {
            $episode = Episode::where('web_series_id', $this->seriesId)
                ->where('episode_number', 1)
                ->first();
            
            $scenes = Scene::whereIn('id', $this->sceneIds)
                ->orderBy('scene_number')
                ->get();
            
            $videoPaths = [];
            foreach ($scenes as $scene) {
                $videoPath = storage_path('app/public/' . str_replace('/storage/', '', $scene->video_url));
                if (file_exists($videoPath)) {
                    $videoPaths[] = $videoPath;
                }
            }
            
            // Merge videos using FFmpeg
            $outputPath = storage_path("app/public/episodes/merged_{$this->seriesId}_" . time() . ".mp4");
            $this->mergeVideos($videoPaths, $outputPath, $this->musicUrl);
            
            // Update episode with merged video URL
            $episode->update([
                'merged_video_url' => 'episodes/' . basename($outputPath),
                'merged_video_status' => 'completed',
                'merged_video_completed_at' => now()
            ]);
            
            Log::info("Episode merged successfully for series {$this->seriesId}");
            
        } catch (\Exception $e) {
            Log::error('Merge episode videos error: ' . $e->getMessage());
            
            if (isset($episode)) {
                $episode->update([
                    'merged_video_status' => 'failed',
                    'merged_video_error' => $e->getMessage()
                ]);
            }
        }
    }
    
    private function mergeVideos($videoPaths, $outputPath, $musicUrl = null)
    {
        // Create file list for FFmpeg
        $listFile = tempnam(sys_get_temp_dir(), 'filelist');
        $listContent = '';
        foreach ($videoPaths as $path) {
            $listContent .= "file '" . str_replace("'", "'\\''", $path) . "'\n";
        }
        file_put_contents($listFile, $listContent);
        
        $ffmpeg = FFMpeg::create();
        
        // Merge videos
        $command = "ffmpeg -f concat -safe 0 -i {$listFile} -c copy {$outputPath}";
        exec($command, $output, $returnCode);
        
        unlink($listFile);
        
        if ($returnCode !== 0) {
            throw new \Exception("Failed to merge videos");
        }
        
        // Add music if provided
        if ($musicUrl && file_exists($musicUrl)) {
            $tempOutput = tempnam(sys_get_temp_dir(), 'temp');
            $command = "ffmpeg -i {$outputPath} -i {$musicUrl} -filter_complex '[0:a]aformat=fltp,volume=0.3[a0];[1:a]aformat=fltp,volume=0.7[a1];[a0][a1]amix=inputs=2:duration=longest[a]' -map 0:v -map '[a]' -c:v copy -c:a aac -shortest {$tempOutput}";
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($tempOutput)) {
                rename($tempOutput, $outputPath);
            }
        }
    }
}