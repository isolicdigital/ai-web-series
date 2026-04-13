{{-- resources/views/web-series/drafts.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 py-12 px-4">
    <div class="container mx-auto max-w-6xl">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Your Drafts</h1>
                <p class="text-gray-400">Continue working on your saved projects</p>
            </div>
            <a href="{{ route('web-series.create') }}" 
               class="px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg text-white font-medium transition flex items-center gap-2">
                <i class="fas fa-plus"></i> New Project
            </a>
        </div>
        
        @if($drafts->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($drafts as $draft)
            <div class="bg-white/5 backdrop-blur-lg rounded-xl border border-white/10 p-5 hover:border-purple-500/30 transition">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center">
                        <i class="fas fa-folder-open text-purple-400"></i>
                    </div>
                    <span class="text-xs text-gray-400">{{ $draft->created_at->diffForHumans() }}</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">{{ $draft->project_name }}</h3>
                <p class="text-gray-400 text-sm mb-4">Status: Draft - Incomplete</p>
                <div class="flex gap-2">
                    <button onclick="continueProject({{ $draft->id }})" 
                            class="flex-1 px-3 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg text-white text-sm font-medium transition">
                        Continue
                    </button>
                    <button onclick="deleteDraft({{ $draft->id }})" 
                            class="px-3 py-2 bg-red-600/20 hover:bg-red-600 text-red-400 hover:text-white rounded-lg text-sm font-medium transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-16">
            <i class="fas fa-folder-open text-6xl text-gray-600 mb-4"></i>
            <h3 class="text-xl font-semibold text-white mb-2">No Drafts Yet</h3>
            <p class="text-gray-400 mb-4">Start creating your first web series</p>
            <a href="{{ route('web-series.create') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 rounded-lg text-white font-medium transition">
                <i class="fas fa-plus"></i> Create New Series
            </a>
        </div>
        @endif
    </div>
</div>

<script>
function continueProject(id) {
    window.location.href = `{{ url('web-series/create') }}?project_id=${id}`;
}

async function deleteDraft(id) {
    if (confirm('Are you sure you want to delete this draft?')) {
        const response = await fetch(`{{ url('web-series/drafts') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert('Failed to delete draft');
        }
    }
}
</script>
@endsection