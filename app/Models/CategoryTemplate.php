<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTemplate extends Model
{
    protected $table = 'category_templates';
    
    protected $fillable = [
        'category_id',
        'category_prompt',
        'init_image',
        'is_active'
    ];
    
    protected $casts = [
        'category_prompt' => 'array',
        'is_active' => 'boolean',
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}