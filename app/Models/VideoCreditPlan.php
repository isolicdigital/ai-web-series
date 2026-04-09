<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoCreditPlan extends Model
{
    protected $table = 'video_credit_plans';
    
    protected $fillable = [
        'wp_id',
        'name',
        'slug',
        'price',
        'original_price',
        'video_credits',
        'is_featured',
        'is_active',
        'display_order',
        'description'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function getPricePerCreditAttribute()
    {
        return $this->video_credits > 0 ? $this->price / $this->video_credits : 0;
    }

    public function getSavingsPercentageAttribute()
    {
        if ($this->original_price && $this->original_price > $this->price) {
            return round((($this->original_price - $this->price) / $this->original_price) * 100);
        }
        return 0;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('price');
    }
}