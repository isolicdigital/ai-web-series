<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subscription;

class CheckSubscriptionAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            return redirect('/login')->withErrors(['access' => 'Your account is inactive.']);
        }

        $ownerId = $user->agency_id ? $user->agency->owner_user_id : $user->id;
        $owner = \App\Models\User::find($ownerId);

        $hasActiveSubscription = $owner->subscriptions()->exists();

        if (!$hasActiveSubscription) {
            Auth::logout();
            return redirect('/login')->withErrors(['access' => 'No active subscription.']);
        }

        return $next($request);
    }

    public function subscriptions()
    {
        return $this->hasOne(Subscription::class)->where('status', 'active');
    }
}