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
        'summary',
        'status'
    ];
    
    protected $casts = [
        'scene_number' => 'integer'
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