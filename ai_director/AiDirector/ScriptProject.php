<?php
// app/Models/AiDirector/ScriptProject.php

namespace App\Models\AiDirector;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\AiCustomGenerator;

class ScriptProject extends Model
{
    protected $table = 'ai_director_script_projects';
    
    protected $fillable = [
        'user_id',
        'title',
        'prompt',
        'style',
        'scene_count',
        'model_id',
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

    public function model(): BelongsTo
    {
        return $this->belongsTo(AiCustomGenerator::class, 'model_id');
    }

    public function scenes(): HasMany
    {
        return $this->hasMany(ScriptScene::class, 'project_id');
    }
}