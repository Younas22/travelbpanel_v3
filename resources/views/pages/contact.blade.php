@extends('common.layout')
@section('content')
@php
    $socialLinks = getSetting('all', 'social');
    $hasLinks = !empty(array_filter($socialLinks));
    $contactRows = array_filter([
        ['fa-location-dot', t('contact.address'), getSetting('business_address', 'contact'), null],
        ['fa-phone', t('contact.phone'), getSetting('contact_phone', 'contact'), 'tel:'],
        ['fa-phone', t('contact.alternate_phone'), getSetting('emergency_contact', 'contact'), 'tel:'],
        ['fa-envelope', t('contact.email'), getSetting('contact_email', 'contact'), 'mailto:'],
        ['fa-envelope', 'Support Email', getSetting('support_email', 'contact'), 'mailto:'],
    ], fn($row) => trim((string) $row[2]) !== '');
@endphp
<style>
    /* Hero layout lives in style.css (#siteRedesign); only the cover image is set here. */
    #siteRedesign .hero-section {
        background-image: url('<?=getSettingImage('cover_image', 'homepage')?>');
    }
</style>

<div id="siteRedesign">
    <section class="hero-section is-compact">
        <div class="hero-content">
            <h1 class="hero-title">{{ t('contact.contact_us') }}</h1>
            <p class="hero-subtitle">{{ t('contact.contact_us_desc') }}</p>
        </div>
    </section>

    <div class="form-container is-overlap pg-panel pg-contact">
        <!-- Contact information -->
        <div class="pg-contact-info">
            <h2 class="td-side-title">{{ t('contact.contact_information') }}</h2>
            <ul class="pg-info-list">
                @foreach($contactRows as [$icon, $label, $value, $scheme])
                    <li>
                        <span class="td-fact-icon"><i class="fas {{ $icon }}"></i></span>
                        <div>
                            <small>{{ $label }}</small>
                            @if($scheme)
                                <a href="{{ $scheme . $value }}" dir="ltr">{{ $value }}</a>
                            @else
                                <span dir="auto">{!! nl2br(e($value)) !!}</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="pg-hours">
                <div class="pg-hours-title"><i class="fas fa-clock"></i> {{ t('contact.office_hours') }}</div>
                <div class="td-row"><span>{{ t('contact.monday_friday') }}</span><b>09:00 AM - 06:00 PM</b></div>
                <div class="td-row"><span>{{ t('contact.saturday') }}</span><b>10:00 AM - 04:00 PM</b></div>
                <div class="td-row"><span>{{ t('contact.sunday') }}</span><b>{{ t('contact.closed') }}</b></div>
            </div>

            @if($hasLinks)
                <div class="sf-social pg-social">
                    @if(!empty($socialLinks['facebook_url']))<a href="{{ $socialLinks['facebook_url'] }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>@endif
                    @if(!empty($socialLinks['twitter_url']))<a href="{{ $socialLinks['twitter_url'] }}" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a>@endif
                    @if(!empty($socialLinks['instagram_url']))<a href="{{ $socialLinks['instagram_url'] }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>@endif
                    @if(!empty($socialLinks['linkedin_url']))<a href="{{ $socialLinks['linkedin_url'] }}" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>@endif
                    @if(!empty($socialLinks['youtube_url']))<a href="{{ $socialLinks['youtube_url'] }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>@endif
                    @if(!empty($socialLinks['whatsapp_number']))<a href="https://wa.me/{{ preg_replace('/\D/', '', $socialLinks['whatsapp_number']) }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>@endif
                </div>
            @endif
        </div>

        <!-- Contact form -->
        <div class="pg-contact-form">
            <h2 class="td-section-title">{{ t('contact.send_message') }}</h2>
            <form id="contactForm" onsubmit="handleSubmit(event)">
                <div class="fb-grid">
                    <label class="fb-field">
                        <span>{{ t('contact.first_name') }} *</span>
                        <input type="text" name="firstName" class="fb-input" placeholder="{{ t('contact.first_name_placeholder') }}" required>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('contact.last_name') }} *</span>
                        <input type="text" name="lastName" class="fb-input" placeholder="{{ t('contact.last_name_placeholder') }}" required>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('contact.email') }} *</span>
                        <input type="email" name="email" class="fb-input" placeholder="{{ t('contact.email_placeholder') }}" required>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('contact.phone_optional') }}</span>
                        <input type="tel" name="phone" class="fb-input" placeholder="{{ t('contact.phone_placeholder') }}">
                    </label>
                    <label class="fb-field">
                        <span>{{ t('contact.subject') }} *</span>
                        <select name="subject" class="fb-input" required>
                            <option value="">{{ t('contact.select_subject') }}</option>
                            <option value="flight_booking">{{ t('contact.flight_ticket_booking') }}</option>
                            <option value="hotel_booking">{{ t('contact.hotel_booking') }}</option>
                            <option value="existing_booking">{{ t('contact.existing_booking') }}</option>
                            <option value="price_quote">{{ t('contact.price_quote') }}</option>
                            <option value="complaint">{{ t('contact.complaint') }}</option>
                            <option value="general_question">{{ t('contact.general_question') }}</option>
                        </select>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('contact.booking_reference_optional') }}</span>
                        <input type="text" name="bookingRef" class="fb-input" placeholder="{{ t('contact.booking_reference_placeholder') }}">
                    </label>
                    <label class="fb-field is-wide">
                        <span>{{ t('contact.message') }} *</span>
                        <textarea name="message" class="fb-input pg-textarea" placeholder="{{ t('contact.message_placeholder') }}" rows="5" required></textarea>
                    </label>
                </div>

                <label class="pg-check">
                    <input type="checkbox" id="privacy" required>
                    <span>{{ t('contact.privacy_agreement') }}</span>
                </label>

                <button type="submit" class="td-book-btn pg-submit" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> {{ t('contact.send_message_btn') }}
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    async function handleSubmit(event) {
        event.preventDefault();

        const form = document.getElementById('contactForm');
        const submitBtn = document.getElementById('submitBtn');
        const data = {};
        new FormData(form).forEach((value, key) => { data[key] = value; });

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

        try {
            const response = await fetch('{{ route("contact.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (result.success) {
                window.location.href = '{{ route("contact.thank-you") }}';
            } else if (result.errors) {
                alert('Please correct the following errors:\n\n' + Object.values(result.errors).flat().join('\n'));
            } else {
                alert(result.message || 'Something went wrong. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while sending your message. Please try again later.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> {{ t('contact.send_message_btn') }}';
        }
    }
</script>
@endsection
