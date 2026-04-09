<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    public function index()
    {
        $agencies = Agency::with('owner', 'users')->paginate(20);
        return view('admin.agencies.index', compact('agencies'));
    }
    public function create()
    {
        $users = User::all();
        return view('admin.agencies.create', compact('users'));
    }

    public function store(Request $request)
    {
        $agency = Agency::create([
            'name' => $request->name,
            'owner_user_id' => $request->owner_user_id,
            'status' => $request->status,
        ]);
        
        $owner = User::find($request->owner_user_id);
        $owner->update([
            'agency_id' => $agency->id,
            'is_agency_owner' => true,
        ]);
        
        return redirect()->route('admin.agencies.index')->with('success', 'Agency created.');
    }
}