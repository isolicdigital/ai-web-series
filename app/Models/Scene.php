<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scene extends Model
{
    protected $table = 'scenes';
    
    protected $fillable = [
        'episode_id',
        'web_series_id',
        'scene_number',
        'title',
        'content',
        'image_prompt',
        'generated_image_url',
        'video_url',           
    'video_status',        
    'video_generation_started_at',  
    'video_generation_completed_at', 
    'video_error_message', 
        'summary',
        'status',
        
    ];
    
    protected $casts = [
        'scene_number' => 'integer',
         'video_generation_started_at' => 'datetime',
    'video_generation_completed_at' => 'datetime',
        
    ];
    
    public function episode()
    {
        return $this->belongsTo(Episode::class, 'episode_id');
    }
    
    public function webSeries()
    {
        return $this->belongsTo(WebSeries::class, 'web_series_id');
    }
}