<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComedyVideo extends Model
{
    protected $table = 'comedy_videos';
    
    protected $fillable = [
        'user_id', 'comedian_id', 'category_id', 'title', 'description',
        'video_url', 'thumbnail_url', 'processing_status', 'duration_seconds', 'view_count'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comedian(): BelongsTo
    {
        return $this->belongsTo(ComedyComedian::class, 'comedian_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComedyCategory::class, 'category_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ComedyTemplate::class, 'template_id');
    }
}