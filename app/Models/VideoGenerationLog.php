<?php
// app/Models/VideoGenerationLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoGenerationLog extends Model
{
    protected $fillable = [
        'user_id',
        'scene_id',
        'series_id',
        'prediction_id',
        'status',
        'image_url',
        'video_url',
        'prompt',
        'input_params',
        'output_data',
        'error_message',
        'attempts',
        'started_at',
        'completed_at'
    ];
    
    protected $casts = [
        'input_params' => 'array',
        'output_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }
    
    public function series(): BelongsTo
    {
        return $this->belongsTo(WebSeries::class, 'series_id');
    }
}