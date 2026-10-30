@extends('common.layout')
@section('page_class', 'view-contact-thanks')
@section('content')

<div class="flight-container">
    <!-- Thank You Header -->
    <div class="thank-you-container">
        <div class="thank-you-icon">
            <i class="fas fa-check-circle"></i>
        </div>

        <h1 class="thank-you-title">
            {{ t('contact_thank_you.heading') }}
        </h1>

        <p class="thank-you-message">
            {{ t('contact_thank_you.message') }}
        </p>

        <!-- Message Details Card -->
        <div class="confirmation-card">
            <h3 class="confirmation-title">
                {{ t('contact_thank_you.what_next_title') }}
            </h3>

            <div class="next-steps">
                <div class="step-item">
                    <div class="step-icon">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <div class="step-content">
                        <h4>{{ t('contact_thank_you.step_review_title') }}</h4>
                        <p>{{ t('contact_thank_you.step_review_desc') }}</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="step-content">
                        <h4>{{ t('contact_thank_you.step_response_title') }}</h4>
                        <p>{{ t('contact_thank_you.step_response_desc') }}</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-icon">
                        <i class="fas fa-reply"></i>
                    </div>
                    <div class="step-content">
                        <h4>{{ t('contact_thank_you.step_reply_title') }}</h4>
                        <p>{{ t('contact_thank_you.step_reply_desc') }}</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="step-content">
                        <h4>{{ t('contact_thank_you.step_urgent_title') }}</h4>
                        <p>
                            {{ t('contact_thank_you.step_urgent_desc') }}
                            <?= getSetting('contact_phone', 'contact'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="contact-info-box">
            <h3>{{ t('contact_thank_you.contact_title') }}</h3>
            <p>{{ t('contact_thank_you.contact_desc') }}</p>

            <div class="info-row">
                <div class="info-col">
                    <i class="fas fa-envelope"></i>
                    <span>
                        {{ t('contact_thank_you.email_label') }}:
                        <a href="mailto:<?= getSetting('contact_email', 'contact'); ?>">
                            <?= getSetting('contact_email', 'contact'); ?>
                        </a>
                    </span>
                </div>
                <div class="info-col">
                    <i class="fas fa-phone"></i>
                    <span>
                        {{ t('contact_thank_you.phone_label') }}:
                        <a href="tel:<?= getSetting('contact_phone', 'contact'); ?>">
                            <?= getSetting('contact_phone', 'contact'); ?>
                        </a>
                    </span>
                </div>
            </div>

            <div class="office-hours-box">
                <i class="fas fa-clock"></i>
                <span>
                    <strong>{{ t('contact_thank_you.office_hours') }}:</strong>
                    {{ t('contact_thank_you.office_time') }}
                </span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('home') }}" class="btn-primary">
                <i class="fas fa-home"></i>
                {{ t('contact_thank_you.btn_home') }}
            </a>
            <a href="{{ route('contact') }}" class="btn-secondary">
                <i class="fas fa-envelope"></i>
                {{ t('contact_thank_you.btn_contact') }}
            </a>
        </div>
    </div>
</div>



@endsection
