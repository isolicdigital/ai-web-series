<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::with('user', 'plan')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        $owners = User::where('is_agency_owner', true)->orWhereNull('agency_id')->get();
        $plans = Plan::where('status', 'active')->get();
        return view('admin.subscriptions.create', compact('owners', 'plans'));
    }

    public function store(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $plan = Plan::findOrFail($request->plan_id);
        
        $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addYears(10),
            'status' => 'active',
        ]);
        
        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription assigned.');
    }
}