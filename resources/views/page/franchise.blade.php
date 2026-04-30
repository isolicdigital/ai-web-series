@extends('layouts.app')

@section('title', 'Thank You - ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-black py-[120px] px-4">
    <div class="container mx-auto max-w-4xl">
        <div class="max-w-5xl w-full text-center">
            <!-- Header -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 mb-4 shadow-lg shadow-purple-500/25">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-3 bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                    Thank You for Your Purchase!
                </h1>
                
                <p class="text-gray-300 text-lg">
                    We're thrilled to have you as an {{ config('app.name') }} customer. Your purchase has been successfully processed.
                </p>
            </div>
            
            <!-- Support Button -->
            <div class="text-center mb-8">
                <a href="{{ env('SUPPORT_EXT', 'https://aistandup.tawk.help/') }}" 
                   target="_blank" 
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-8 py-4 rounded-full font-semibold text-lg transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-purple-500/25">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                    </svg>
                    Contact Support for Franchise Access
                </a>
            </div>
            
            <!-- Help Text -->
            <div class="bg-purple-500/5 rounded-xl p-6 border-l-4 border-purple-500 text-left">
                <p class="text-gray-400 text-sm">
                    Please open a support ticket to request your Franchise access. Our team will get back to you within 24 hours.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection