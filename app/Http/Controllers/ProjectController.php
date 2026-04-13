<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function create()
    {
        return view('projects.create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:action,drama,comedy,horror,sci-fi,other'
        ]);
        
        $project = Project::create($validated);
        
        return redirect()->route('episodes.create', ['project' => $project->id])
                        ->with('success', 'Project created successfully!');
    }
}