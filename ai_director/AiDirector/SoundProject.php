<?php
// app/Models/AiDirector/SoundProject.php

namespace App\Models\AiDirector;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class SoundProject extends Model
{
    protected $table = 'ai_director_sound_projects';
    
    protected $fillable = [
        'user_id',
        'title',
        'shoot_project_id',
        'script_project_id',
        'scene_count',
        'status',
        'payload'
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shootProject(): BelongsTo
    {
        return $this->belongsTo(ShootProject::class, 'shoot_project_id');
    }

    public function scriptProject(): BelongsTo
    {
        return $this->belongsTo(ScriptProject::class, 'script_project_id');
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(SoundTrack::class, 'project_id');
    }

    public function sfxTracks(): HasMany
    {
        return $this->hasMany(SFXTrack::class, 'project_id');
    }

    public function voiceoverScenes(): HasMany
    {
        return $this->hasMany(ScriptScene::class, 'project_id', 'script_project_id')
            ->whereNotNull('voiceover_script');
    }
}