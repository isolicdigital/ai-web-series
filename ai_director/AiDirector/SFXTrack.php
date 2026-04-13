<?php
// app/Models/AiDirector/SFXTrack.php

namespace App\Models\AiDirector;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class SFXTrack extends Model
{
    protected $table = 'ai_director_sfx_tracks';
    
    protected $fillable = [
        'project_id',
        'script_project_id',
        'prompt',
        'status',
        'url',
        'track_id',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAILED = 3;

    public function soundProject(): BelongsTo
    {
        return $this->belongsTo(SoundProject::class, 'project_id');
    }

    public function scriptProject(): BelongsTo
    {
        return $this->belongsTo(ScriptProject::class, 'script_project_id');
    }

    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_SUCCESS => 'Ready',
            self::STATUS_FAILED => 'Failed',
            default => 'Unknown'
        };
    }

    public function getStatusClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'status-pending',
            self::STATUS_PROCESSING => 'status-processing',
            self::STATUS_SUCCESS => 'status-success',
            self::STATUS_FAILED => 'status-failed',
            default => ''
        };
    }
}