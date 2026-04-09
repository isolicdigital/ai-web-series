@extends('layouts.app')

@section('title', 'Templates')

@section('css')
<link rel="stylesheet" href="{{ asset('custom/css/dashboard.css') }}">
@endsection

@section('content')
<div class="templates-page">
    <div class="templates-header">
        <h1>All Templates</h1>
        <p>Browse all available comedy templates</p>
    </div>
    
    <div class="templates-grid">
        @foreach($templates as $template)
            <div class="template-card" data-category="{{ $template->category->slug }}">
                <div class="card-image" style="background-image: url('{{ asset($template->preview_image ?? $template->init_image) }}')"></div>
                <div class="card-gradient"></div>
                <div class="card-content">
                    <h3>{{ $template->name }}</h3>
                    <p>{{ $template->description }}</p>
                    <div class="template-meta">
                        <span class="category-badge">{{ $template->category->name }}</span>
                    </div>
                    <button class="use-template-btn" data-template-id="{{ $template->id }}">
                        Use Template
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category filter
    const filterBtns = document.querySelectorAll('.filter-btn');
    const templateCards = document.querySelectorAll('.template-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const category = this.dataset.category;
            
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            templateCards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
    
    // Use template action
    document.querySelectorAll('.use-template-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const templateId = this.dataset.templateId;
            window.location.href = `{{ route('comedy.create-video') }}?template_id=${templateId}`;
        });
    });
});
</script>
@endsection