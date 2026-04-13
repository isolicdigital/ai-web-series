<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    protected $table = 'episodes';
    
    protected $fillable = [
        'web_series_id',
        'episode_number',
        'title',
        'prompt',
        'concept',
        'total_scenes',
        'status'
    ];
    
    protected $casts = [
        'episode_number' => 'integer',
        'total_scenes' => 'integer'
    ];
    
    public function webSeries()
    {
        return $this->belongsTo(WebSeries::class, 'web_series_id');
    }
    
    public function scenes()
    {
        return $this->hasMany(Scene::class, 'episode_id')->orderBy('scene_number');
    }
}