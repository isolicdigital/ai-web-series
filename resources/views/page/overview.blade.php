@extends('layouts.app')

@section('title', 'AI Standup: Full Walkthrough Video')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-950 via-purple-950 to-gray-950 flex items-center justify-center p-8">
    <div class="max-w-5xl w-full text-center">
        <!-- Title -->
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold mb-8 bg-gradient-to-r from-white via-purple-400 to-pink-500 bg-clip-text text-transparent">
            AI Standup: Full Walkthrough Video
        </h1>
        
        <!-- Video Wrapper -->
        <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-purple-500/20 p-3 shadow-2xl transition-all duration-300 hover:border-purple-500/40">
            <video controls class="w-full rounded-xl">
                <source src="{{ asset('standup-demo.mp4') }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>

        <!-- Upgrades Section -->
        <div class="mt-8">
            <p class="text-gray-400 text-base mb-3 font-medium">
                Missed the upgrades? Take a look at them below:
            </p>
            <a href="https://aistandup.live/upgrades" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-full transition-all duration-300 shadow-lg hover:shadow-purple-500/25 hover:scale-105"
               target="_blank">
                View Upgrades
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    /* Custom video player styling */
    video {
        width: 100%;
        max-height: 500px;
        object-fit: contain;
        background: #000;
        outline: none;
    }
    
    video:focus {
        outline: none;
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #1a1a1a;
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(to bottom, #8b5cf6, #ec4899);
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(to bottom, #7c3aed, #db2777);
    }
</style>
@endsection