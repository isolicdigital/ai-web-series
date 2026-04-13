@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 py-[120px] px-4">
    <div class="container mx-auto max-w-4xl">
        
        <!-- Debug Info (remove after fixing) -->
        @if(!isset($episode))
        <div class="bg-red-600/20 border border-red-500 p-4 rounded-xl mb-4">
            <p class="text-red-300">Debug: Episode variable is missing!</p>
        </div>
        @endif
        
        <!-- Scene Header -->
        <div class="bg-white/5 backdrop-blur-xl rounded-2xl border border-white/10 overflow-hidden">
            <div class="border-b border-white/10 p-6 bg-gradient-to-r from-purple-900/40 to-pink-900/40">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                        <span class="text-white text-lg font-bold">{{ $scene->scene_number }}</span>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white">{{ $scene->title }}</h1>
                        @if(isset($episode))
                        <p class="text-purple-400 text-sm">Episode {{ $episode->episode_number }} • Scene {{ $scene->scene_number }} of {{ $episode->total_scenes }}</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Scene Content -->
            <div class="p-6">
                <div class="text-gray-300 leading-relaxed">
                    {!! $scene->content !!}
                </div>
            </div>
            
            <!-- Image Prompt -->
            @if($scene->image_prompt)
            <div class="border-t border-white/10 p-6 bg-purple-600/5">
                <h3 class="text-purple-400 font-semibold mb-2">🎨 AI Image Generation Prompt</h3>
                <div class="bg-slate-800/50 rounded-lg p-4 mb-3">
                    <p class="text-gray-300 text-sm">{{ $scene->image_prompt }}</p>
                </div>
                <button onclick="copyPrompt()" class="px-4 py-2 bg-purple-600 rounded-lg text-white text-sm">
                    Copy Prompt
                </button>
            </div>
            @endif
            
            <!-- Navigation -->
            <div class="border-t border-white/10 p-6 flex justify-between">
                @php
                    $prevScene = \App\Models\Scene::where('web_series_id', $series->id)
                        ->where('scene_number', $scene->scene_number - 1)
                        ->first();
                    $nextScene = \App\Models\Scene::where('web_series_id', $series->id)
                        ->where('scene_number', $scene->scene_number + 1)
                        ->first();
                @endphp
                
                @if($prevScene)
                <a href="{{ route('web-series.scene', [$series->id, $prevScene->id]) }}" 
                   class="px-5 py-2 bg-purple-600 rounded-xl text-white hover:bg-purple-700 transition">
                    ← Previous Scene
                </a>
                @else
                <div></div>
                @endif
                
                @if($nextScene)
                <a href="{{ route('web-series.scene', [$series->id, $nextScene->id]) }}" 
                   class="px-5 py-2 bg-purple-600 rounded-xl text-white hover:bg-purple-700 transition">
                    Next Scene →
                </a>
                @endif
            </div>
        </div>
        
        <!-- All Scenes Navigation -->
        <div class="mt-6">
            <h3 class="text-white mb-3">All Scenes</h3>
            <div class="grid grid-cols-5 gap-2">
                @foreach(\App\Models\Scene::where('web_series_id', $series->id)->orderBy('scene_number')->get() as $s)
                <a href="{{ route('web-series.scene', [$series->id, $s->id]) }}" 
                   class="text-center p-2 rounded-lg {{ $s->id == $scene->id ? 'bg-purple-600' : 'bg-white/10 hover:bg-white/20' }} text-white text-sm transition">
                    {{ $s->scene_number }}
                </a>
                @endforeach
            </div>
        </div>
        
    </div>
</div>

<script>
function copyPrompt() {
    const prompt = `{{ addslashes($scene->image_prompt) }}`;
    navigator.clipboard.writeText(prompt);
    alert('Prompt copied to clipboard!');
}
</script>
@endsection