@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Generate Stand-Up Script</h2>
            <p class="text-muted">Select a comedian first to begin</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5>Your Comedians</h5>
                    <div class="row">
                        @forelse($comedians as $comedian)
                        <div class="col-md-3 mb-3">
                            <div class="card {{ session('selected_comedian_id') == $comedian->id ? 'border-primary' : '' }}">
                                <img src="{{ asset($comedian->final_image) }}" class="card-img-top" style="height: 150px; object-fit: cover;">
                                <div class="card-body text-center">
                                    <h6>{{ $comedian->name }}</h6>
                                    <button class="btn btn-sm btn-outline-primary select-comedian" data-id="{{ $comedian->id }}" data-name="{{ $comedian->name }}">Select</button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <p>No comedians yet. <a href="{{ route('standup.templates') }}">Create one now</a></p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form id="scriptForm">
                        <div class="mb-3">
                            <label>Selected Comedian</label>
                            <div id="selectedComedianDisplay" class="alert alert-info">
                                {{ session('selected_comedian_id') ? 'Comedian selected' : 'No comedian selected' }}
                            </div>
                            <input type="hidden" id="comedianId" name="comedian_id" value="{{ session('selected_comedian_id') }}">
                        </div>
                        
                        <div class="mb-3">
                            <label>Topic / Idea</label>
                            <textarea name="topic" class="form-control" rows="3" placeholder="e.g., Struggles of working from home, dating apps, getting older..." required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label>Tone</label>
                            <select name="tone" class="form-control">
                                <option value="">Default</option>
                                <option value="sarcastic">Sarcastic</option>
                                <option value="dark">Dark Humor</option>
                                <option value="wholesome">Wholesome</option>
                                <option value="absurd">Absurd / Surreal</option>
                                <option value="observational">Observational</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label>Duration</label>
                            <select name="duration" class="form-control">
                                <option value="1">1 minute (Short)</option>
                                <option value="3" selected>3 minutes (Standard)</option>
                                <option value="5">5 minutes (Extended)</option>
                                <option value="10">10 minutes (Full Set)</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100" id="generateBtn" disabled>Generate Script</button>
                    </form>
                    
                    <div id="loadingIndicator" class="d-none mt-3 text-center">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2">Crafting your comedy...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Your Script</h5>
                </div>
                <div class="card-body">
                    <div id="scriptOutput" class="bg-light p-3 rounded" style="min-height: 400px; white-space: pre-wrap; font-family: monospace;">
                        <em class="text-muted">Script will appear here after generation</em>
                    </div>
                    <div id="scriptActions" class="mt-3 d-none">
                        <button class="btn btn-warning btn-sm" id="editScriptBtn">✏️ Edit</button>
                        <button class="btn btn-danger btn-sm" id="regenerateScriptBtn">🔄 Regenerate</button>
                        <button class="btn btn-success btn-sm" id="useForVideoBtn">🎬 Use for Video</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentScriptId = null;
let currentComedianId = document.getElementById('comedianId').value;

function updateGenerateButton() {
    const btn = document.getElementById('generateBtn');
    btn.disabled = !currentComedianId;
}

document.querySelectorAll('.select-comedian').forEach(btn => {
    btn.addEventListener('click', () => {
        currentComedianId = btn.dataset.id;
        document.getElementById('comedianId').value = currentComedianId;
        document.getElementById('selectedComedianDisplay').innerHTML = `Selected: ${btn.dataset.name}`;
        updateGenerateButton();
        
        fetch('/standup/select-comedian', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ comedian_id: currentComedianId })
        });
    });
});

updateGenerateButton();

document.getElementById('scriptForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!currentComedianId) {
        alert('Please select a comedian first');
        return;
    }
    
    const formData = new FormData(e.target);
    formData.append('comedian_id', currentComedianId);
    
    document.getElementById('loadingIndicator').classList.remove('d-none');
    document.getElementById('scriptOutput').innerHTML = '<em class="text-muted">Generating...</em>';
    document.getElementById('scriptActions').classList.add('d-none');
    
    const response = await fetch('{{ route("standup.script.generate") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: formData
    });
    
    const data = await response.json();
    pollScriptStatus(data.script_id);
});

function pollScriptStatus(scriptId) {
    const interval = setInterval(async () => {
        const response = await fetch(`/standup/script/${scriptId}`);
        const data = await response.json();
        
        if (data.status === 'completed') {
            clearInterval(interval);
            document.getElementById('loadingIndicator').classList.add('d-none');
            document.getElementById('scriptOutput').innerText = data.generated_script;
            document.getElementById('scriptActions').classList.remove('d-none');
            currentScriptId = scriptId;
        } else if (data.status === 'failed') {
            clearInterval(interval);
            document.getElementById('loadingIndicator').classList.add('d-none');
            document.getElementById('scriptOutput').innerHTML = '<span class="text-danger">Generation failed. Please try again.</span>';
        }
    }, 2000);
}

document.getElementById('editScriptBtn').addEventListener('click', () => {
    const newContent = prompt('Edit your script:', document.getElementById('scriptOutput').innerText);
    if (newContent) {
        fetch(`/standup/script/${currentScriptId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ script_content: newContent })
        }).then(() => {
            document.getElementById('scriptOutput').innerText = newContent;
        });
    }
});

document.getElementById('regenerateScriptBtn').addEventListener('click', async () => {
    document.getElementById('loadingIndicator').classList.remove('d-none');
    document.getElementById('scriptActions').classList.add('d-none');
    document.getElementById('scriptOutput').innerHTML = '<em class="text-muted">Regenerating...</em>';
    
    await fetch(`/standup/script/${currentScriptId}/regenerate`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    
    pollScriptStatus(currentScriptId);
});

document.getElementById('useForVideoBtn').addEventListener('click', () => {
    window.location.href = `/standup/video-generator?comedian_id=${currentComedianId}&script_id=${currentScriptId}`;
});
</script>
@endsection