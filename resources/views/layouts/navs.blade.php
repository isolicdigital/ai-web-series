
    <!-- Navigation Bar -->
    <div class="nav-icons" id="mainNav">
        @php 
            $planLevel = Auth::user()->plan_level;
            $isAdmin = Auth::user()->role === 'admin';
            $userCredits = Auth::user()->videoCredit;
            
            $freeCreditsRemaining = $userCredits ? ($userCredits->free_credits - $userCredits->free_credits_used) : 0;
            $paidCreditsRemaining = $userCredits ? ($userCredits->paid_credits - $userCredits->paid_credits_used) : 0;
        @endphp

        <div class="icon-item">
            <div class="icon-circle">
                <i class="fas fa-user-circle"></i>
            </div>
            <span>{{ Auth::user()->name }}</span>
        </div>

        <a href="{{ route('comedy.index') }}" class="icon-item {{ request()->routeIs('comedy.index') ? 'active' : '' }}">
            <div class="icon-circle">
                <i class="fas fa-microphone-alt"></i>
            </div>
            <span>Comedy Studio</span>
        </a>

        <a href="{{ route('comedy.jokes') }}" class="icon-item {{ request()->routeIs('comedy.jokes') ? 'active' : '' }}">
            <div class="icon-circle">
                <i class="fas fa-book-open"></i>
            </div>
            <span>My Jokes</span>
        </a>

        <a href="{{ route('comedy.my-videos') }}" class="icon-item">
            <div class="icon-circle">
                <i class="fas fa-video"></i>
            </div>
            <span>My Videos</span>
        </a>

        @if($isAdmin || $planLevel >= 3)
        @php
            $activeDfy = [
                'images' => request()->routeIs('dfy.images'),
                'videos' => request()->routeIs('dfy.videos'),
                'sites' => request()->routeIs('page-builder.*'),
            ];
            $isAnyDfyActive = in_array(true, $activeDfy);
        @endphp

        <div class="icon-item dropdown {{ $isAnyDfyActive ? 'active' : '' }}">
            <div class="icon-circle">
                <i class="fas fa-box-open"></i>
            </div>
            <span>DFY <i class="fas fa-chevron-down"></i></span>
            <div class="dropdown-menu">
                <a href="{{ route('dfy.images') }}" class="{{ $activeDfy['images'] ? 'active' : '' }}">
                    <i class="fas fa-image"></i> DFY Visuals
                </a>
                <a href="{{ route('dfy.videos') }}" class="{{ $activeDfy['videos'] ? 'active' : '' }}">
                    <i class="fas fa-video"></i> DFY Footages
                </a>
                <a href="{{ route('page-builder.index') }}" class="{{ $activeDfy['sites'] ? 'active' : '' }}">
                    <i class="fas fa-globe"></i> DFY Sites
                </a>
            </div>
        </div>
        @endif

        @if($isAdmin || $planLevel >= 4)
        <a href="#" class="icon-item" onclick="showComingSoon(event)">
            <div class="icon-circle">
                <i class="fas fa-film"></i>
            </div>
            <span>Reel Maker</span>
        </a>
        @endif

        @if($isAdmin || $planLevel >= 5)
        <a href="{{ route('whitelabel') }}" class="icon-item {{ request()->routeIs('whitelabel') ? 'active' : '' }}">
            <div class="icon-circle">
                <i class="fas fa-tags"></i>
            </div>
            <span>Whitelabel</span>
        </a>
        @endif
        
        @if(Auth::user()->role === 'admin')
        <div class="icon-item dropdown">
            <div class="icon-circle">
                <i class="fas fa-crown"></i>
            </div>
            <span>Admin <i class="fas fa-chevron-down"></i></span>
            <div class="dropdown-menu">
                <a href="{{ route('admin.users.index') }}">Users</a>
                <a href="{{ route('admin.plans.index') }}">Plans</a>
                <!-- <a href="{{ route('admin.agencies.index') }}">Agencies</a>
                <a href="{{ route('admin.subscriptions.index') }}">Subscriptions</a> -->
            </div>
        </div>
        @endif

        <a href="{{ env('TRAINING_URL') }}" class="icon-item">
            <div class="icon-circle">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <span>Training</span>
        </a>

        <a href="{{ env('SUPPORT_EXT', '#') }}" class="icon-item" target="_blank">
            <div class="icon-circle">
                <i class="fas fa-headset"></i>
            </div>
            <span>Support</span>
        </a>

        <a href="https://aistandup.live/upgrades" class="icon-item" target="_blank">
            <div class="icon-circle">
                <i class="fas fa-rocket"></i>
            </div>
            <span>Upgrades</span>
        </a>

        <!-- Credits Display / Buy Credits Link -->
        <a href="{{ route('buycredits') }}" class="icon-item credits-item">
            <div class="icon-circle credits-circle">
                <i class="fas fa-coins"></i>
            </div>
            <span class="credits-text">
                @if($freeCreditsRemaining > 0 && $paidCreditsRemaining > 0)
                    {{ number_format($freeCreditsRemaining + $paidCreditsRemaining) }}
                    <small class="credit-badge">({{ number_format($freeCreditsRemaining / 1000, 1) }}k F + {{ number_format($paidCreditsRemaining / 1000, 1) }}k P)</small>
                @elseif($freeCreditsRemaining > 0)
                    {{ number_format($freeCreditsRemaining) }}
                    <small class="credit-badge">Free</small>
                @elseif($paidCreditsRemaining > 0)
                    {{ number_format($paidCreditsRemaining) }}
                    <small class="credit-badge">Paid</small>
                @else
                    Buy Credits
                @endif
            </span>
        </a>
        
        <form method="POST" action="{{ route('logout') }}" class="icon-item">
            @csrf
            <button type="submit" class="icon-circle logout-icon">
                <i class="fas fa-sign-out-alt"></i>
            </button>
            <span>Logout</span>
        </form>
    </div>