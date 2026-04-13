<?php
// app/Models/AiDirector/MovieScene.php

namespace App\Models\AiDirector;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieScene extends Model
{
    protected $table = 'ai_director_movie_scenes';
    
    protected $fillable = [
        'movie_id',
        'scene_number',
        'video_path',
        'thumbnail',
        'duration',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(MovieProject::class, 'movie_id');
    }
}