@extends('layouts.auth')

@section('title', 'Server Error')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-black to-gray-900 flex items-center justify-center p-4">
    <div class="max-w-6xl w-full mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            
            <!-- Left Panel - Error Message -->
            <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8 md:p-10 text-center shadow-2xl">
                <div class="mb-8">
                    <a href="{{ url('/') }}" class="inline-block">
                        <img src="{{ asset('custom/brand/frontend-logo.png') }}" alt="{{ config('app.name') }}" class="h-12 w-auto mx-auto">
                    </a>
                </div>
                
                <div class="mb-6">
                    <div class="text-8xl md:text-9xl font-black bg-gradient-to-r from-red-500 to-orange-500 bg-clip-text text-transparent mb-4">
                        500
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white mb-3">Server Error</h1>
                    <p class="text-gray-400">Something went wrong on our end. Please try again later.</p>
                </div>
                
                <a href="{{ url('/') }}" 
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-purple-500/25 group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Go to Homepage
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            
            <!-- Right Panel - Graphic -->
            <div class="hidden lg:flex flex-col items-center justify-center text-center">
                <div class="relative mb-6">
                    <!-- Animated Warning Rings -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-40 h-40 border-4 border-red-500/20 rounded-full animate-ping"></div>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-32 h-32 border-4 border-orange-500/30 rounded-full animate-pulse"></div>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-24 h-24 border-4 border-red-500/20 rounded-full animate-ping" style="animation-delay: 0.5s;"></div>
                    </div>
                    <div class="relative w-16 h-16 bg-gradient-to-br from-red-500 to-orange-500 rounded-full flex items-center justify-center shadow-2xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
                
                <h2 class="text-2xl font-bold text-white mb-2">Technical Difficulties</h2>
                <p class="text-gray-400">Our team has been notified. Please check back soon.</p>
                
                <div class="mt-8 flex gap-3">
                    <div class="w-2 h-2 rounded-full bg-red-500 animate-ping-slow"></div>
                    <div class="w-2 h-2 rounded-full bg-orange-500 animate-ping-slow animation-delay-200"></div>
                    <div class="w-2 h-2 rounded-full bg-red-500 animate-ping-slow animation-delay-500"></div>
                </div>
                
                <!-- Retry Button -->
                <button onclick="location.reload()" 
                        class="mt-6 inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white px-4 py-2 rounded-lg font-medium transition-all duration-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Try Again
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes ping-slow {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        75%, 100% {
            transform: scale(2);
            opacity: 0;
        }
    }
    
    .animate-ping-slow {
        animation: ping-slow 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
    }
    
    .animation-delay-200 {
        animation-delay: 200ms;
    }
    
    .animation-delay-500 {
        animation-delay: 500ms;
    }
</style>
@endsection