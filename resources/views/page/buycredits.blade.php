@extends('layouts.app')

@section('title', 'Buy Credits - ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-black py-[120px] px-4 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-pink-500/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-r from-purple-600/5 to-pink-600/5 rounded-full blur-3xl"></div>
        <div class="absolute top-20 right-20 w-64 h-64 bg-blue-500/5 rounded-full blur-3xl animate-pulse delay-700"></div>
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" xmlns="http://www.w3.org/2000/svg"%3E%3Cdefs%3E%3Cpattern id="grid" width="60" height="60" patternUnits="userSpaceOnUse"%3E%3Cpath d="M 60 0 L 0 0 0 60" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="1"/%3E%3C/pattern%3E%3C/defs%3E%3Crect width="100%25" height="100%25" fill="url(%23grid)"/%3E%3C/svg%3E')] opacity-30"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="text-center mb-12" data-aos="fade-up">
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
                <span class="bg-gradient-to-r from-purple-400 via-pink-500 to-orange-500 bg-clip-text text-transparent">Credit Plan</span>
            </h1>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Select the perfect plan for your needs. Transform your imagination into cinematic masterpieces!
            </p>
            
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <div class="inline-flex items-center gap-2 bg-white/5 rounded-full px-4 py-2 border border-white/10">
                    <i class="fas fa-film text-purple-400 text-sm"></i>
                    <span class="text-gray-300 text-sm">Each video segment costs <strong class="text-yellow-400">1,000 credits</strong></span>
                </div>
                <div class="inline-flex items-center gap-2 bg-white/5 rounded-full px-4 py-2 border border-white/10">
                    <i class="fas fa-infinity text-green-400 text-sm"></i>
                    <span class="text-gray-300 text-sm">Credits valid for 1 year</span>
                </div>
                <div class="inline-flex items-center gap-2 bg-white/5 rounded-full px-4 py-2 border border-white/10">
                    <i class="fas fa-bolt text-yellow-400 text-sm"></i>
                    <span class="text-gray-300 text-sm">Instant activation</span>
                </div>
                <div class="inline-flex items-center gap-2 bg-white/5 rounded-full px-4 py-2 border border-white/10">
                    <i class="fas fa-watermark text-red-400 text-sm"></i>
                    <span class="text-gray-300 text-sm">No watermarks</span>
                </div>
            </div>

            <div class="mt-8">
                <button onclick="openCreditModal()" 
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600/20 to-pink-600/20 hover:from-purple-600/30 hover:to-pink-600/30 border border-purple-500/30 rounded-full px-6 py-2.5 text-sm font-medium text-purple-300 transition-all duration-300">
                    <i class="fas fa-calculator"></i>
                    <span>How Credits Are Calculated?</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Pricing Grid - Adjusted card sizes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-6 mt-8">
            @foreach($plans as $plan)
            <div class="relative group h-full" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl opacity-0 group-hover:opacity-100 transition duration-300 blur-xl"></div>
                
                <div class="relative bg-gray-900/80 backdrop-blur-xl rounded-2xl border border-gray-800 group-hover:border-purple-500/50 transition-all duration-300 overflow-hidden h-full flex flex-col">
                    
                    @if($plan->is_featured)
                    <div class="absolute top-4 right-4 z-10">
                        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1 shadow-lg">
                            <i class="fas fa-crown text-xs"></i>
                            <span>BEST VALUE</span>
                        </div>
                    </div>
                    @endif

                    @if($plan->slug == 'unlimited')
                    <div class="absolute top-4 right-4 z-10">
                        <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1 shadow-lg">
                            <i class="fas fa-infinity text-xs"></i>
                            <span>PREMIUM</span>
                        </div>
                    </div>
                    @endif

                    @if($plan->savings_percentage > 0)
                    <div class="absolute top-4 left-4 z-10">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                            SAVE {{ $plan->savings_percentage }}%
                        </div>
                    </div>
                    @endif

                    <div class="p-5 md:p-6 flex flex-col h-full">
                        <div class="text-center mb-4">
                            <div class="w-14 h-14 mx-auto bg-gradient-to-br from-purple-600/20 to-pink-600/20 rounded-2xl flex items-center justify-center border border-purple-500/20 group-hover:scale-110 transition-transform duration-300">
                                @if($plan->slug == 'film')
                                    <i class="fas fa-film text-2xl text-purple-400"></i>
                                @elseif($plan->slug == 'series')
                                    <i class="fas fa-tv text-2xl text-pink-400"></i>
                                @elseif($plan->slug == 'season')
                                    <i class="fas fa-layer-group text-2xl text-yellow-400"></i>
                                @else
                                    <i class="fas fa-infinity text-2xl text-purple-400"></i>
                                @endif
                            </div>
                        </div>

                        <div class="text-center mb-3">
                            <h3 class="text-xl md:text-2xl font-bold text-white mb-1">{{ $plan->name }}</h3>
                            <div class="w-10 h-0.5 bg-gradient-to-r from-purple-500 to-pink-500 mx-auto rounded-full"></div>
                        </div>

                        <div class="text-center mb-3">
                            @if($plan->original_price)
                            <div class="text-gray-500 text-sm line-through mb-1">
                                ${{ number_format($plan->original_price, 2) }}
                            </div>
                            @endif
                            <div class="flex items-center justify-center gap-1">
                                <span class="text-xl text-gray-400 font-semibold">$</span>
                                <span class="text-4xl md:text-5xl font-black text-white">{{ number_format($plan->price, 2) }}</span>
                            </div>
                            <div class="text-gray-500 text-xs mt-1">one-time payment</div>
                        </div>

                        <div class="bg-gradient-to-r from-purple-600/10 to-pink-600/10 rounded-xl p-3 text-center mb-5 border border-purple-500/10">
                            <div class="flex items-center justify-center gap-2">
                                <i class="fas fa-gem text-yellow-400 text-base"></i>
                                @if($plan->slug == 'unlimited')
                                    <span class="text-2xl md:text-3xl font-bold text-white">∞</span>
                                    <span class="text-gray-300 text-sm">Unlimited Credits</span>
                                @else
                                    <span class="text-2xl md:text-3xl font-bold text-white">{{ number_format($plan->video_credits) }}</span>
                                    <span class="text-gray-300 text-sm">Credits</span>
                                @endif
                            </div>
                            @if($plan->slug != 'unlimited')
                            <div class="text-xs text-gray-400 mt-1">
                                ≈ {{ floor($plan->video_credits / 1000) }} Full Episodes
                            </div>
                            @else
                            <div class="text-xs text-gray-400 mt-1">
                                Unlimited • Fair use policy
                            </div>
                            @endif
                        </div>

                        <ul class="space-y-2 mb-5 flex-1">
                            @if($plan->slug == 'film')
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-check-circle text-green-400 text-xs"></i>
                                <span>Full HD 1080p Export</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-watermark text-green-400 text-xs"></i>
                                <span>No Watermarks</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-user text-green-400 text-xs"></i>
                                <span>Personal Use License</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-tachometer-alt text-green-400 text-xs"></i>
                                <span>Standard Rendering</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-envelope text-green-400 text-xs"></i>
                                <span>Email Support</span>
                            </li>
                            @elseif($plan->slug == 'series')
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-check-circle text-green-400 text-xs"></i>
                                <span>Everything in Film</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-briefcase text-green-400 text-xs"></i>
                                <span>Commercial License</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-tachometer-alt text-green-400 text-xs"></i>
                                <span>Priority Email Support</span>
                            </li>
                            @elseif($plan->slug == 'season')
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-check-circle text-green-400 text-xs"></i>
                                <span>Everything in Series</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-4k text-green-400 text-xs"></i>
                                <span>4K Export Quality</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-rocket text-green-400 text-xs"></i>
                                <span>Priority Rendering</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-microphone-alt text-green-400 text-xs"></i>
                                <span>Premium Voice Library</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-headset text-green-400 text-xs"></i>
                                <span>24/7 Chat Support</span>
                            </li>
                            @else
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-check-circle text-green-400 text-xs"></i>
                                <span>Everything in Season</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-infinity text-green-400 text-xs"></i>
                                <span>Unlimited Credits (1 Year)</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-building text-green-400 text-xs"></i>
                                <span>Agency + White Label</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-bolt text-green-400 text-xs"></i>
                                <span>Fastest Rendering</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-crown text-green-400 text-xs"></i>
                                <span>24/7 VIP Support</span>
                            </li>
                            @endif
                            <li class="flex items-center gap-2 text-gray-300 text-xs md:text-sm">
                                <i class="fas fa-calendar-alt text-blue-400 text-xs"></i>
                                <span>Credits Valid 1 Year</span>
                            </li>
                        </ul>

                        <a href="https://www.jvzoo.com/b/116137/{{ $plan->wp_id }}/2" 
                           class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-lg transition-all duration-300 transform hover:scale-105 text-sm md:text-base"
                           target="_blank">
                            <span>Buy {{ $plan->name }}</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- No Thanks Link -->
        <div class="text-center mt-10">
            <a href="https://www.jvzoo.com/nothanks/437027" 
               class="inline-flex items-center gap-2 text-gray-500 hover:text-white transition-colors text-sm"
               target="_blank">
                <i class="fas fa-times-circle"></i>
                <span>No thanks, I'll continue with the free version</span>
            </a>
        </div>

        <!-- Trust Badges -->
        <div class="mt-12 flex flex-wrap items-center justify-center gap-6">
            <div class="flex items-center gap-2 text-gray-500">
                <i class="fas fa-lock text-green-500"></i>
                <span class="text-sm">Secure SSL Payment</span>
            </div>
            <div class="w-px h-4 bg-gray-700"></div>
            <div class="flex items-center gap-2 text-gray-500">
                <i class="fas fa-undo-alt text-blue-500"></i>
                <span class="text-sm">30-Day Money-Back</span>
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
        
        <!-- Guarantee Section -->
        <div class="mt-12 p-8 bg-gradient-to-r from-purple-600/10 to-pink-600/10 rounded-2xl border border-purple-500/20 text-center">
            <i class="fas fa-shield-alt text-4xl text-purple-400 mb-3"></i>
            <h3 class="text-xl font-bold text-white mb-2">30-Day Money-Back Guarantee</h3>
            <p class="text-gray-400 text-sm max-w-2xl mx-auto">
                Try any plan risk-free for 30 days. If you're not completely satisfied, we'll refund your payment in full. No questions asked.
            </p>
        </div>

        <!-- FAQ Section -->
        <div class="mt-12 pb-12">
            <h3 class="text-xl font-bold text-white text-center mb-6">Frequently Asked Questions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-900/30 rounded-xl p-4 border border-gray-800">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-clock text-purple-400"></i>
                        <h4 class="text-white font-semibold">Do credits expire?</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Paid credits are valid for 1 full year from the date of purchase.</p>
                </div>
                <div class="bg-gray-900/30 rounded-xl p-4 border border-gray-800">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-chart-line text-purple-400"></i>
                        <h4 class="text-white font-semibold">Can I upgrade my plan later?</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Yes! Purchase additional credits or upgrade anytime.</p>
                </div>
                <div class="bg-gray-900/30 rounded-xl p-4 border border-gray-800">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-calendar-alt text-purple-400"></i>
                        <h4 class="text-white font-semibold">Is this a subscription?</h4>
                    </div>
                    <p class="text-gray-400 text-sm">No! One-time payment, no recurring charges.</p>
                </div>
                <div class="bg-gray-900/30 rounded-xl p-4 border border-gray-800">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-briefcase text-purple-400"></i>
                        <h4 class="text-white font-semibold">Commercial use allowed?</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Yes, from Series plan and above.</p>
                </div>
                <div class="bg-gray-900/30 rounded-xl p-4 border border-gray-800">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-watermark text-purple-400"></i>
                        <h4 class="text-white font-semibold">Watermark on videos?</h4>
                    </div>
                    <p class="text-gray-400 text-sm">No watermarks on any plan!</p>
                </div>
                <div class="bg-gray-900/30 rounded-xl p-4 border border-gray-800">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-credit-card text-purple-400"></i>
                        <h4 class="text-white font-semibold">Payment methods?</h4>
                    </div>
                    <p class="text-gray-400 text-sm">All major cards, PayPal, SSL secured.</p>
                </div>
            </div>
        </div>

        <!-- Disclaimer Section -->
        <div class="mt-8 p-6 bg-gray-900/30 backdrop-blur-sm rounded-2xl border border-gray-800">
            <div class="flex items-start gap-3">
                <i class="fas fa-shield-alt text-gray-600 text-lg mt-0.5"></i>
                <div class="text-xs text-gray-500 leading-relaxed space-y-2">
                    <p><strong class="text-gray-400">Disclaimer:</strong> This product does not provide any guarantee of income or success. The results achieved by the product owner or any other individuals mentioned are not indicative of future success or earnings. This website is not affiliated with FaceBook or any of its associated entities. Once you navigate away from FaceBook, the responsibility for the content and its usage lies solely with the user.</p>
                   <p>All content on this website, including but not limited to text, images, and multimedia, is protected by copyright law and the Digital Millennium Copyright Act. Unauthorized copying, duplication, modification, or theft, whether intentional or unintentional, is strictly prohibited. Violators will be prosecuted to the fullest extent of the law.</p>
                    <p>We want to clarify that JVZoo serves as the retailer for the products featured on this site. JVZoo® is a registered trademark of BBC Systems Inc., a Florida corporation located at 1809 E. Broadway Street, Suite 125, Oviedo, FL 32765, USA, and is used with permission. The role of JVZoo as a retailer does not constitute an endorsement, approval, or review of these products or any claims, statements, or opinions used in their promotion. The word "lifetime" applies to the lifetime of the product. This average lifetime of a product of this nature and price to be supported is approximately 5 years.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal - How Credits Work -->
<div id="creditModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background: rgba(0,0,0,0.9); backdrop-filter: blur(12px);">
    <div class="relative bg-gray-900 rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto border border-purple-500/30 shadow-2xl">
        <button onclick="closeCreditModal()" class="sticky top-4 float-right mr-4 mt-4 w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center text-gray-400 hover:text-white hover:bg-gray-700 transition z-10">
            <i class="fas fa-times text-xl"></i>
        </button>
        <div class="clear-both"></div>
        
        <div class="p-6 md:p-8">
            <div class="text-center mb-6">
                <div class="inline-block bg-purple-600/20 rounded-full px-4 py-1 text-purple-400 text-sm font-semibold mb-3">
                    💎 CREDIT SYSTEM EXPLAINED
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-2">How Credits Work</h2>
                <p class="text-gray-400">Transparent, flexible, and fair credit usage</p>
            </div>

            <div class="bg-gray-800/50 rounded-xl p-5 mb-6 text-center border border-gray-700">
                <h3 class="text-lg font-bold text-white mb-1">🧩 What is a Segment?</h3>
                <p class="text-gray-400 text-sm">Every video you create is called a <strong class="text-purple-400">"Segment"</strong>. Each segment is produced in 4 AI-powered stages.</p>
            </div>

            <div class="space-y-3 mb-6">
                <div class="flex items-center gap-4 p-4 bg-gray-800/30 rounded-xl border border-gray-700">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl flex items-center justify-center font-bold text-white">1</div>
                    <div class="w-12 h-12 bg-gray-800 rounded-xl flex items-center justify-center text-2xl">🔤</div>
                    <div class="flex-1">
                        <div class="font-semibold text-white">Script Generation</div>
                        <div class="text-gray-400 text-xs">AI writes dialogue & scene descriptions</div>
                    </div>
                    <div class="text-purple-400 font-bold">100 credits</div>
                </div>
                <div class="flex items-center gap-4 p-4 bg-gray-800/30 rounded-xl border border-gray-700">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl flex items-center justify-center font-bold text-white">2</div>
                    <div class="w-12 h-12 bg-gray-800 rounded-xl flex items-center justify-center text-2xl">🖼️</div>
                    <div class="flex-1">
                        <div class="font-semibold text-white">Image Creation</div>
                        <div class="text-gray-400 text-xs">AI generates stunning visuals & characters</div>
                    </div>
                    <div class="text-purple-400 font-bold">300 credits</div>
                </div>
                <div class="flex items-center gap-4 p-4 bg-gray-800/30 rounded-xl border border-gray-700">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl flex items-center justify-center font-bold text-white">3</div>
                    <div class="w-12 h-12 bg-gray-800 rounded-xl flex items-center justify-center text-2xl">🎙️</div>
                    <div class="flex-1">
                        <div class="font-semibold text-white">Voice Generation</div>
                        <div class="text-gray-400 text-xs">AI creates professional voiceovers</div>
                    </div>
                    <div class="text-purple-400 font-bold">200 credits</div>
                </div>
                <div class="flex items-center gap-4 p-4 bg-gray-800/30 rounded-xl border border-gray-700">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl flex items-center justify-center font-bold text-white">4</div>
                    <div class="w-12 h-12 bg-gray-800 rounded-xl flex items-center justify-center text-2xl">🎬</div>
                    <div class="flex-1">
                        <div class="font-semibold text-white">Video Animation</div>
                        <div class="text-gray-400 text-xs">AI brings images to life with cinematic motion</div>
                    </div>
                    <div class="text-purple-400 font-bold">400 credits</div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl p-5 text-center mb-6">
                <div class="text-white/80 text-sm uppercase tracking-wide">Total Per Segment</div>
                <div class="text-3xl font-bold text-white">1,000 Credits</div>
            </div>

            <div class="space-y-4">
                <div class="p-4 bg-gray-800/30 rounded-xl border border-gray-700">
                    <h4 class="font-bold text-white mb-2 flex items-center gap-2"><i class="fas fa-layer-group text-purple-400"></i> Building Episodes</h4>
                    <p class="text-gray-400 text-sm">An <strong class="text-white">Episode</strong> is made up of multiple segments combined together.</p>
                    <ul class="mt-2 space-y-1 text-sm text-gray-400">
                        <li>• Typical episode = 5 segments (5,000 credits)</li>
                        <li>• Short episode = 3 segments (3,000 credits)</li>
                        <li>• Long episode = 10+ segments (10,000+ credits)</li>
                    </ul>
                </div>
                <div class="p-4 bg-gray-800/30 rounded-xl border border-gray-700">
                    <h4 class="font-bold text-white mb-2 flex items-center gap-2"><i class="fas fa-sync-alt text-green-400"></i> Flexible Regeneration</h4>
                    <p class="text-gray-400 text-sm">Not happy with a specific part? Regenerate only what you need:</p>
                    <ul class="mt-2 space-y-1 text-sm text-gray-400">
                        <li>• Regenerate Script → 100 credits</li>
                        <li>• Regenerate Image → 300 credits</li>
                        <li>• Regenerate Voice → 200 credits</li>
                        <li>• Regenerate Video → 400 credits</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes pulse {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 0.6; }
    }
    
    .delay-1000 { animation-delay: 1s; }
    .delay-700 { animation-delay: 0.7s; }
    
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
    
    @media (max-width: 768px) {
        .py-\[120px\] {
            padding-top: 60px !important;
            padding-bottom: 60px !important;
        }
    }
</style>

<script>
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

    function openCreditModal() {
        document.getElementById('creditModal').classList.remove('hidden');
        document.getElementById('creditModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeCreditModal() {
        document.getElementById('creditModal').classList.add('hidden');
        document.getElementById('creditModal').classList.remove('flex');
        document.body.style.overflow = '';
    }

    document.getElementById('creditModal').addEventListener('click', function(e) {
        if (e.target === this) closeCreditModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeCreditModal();
    });
</script>
@endsection