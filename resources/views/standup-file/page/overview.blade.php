@extends('layouts.app')

@section('title', 'AI Standup: Full Walkthrough Video')

@section('content')
<div class="demo-page">
    <div class="demo-container">
        <h1 class="demo-title">AI Standup: Full Walkthrough Video</h1>
        
        <div class="video-wrapper">
            <video controls class="demo-video">
                <source src="{{ asset('standup-demo.mp4') }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>

        <div class="upgrades-section">
            <p class="upgrades-text">Missed the upgrades? Take a look at them below:</p>
            <a href="https://aistandup.live/upgrades" class="upgrades-link" target="_blank">
                View Upgrades <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.demo-page {
    min-height: calc(100vh - 70px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.demo-container {
    max-width: 900px;
    width: 100%;
    text-align: center;
}

.demo-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 2rem;
    background: linear-gradient(135deg, var(--text-main) 0%, var(--accent) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.video-wrapper {
    background: var(--card-bg);
    border-radius: 20px;
    border: 1px solid var(--glass-border);
    padding: 0.5rem;
    box-shadow: var(--shadow);
}

.demo-video {
    width: 100%;
    border-radius: 16px;
}

.upgrades-section {
    margin-top: 2rem;
}

.upgrades-text {
    color: var(--text-secondary);
    font-size: 1rem;
    margin-bottom: 1rem;
}

.upgrades-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: var(--accent);
    color: white;
    border-radius: 40px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
}

.upgrades-link:hover {
    background: var(--accent-dark);
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .demo-title {
        font-size: 1.5rem;
    }
    
    .demo-container {
        padding: 0 1rem;
    }
}
</style>
@endsection