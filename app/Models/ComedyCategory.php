<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComedyCategory extends Model
{
    protected $table = 'comedy_categories';
    
    protected $fillable = ['name', 'slug', 'icon', 'description', 'display_order', 'is_active'];

    public function templates(): HasMany
    {
        return $this->hasMany(ComedyTemplate::class, 'category_id');
    }

    public function comedians(): HasMany
    {
        return $this->hasMany(ComedyComedian::class, 'category_id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(ComedyVideo::class, 'category_id');
    }
}