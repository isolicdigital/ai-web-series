<?php
// app/Models/AiDirector/ShootProject.php

namespace App\Models\AiDirector;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\AiCustomGenerator;

class ShootProject extends Model
{
    protected $table = 'ai_director_shoot_projects';
    
    protected $fillable = [
        'user_id',
        'title',
        'source_type',
        'source_id',
        'image_model_id',
        'video_model_id',
        'seed',
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

    public function imageModel(): BelongsTo
    {
        return $this->belongsTo(AiCustomGenerator::class, 'image_model_id');
    }

    public function videoModel(): BelongsTo
    {
        return $this->belongsTo(AiCustomGenerator::class, 'video_model_id');
    }

    public function frames(): HasMany
    {
        return $this->hasMany(ShootFrame::class, 'project_id');
    }

    public function scriptProject(): BelongsTo
    {
        return $this->belongsTo(ScriptProject::class, 'source_id');
    }
}