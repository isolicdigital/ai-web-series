<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComedyJoke extends Model
{
    protected $table = 'comedy_jokes';
    
    protected $fillable = [
        'user_id',
        'template_id',
        'prompt',
        'generated_joke',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ComedyTemplate::class, 'template_id');
    }
}