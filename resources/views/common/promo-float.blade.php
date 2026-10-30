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
<div id="promoModal" class="promo-modal">
    <div class="promo-modal-overlay"></div>
    <div class="promo-modal-content">
        <button class="promo-modal-close" id="promoModalClose">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        <div class="promo-modal-header">
            <div class="promo-modal-badge">
                <span class="promo-modal-badge-pulse"></span>
                {{ $promoTrans['limited_offer'] ?? 'Limited Time Offer' }}
            </div>
            <h2 class="promo-modal-title">{{ $promoTrans['modal_title'] ?? 'Start Your Travel Business Today!' }}</h2>
            <p class="promo-modal-subtitle">{{ $promoTrans['modal_subtitle'] ?? 'Complete white-label solution for your travel agency' }}</p>
        </div>

        <div class="promo-modal-body">
            <!-- Features -->
            <div class="promo-features">
                <div class="promo-feature">
                    <div class="promo-feature-icon">✓</div>
                    <span>{{ $promoTrans['feature_1'] ?? 'Hotels, Flights, Tours, Visa & Umrah' }}</span>
                </div>
                <div class="promo-feature">
                    <div class="promo-feature-icon">✓</div>
                    <span>{{ $promoTrans['feature_2'] ?? 'White Label Solution' }}</span>
                </div>
                <div class="promo-feature">
                    <div class="promo-feature-icon">✓</div>
                    <span>{{ $promoTrans['feature_3'] ?? 'Setup in 15 Minutes' }}</span>
                </div>
                <div class="promo-feature">
                    <div class="promo-feature-icon">✓</div>
                    <span>{{ $promoTrans['feature_4'] ?? 'Full Admin Panel' }}</span>
                </div>
            </div>

            <!-- Price -->
            <div class="promo-price-card">
                <div class="promo-price-label">{{ $promoTrans['starting_from'] ?? 'Starting <b>Starter Plan</b> from just' }}</div>
                <div class="promo-price-value">$1599</div>
                <div class="promo-price-desc">{{ $promoTrans['one_time_payment'] ?? 'One-time payment, no monthly fees' }}</div>
            </div>

            <!-- CTA Buttons -->
            <div class="promo-cta-group">
                <a href="https://travelbookingpanel.com/pricing" target="_blank" class="promo-btn promo-btn-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    {{ $promoTrans['view_pricing'] ?? 'View Pricing Plans' }}
                </a>
                <a href="https://demo.travelbookingpanel.com/" target="_blank" class="promo-btn promo-btn-secondary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    {{ $promoTrans['explore_demo'] ?? 'Explore Demo' }}
                </a>
            </div>

            <!-- Contact -->
            <div class="promo-contact">
                <p>{{ $promoTrans['questions'] ?? 'Have questions?' }}</p>
                <div class="promo-contact-links">
                    <a href="https://wa.me/923207560200" target="_blank" class="promo-whatsapp">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        +92 320 7560200
                    </a>
                    <a href="mailto:contact@travelbookingpanel.com" class="promo-email">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        contact@travelbookingpanel.com
                    </a>
                </div>
            </div>
        </div>
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
