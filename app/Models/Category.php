<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    
    protected $fillable = [
        'name',
        'icon',
        'description',
        'display_order',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }
    
    public function webSeries()
    {
        return $this->hasMany(WebSeries::class);
    }
    
    public function templates()
    {
        return $this->hasMany(CategoryTemplate::class);
    }
    
    public function activeTemplate()
    {
        return $this->hasOne(CategoryTemplate::class)->where('is_active', true);
    }
    // Get image from template
    public function getImageUrlAttribute()
    {
        if ($this->template && $this->template->init_image) {
            return asset($this->template->init_image);
        }
        return null;
    }
    
}