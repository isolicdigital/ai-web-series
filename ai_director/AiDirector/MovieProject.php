<?php
// app/Models/AiDirector/MovieProject.php

namespace App\Models\AiDirector;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class MovieProject extends Model
{
    protected $table = 'ai_director_movie_projects';
    
    protected $fillable = [
        'user_id',
        'title',
        'shoot_project_id',
        'sound_project_id',
        'scene_count',
        'final_path',
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

    public function soundProject(): BelongsTo
    {
        return $this->belongsTo(SoundProject::class, 'sound_project_id');
    }

    public function scenes(): HasMany
    {
        return $this->hasMany(MovieScene::class, 'movie_id');
    }
}