@extends('layouts.app')

@section('title', 'Buy Credits - ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 py-[120px] px-4 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-pink-500/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="text-center mb-12 animate-fade-in">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full mb-4 border border-white/20">
                <i class="fas fa-gem text-yellow-400 text-sm"></i>
                <span class="text-white/90 text-sm font-medium">Credit Plans</span>
                <span class="text-white/40 text-xs">Best Value</span>
            </div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-4 tracking-tight">
                Choose Your
                <span class="bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent">Credit Plan</span>
            </h1>
            <p class="text-gray-300 text-lg max-w-2xl mx-auto">
                Select the perfect plan for your needs. All credits never expire!
            </p>
            
            <!-- Credit Info Banner -->
            <div class="mt-6 inline-flex items-center gap-3 bg-white/5 rounded-full px-4 py-2">
                <i class="fas fa-info-circle text-blue-400 text-sm"></i>
                <span class="text-gray-300 text-sm">Each video costs <strong class="text-yellow-400">200 credits</strong></span>
                <i class="fas fa-infinity text-green-400 text-sm"></i>
                <span class="text-gray-300 text-sm">Credits never expire</span>
            </div>
        </div>

        <!-- Pricing Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 mt-8">
            @foreach($plans as $plan)
            <div class="group relative transform transition-all duration-300 hover:-translate-y-2">
                <!-- Glow Effect -->
                <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl blur opacity-0 group-hover:opacity-100 transition duration-300 {{ $plan->is_featured ? 'opacity-50' : '' }}"></div>
                
                <!-- Card -->
                <div class="relative bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/20 overflow-hidden transition-all duration-300 group-hover:border-purple-500/50">
                    
                    <!-- Featured Badge -->
                    @if($plan->is_featured)
                    <div class="absolute top-4 right-4">
                        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1 shadow-lg">
                            <i class="fas fa-crown text-xs"></i>
                            <span>BEST VALUE</span>
                        </div>
                    </div>
                    @endif

                    <!-- Savings Badge -->
                    @if($plan->savings_percentage > 0)
                    <div class="absolute top-4 left-4">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                            SAVE {{ $plan->savings_percentage }}%
                        </div>
                    </div>
                    @endif

                    <!-- Card Content -->
                    <div class="p-6 md:p-8">
                        <!-- Plan Name -->
                        <div class="text-center mb-6">
                            <h3 class="text-2xl md:text-3xl font-bold text-white mb-2">{{ $plan->name }}</h3>
                            <div class="w-20 h-1 bg-gradient-to-r from-purple-500 to-pink-500 mx-auto rounded-full"></div>
                        </div>

                        <!-- Price -->
                        <div class="text-center mb-6">
                            @if($plan->original_price)
                            <div class="text-gray-400 text-sm line-through mb-1">
                                ${{ number_format($plan->original_price, 2) }}
                            </div>
                            @endif
                            <div class="flex items-center justify-center gap-1">
                                <span class="text-3xl text-gray-400 font-semibold">$</span>
                                <span class="text-5xl md:text-6xl font-black text-white">{{ number_format($plan->price, 2) }}</span>
                            </div>
                            <div class="text-gray-400 text-sm mt-2">One-time payment</div>
                        </div>

                        <!-- Credits Info -->
                        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-xl p-4 text-center mb-6">
                            <div class="flex items-center justify-center gap-2">
                                <i class="fas fa-gem text-yellow-400 text-xl"></i>
                                <span class="text-3xl font-bold text-white">{{ number_format($plan->video_credits) }}</span>
                                <span class="text-gray-300">Credits</span>
                            </div>
                            <div class="text-sm text-gray-300 mt-1">
                                ≈ {{ floor($plan->video_credits / 200) }} Videos
                            </div>
                        </div>

                        <!-- Features List -->
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-3 text-gray-300">
                                <i class="fas fa-check-circle text-green-400 text-sm"></i>
                                <span>{{ number_format($plan->video_credits) }} Credits Included</span>
                            </li>
                            <li class="flex items-center gap-3 text-gray-300">
                                <i class="fas fa-infinity text-green-400 text-sm"></i>
                                <span>Never Expire</span>
                            </li>
                            <li class="flex items-center gap-3 text-gray-300">
                                <i class="fas fa-bolt text-yellow-400 text-sm"></i>
                                <span>Instant Activation</span>
                            </li>
                            <li class="flex items-center gap-3 text-gray-300">
                                <i class="fas fa-video text-purple-400 text-sm"></i>
                                <span>Flat 200 Credits/Video</span>
                            </li>
                            @if($plan->slug == 'starter')
                            <li class="flex items-center gap-3 text-gray-300">
                                <i class="fas fa-headset text-blue-400 text-sm"></i>
                                <span>24/7 Standard Support</span>
                            </li>
                            @elseif($plan->slug == 'pro')
                            <li class="flex items-center gap-3 text-gray-300">
                                <i class="fas fa-star text-yellow-400 text-sm"></i>
                                <span>Priority Support</span>
                            </li>
                            <li class="flex items-center gap-3 text-gray-300">
                                <i class="fas fa-chart-line text-green-400 text-sm"></i>
                                <span>Analytics Dashboard</span>
                            </li>
                            @else
                            <li class="flex items-center gap-3 text-gray-300">
                                <i class="fas fa-crown text-yellow-400 text-sm"></i>
                                <span>VIP Support</span>
                            </li>
                            <li class="flex items-center gap-3 text-gray-300">
                                <i class="fas fa-gem text-purple-400 text-sm"></i>
                                <span>Maximum Savings</span>
                            </li>
                            <li class="flex items-center gap-3 text-gray-300">
                                <i class="fas fa-rocket text-blue-400 text-sm"></i>
                                <span>Premium Features</span>
                            </li>
                            <li class="flex items-center gap-3 text-gray-300">
                                <i class="fas fa-shield-alt text-green-400 text-sm"></i>
                                <span>No Temporary Blocks</span>
                            </li>
                            @endif
                        </ul>

                        <!-- Buy Button -->
                        <a href="https://www.jvzoo.com/b/116137/{{ $plan->wp_id }}/2" 
                           class="group relative w-full py-3.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 overflow-hidden"
                           target="_blank">
                            <span class="relative z-10">Buy Now</span>
                            <i class="fas fa-arrow-right relative z-10 group-hover:translate-x-1 transition-transform"></i>
                            <div class="absolute inset-0 bg-gradient-to-r from-purple-500 to-pink-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- No Thanks Link -->
        <div class="text-center mt-10">
            <a href="https://www.jvzoo.com/nothanks/437027" 
               class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition-colors group"
               target="_blank">
                <i class="fas fa-times-circle group-hover:rotate-90 transition-transform"></i>
                <span>No thanks, I'll use the free version</span>
            </a>
        </div>

        <!-- Disclaimer Section -->
        <div class="mt-12 p-6 bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10">
            <div class="flex items-start gap-3">
                <i class="fas fa-shield-alt text-gray-500 text-lg mt-0.5"></i>
                <div class="text-xs text-gray-400 leading-relaxed space-y-2">
                    <p><strong class="text-gray-300">Disclaimer:</strong> This product does not provide any guarantee of income or success. The results achieved by the product owner or any other individuals mentioned are not indicative of future success or earnings. This website is not affiliated with FaceBook or any of its associated entities. Once you navigate away from FaceBook, the responsibility for the content and its usage lies solely with the user.</p>
                    <p>All content on this website, including but not limited to text, images, and multimedia, is protected by copyright law and the Digital Millennium Copyright Act. Unauthorized copying, duplication, modification, or theft, whether intentional or unintentional, is strictly prohibited. Violators will be prosecuted to the fullest extent of the law.</p>
                    <p>We want to clarify that JVZoo serves as the retailer for the products featured on this site. JVZoo® is a registered trademark of BBC Systems Inc., a Florida corporation located at 1809 E. Broadway Street, Suite 125, Oviedo, FL 32765, USA, and is used with permission. The role of JVZoo as a retailer does not constitute an endorsement, approval, or review of these products or any claims, statements, or opinions used in their promotion. The word "lifetime" applies to the lifetime of the product. This average lifetime of a product of this nature and price to be supported is approximately 5 years.</p>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-12 pb-12">
            <h3 class="text-xl font-bold text-white text-center mb-6">Frequently Asked Questions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white/5 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-question-circle text-purple-400"></i>
                        <h4 class="text-white font-semibold">Do credits expire?</h4>
                    </div>
                    <p class="text-gray-400 text-sm">No, all purchased credits never expire. You can use them anytime.</p>
                </div>
                <div class="bg-white/5 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-bolt text-purple-400"></i>
                        <h4 class="text-white font-semibold">How fast is activation?</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Credits are activated instantly after successful payment.</p>
                </div>
                <div class="bg-white/5 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-refresh text-purple-400"></i>
                        <h4 class="text-white font-semibold">Can I get a refund?</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Please contact support for refund policy details.</p>
                </div>
                <div class="bg-white/5 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-headset text-purple-400"></i>
                        <h4 class="text-white font-semibold">Need more help?</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Contact our support team for any questions.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in {
        animation: fade-in 0.6s ease-out;
    }
    
    .delay-1000 {
        animation-delay: 1s;
    }
    
    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #1e1b4b;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #7c3aed;
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #8b5cf6;
    }
    
    /* Responsive spacing */
    @media (max-width: 768px) {
        .py-\[120px\] {
            padding-top: 60px !important;
            padding-bottom: 60px !important;
        }
    }
</style>

<!-- Add responsive spacing override -->
<script>
    // Adjust spacing for mobile devices
    if (window.innerWidth <= 768) {
        const container = document.querySelector('.min-h-screen');
        if (container) {
            container.classList.remove('py-[120px]');
            container.classList.add('py-[60px]');
        }
    }
    
    // Listen for resize events
    window.addEventListener('resize', function() {
        const container = document.querySelector('.min-h-screen');
        if (container) {
            if (window.innerWidth <= 768) {
                container.classList.remove('py-[120px]');
                container.classList.add('py-[60px]');
            } else {
                container.classList.remove('py-[60px]');
                container.classList.add('py-[120px]');
            }
        }
    });
</script>
@endsection