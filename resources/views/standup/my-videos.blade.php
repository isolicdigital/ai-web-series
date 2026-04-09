@extends('layouts.app')

@section('content')
<div class="container">
    <h2>My Stand-Up Videos</h2>
    
    <div class="row">
        @forelse($videos as $video)
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    @if($video->status === 'completed')
                        <video controls class="w-100 mb-2">
                            <source src="{{ asset($video->video_url) }}" type="video/mp4">
                        </video>
                        <a href="{{ asset($video->video_url) }}" class="btn btn-sm btn-primary" download>Download</a>
                    @elseif($video->status === 'processing')
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-2">Processing...</p>
                        </div>
                    @elseif($video->status === 'failed')
                        <div class="alert alert-danger">Failed to generate video</div>
                    @endif
                    
                    <hr>
                    <small class="text-muted">Created: {{ $video->created_at->diffForHumans() }}</small>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                No videos yet. <a href="/standup/templates">Create your first comedian</a>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection