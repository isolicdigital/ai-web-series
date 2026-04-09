<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Subscription;

class SubscriptionPolicy
{
    public function view(User $user, Subscription $subscription)
    {
        return $user->id === $subscription->user_id || $user->role === 'admin';
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }
}