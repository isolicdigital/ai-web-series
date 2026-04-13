<?php
// app/Models/AiDirector/ScriptScene.php

namespace App\Models\AiDirector;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScriptScene extends Model
{
    protected $table = 'ai_director_script_scenes';
    
    protected $fillable = [
        'project_id',
        'scene_number',
        'voiceover_script',
        'visual_prompt',
        'status',
        'track_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ScriptProject::class, 'project_id');
    }
}