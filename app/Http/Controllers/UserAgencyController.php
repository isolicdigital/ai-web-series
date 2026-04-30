<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class UserAgencyController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $isAdmin = $user->role === 'admin';
            $isAgencyOwner = $user->is_agency_owner && $user->agency_id;

            if (!$isAdmin && !$isAgencyOwner) {
                abort(403, 'You are not authorized to access this page.');
            }
            return $next($request);
        });
    }
    /**
     * Show agency dashboard
     */
    public function members()
    {
        $user = Auth::user();
        $agency = $user->agency;
        $members = User::where('agency_id', $agency->id)
            ->where('id', '!=', $user->id)
            ->paginate(20);
        
        $totalMembers = User::where('agency_id', $agency->id)->count();
        $activeMembers = User::where('agency_id', $agency->id)
            ->where('status', 'active')
            ->count();
        $totalSessions = 0; // Replace with your sessions table query if exists

        return view('agency.members', compact('agency', 'members', 'totalMembers', 'activeMembers', 'totalSessions'));
    }

    /**
     * Show add member form
     */
    public function createMember()
    {
        $user = Auth::user();
        return view('agency.members-create');
    }
    
    /**
     * Store new member
     */
    public function storeMember(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);
        
        try {
            $member = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'agency_id' => $user->agency_id,
                'is_agency_owner' => false,
                'status' => 'active',
                'role' => 'user',
            ]);

            // Create subscription
            $member->subscriptions()->create([
                'plan_id' => 1,
                'starts_at' => now(),
                'ends_at' => now()->addYears(10),
                'status' => 'active',
            ]);

            // Add to AWeber
            $this->addToAWeberList($member->email, $member->name);
            
            return redirect()->route('agency.members')
                ->with('success', 'Member added successfully.');
            
        } catch (\Exception $e) {
            \Log::error('Failed to create member:', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to add member: ' . $e->getMessage());
        }
    }

    /**
     * Add user to AWeber
     */
    private function addToAWeberList($email, $name)
    {
        try {
            $apiUrl = config('services.aweber.endpoint', 'https://softprohub.com/api/aweber/subscribe');
            $tag = config('app.name');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'email' => $email,
                'name' => $name,
                'source' => config('app.name'),
                'app_id' => config('app.name'),
                'tags' => [$tag],
            ]);

            if ($response->successful()) {
                Log::info('User added to AWeber', ['email' => $email]);
                return true;
            }

            Log::warning('Failed to add user to AWeber', ['email' => $email, 'status' => $response->status()]);
            return false;

        } catch (\Exception $e) {
            Log::error('AWeber API call failed', ['email' => $email, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Edit member
     */
    public function editMember($id)
    {
        $user = Auth::user();
        $member = User::where('agency_id', $user->agency_id)
            ->where('id', $id)
            ->firstOrFail();
        
        return view('agency.members-edit', compact('member'));
    }

    /**
     * Update member
     */
    public function updateMember(Request $request, $id)
    {
        $user = Auth::user();
        $member = User::where('agency_id', $user->agency_id)
            ->where('id', $id)
            ->firstOrFail();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $member->id,
            'status' => 'required|in:active,inactive',
        ]);
        
        $member->update([
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status,
        ]);
        
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $member->update(['password' => bcrypt($request->password)]);
        }
        
        return redirect()->route('agency.members')
            ->with('success', 'Member updated successfully.');
    }

    /**
     * Delete member
     */
    public function destroyMember($id)
    {
        $user = Auth::user();
        $member = User::where('agency_id', $user->agency_id)
            ->where('id', $id)
            ->firstOrFail();
        
        $member->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Update agency settings
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $agency = $user->agency;
        
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        $agency->update([
            'name' => $request->name,
        ]);
        
        return redirect()->route('agency.members')
            ->with('success', 'Agency settings updated.');
    }
}