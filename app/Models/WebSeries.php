<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WebSeries extends Model
{
    use HasFactory;
    
    protected $table = 'web_series';
    
    protected $fillable = [
        'user_id',
        'project_name',
        'category_id',
        'concept',
        'total_episodes',
        'status'
    ];

    // Relationship with Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    protected $casts = [
        'total_episodes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function episodes()
    {
        return $this->hasMany(Episode::class, 'web_series_id');
    }
    
    public function scenes()
    {
        return $this->hasManyThrough(Scene::class, Episode::class, 'web_series_id', 'episode_id');
    }
    
    public function scopeForUser($query, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $query->where('user_id', $userId);
    }
    
    public function isComplete()
    {
        return $this->status === 'completed';
    }
    
    public function progress()
    {
        $totalEpisodes = $this->total_episodes;
        $completedEpisodes = $this->episodes()->where('status', 'completed')->count();
        
        if ($totalEpisodes == 0) return 0;
        
        return round(($completedEpisodes / $totalEpisodes) * 100);
    }
}