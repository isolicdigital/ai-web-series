@extends('layouts.app')

@section('title', 'Buy Credits - ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-black py-[120px] px-4 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-pink-500/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-500/5 rounded-full blur-3xl"></div>
        <!-- Grid Pattern -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" xmlns="http://www.w3.org/2000/svg"%3E%3Cdefs%3E%3Cpattern id="grid" width="60" height="60" patternUnits="userSpaceOnUse"%3E%3Cpath d="M 60 0 L 0 0 0 60" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="1"/%3E%3C/pattern%3E%3C/defs%3E%3Crect width="100%25" height="100%25" fill="url(%23grid)"/%3E%3C/svg%3E')] opacity-50"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-2 bg-white/5 backdrop-blur-sm px-4 py-2 rounded-full mb-4 border border-white/10">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                <span class="text-white/90 text-sm font-medium">Credit Plans</span>
                <span class="w-px h-4 bg-white/20"></span>
                <span class="text-yellow-400 text-xs font-semibold">Best Value</span>
            </div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-4 tracking-tight">
                Choose Your
                <span class="bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent">Credit Plan</span>
            </h1>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Select the perfect plan for your needs. All credits never expire!
            </p>
            
            <!-- Credit Info Banner -->
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <div class="inline-flex items-center gap-2 bg-white/5 rounded-full px-4 py-2 border border-white/10">
                    <i class="fas fa-info-circle text-blue-400 text-sm"></i>
                    <span class="text-gray-300 text-sm">Each video costs <strong class="text-yellow-400">200 credits</strong></span>
                </div>
                <div class="inline-flex items-center gap-2 bg-white/5 rounded-full px-4 py-2 border border-white/10">
                    <i class="fas fa-infinity text-green-400 text-sm"></i>
                    <span class="text-gray-300 text-sm">Credits never expire</span>
                </div>
                <div class="inline-flex items-center gap-2 bg-white/5 rounded-full px-4 py-2 border border-white/10">
                    <i class="fas fa-bolt text-yellow-400 text-sm"></i>
                    <span class="text-gray-300 text-sm">Instant activation</span>
                </div>
            </div>
        </div>

        <!-- Pricing Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 mt-8">
            @foreach($plans as $plan)
            <div class="relative">
                <!-- Card -->
                <div class="bg-gray-900/50 backdrop-blur-xl rounded-2xl border border-gray-800 overflow-hidden">
                    
                    <!-- Featured Badge -->
                    @if($plan->is_featured)
                    <div class="absolute top-4 right-4 z-10">
                        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1 shadow-lg">
                            <i class="fas fa-crown text-xs"></i>
                            <span>BEST VALUE</span>
                        </div>
                    </div>
                    @endif

                    <!-- Savings Badge -->
                    @if($plan->savings_percentage > 0)
                    <div class="absolute top-4 left-4 z-10">
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
                            <div class="w-16 h-1 bg-gradient-to-r from-purple-500 to-pink-500 mx-auto rounded-full"></div>
                        </div>

                        <!-- Price -->
                        <div class="text-center mb-6">
                            @if($plan->original_price)
                            <div class="text-gray-500 text-sm line-through mb-1">
                                ${{ number_format($plan->original_price, 2) }}
                            </div>
                            @endif
                            <div class="flex items-center justify-center gap-1">
                                <span class="text-2xl text-gray-400 font-semibold">$</span>
                                <span class="text-5xl md:text-6xl font-black text-white">{{ number_format($plan->price, 2) }}</span>
                            </div>
                            <div class="text-gray-500 text-sm mt-2">One-time payment</div>
                        </div>

                        <!-- Credits Info -->
                        <div class="bg-gradient-to-r from-purple-600/20 to-pink-600/20 rounded-xl p-4 text-center mb-6 border border-purple-500/20">
                            <div class="flex items-center justify-center gap-2">
                                <i class="fas fa-gem text-yellow-400 text-xl"></i>
                                <span class="text-3xl font-bold text-white">{{ number_format($plan->video_credits) }}</span>
                                <span class="text-gray-300">Credits</span>
                            </div>
                            <div class="text-sm text-gray-400 mt-1">
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
                           class="w-full py-3.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-lg"
                           target="_blank">
                            <span>Buy Now</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- No Thanks Link -->
        <div class="text-center mt-10">
            <a href="https://www.jvzoo.com/nothanks/437027" 
               class="inline-flex items-center gap-2 text-gray-500 hover:text-white transition-colors"
               target="_blank">
                <i class="fas fa-times-circle"></i>
                <span>No thanks, I'll use the free version</span>
            </a>
        </div>

        <!-- Trust Badges -->
        <div class="mt-12 flex flex-wrap items-center justify-center gap-6">
            <div class="flex items-center gap-2 text-gray-500">
                <i class="fas fa-lock text-green-500"></i>
                <span class="text-sm">Secure Payment</span>
            </div>
            <div class="w-px h-4 bg-gray-700"></div>
            <div class="flex items-center gap-2 text-gray-500">
                <i class="fas fa-undo-alt text-blue-500"></i>
                <span class="text-sm">30-Day Refund Policy</span>
            </div>
            <div class="w-px h-4 bg-gray-700"></div>
            <div class="flex items-center gap-2 text-gray-500">
                <i class="fas fa-headset text-purple-500"></i>
                <span class="text-sm">24/7 Support</span>
            </div>
            <div class="w-px h-4 bg-gray-700"></div>
            <div class="flex items-center gap-2 text-gray-500">
                <i class="fas fa-bolt text-yellow-500"></i>
                <span class="text-sm">Instant Delivery</span>
            </div>
        </div>

        <!-- Disclaimer Section -->
        <div class="mt-12 p-6 bg-gray-900/30 backdrop-blur-sm rounded-2xl border border-gray-800">
            <div class="flex items-start gap-3">
                <i class="fas fa-shield-alt text-gray-600 text-lg mt-0.5"></i>
                <div class="text-xs text-gray-500 leading-relaxed space-y-2">
                    <p><strong class="text-gray-400">Disclaimer:</strong> This product does not provide any guarantee of income or success. The results achieved by the product owner or any other individuals mentioned are not indicative of future success or earnings. This website is not affiliated with FaceBook or any of its associated entities. Once you navigate away from FaceBook, the responsibility for the content and its usage lies solely with the user.</p>
                    <p>All content on this website, including but not limited to text, images, and multimedia, is protected by copyright law and the Digital Millennium Copyright Act. Unauthorized copying, duplication, modification, or theft, whether intentional or unintentional, is strictly prohibited. Violators will be prosecuted to the fullest extent of the law.</p>
                    <p>We want to clarify that JVZoo serves as the retailer for the products featured on this site. JVZoo® is a registered trademark of BBC Systems Inc., a Florida corporation located at 1809 E. Broadway Street, Suite 125, Oviedo, FL 32765, USA, and is used with permission. The role of JVZoo as a retailer does not constitute an endorsement, approval, or review of these products or any claims, statements, or opinions used in their promotion. The word "lifetime" applies to the lifetime of the product. This average lifetime of a product of this nature and price to be supported is approximately 5 years.</p>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-12 pb-12">
            <h3 class="text-xl font-bold text-white text-center mb-6">Frequently Asked Questions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-900/30 rounded-xl p-4 border border-gray-800">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-question-circle text-purple-400"></i>
                        <h4 class="text-white font-semibold">Do credits expire?</h4>
                    </div>
                    <p class="text-gray-400 text-sm">No, all purchased credits never expire. You can use them anytime you want.</p>
                </div>
                <div class="bg-gray-900/30 rounded-xl p-4 border border-gray-800">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-bolt text-purple-400"></i>
                        <h4 class="text-white font-semibold">How fast is activation?</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Credits are activated instantly after successful payment confirmation.</p>
                </div>
                <div class="bg-gray-900/30 rounded-xl p-4 border border-gray-800">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-refresh text-purple-400"></i>
                        <h4 class="text-white font-semibold">Can I get a refund?</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Yes, we offer a 30-day money-back guarantee. Contact support for assistance.</p>
                </div>
                <div class="bg-gray-900/30 rounded-xl p-4 border border-gray-800">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-headset text-purple-400"></i>
                        <h4 class="text-white font-semibold">Need more help?</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Contact our 24/7 support team at support@example.com for any questions.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes pulse {
        0%, 100% {
            opacity: 0.3;
        }
        50% {
            opacity: 0.6;
        }
    }
    
    .delay-1000 {
        animation-delay: 1s;
    }
    
    /* Custom Scrollbar */
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
    function adjustSpacing() {
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
    }
    
    adjustSpacing();
    window.addEventListener('resize', adjustSpacing);
</script>
@endsection