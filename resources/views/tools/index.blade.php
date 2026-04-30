@extends('layouts.app')

@section('title', 'Content Tools')

@section('content')
<div class="min-h-screen bg-black py-[120px] px-4">
    <div class="container mx-auto max-w-4xl">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 mb-4 shadow-lg">
                    <i class="fas {{ $icon ?? 'fa-tools' }} text-white text-3xl"></i>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-3 bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">{{ $title }}</h1>
                <p class="text-gray-300 text-lg">{{ $subtitle }}</p>
            </div>
            <!-- Tools Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($tools as $tool)
                <a href="{{ route($routePrefix . '.show', $tool->slug) }}" 
                class="group relative block p-8 rounded-3xl bg-white/5 backdrop-blur-lg border border-purple-500/20 transition-all duration-500 hover:-translate-y-2 hover:scale-[1.01] hover:border-purple-500/40 overflow-hidden cursor-pointer no-underline">
                    
                    <!-- Icon Background -->
                    <div class="absolute -bottom-5 -right-5 w-[180px] h-[180px] flex items-center justify-center opacity-10 group-hover:opacity-20 group-hover:scale-110 transition-all duration-500 pointer-events-none">
                        <i class="fas {{ $tool->icon ?? 'fa-magic' }} text-9xl text-white group-hover:text-purple-500 transition-colors duration-500" style="transform: rotate(-8deg);"></i>
                    </div>
                    
                    <!-- Content -->
                    <div class="relative z-10 flex flex-col gap-2 max-w-[72%]">
                        <h3 class="text-2xl font-medium tracking-wide text-white leading-tight">
                            {{ $tool->title }}
                        </h3>
                        <p class="text-gray-400 text-sm font-light leading-relaxed">
                            {{ Str::limit($tool->description ?? 'Generate professional content instantly', 100) }}
                        </p>
                        @if($tool->premium)
                            <span class="inline-block bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs font-semibold px-3 py-1 rounded-full w-fit mt-2 shadow-lg">
                                Premium
                            </span>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection