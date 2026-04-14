@extends('layouts.app')

@section('title', 'WhiteLabel Setup - ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-950 via-purple-950 to-slate-950 py-[120px] sm:py-[60px] px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Main Container -->
        <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-purple-500/20 p-6 md:p-8 transition-all duration-300 hover:border-purple-500/40">
            
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-white via-purple-400 to-pink-500 bg-clip-text text-transparent mb-3">
                    Congratulations On Your WhiteLabel Purchase!
                </h1>
                <p class="text-gray-400 text-base">You're now ready to launch your own branded AI comedy platform</p>
            </div>
            
            <!-- Featured Image -->
            <div class="mb-8">
                <img src="https://cdn.convertri.com/ae86ebe2-499f-11e6-829d-066a9bd5fb79%2F103a9277c0a16132cdb65e74bbc6a1d535399f2a%2FIntro%20IMG.png" 
                     alt="{{ config('app.name') }}" 
                     class="w-full rounded-xl border border-purple-500/20">
            </div>
            
            <!-- Info Card -->
            <div class="bg-purple-500/5 rounded-xl p-6 mb-6 border-l-4 border-purple-500">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-clipboard-list text-purple-400"></i>
                    📋 Follow These Steps
                </h3>
                <ul class="space-y-6">
                    <!-- Step 1 -->
                    <li class="flex gap-4">
                        <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                            1
                        </div>
                        <div class="flex-1">
                            <h4 class="text-white font-semibold mb-1">Prepare Your Domain</h4>
                            <p class="text-gray-400 text-sm">Decide on your domain name (e.g., <code class="bg-black/50 px-2 py-0.5 rounded text-purple-400">yourbrand.com</code> or <code class="bg-black/50 px-2 py-0.5 rounded text-purple-400">app.yourbrand.com</code>)</p>
                        </div>
                    </li>
                    
                    <!-- Step 2 -->
                    <li class="flex gap-4">
                        <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                            2
                        </div>
                        <div class="flex-1">
                            <h4 class="text-white font-semibold mb-1">Prepare Your Logo</h4>
                            <p class="text-gray-400 text-sm">If you have a logo, keep it ready. If not, we'll create one for you.</p>
                        </div>
                    </li>
                    
                    <!-- Step 3 -->
                    <li class="flex gap-4">
                        <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                            3
                        </div>
                        <div class="flex-1">
                            <h4 class="text-white font-semibold mb-1">Point Your Domain to Our Server</h4>
                            <p class="text-gray-400 text-sm mb-2">Create an <strong class="text-purple-400">A record</strong> in your DNS settings:</p>
                            <div class="flex flex-wrap items-center gap-3 mt-2">
                                <code class="bg-black/50 px-4 py-2 rounded-lg text-yellow-400 font-mono text-sm border border-yellow-500/30">
                                    62.146.182.136
                                </code>
                                <button onclick="copyIP()" 
                                        class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-all duration-300 hover:scale-105">
                                    <i class="fas fa-copy"></i>
                                    Copy IP
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            
            <!-- Support Section -->
            <div class="text-center mt-8 pt-6 border-t border-white/10">
                <a href="https://aistandup.tawk.help/" 
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-6 py-3 rounded-full font-semibold transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-purple-500/25"
                   target="_blank">
                    <i class="fas fa-headset"></i>
                    Contact Support
                </a>
                <p class="text-gray-400 text-sm mt-4">
                    After completing the steps above, contact support to submit your domain and logo.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function copyIP() {
    const ip = '62.146.182.136';
    navigator.clipboard.writeText(ip);
    
    // Show toast notification
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-3 rounded-xl shadow-lg z-50 transition-all duration-300 opacity-0 translate-y-4';
    toast.innerHTML = '<i class="fas fa-check-circle mr-2"></i> IP Address copied to clipboard!';
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('opacity-0', 'translate-y-4');
        toast.classList.add('opacity-100', 'translate-y-0');
    }, 10);
    
    setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', 'translate-y-4');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
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
</style>
@endsection