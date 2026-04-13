@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 py-12 px-4">
    <div class="container mx-auto max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('web-series.show', $series->id) }}" class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-arrow-left"></i>
                Back to Series
            </a>
        </div>

        <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-6 md:p-8">
            <h1 class="text-2xl font-bold text-white mb-6">Edit Series</h1>
            
            <form action="{{ route('web-series.update', $series->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-white font-semibold mb-2">Series Name</label>
                    <input type="text" name="name" value="{{ old('name', $series->name) }}" 
                           class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition"
                           required>
                </div>

                <div>
                    <label class="block text-white font-semibold mb-2">Description</label>
                    <textarea name="description" rows="4" 
                              class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition">{{ old('description', $series->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-white font-semibold mb-2">Topic / Theme</label>
                    <textarea name="topic" rows="3" required
                              class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition">{{ old('topic', $series->topic) }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl transition">
                        Save Changes
                    </button>
                    <a href="{{ route('web-series.show', $series->id) }}" 
                       class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-xl transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection