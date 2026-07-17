@extends('common.layout')
@section('content')

<div class="flight-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1>✓ {{t('visa.successTitle')}}</h1>
        <p>{{t('visa.successMessage')}} {{ getSetting('business_name', 'main', 'Travel Booking Panel') }}! {{t('visa.successDescription')}}</p>
    </div>

    <!-- Success Message Card -->
    <div class="form-section" style="background: white; border-radius: 12px; padding: 30px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);">
        <!-- <div style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; padding: 25px; border-radius: 10px; text-align: center; margin-bottom: 30px;">
            <div style="font-size: 48px; margin-bottom: 15px;">✓</div>
            <h2 style="margin: 0 0 10px 0; font-size: 24px; font-weight: 700;">Your Application Has Been Received!</h2>
            <p style="margin: 0; opacity: 0.95; font-size: 15px;">We're processing your visa application and will contact you shortly with updates.</p>
        </div> -->

        <!-- What Happens Next -->
        <div class="section-title" style="margin-bottom: 20px;">
            <i class="fas fa-clock"></i> {{t('visa.whatHappensNext')}}
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <!-- Step 1 -->
            <div style="background: #F0F9FF; border: 2px solid #0077BE; border-radius: 10px; padding: 20px; text-align: center;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #0077BE 0%, #005A9C 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; color: white; font-size: 24px;">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #003580; margin-bottom: 10px;">{{t('visa.documentReview')}}</h3>
                <p style="font-size: 14px; color: #6B7280; margin: 0; line-height: 1.5;">{{t('visa.documentReviewDesc')}}</p>
            </div>

            <!-- Step 2 -->
            <div style="background: #F0FDF4; border: 2px solid #10B981; border-radius: 10px; padding: 20px; text-align: center;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #10B981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; color: white; font-size: 24px;">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #003580; margin-bottom: 10px;">{{t('visa.contactYou')}}</h3>
                <p style="font-size: 14px; color: #6B7280; margin: 0; line-height: 1.5;">{{t('visa.contactYouDesc')}}</p>
            </div>

            <!-- Step 3 -->
            <div style="background: #FFFBEB; border: 2px solid #F59E0B; border-radius: 10px; padding: 20px; text-align: center;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; color: white; font-size: 24px;">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #003580; margin-bottom: 10px;">{{t('visa.processingTime')}}</h3>
                <p style="font-size: 14px; color: #6B7280; margin: 0; line-height: 1.5;">{{t('visa.processingTimeDesc')}}</p>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div style="background: #F9FAFB; border-left: 4px solid #EF4444; border-radius: 8px; padding: 25px; margin-bottom: 25px;">
            <div class="section-title" style="margin-bottom: 20px; color: #DC2626;">
                <i class="fas fa-exclamation-circle"></i> {{t('visa.urgentQueries')}}
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <!-- Email -->
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{t('visa.email')}}</div>
                        <a href="mailto:{{ getSetting('contact_email', 'contact', 'support@travelbookingpanel.com') }}" class="info-link">
                            {{ getSetting('contact_email', 'contact', 'support@travelbookingpanel.com') }}
                        </a>
                    </div>
                </div>

                <!-- Phone -->
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{t('visa.phone')}}</div>
                        <a href="tel:{{ getSetting('contact_phone', 'contact', '+92 21 1234 5678') }}" class="info-link">
                            {{ getSetting('contact_phone', 'contact', '+92 21 1234 5678') }}
                        </a>
                    </div>
                </div>

                @if(getSetting('business_address', 'contact'))
                <!-- Address -->
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{t('visa.officeAddress')}}</div>
                        <div class="info-value">{{ getSetting('business_address', 'contact') }}</div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Office Hours -->
            <div class="office-hours" style="margin-top: 20px;">
                <div class="hours-title">{{t('visa.officeHours')}}</div>
                <div class="hours-item">
                    <span>{{t('visa.mondayFriday')}}</span>
                    <span>{{t('visa.mondayFridayTime')}}</span>
                </div>
                <div class="hours-item">
                    <span>{{t('visa.saturdaySunday')}}</span>
                    <span>{{t('visa.closed')}}</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="button-group" style="justify-content: center; background: transparent; border: none; padding: 0;">
            <a href="{{ url('/') }}" class="btn btn-submit">
                <i class="fas fa-home"></i> {{t('visa.backToHome')}}
            </a>
            <a href="{{ route('visa.create') }}" class="btn btn-reset">
                <i class="fas fa-plus"></i> {{t('visa.submitAnotherApplication')}}
            </a>
        </div>

        <!-- Security Notice -->
        <div style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #E5E7EB;">
            <p style="color: #6B7280; font-size: 13px; margin: 0;">
                <i class="fas fa-shield-alt" style="color: #10B981;"></i>
                {{t('visa.securityNotice')}}
            </p>
        </div>
    </div>
</div>

@endsection
