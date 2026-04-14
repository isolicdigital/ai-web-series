<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageGenerationLog extends Model
{
    protected $table = 'image_generation_logs';
    
    protected $fillable = [
        'tracking_id',
        'scene_id',
        'web_series_id',
        'user_id',
        'prompt',
        'model_id',
        'samples',
        'num_inference_steps',
        'guidance_scale',
        'api_request_id',
        'status',
        'image_urls',
        'error_message',
        'full_api_response',
        'webhook_url',
        'webhook_received_at',
        'webhook_payload',
        'api_called_at',
        'completed_at',
        'attempts',
        'retry_count'
    ];
    
    protected $casts = [
        'image_urls' => 'array',
        'webhook_payload' => 'array',
        'full_api_response' => 'array',
        'api_called_at' => 'datetime',
        'completed_at' => 'datetime',
        'webhook_received_at' => 'datetime'
    ];
    
    public function scene()
    {
        return $this->belongsTo(Scene::class);
    }
    
    public function webSeries()
    {
        return $this->belongsTo(WebSeries::class, 'web_series_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}