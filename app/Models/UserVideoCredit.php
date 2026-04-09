<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVideoCredit extends Model
{
    protected $table = 'user_video_credits';
    
    protected $fillable = [
        'user_id',
        'total_credits',
        'used_credits',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purchases()
    {
        return $this->hasMany(VideoCreditPurchase::class, 'user_id', 'user_id');
    }

    public function getRemainingCreditsAttribute()
    {
        return $this->total_credits - $this->used_credits;
    }

    public function addCredits($credits, $expiresAt = null)
    {
        $this->total_credits += $credits;
        if ($expiresAt && (!$this->expires_at || $expiresAt > $this->expires_at)) {
            $this->expires_at = $expiresAt;
        }
        $this->save();
    }

    public function useCredits($credits)
    {
        if ($this->remaining_credits >= $credits) {
            $this->used_credits += $credits;
            $this->save();
            return true;
        }
        return false;
    }

    public function hasEnoughCredits($credits = 1)
    {
        return $this->remaining_credits >= $credits;
    }
}