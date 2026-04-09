<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiModel extends Model
{
    protected $table = 'ai_models';
    
    protected $fillable = [
        'name',
        'endpoint', 
        'platform',
        'handler_method',
        'input_fields',
        'default_parameters',
        'needs_image_modal',
        'needs_video_modal', 
        'needs_3d_modal',
        'needs_audio_scripts',
        'result_type',
        'result_icon',
        'result_title',
        'empty_icon',
        'empty_title',
        'active'
    ];

    protected $casts = [
        'input_fields' => 'array',
        'default_parameters' => 'array',
        'needs_image_modal' => 'boolean',
        'needs_video_modal' => 'boolean',
        'needs_3d_modal' => 'boolean',
        'needs_audio_scripts' => 'boolean',
        'active' => 'boolean'
    ];

    public function customGenerators(): HasMany
    {
        return $this->hasMany(AiCustomGenerator::class);
    }
}