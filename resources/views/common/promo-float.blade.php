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

<style>
/* Floating Button Styles */
.promo-float-button {
    position: fixed;
    right: 20px;
    top: 50%;
    transform: translateY(-50%) rotate(-90deg);
    transform-origin: center;
    z-index: 9998;
    cursor: pointer;
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    color: white;
    padding: 12px 20px;
    border-radius: 12px 12px 0 0;
    box-shadow: -4px 0 15px rgba(220, 38, 38, 0.3);
    transition: all 0.3s ease;
    animation: floatPulse 2s ease-in-out infinite, floatShake 3s ease-in-out infinite;
}

.promo-float-button:hover {
    box-shadow: -4px 0 20px rgba(220, 38, 38, 0.5);
    transform: translateY(-50%) rotate(-90deg) scale(1.08);
    animation-play-state: paused;
}

.promo-float-content {
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.promo-float-icon {
    font-size: 20px;
    animation: bounce 1s ease-in-out infinite;
}

.promo-float-text {
    font-weight: 600;
    font-size: 14px;
    letter-spacing: 0.5px;
}

@keyframes floatPulse {
    0%, 100% {
        box-shadow: -4px 0 15px rgba(220, 38, 38, 0.3);
    }
    50% {
        box-shadow: -4px 0 30px rgba(220, 38, 38, 0.6), 0 0 40px rgba(220, 38, 38, 0.2);
    }
}

@keyframes floatShake {
    0%, 100% {
        transform: translateY(-50%) rotate(-90deg) scale(1);
    }
    15% {
        transform: translateY(-50%) rotate(-90deg) scale(1.05);
    }
    30% {
        transform: translateY(-50%) rotate(-90deg) scale(0.97);
    }
    45% {
        transform: translateY(-50%) rotate(-90deg) scale(1.03);
    }
    60% {
        transform: translateY(-50%) rotate(-90deg) scale(1);
    }
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-3px);
    }
}

/* Modal Styles */
.promo-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
}

.promo-modal.active {
    display: block;
}

.promo-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
}

.promo-modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 20px;
    max-width: 420px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    overflow-x: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    scrollbar-width: thin;
    scrollbar-color: rgba(0, 119, 190, 0.3) transparent;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translate(-50%, -45%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

.promo-modal-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(0, 0, 0, 0.1);
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    z-index: 10;
}

.promo-modal-close:hover {
    background: rgba(0, 0, 0, 0.2);
    transform: rotate(90deg);
}

.promo-modal-close svg {
    color: #666;
}

.promo-modal-header {
    padding: 28px 22px 14px;
    text-align: center;
    background: linear-gradient(135deg, #f0f8ff 0%, #e6f2fa 100%);
    border-radius: 20px 20px 0 0;
}

.promo-modal-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #10b981;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 14px;
}

.promo-modal-badge-pulse {
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.promo-modal-title {
    font-size: 19px;
    font-weight: 800;
    color: #0077BE;
    margin: 0 0 6px 0;
    line-height: 1.3;
}

.promo-modal-subtitle {
    font-size: 13px;
    color: #666;
    margin: 0;
}

.promo-modal-body {
    padding: 20px;
}

.promo-features {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 18px;
}

.promo-feature {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    background: #f8fafc;
    border-radius: 10px;
}

.promo-feature-icon {
    width: 20px;
    height: 20px;
    background: #10b981;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 11px;
    flex-shrink: 0;
}

.promo-feature span {
    font-size: 12px;
    color: #333;
    font-weight: 500;
}

.promo-price-card {
    background: linear-gradient(135deg, #0077BE 0%, #00b4d8 100%);
    color: white;
    padding: 18px;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 18px;
}

.promo-price-label {
    font-size: 12px;
    opacity: 0.9;
    margin-bottom: 5px;
}

.promo-price-value {
    font-size: 36px;
    font-weight: 900;
    line-height: 1;
    margin-bottom: 5px;
}

.promo-price-desc {
    font-size: 11px;
    opacity: 0.9;
}

.promo-cta-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 18px;
}

.promo-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
}

.promo-btn-primary {
    background: #0077BE;
    color: white;
    box-shadow: 0 4px 12px rgba(0, 119, 190, 0.3);
}

.promo-btn-primary:hover {
    background: #005a91;
    box-shadow: 0 6px 16px rgba(0, 119, 190, 0.4);
    transform: translateY(-2px);
}

.promo-btn-secondary {
    background: white;
    color: #0077BE;
    border: 2px solid #0077BE;
}

.promo-btn-secondary:hover {
    background: #f0f8ff;
    transform: translateY(-2px);
}

.promo-contact {
    text-align: center;
    padding-top: 14px;
    border-top: 1px solid #e5e7eb;
}

.promo-contact p {
    font-size: 12px;
    color: #666;
    margin: 0 0 8px 0;
}

.promo-whatsapp {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #25D366;
    color: white;
    border-radius: 25px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.3s;
}

.promo-whatsapp:hover {
    background: #20BA5A;
    transform: scale(1.05);
}

.promo-contact-links {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.promo-email {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #0077BE;
    color: white;
    border-radius: 25px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.3s;
}

.promo-email:hover {
    background: #005a91;
    transform: scale(1.05);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .promo-float-button {
        right: 10px;
        padding: 10px 16px;
    }

    .promo-float-text {
        font-size: 12px;
    }

    .promo-float-icon {
        font-size: 18px;
    }

    .promo-modal-content {
        width: 95%;
        max-height: 95vh;
    }

    .promo-modal-header {
        padding: 30px 20px 15px;
    }

    .promo-modal-title {
        font-size: 20px;
    }

    .promo-modal-body {
        padding: 20px;
    }

    .promo-price-value {
        font-size: 36px;
    }
}

/* Custom Scrollbar for Modal */
.promo-modal-content::-webkit-scrollbar {
    width: 5px;
}

.promo-modal-content::-webkit-scrollbar-track {
    background: transparent;
    margin: 20px 0;
}

.promo-modal-content::-webkit-scrollbar-thumb {
    background: rgba(0, 119, 190, 0.3);
    border-radius: 20px;
}

.promo-modal-content::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 119, 190, 0.5);
}

/* Hide float button when modal is open */
.promo-modal.active ~ .promo-float-button {
    opacity: 0;
    pointer-events: none;
}
</style>

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
