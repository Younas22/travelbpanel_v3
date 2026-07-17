@extends('common.layout')
@section('content')


    <div class="flight-container">
        <!-- Hero Section -->
        <div class="page-header">
            <h1>{{t('contact.contact_us')}}</h1>
            <p>{{t('contact.contact_us_desc')}}</p>
        </div>

        <!-- Main Content -->
        <div class="content-grid">
            <!-- Contact Info -->
            <div class="contact-info">
                <h2 class="info-title">{{t('contact.contact_information')}}</h2>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{t('contact.address')}}</div>
                        <div class="info-value">
                            <?= getSetting('business_address', 'contact'); ?>
                        </div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{t('contact.phone')}}</div>
                        <a href="tel:<?= getSetting('contact_phone', 'contact'); ?>" class="info-link"><?= getSetting('contact_phone', 'contact'); ?></a>
                    </div>
                </div>

                @if(getSetting('emergency_contact', 'contact'))
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{t('contact.alternate_phone')}}</div>
                        <a href="tel:<?= getSetting('emergency_contact', 'contact'); ?>" class="info-link">
                            <?= getSetting('emergency_contact', 'contact'); ?></a>
                    </div>
                </div>
                @endif
 
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{t('contact.email')}}</div>
                        <a href="mailto:<?= getSetting('contact_email', 'contact'); ?>" class="info-link"><?= getSetting('contact_email', 'contact'); ?></a>
                    </div>
                </div>

                @if(getSetting('support_email', 'contact'))
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{t('contact.support_email')}}</div>
                        <a href="mailto:<?= getSetting('support_email', 'contact'); ?>" class="info-link"><?= getSetting('support_email', 'contact'); ?></a>
                    </div>
                </div>
                @endif

                <!-- Office Hours -->
                <div class="office-hours">
                    <div class="hours-title">{{t('contact.office_hours')}}</div>
                    <div class="hours-item">
                        <span>{{t('contact.monday_friday')}}</span>
                        <span>09:00 AM - 06:00 PM</span>
                    </div>
                    <div class="hours-item">
                        <span>{{t('contact.saturday')}}</span>
                        <span>10:00 AM - 04:00 PM</span>
                    </div>
                    <div class="hours-item">
                        <span>{{t('contact.sunday')}}</span>
                        <span>{{t('contact.closed')}}</span>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-wrapper">
                <h2 class="form-title">{{t('contact.send_message')}}</h2>
                <form id="contactForm" onsubmit="handleSubmit(event)">
                    <div class="form-group two-col">
                        <div class="form-group">
                            <label class="form-label required">{{t('contact.first_name')}}</label>
                            <input type="text" name="firstName" class="form-input" placeholder="{{t('contact.first_name_placeholder')}}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">{{t('contact.last_name')}}</label>
                            <input type="text" name="lastName" class="form-input" placeholder="{{t('contact.last_name_placeholder')}}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">{{t('contact.email')}}</label>
                        <input type="email" name="email" class="form-input" placeholder="{{t('contact.email_placeholder')}}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{t('contact.phone_optional')}}</label>
                        <input type="tel" name="phone" class="form-input" placeholder="{{t('contact.phone_placeholder')}}">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">{{t('contact.subject')}}</label>
                        <select name="subject" class="form-select" required>
                            <option value="">{{t('contact.select_subject')}}</option>

                            <option value="flight_booking">{{t('contact.flight_ticket_booking')}}</option>
                            <option value="hotel_booking">{{t('contact.hotel_booking')}}</option>
                            <option value="existing_booking">{{t('contact.existing_booking')}}</option>
                            <option value="price_quote">{{t('contact.price_quote')}}</option>
                            <option value="complaint">{{t('contact.complaint')}}</option>
                            <option value="general_question">{{t('contact.general_question')}}</option>
                        </select>
                    </div>


                    <div class="form-group">
                        <label class="form-label">{{t('contact.booking_reference')}}</label>
                        <input type="text" name="bookingRef" class="form-input" placeholder="{{t('contact.booking_reference_placeholder')}}">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">{{t('contact.message')}}</label>
                        <textarea name="message" class="form-textarea" placeholder="{{t('contact.message_placeholder')}}" rows="5" required></textarea>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="privacy" required>
                        <label for="privacy" class="checkbox-text">
                            {{t('contact.privacy_agreement')}}
                        </label>
                    </div>

                    <button type="submit" class="submit-btn" id="submitBtn">
                        <i class="fas fa-paper-plane"></i>  {{t('contact.send_message_btn')}}
                    </button>
                </form>
            </div>
        </div>

        <!-- Social Section -->
        @php
            $socialLinks = getSetting('all', 'social');
            $hasLinks = !empty(array_filter($socialLinks)); // true if at least one link has value
        @endphp

        @if($hasLinks)
            <div class="social-section">
                <h2 class="social-title">Follow Us</h2>

                <div class="social-links flex gap-2">
                    @if(!empty($socialLinks['facebook_url']))
                        <a href="{{ $socialLinks['facebook_url'] }}" target="_blank" class="social-btn" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    @endif

                    @if(!empty($socialLinks['twitter_url']))
                        <a href="{{ $socialLinks['twitter_url'] }}" target="_blank" class="social-btn" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                    @endif

                    @if(!empty($socialLinks['instagram_url']))
                        <a href="{{ $socialLinks['instagram_url'] }}" target="_blank" class="social-btn" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    @endif

                    @if(!empty($socialLinks['linkedin_url']))
                        <a href="{{ $socialLinks['linkedin_url'] }}" target="_blank" class="social-btn" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    @endif

                    @if(!empty($socialLinks['youtube_url']))
                        <a href="{{ $socialLinks['youtube_url'] }}" target="_blank" class="social-btn" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    @endif

                    @if(!empty($socialLinks['whatsapp_number']))
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $socialLinks['whatsapp_number']) }}" target="_blank" class="social-btn" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    @endif
                </div>
            </div>
        @endif


        <!-- FAQ Section -->
        <div class="faq-section" style="display: none;">
            <h2 class="faq-title">Frequently Asked Questions</h2>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>What is your average response time?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    We typically respond to inquiries within 24-48 business hours. For urgent matters, please call our phone number directly.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>How can I track my booking?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    You can track your booking by logging into your account and visiting the "My Bookings" section. You'll also receive email updates at each stage.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>What payment methods do you accept?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    We accept all major credit cards (Visa, Mastercard, American Express), PayPal, bank transfers, and digital payment methods.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>Can I modify or cancel my booking?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Yes, modifications and cancellations are possible depending on the terms of your specific booking. Please contact our support team for assistance.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>Do you offer group discounts?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Yes! We offer special rates for group bookings. Please contact our group travel specialists for a custom quote.
                </div>
            </div>
        </div>
    </div>

    <script>
        async function handleSubmit(event) {
            event.preventDefault();

            const form = document.getElementById('contactForm');
            const submitBtn = document.getElementById('submitBtn');
            const formData = new FormData(form);

            // Convert FormData to JSON
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });

            // Disable submit button and show loading state
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
                    // Redirect to thank you page
                    window.location.href = '{{ route("contact.thank-you") }}';
                } else {
                    // Show error message
                    if (result.errors) {
                        const errorMessages = Object.values(result.errors).flat().join('\n');
                        alert('Please correct the following errors:\n\n' + errorMessages);
                    } else {
                        alert(result.message || 'Something went wrong. Please try again.');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while sending your message. Please try again later.');
            } finally {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Message';
            }
        }

        function toggleFAQ(element) {
            element.closest('.faq-item').classList.toggle('active');
        }
    </script>
 

@endsection
