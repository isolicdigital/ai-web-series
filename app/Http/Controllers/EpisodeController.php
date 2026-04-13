<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Episode;
use App\Services\AIService;
use Illuminate\Http\Request;

class EpisodeController extends Controller
{
    protected $aiService;
    
    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }
    
    public function create(Project $project)
    {
        return view('episodes.create', compact('project'));
    }
    
    public function generateConcept(Request $request, Project $project)
    {
        $validated = $request->validate([
            'episode_number' => 'required|integer|min:1',
            'prompt' => 'required|string|min:10'
        ]);
        
        // Create episode in draft status
        $episode = Episode::create([
            'project_id' => $project->id,
            'episode_number' => $validated['episode_number'],
            'prompt' => $validated['prompt'],
            'status' => 'draft'
        ]);
        
        // Generate concept using AI
        $concept = $this->aiService->generateConcept($validated['prompt']);
        
        // Update episode with concept
        $episode->update([
            'concept' => $concept,
            'status' => 'concept_ready'
        ]);
        
        return view('episodes.concept-review', compact('episode', 'project'));
    }
    
    public function updateConcept(Request $request, Project $project, Episode $episode)
    {
        $validated = $request->validate([
            'concept' => 'required|string|max:600'
        ]);
        
        $episode->update([
            'concept' => $validated['concept'],
            'status' => 'concept_ready'
        ]);
        
        return response()->json(['success' => true, 'message' => 'Concept updated successfully']);
    }
    
    public function saveConcept(Request $request, Project $project, Episode $episode)
    {
        $episode->update(['status' => 'concept_ready']);
        
        return redirect()->route('scenes.setup', [
            'project' => $project->id,
            'episode' => $episode->id
        ])->with('success', 'Concept saved! Now define how many scenes you want.');
    }
    
    public function setupScenes(Project $project, Episode $episode)
    {
        return view('scenes.setup', compact('project', 'episode'));
    }
    
    public function generateScenes(Request $request, Project $project, Episode $episode)
    {
        $validated = $request->validate([
            'scene_count' => 'required|integer|min:5|max:10'
        ]);
        
        // Generate scenes using AI
        $scenesData = $this->aiService->generateScenes(
            $episode->concept,
            $validated['scene_count']
        );
        
        // Save scenes to database
        foreach ($scenesData as $sceneData) {
            $episode->scenes()->create([
                'scene_number' => $sceneData['scene_number'],
                'content' => $sceneData['content']
            ]);
        }
        
        $episode->update(['status' => 'scenes_ready']);
        
        return redirect()->route('episodes.show', [
            'project' => $project->id,
            'episode' => $episode->id
        ])->with('success', 'Scenes generated successfully!');
    }
    
    public function show(Project $project, Episode $episode)
    {
        $scenes = $episode->scenes;
        return view('episodes.show', compact('project', 'episode', 'scenes'));
    }
}