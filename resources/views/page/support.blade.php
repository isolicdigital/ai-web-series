@extends('layouts.blank')

@section('metadata')
    <title>Login Instructions - {{ config('app.name') }}</title>
    <meta name="description" content="Your login credentials and support information for {{ config('app.name') }}">
@endsection

@section('content')
<div class="min-h-screen bg-black py-[120px] md:py-[120px] sm:py-[60px] px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Logo -->
        <div class="text-center mb-10">
            <a href="/" class="inline-block transition-transform hover:scale-105 duration-300">
                <img src="{{ asset('custom/brand/frontend-logo.png') }}" alt="{{ config('app.name') }}" class="h-14 w-auto">
            </a>
        </div>
        
        <!-- Hero Section -->
        <div class="text-center mb-10">
            <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-white via-purple-400 to-pink-500 bg-clip-text text-transparent mb-3">
                Welcome to {{ config('app.name') }}
            </h1>
            <p class="text-gray-400 text-lg">Your login information and support options</p>
        </div>
        
        <!-- Credentials Card -->
        <div class="bg-gray-900/50 backdrop-blur-lg rounded-2xl border border-gray-800 p-6 md:p-8 mb-8 transition-all duration-300 hover:border-purple-500/40 hover:shadow-lg hover:shadow-purple-500/10">
            <div class="flex flex-wrap justify-between items-center gap-4 pb-4 border-b border-gray-800 mb-5">
                <div class="flex items-center gap-3">
                    <i class="fas fa-user-circle text-3xl text-purple-400"></i>
                    <h3 class="text-xl font-semibold text-white">Your Login Credentials</h3>
                </div>
                <button onclick="copyAllCredentials()" 
                        class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-5 py-2 rounded-full text-sm font-semibold flex items-center gap-2 transition-all duration-300 hover:scale-105 hover:shadow-lg">
                    <i class="fas fa-copy"></i>
                    Copy All
                </button>
            </div>
            
            <p class="text-gray-300 text-sm mb-6">Thank you for purchasing <strong class="text-purple-400">{{ config('app.name') }}</strong>! Below are your login details:</p>
            
            <div class="space-y-4">
                <!-- Login URL -->
                <div class="flex flex-wrap items-center gap-4 p-3 bg-black/50 rounded-xl border border-gray-800 transition-all duration-300 hover:border-purple-500/30">
                    <div class="flex items-center gap-2 min-w-[120px]">
                        <i class="fas fa-globe text-purple-400"></i>
                        <span class="text-gray-400 font-medium">Login URL:</span>
                    </div>
                    <div class="flex-1">
                        <a href="{{ url('/') }}" target="_blank" class="text-purple-400 hover:text-pink-400 font-medium inline-flex items-center gap-2 transition-all duration-300 hover:gap-3">
                            {{ url('/') }}
                            <i class="fas fa-external-link-alt text-xs"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Email -->
                <div class="flex flex-wrap items-center gap-4 p-3 bg-black/50 rounded-xl border border-gray-800 transition-all duration-300 hover:border-purple-500/30">
                    <div class="flex items-center gap-2 min-w-[120px]">
                        <i class="fas fa-envelope text-purple-400"></i>
                        <span class="text-gray-400 font-medium">Email:</span>
                    </div>
                    <div class="flex-1 flex items-center gap-2">
                        <span class="text-gray-300">Your Purchase Email Address</span>
                        <div class="relative group">
                            <i class="fas fa-info-circle text-gray-500 cursor-help hover:text-purple-400 transition-colors"></i>
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none border border-gray-700">
                                Use the email address associated with your purchase
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Password -->
                <div class="flex flex-wrap items-center gap-4 p-3 bg-black/50 rounded-xl border border-gray-800 transition-all duration-300 hover:border-purple-500/30">
                    <div class="flex items-center gap-2 min-w-[120px]">
                        <i class="fas fa-lock text-purple-400"></i>
                        <span class="text-gray-400 font-medium">Password:</span>
                    </div>
                    <div class="flex-1 flex items-center gap-3">
                        <code id="password-text" class="bg-black/50 px-3 py-1.5 rounded-lg text-yellow-400 font-mono text-sm border border-yellow-500/30">
                            {{ env('DEF_PW', 'default123') }}
                        </code>
                        <button onclick="copyPassword()" 
                                class="text-purple-400 hover:text-pink-400 text-sm flex items-center gap-1 transition-all duration-300 hover:gap-2">
                            <i class="fas fa-copy"></i>
                            Copy
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 p-3 bg-blue-500/10 rounded-lg border border-blue-500/20">
                <p class="text-gray-300 text-sm text-center">
                    <i class="fas fa-info-circle text-blue-400 mr-2"></i>
                    Once logged in, you'll find all training materials in the <strong class="text-blue-400">left menu at the top</strong>. These resources will help you use the platform effectively.
                </p>
            </div>
        </div>

        <!-- Support Card -->
        <div class="bg-gray-900/50 backdrop-blur-lg rounded-2xl border border-gray-800 p-6 md:p-8 mb-8 transition-all duration-300 hover:border-purple-500/40 hover:shadow-lg hover:shadow-purple-500/10">
            <h3 class="text-xl font-semibold text-white mb-2">Get Help When You Need It</h3>
            <p class="text-gray-400 mb-5">Our support team is dedicated to helping you succeed:</p>
            
            <div class="bg-purple-500/10 rounded-xl p-4 mb-5 border border-purple-500/20 transition-all duration-300 hover:bg-purple-500/15">
                <div class="flex gap-3">
                    <i class="fas fa-info-circle text-purple-400 text-lg mt-1"></i>
                    <div class="text-gray-300 text-sm space-y-2">
                        <p><strong class="text-purple-400">Our support team is here to assist you as quickly as possible.</strong></p>
                        <p>Due to high demand and different time zones, we operate on a first-come, first-served basis, with a response time of 24–48 hours (excluding Sundays).</p>
                        <p>Your satisfaction is our top priority. We truly appreciate your patience and understanding during this busy time.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-5 p-4 bg-gradient-to-r from-purple-600/10 to-pink-600/10 rounded-xl border border-purple-500/20 mb-5 transition-all duration-300 hover:from-purple-600/15 hover:to-pink-600/15">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center shadow-lg">
                    <i class="fas fa-envelope text-white text-xl"></i>
                </div>
                <div class="flex-1">
                    <h4 class="text-white font-semibold">Support Team</h4>
                    <p class="text-gray-400 text-sm">Direct message support for all your questions</p>
                </div>
                <a href="{{ env('SUPPORT_EXT', '#') }}" target="_blank" 
                   class="bg-white/10 hover:bg-white/20 text-white px-5 py-2 rounded-full text-sm font-medium flex items-center gap-2 transition-all duration-300 hover:gap-3 hover:scale-105">
                    Open Desk
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="bg-yellow-500/10 rounded-xl p-4 border border-yellow-500/20 transition-all duration-300 hover:bg-yellow-500/15">
                <div class="flex gap-3">
                    <i class="fas fa-lightbulb text-yellow-400 text-lg"></i>
                    <p class="text-gray-300 text-sm"><strong class="text-yellow-400">Pro Tip:</strong> Most questions can be answered quickly in our training materials. Check there first to save time!</p>
                </div>
            </div>
        </div>

        <!-- Welcome Card -->
        <div class="bg-gradient-to-br from-purple-600/10 to-pink-600/10 rounded-2xl border border-purple-500/20 p-6 md:p-8 text-center transition-all duration-300 hover:shadow-lg hover:shadow-purple-500/10">
            <div class="welcome-header mb-4">
                <h3 class="text-2xl font-bold text-white">Welcome to the Future of AI</h3>
            </div>
            <p class="text-gray-300 mb-6 leading-relaxed">
                Thank you for choosing <strong class="text-purple-400">{{ config('app.name') }}</strong>. We're thrilled to have you join our community of innovators and creators. Our team is committed to providing you with the tools and support you need to achieve remarkable results.
            </p>
            <div class="flex flex-wrap justify-center gap-6 mb-6">
                <div class="flex items-center gap-2 text-gray-300 transition-all duration-300 hover:scale-105">
                    <i class="fas fa-bolt text-yellow-400"></i>
                    <span class="text-sm">Powerful AI Technology</span>
                </div>
                <div class="flex items-center gap-2 text-gray-300 transition-all duration-300 hover:scale-105">
                    <i class="fas fa-shield-alt text-green-400"></i>
                    <span class="text-sm">Enterprise-Grade Security</span>
                </div>
                <div class="flex items-center gap-2 text-gray-300 transition-all duration-300 hover:scale-105">
                    <i class="fas fa-sync text-blue-400"></i>
                    <span class="text-sm">Continuous Updates</span>
                </div>
            </div>
            <div class="signature pt-4 border-t border-white/10">
                <strong class="text-white">Ready to create something amazing?</strong>
                <p class="text-gray-400 text-sm mt-2">The {{ config('app.name') }} Team</p>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="global-toast" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-3 rounded-xl shadow-lg z-50 transition-all duration-300 opacity-0 translate-y-4 pointer-events-none">
    <i class="fas fa-check-circle mr-2"></i>
    <span id="toast-message">Copied to clipboard!</span>
</div>

<script>
    function copyPassword() {
        const passwordText = document.getElementById('password-text').textContent;
        copyToClipboard(passwordText, 'Password');
    }

    function copyAllCredentials() {
        const introText = document.querySelector('.credentials-intro').textContent;
        const credentialsItems = document.querySelectorAll('.credential-item');
        
        let credentialsText = '';
        credentialsItems.forEach(item => {
            const label = item.querySelector('.credential-label span').textContent;
            let value = '';
            
            if (item.querySelector('.url-link')) {
                value = item.querySelector('.url-link').textContent.trim();
            } else if (item.querySelector('.password-text')) {
                value = item.querySelector('.password-text').textContent;
            } else {
                value = item.querySelector('.credential-value span').textContent;
            }
            
            credentialsText += `${label} ${value}\n`;
        });
        
        const fullText = `${introText}\n\n${credentialsText}`;
        copyToClipboard(fullText, 'All credentials');
    }

    function copyToClipboard(text, type) {
        navigator.clipboard.writeText(text).then(() => {
            showToast(`${type} copied to clipboard!`);
        }).catch(() => {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showToast(`${type} copied to clipboard!`);
        });
    }

    function showToast(message) {
        const toast = document.getElementById('global-toast');
        const toastMessage = document.getElementById('toast-message');
        
        toastMessage.textContent = message;
        toast.classList.remove('opacity-0', 'translate-y-4');
        toast.classList.add('opacity-100', 'translate-y-0');
        
        setTimeout(() => {
            toast.classList.remove('opacity-100', 'translate-y-0');
            toast.classList.add('opacity-0', 'translate-y-4');
        }, 3000);
    }
</script>

<style>
    @media (max-width: 640px) {
        .py-\[120px\] {
            padding-top: 60px !important;
            padding-bottom: 60px !important;
        }
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