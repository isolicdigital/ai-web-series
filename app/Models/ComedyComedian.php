<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComedyComedian extends Model
{
    protected $table = 'comedy_comedians';
    
    protected $fillable = [
        'user_id', 'template_id', 'category_id', 'name', 
        'origin_type', 'avatar_image', 'generation_status', 'generation_metadata'
    ];
    
    protected $casts = [
        'generation_metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ComedyTemplate::class, 'template_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComedyCategory::class, 'category_id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(ComedyVideo::class, 'comedian_id');
    }
}