<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoCreditPurchase extends Model
{
    protected $table = 'video_credit_purchases';
    
    protected $fillable = [
        'user_id',
        'transaction_id',
        'credits_purchased',
        'credits_used',
        'expires_at',
        'is_active'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCreditsRemainingAttribute()
    {
        return $this->credits_purchased - $this->credits_used;
    }

    public function useCredits($credits)
    {
        if ($this->credits_remaining >= $credits) {
            $this->credits_used += $credits;
            $this->save();
            return true;
        }
        return false;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    })
                    ->whereRaw('credits_purchased - credits_used > 0');
    }
}