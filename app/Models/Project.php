<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'category'];
    
    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }
}

