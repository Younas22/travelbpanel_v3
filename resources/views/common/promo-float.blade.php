@php
    $currentLocale = app()->getLocale();
    $isDemoSite = request()->getSchemeAndHttpHost() === 'https://demo.travelbookingpanel.com';

    // Load promo translations
    $promoTransPath = resource_path("lang/{$currentLocale}/promo.json");
    $promoTrans = file_exists($promoTransPath)
        ? json_decode(file_get_contents($promoTransPath), true)
        : [];
@endphp

@if($isDemoSite)
<!-- Floating Promo Button -->
<div id="promoFloatButton" class="promo-float-button" style="display: none;">
    <div class="promo-float-content">
        <span class="promo-float-icon">🎉</span>
        <span class="promo-float-text">{{ $promoTrans['float_text'] ?? 'Special Offer!' }}</span>
    </div>
</div>

<!-- Promo Modal -->
@php
    $pt = fn($key, $fallback) => $promoTrans[$key] ?? $fallback;
    $promoSuppliers = [
        ['Amadeus', 'partners/amadeus_enterprise.png', false],
        ['Sabre', 'promo/sabre-logo.png', true],
        ['Hotelbeds', 'partners/hotelbeds.png', false],
        ['WebBeds', 'partners/webbeds.png', false],
        ['Agoda', 'partners/agoda.png', true],
        ['Duffel', 'partners/duffel.png', true],
    ];
    $promoExperts = [
        ['label' => $pt('ceo', 'CEO'), 'phone' => '+92 320 7560200', 'wa' => '923207560200'],
        ['label' => $pt('ota_expert', 'OTA Expert'), 'phone' => '+92 346 0820722', 'wa' => '923460820722'],
    ];
@endphp
<div id="promoModal" class="promo-modal" role="dialog" aria-modal="true" aria-labelledby="promoModalTitle">
    <div class="promo-modal-overlay"></div>
    <div class="promo-modal-content">
        <!-- Branding + close -->
        <div class="promo-top">
            <img src="{{ getSettingImage('business_logo', 'branding') }}" alt="TravelBookingPanel" class="promo-logo">
            <button type="button" class="promo-modal-close" id="promoModalClose" aria-label="{{ $pt('close', 'Close') }}">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <div class="promo-hero">
            <!-- Offer -->
            <div class="promo-hero-text">
                <span class="promo-modal-badge"><i class="fas fa-tag"></i> {{ $pt('limited_offer', 'Limited Time Offer') }}</span>
                <h2 class="promo-modal-title" id="promoModalTitle">
                    {{ $pt('title_lead', 'Start Your') }} <span>{{ $pt('title_highlight', 'Travel Business Today!') }}</span>
                </h2>
                <p class="promo-modal-subtitle">{{ $pt('modal_subtitle', 'Complete white-label solution for your travel agency') }}</p>

                <!-- Portals it supports (information only) -->
                <p class="promo-supports">
                    <span class="promo-supports-label">{{ $pt('supports', 'Supports') }}</span>
                    <span class="promo-support"><i class="fas fa-users"></i> <b>{{ $pt('b2b', 'B2B') }}</b> {{ $pt('agent_portal', 'Agent Portal') }}</span>
                    <span class="promo-support"><i class="fas fa-user"></i> <b>{{ $pt('b2c', 'B2C') }}</b> {{ $pt('customer_portal', 'Customer Portal') }}</span>
                </p>

                <ul class="promo-features">
                    @foreach([
                        $pt('feature_1', 'Hotels, Flights, Tours, Visa & Umrah'),
                        $pt('feature_2', 'White Label Solution'),
                        $pt('feature_3', 'Setup in 15 Minutes'),
                        $pt('feature_4', 'Full Admin Panel'),
                    ] as $feature)
                        <li class="promo-feature"><i class="fas fa-circle-check"></i> <span>{{ $feature }}</span></li>
                    @endforeach
                </ul>
            </div>

            <!-- Product visual + price -->
            <div class="promo-hero-side">
                <img src="{{ asset('public/assets/images/promo/promo-mockup.jpg') }}?v={{ filemtime(public_path('assets/images/promo/promo-mockup.jpg')) }}" alt="" class="promo-mockup" loading="lazy">
                <div class="promo-price-card">
                    <div class="promo-price-label">{{ $pt('starting_from', 'Starting from just') }}</div>
                    <div class="promo-price-value">$1599</div>
                    <div class="promo-price-desc">{{ $pt('one_time_payment', 'One-time payment, no monthly fees') }}</div>
                </div>
                <div class="promo-cta-group">
                    <a href="https://travelbookingpanel.com/pricing" target="_blank" rel="noopener" class="promo-btn promo-btn-primary">
                        <i class="fas fa-layer-group"></i> {{ $pt('view_pricing', 'View Pricing Plans') }}
                    </a>
                    <a href="https://demo.travelbookingpanel.com/" target="_blank" rel="noopener" class="promo-btn promo-btn-secondary">
                        <i class="far fa-eye"></i> {{ $pt('explore_demo', 'Explore Demo') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Supplier integrations -->
        <div class="promo-suppliers">
            <h3 class="promo-section-title"><span>{{ $pt('trusted_suppliers', 'Trusted Supplier Integrations') }}</span></h3>
            <div class="promo-supplier-grid">
                @foreach($promoSuppliers as [$name, $logo, $padded])
                    <div class="promo-supplier"><img src="{{ asset('public/assets/images/' . $logo) }}" alt="{{ $name }}" loading="lazy" @class(['is-padded' => $padded])></div>
                @endforeach
                <div class="promo-supplier is-more"><i class="fas fa-plus"></i> {{ $pt('much_more', '& Much More') }}</div>
            </div>
        </div>

        <!-- WhatsApp experts -->
        <div class="promo-contact">
            <div class="promo-contact-title"><i class="fas fa-headset"></i> <b>{{ $pt('talk_experts', 'Talk to Our Experts') }}</b></div>
            <div class="promo-contact-links">
                @foreach($promoExperts as $expert)
                    <a href="https://wa.me/{{ $expert['wa'] }}" target="_blank" rel="noopener" class="promo-whatsapp">
                        <i class="fab fa-whatsapp"></i>
                        <span><b>{{ $expert['label'] }}</b><small dir="ltr">{{ $expert['phone'] }}</small></span>
                        <i class="fas fa-arrow-right promo-whatsapp-arrow"></i>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="promo-waves" aria-hidden="true"></div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const floatButton = document.getElementById('promoFloatButton');
    const modal = document.getElementById('promoModal');
    const modalClose = document.getElementById('promoModalClose');
    const modalOverlay = modal.querySelector('.promo-modal-overlay');

    // Check if user has closed the promo before
    const promoClosed = localStorage.getItem('promoClosed');

    // Show float button after 2 seconds if not closed before
    if (!promoClosed) {
        setTimeout(() => {
            floatButton.style.display = 'block';
        }, 2000);
    } else {
        // Show button immediately if previously closed (so they can reopen)
        floatButton.style.display = 'block';
    }

    // Open modal on float button click
    floatButton.addEventListener('click', function() {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        modalClose.focus();
    });

    // Close modal functions
    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        // Remember that user closed it
        localStorage.setItem('promoClosed', 'true');
    }

    modalClose.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', closeModal);

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });
});
</script>
@endif
