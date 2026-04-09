<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Agency;
use App\Models\Plan;
use App\Models\UserVideoCredit;
use App\Models\VideoCreditPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;


class UserController extends Controller
{
    public function index()
    {
        $users = User::with('agency')->paginate(20);
        return view('admin.users.index', compact('users'));
    }
    
    public function create()
    {
        $agencies = Agency::all();
        $plans = Plan::where('status', 'active')->get();
        return view('admin.users.create', compact('agencies', 'plans'));
    }

    public function edit(User $user)
    {
        $agencies = Agency::all();
        $plans = Plan::where('status', 'active')->get();
        return view('admin.users.edit', compact('user', 'agencies', 'plans'));
    }
    
    public function store(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status,
            'agency_id' => $request->agency_id,
            'is_agency_owner' => $request->agency_id ? false : true,
        ]);
        
        // Allocate credits based on plan
        $creditsToAdd = 5000; // Default for Plan 1-7
        
        if ($request->plan_id && !$user->agency_id) {
            $plan = Plan::find($request->plan_id);
            $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'ends_at' => now()->addYears(10),
                'status' => 'active',
            ]);
            
            // Plan 8 gets 10000 credits
            if ($plan->id == 8) {
                $creditsToAdd = 10000;
            }
        }
        
        // Allocate credits
        UserVideoCredit::create([
            'user_id' => $user->id,
            'free_credits' => $creditsToAdd,
            'free_credits_used' => 0,
            'paid_credits' => 0,
            'paid_credits_used' => 0,
            'total_credits' => $creditsToAdd,
            'used_credits' => 0,
        ]);
        
        VideoCreditPurchase::create([
            'user_id' => $user->id,
            'transaction_id' => 'AD_SIGNUP_' . $user->id,
            'credits_purchased' => $creditsToAdd,
            'credits_used' => 0,
            'expires_at' => null,
            'is_active' => true
        ]);
        
        return redirect()->route('admin.users.index');
    }

    public function update(Request $request, User $user)
    {
        $oldPlanId = $user->subscriptions()->where('status', 'active')->first()?->plan_id;
        
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'status' => $request->status,
            'agency_id' => $request->agency_id,
            'is_agency_owner' => $request->agency_id ? false : true,
        ]);
        
        if ($request->plan_id && !$user->agency_id) {
            $plan = Plan::find($request->plan_id);
            
            // Check if upgrading to Plan 8
            if ($plan->id == 8 && $oldPlanId != 8) {
                $userCredit = UserVideoCredit::where('user_id', $user->id)->first();
                
                if (!$userCredit) {
                    $userCredit = UserVideoCredit::create([
                        'user_id' => $user->id,
                        'free_credits' => 0,
                        'free_credits_used' => 0,
                        'paid_credits' => 0,
                        'paid_credits_used' => 0,
                        'total_credits' => 0,
                        'used_credits' => 0,
                    ]);
                }
                
                // Add 5000 free credits on upgrade to Plan 8
                $userCredit->free_credits += 5000;
                $userCredit->total_credits += 5000;
                $userCredit->save();
                
                VideoCreditPurchase::create([
                    'user_id' => $user->id,
                    'transaction_id' => 'AD_UPGRADE_' . $user->id,
                    'credits_purchased' => 5000,
                    'credits_used' => 0,
                    'expires_at' => null,
                    'is_active' => true
                ]);
            }
            
            // Update or create subscription
            $user->subscriptions()->delete();
            $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'ends_at' => now()->addYears(10),
                'status' => 'active',
            ]);
        }
        
        return redirect()->route('admin.users.index');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
    }
}