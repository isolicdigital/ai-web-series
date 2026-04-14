@extends('layouts.auth')

@section('title', 'Forbidden')

@section('content')
<div class="min-h-screen bg-black flex items-center justify-center p-4">
    <div class="max-w-6xl w-full mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <!-- Left Panel - Error Message -->
            <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8 md:p-10 text-center">
                <div class="mb-8">
                    <a href="{{ url('/') }}" class="inline-block">
                        <img src="{{ asset('custom/brand/frontend-logo.png') }}" alt="{{ config('app.name') }}" class="h-12 w-auto mx-auto">
                    </a>
                </div>
                
                <div class="mb-6">
                    <div class="text-8xl md:text-9xl font-black bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-4">
                        403
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white mb-3">Access Forbidden</h1>
                    <p class="text-gray-400">You don't have permission to access this page.</p>
                </div>
                
                <a href="{{ url('/') }}" 
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-purple-500/25">
                    <i class="fas fa-home"></i>
                    Go to Homepage
                    <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
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
                        <i class="fas fa-lock text-white text-4xl"></i>
                    </div>
                </div>
                
                <h2 class="text-2xl font-bold text-white mb-2">Restricted Area</h2>
                <p class="text-gray-400">This area is off-limits to your account.</p>
                
                <div class="mt-8 flex gap-3">
                    <div class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></div>
                    <div class="w-2 h-2 rounded-full bg-pink-500 animate-pulse delay-150"></div>
                    <div class="w-2 h-2 rounded-full bg-purple-500 animate-pulse delay-300"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes ping {
        75%, 100% {
            transform: scale(2);
            opacity: 0;
        }
    }
    
    .animate-ping {
        animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    .delay-150 {
        animation-delay: 150ms;
    }
    
    .delay-300 {
        animation-delay: 300ms;
    }
</style>
@endsection