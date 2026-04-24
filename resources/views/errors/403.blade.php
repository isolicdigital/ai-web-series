@extends('layouts.auth')

@section('title', 'Forbidden')

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
                    <div class="text-8xl md:text-9xl font-black bg-gradient-to-r from-red-500 to-pink-600 bg-clip-text text-transparent mb-4">
                        403
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white mb-3">Access Forbidden</h1>
                    <p class="text-gray-400">You don't have permission to access this page.</p>
                </div>
                
                <a href="{{ url('/') }}" 
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-purple-500/25">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Go to Homepage
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            
            <!-- Right Panel - Graphic -->
            <div class="hidden lg:flex flex-col items-center justify-center text-center">
                <div class="relative mb-6">
                    <!-- Animated Rings -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-40 h-40 border-4 border-purple-500/20 rounded-full animate-ping"></div>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-32 h-32 border-4 border-purple-500/30 rounded-full animate-pulse"></div>
                    </div>
                    <div class="relative w-24 h-24 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full flex items-center justify-center shadow-2xl">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                </div>
                
                <h2 class="text-2xl font-bold text-white mb-2">Restricted Area</h2>
                <p class="text-gray-400">This area is off-limits to your account.</p>
                
                <div class="mt-8 flex gap-3">
                    <div class="w-2 h-2 rounded-full bg-purple-500 animate-ping-slow"></div>
                    <div class="w-2 h-2 rounded-full bg-pink-500 animate-ping-slow animation-delay-200"></div>
                    <div class="w-2 h-2 rounded-full bg-purple-500 animate-ping-slow animation-delay-500"></div>
                </div>
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
    
    @keyframes pulse-ring {
        0%, 100% {
            transform: scale(1);
            opacity: 0.3;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.6;
        }
    }
    
    .animate-pulse-ring {
        animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
@endsection