@extends('layouts.app')

@section('title', 'Buy Credits - ' . config('app.name'))

@section('css')
<style>
/* Pricing Specific Styles */
.pricing-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.pricing-header {
    text-align: center;
    margin-bottom: 2rem;
}

.pricing-header h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, var(--text-main) 0%, var(--accent) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.pricing-header p {
    color: var(--text-muted);
    font-size: 1rem;
}

.pricing-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin: 2rem 0;
}

.pricing-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    padding: 1.5rem;
    transition: var(--transition);
    position: relative;
}

.pricing-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary);
    box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.3);
}

.pricing-card.featured {
    border-color: var(--primary);
    background: linear-gradient(135deg, rgba(230, 88, 86, 0.05), rgba(230, 88, 86, 0.02));
}

.pricing-header-card {
    text-align: center;
    margin-bottom: 1.5rem;
}

.pricing-header-card h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--text-main);
}

.pricing-price {
    text-align: center;
    margin-bottom: 0.5rem;
}

.original-price {
    font-size: 1rem;
    color: var(--text-muted);
    text-decoration: line-through;
    display: block;
}

.current-price {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-main);
    display: inline-block;
}

.price-period {
    font-size: 0.8rem;
    color: var(--text-muted);
    display: inline-block;
    margin-left: 0.25rem;
}

.savings {
    font-size: 0.75rem;
    color: #48bb78;
    margin-top: 0.25rem;
}

.pricing-price-btn {
    margin: 1.5rem 0;
}

.pricing-btn {
    display: inline-block;
    padding: 0.85rem 1.5rem;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
    transition: var(--transition);
    width: 100%;
    text-align: center;
    background: linear-gradient(135deg, #f59e0b, #e65856);
    color: white;
    border: none;
    cursor: pointer;
    letter-spacing: 0.5px;
}

.pricing-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(230, 88, 86, 0.4);
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
}

.pricing-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #f59e0b, #e65856);
    color: white;
    padding: 0.35rem 1.25rem;
    border-radius: 40px;
    font-size: 0.7rem;
    font-weight: 700;
    white-space: nowrap;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    z-index: 2;
}

.pricing-features {
    list-style: none;
    padding: 0;
    margin: 0;
}

.pricing-features li {
    padding: 0.5rem 0;
    color: var(--text-secondary);
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pricing-features li i {
    color: var(--primary);
    font-size: 0.75rem;
    width: 18px;
}

.pricing-footer {
    text-align: center;
    margin: 2rem 0;
}

.no-thanks-link {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.875rem;
    transition: var(--transition);
}

.no-thanks-link:hover {
    color: var(--primary);
}

.disclaimer {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--glass-border);
    font-size: 0.7rem;
    color: var(--text-muted);
    line-height: 1.5;
}

.disclaimer p {
    margin-bottom: 0.75rem;
}

.disclaimer strong {
    color: var(--text-secondary);
}



@media (max-width: 900px) {
    .pricing-grid {
        grid-template-columns: 1fr;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }
}

@media (max-width: 768px) {
    .pricing-container {
        padding: 1rem;
    }
}
</style>
@endsection

@section('content')
<div class="pricing-container">
    <div class="pricing-header">
        <h1>Choose Your Credit Plan</h1>
        <p>Select the perfect plan for your needs. All credits never expire!</p>
    </div>

    <div class="pricing-grid">
        @foreach($plans as $plan)
        <div class="pricing-card {{ $plan->is_featured ? 'featured' : '' }}">
            @if($plan->savings_percentage > 0)
            <div class="pricing-badge">SAVE {{ $plan->savings_percentage }}%</div>
            @endif
            <div class="pricing-header-card">
                <h3>{{ $plan->name }}</h3>
                <div class="pricing-price">
                    @if($plan->original_price)
                    <span class="original-price">${{ number_format($plan->original_price, 2) }}</span>
                    @endif
                    <span class="current-price">${{ number_format($plan->price, 2) }}</span>
                    <span class="price-period"></span>
                </div>
                @if($plan->original_price)
                <div class="savings">SAVE {{ $plan->savings_percentage }}%</div>
                @endif
            </div>
            <div class="pricing-price-btn">
                <a href="https://www.jvzoo.com/b/116137/{{ $plan->wp_id }}/2" class="pricing-btn" target="_blank">
                    Buy Now
                </a>
            </div>
            <ul class="pricing-features">
                <li><i class="fas fa-check"></i> {{ number_format($plan->video_credits) }} Credits Included</li>
                <li><i class="fas fa-check"></i> Never Expire</li>
                <li><i class="fas fa-check"></i> Instant Activation</li>
                <li><i class="fas fa-check"></i> Flat 200 Credit/Video</li>
                @if($plan->slug == 'starter')
                <li><i class="fas fa-check"></i> 24/7 Support</li>
                @elseif($plan->slug == 'pro')
                <li><i class="fas fa-check"></i> Priority Support</li>
                <li><i class="fas fa-check"></i> Best Value</li>
                @else
                <li><i class="fas fa-check"></i> VIP Support</li>
                <li><i class="fas fa-check"></i> Maximum Savings</li>
                <li><i class="fas fa-check"></i> Premium Features</li>
                <li><i class="fas fa-check"></i> No Temporary Blocks</li>
                @endif
            </ul>
        </div>
        @endforeach
    </div>

    <div class="pricing-footer">
        <a href="https://www.jvzoo.com/nothanks/437027" class="no-thanks-link" target="_blank">No thanks, I'll use the free version</a>
    </div>

    <div class="disclaimer">
        <p><strong>Disclaimer:</strong> This product does not provide any guarantee of income or success. The results achieved by the product owner or any other individuals mentioned are not indicative of future success or earnings. This website is not affiliated with FaceBook or any of its associated entities. Once you navigate away from FaceBook, the responsibility for the content and its usage lies solely with the user.</p>
        <p>All content on this website, including but not limited to text, images, and multimedia, is protected by copyright law and the Digital Millennium Copyright Act. Unauthorized copying, duplication, modification, or theft, whether intentional or unintentional, is strictly prohibited. Violators will be prosecuted to the fullest extent of the law.</p>
        <p>We want to clarify that JVZoo serves as the retailer for the products featured on this site. JVZoo® is a registered trademark of BBC Systems Inc., a Florida corporation located at 1809 E. Broadway Street, Suite 125, Oviedo, FL 32765, USA, and is used with permission. The role of JVZoo as a retailer does not constitute an endorsement, approval, or review of these products or any claims, statements, or opinions used in their promotion. The word "lifetime" applies to the lifetime of the product. This average lifetime of a product of this nature and price to be supported is approximately 5 years.</p>
    </div>
</div>
@endsection