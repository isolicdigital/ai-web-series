@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 py-[120px] px-4">
    <div class="container mx-auto max-w-6xl">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">My Series</h1>
                <p class="text-gray-400">All your created web series</p>
            </div>
            <a href="{{ route('web-series.create') }}" 
               class="px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg text-white font-medium transition flex items-center gap-2">
                <i class="fas fa-plus"></i> New Series
            </a>
        </div>
        
        @if($webSeries->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($webSeries as $series)
            <div class="bg-white/5 backdrop-blur-lg rounded-xl border border-white/10 hover:border-purple-500/30 transition-all group">
                <div class="relative">
                    @if($series->cover_image)
                        <img src="{{ Storage::url($series->cover_image) }}" 
                             alt="{{ $series->name }}"
                             class="w-full h-48 object-cover rounded-t-xl">
                    @else
                        <div class="w-full h-48 bg-gradient-to-br from-purple-600 to-pink-600 rounded-t-xl flex items-center justify-center">
                            <i class="fas fa-film text-white text-5xl opacity-50"></i>
                        </div>
                    @endif
                    <div class="absolute top-3 right-3">
                        <span class="bg-{{ $series->status === 'completed' ? 'green' : ($series->status === 'generating' ? 'yellow' : 'gray') }}-600 text-white text-xs px-2 py-1 rounded-full">
                            {{ $series->status === 'completed' ? 'Completed' : ucfirst($series->status) }}
                        </span>
                    </div>
                </div>
                <div class="p-5">
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-purple-300 transition">{{ $series->name }}</h3>
                    <p class="text-gray-400 text-sm mb-3 line-clamp-2">{{ $series->description ?: 'No description provided' }}</p>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2 text-gray-400">
                            <i class="fas fa-list-ol"></i>
                            <span>{{ $series->episodes_count }} / {{ $series->total_episodes }} Episodes</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-400">
                            <i class="fas fa-calendar"></i>
                            <span>{{ $series->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-white/10 flex gap-2">
                        <button onclick="viewSeries({{ $series->id }})" 
                                class="flex-1 px-3 py-1.5 bg-purple-600 hover:bg-purple-700 rounded-lg text-white text-sm transition">
                            View Series
                        </button>
                        <button onclick="confirmDelete({{ $series->id }}, '{{ $series->name }}')" 
                                class="px-3 py-1.5 bg-red-600/20 hover:bg-red-600 text-red-400 hover:text-white rounded-lg text-sm transition">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="mt-8">
            {{ $webSeries->links() }}
        </div>
        @else
        <div class="text-center py-16">
            <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-purple-500/20 flex items-center justify-center">
                <i class="fas fa-film text-4xl text-purple-400"></i>
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">No Series Yet</h3>
            <p class="text-gray-400 mb-4">Start your creative journey by creating your first AI web series</p>
            <a href="{{ route('web-series.create') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 rounded-xl text-white font-semibold transition-colors">
                <i class="fas fa-plus-circle"></i>
                Create Your First Series
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden items-center justify-center">
    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl max-w-md w-full mx-4 p-6 border border-red-500/30 shadow-2xl">
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-500/20 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Delete Series</h3>
            <p class="text-gray-400 mb-4">
                Are you sure you want to delete "<span id="deleteSeriesName" class="text-white font-semibold"></span>"?
                <br>
                <span class="text-red-400 text-sm">This action cannot be undone. All episodes will be permanently deleted.</span>
            </p>
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()" 
                        class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-white font-medium transition">
                    Cancel
                </button>
                <button id="confirmDeleteBtn" 
                        class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg text-white font-medium transition flex items-center justify-center gap-2">
                    <i class="fas fa-trash"></i>
                    Delete Permanently
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let seriesToDelete = null;

function viewSeries(id) {
    window.location.href = `/series/${id}`;
}

function confirmDelete(id, name) {
    seriesToDelete = id;
    document.getElementById('deleteSeriesName').textContent = name;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteModal').classList.add('flex');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.getElementById('deleteModal').classList.remove('flex');
    seriesToDelete = null;
}

document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
    if (!seriesToDelete) return;
    
    const btn = this;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
    btn.disabled = true;
    
    try {
        const response = await fetch(`/series/${seriesToDelete}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Series deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error: ' + result.message, 'error');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            closeDeleteModal();
        }
    } catch (error) {
        showToast('Error deleting series: ' + error.message, 'error');
        btn.innerHTML = originalHtml;
        btn.disabled = false;
        closeDeleteModal();
    }
});

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

function showToast(message, type) {
    let toast = document.getElementById('customToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'customToast';
        document.body.appendChild(toast);
    }
    
    const colors = {
        success: 'bg-green-600',
        error: 'bg-red-600',
        info: 'bg-blue-600'
    };
    
    toast.className = `fixed bottom-8 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-xl text-white font-medium z-50 transition-all duration-300 ${colors[type] || colors.info} opacity-0 translate-y-4`;
    toast.textContent = message;
    
    setTimeout(() => {
        toast.classList.remove('opacity-0', 'translate-y-4');
        toast.classList.add('opacity-100', 'translate-y-0');
    }, 10);
    
    setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', 'translate-y-4');
    }, 3000);
}
</script>

<style>
    @media (max-width: 768px) {
        .py-\[120px\] {
            padding-top: 60px !important;
            padding-bottom: 60px !important;
        }
    }
</style>
@endsection