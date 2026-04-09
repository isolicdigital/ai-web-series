<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'role',
        'agency_id',
        'is_agency_owner',
        'photo',
        'theme',
        'last_seen',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function subscriptions()
    {
        return $this->hasOne(Subscription::class)->where('status', 'active');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function getPlanLevelAttribute()
    {
        $subscription = $this->subscriptions()->where('status', 'active')->latest()->first();
        
        if (!$subscription || !$subscription->plan) {
            return 1; // Free plan level
        }
        
        return $subscription->plan->order; // Assuming plans table has 'level' column (1,2,3,4,5)
    }

    public function videoCredit()
    {
        return $this->hasOne(UserVideoCredit::class);
    }
}
