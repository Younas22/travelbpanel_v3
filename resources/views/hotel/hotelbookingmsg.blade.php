<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{t('hotelbookingmsg.page_title')}}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="{{ url('public/assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body class="view-standalone view-hotel-booking-msg">
    <div class="container">
        <div class="message-card">
            <!-- Header -->
            <div class="message-header">
                <div class="message-icon">
                    <i class="fas fa-flask"></i>
                </div>
                <h1>{{t('hotelbookingmsg.header_title')}}</h1>
                <p>{{t('hotelbookingmsg.header_subtitle')}}</p>
            </div>

            <!-- Content -->
            <div class="message-content">
                <div class="demo-badge">
                    <i class="fas fa-info-circle"></i> {{t('hotelbookingmsg.demo_mode')}}
                </div>

                <div class="message-title">{{t('hotelbookingmsg.booking_not_available')}}</div>

                <div class="message-description">
                    {{t('hotelbookingmsg.demo_description')}}
                </div>

                <!-- Upgrade CTA -->
                <div style="background-color: #FEF3C7; border: 1px solid #FCD34D; border-radius: 8px; padding: 14px; margin-bottom: 24px; text-align: left;">
                    <div style="font-size: 12px; font-weight: 700; color: #92400E; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">
                        <i class="fas fa-rocket"></i> {{t('hotelbookingmsg.start_travel_portal')}}
                    </div>
                    <div style="font-size: 12px; color: #78350F; line-height: 1.6; margin-bottom: 10px;">
                        {{t('hotelbookingmsg.upgrade_description')}}
                    </div>
                    <a href="https://chonkytravel.com/pricing" target="_blank" style="display: inline-block; background-color: #0077BE; color: white; padding: 8px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-decoration: none; transition: all 0.3s ease;">
                        <i class="fas fa-external-link-alt"></i> {{t('hotelbookingmsg.view_pricing')}}
                    </a>
                </div>

                <!-- Features Available -->
                <div class="features-list">
                    <div class="features-title">{{t('hotelbookingmsg.what_you_can_do')}}</div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>{{t('hotelbookingmsg.browse_flights_hotels')}}</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>{{t('hotelbookingmsg.view_detailed_info')}}</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>{{t('hotelbookingmsg.test_booking_flow')}}</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>{{t('hotelbookingmsg.view_invoices')}}</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>{{t('hotelbookingmsg.test_ui_features')}}</span>
                    </div>
                </div>

                <!-- Restriction Notice -->
                <div class="restriction-box">
                    <div class="restriction-title">
                        <i class="fas fa-ban"></i> {{t('hotelbookingmsg.booking_restricted')}}
                    </div>
                    <div class="restriction-text">
                        {{t('hotelbookingmsg.booking_restricted_text')}}
                    </div>
                </div>

                <!-- Buttons -->
                <div class="button-group">
                    <button class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> <a href="{{url('/')}}"> {{t('hotelbookingmsg.go_home')}}</a>
                    </button>
                    <button class="btn btn-secondary" onclick="exploreMore()">
                        <i class="fas fa-compass"></i> {{t('hotelbookingmsg.explore_more')}}
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <!-- <div class="message-footer">
                <div class="footer-text">
                    To make real bookings, please <strong>create a regular account</strong> or <strong>upgrade your demo account</strong>.
                </div>
                <div class="credentials-box">
                    <div class="credentials-title">Test Credentials:</div>
                    <div class="credential-item">
                        Email: demo@example.com
                        <button class="copy-btn" onclick="copyText('demo@example.com')">Copy</button>
                    </div>
                    <div class="credential-item">
                        Password: Demo@12345
                        <button class="copy-btn" onclick="copyText('Demo@12345')">Copy</button>
                    </div>
                </div>
            </div> -->
        </div>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }

        function exploreMore() {
            alert('Redirecting to feature tour...');
            // window.location.href = '/features';
        }

        function copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Copied: ' + text);
            });
        }
    </script>
</body>
</html>
