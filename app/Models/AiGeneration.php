<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiGeneration extends Model
{
    protected $fillable = [
        'user_id', 
        'ai_custom_generator_id', 
        'title', 
        'prompt', 
        'tone', 
        'format', 
        'creativity', 
        'length', 
        'output'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tool()
    {
        return $this->belongsTo(AiCustomGenerator::class, 'ai_custom_generator_id');
    }
}