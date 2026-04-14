<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComedyTemplate extends Model
{
    protected $table = 'comedy_templates';
    
    protected $casts = [
        'joke_template' => 'array',
        'is_active' => 'boolean',
    ];
    
    protected $fillable = ['category_id', 'name', 'description', 'init_image', 'preview_image', 'is_active', 'usage_count'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComedyCategory::class, 'category_id');
    }

    public function comedians(): HasMany
    {
        return $this->hasMany(ComedyComedian::class, 'template_id');
    }
    // Get the image URL
    public function getImageUrlAttribute()
    {
        if ($this->init_image) {
            return asset($this->init_image);
        }
        return null;
    }
}