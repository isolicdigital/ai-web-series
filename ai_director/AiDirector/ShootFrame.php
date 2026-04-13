<?php
// app/Models/AiDirector/ShootFrame.php

namespace App\Models\AiDirector;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShootFrame extends Model
{
    protected $table = 'ai_director_shoot_frames';
    
    protected $fillable = [
        'project_id',
        'scene_number',
        'frame_number',
        'prompt',
        'motion_intensity',
        'image_path',
        'video_path',
        'status_image',
        'status_video',
        'payload_image',
        'payload_video',
        'track_id',
        'video_track_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ShootProject::class, 'project_id');
    }
}