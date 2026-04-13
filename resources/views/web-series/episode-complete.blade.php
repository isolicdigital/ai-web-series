@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 py-[120px] px-4">
    <div class="container mx-auto max-w-6xl">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-white mb-2">{{ $series->project_name }}</h1>
            <p class="text-purple-400 text-lg">{{ $series->category }} • Episode {{ $episode->episode_number }}</p>
            <a href="{{ route('web-series.show', $series->id) }}" class="inline-flex items-center gap-2 text-gray-400 hover:text-white mt-4">← Back to Series</a>
        </div>
        
        <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8 mb-8">
            <h2 class="text-2xl font-bold text-white mb-4">Episode Concept</h2>
            <p class="text-gray-300">{{ $episode->concept }}</p>
        </div>
        
        <div class="grid gap-6">
            <h2 class="text-2xl font-bold text-white">Scenes ({{ $episode->scenes->count() }})</h2>
            @foreach($episode->scenes as $scene)
            <div class="bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
                <div class="bg-purple-600/20 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold">{{ $scene->scene_number }}</div>
                            <h3 class="text-xl font-bold text-white">{{ $scene->title }}</h3>
                        </div>
                        <a href="{{ route('web-series.scene', ['seriesId' => $series->id, 'sceneId' => $scene->id]) }}" class="px-4 py-2 bg-purple-600 rounded-lg text-white text-sm">View Scene →</a>
                    </div>
                </div>
                <div class="p-6">
                    {!! Str::limit($scene->content, 300) !!}
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection