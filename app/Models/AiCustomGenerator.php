<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiCustomGenerator extends Model
{
    protected $table = 'ai_custom_generators';
    
    protected $fillable = [
        'ai_model_id',
        'title',
        'description',
        'slug',
        'image',
        'icon',
        'color',
        'filters',
        'prompt_template',
        'demo_output',
        'custom_template',
        'tone_of_voice',
        'package',
        'premium',
        'daily_limit',
        'temp_block_gap',
        'success_msg',
        'daily_warning',
        'temp_warning',
        'active'
    ];

    protected $casts = [
        'filters' => 'array',
        'custom_template' => 'boolean',
        'tone_of_voice' => 'boolean',
        'premium' => 'boolean',
        'active' => 'boolean'
    ];

    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class);
    }
}