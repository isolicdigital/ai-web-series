<?php
// app/Models/AiDirector/SoundTrack.php

namespace App\Models\AiDirector;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoundTrack extends Model
{
    protected $table = 'ai_director_sound_tracks';
    
    protected $fillable = [
        'project_id',
        'scene_number',
        'track_type',
        'content',
        'prompt',
        'voice_profile',
        'duration',
        'audio_path',
        'status',
        'track_id',
        'track_order',
        'synced_to_video'
    ];

    protected $casts = [
        'synced_to_video' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(SoundProject::class, 'project_id');
    }
}