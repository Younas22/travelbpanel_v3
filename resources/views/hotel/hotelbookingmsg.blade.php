<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{t('hotelbookingmsg.page_title')}}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #F0F4FF 0%, #E8EFFF 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            width: 100%;
        }

        .message-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 119, 190, 0.15);
            overflow: hidden;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header */
        .message-header {
            background: linear-gradient(135deg, #0077BE 0%, #005A9C 100%);
            padding: 30px 24px;
            text-align: center;
            color: white;
        }

        .message-icon {
            font-size: 48px;
            margin-bottom: 12px;
            display: inline-block;
        }

        .message-header h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .message-header p {
            font-size: 13px;
            opacity: 0.9;
            line-height: 1.5;
        }

        /* Content */
        .message-content {
            padding: 32px 24px;
            text-align: center;
        }

        .demo-badge {
            display: inline-block;
            background-color: #FEF3C7;
            color: #92400E;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }

        .message-title {
            font-size: 18px;
            font-weight: 700;
            color: #003580;
            margin-bottom: 12px;
        }

        .message-description {
            font-size: 14px;
            color: #6B7280;
            line-height: 1.8;
            margin-bottom: 24px;
        }

        .features-list {
            background-color: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: left;
        }

        .features-title {
            font-size: 12px;
            font-weight: 700;
            color: #003580;
            text-transform: uppercase;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: #6B7280;
            padding: 8px 0;
            line-height: 1.5;
        }

        .feature-item:last-child {
            padding-bottom: 0;
        }

        .feature-icon {
            color: #10B981;
            font-weight: 700;
            font-size: 14px;
        }

        .restriction-box {
            background-color: #FEE2E2;
            border: 1px solid #FECACA;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 24px;
            text-align: left;
        }

        .restriction-title {
            font-size: 12px;
            font-weight: 700;
            color: #DC2626;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .restriction-text {
            font-size: 12px;
            color: #991B1B;
            line-height: 1.6;
        }

        /* Buttons */
        .button-group {
            display: flex;
            gap: 12px;
            flex-direction: column;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0077BE 0%, #005A9C 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 119, 190, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 119, 190, 0.3);
        }

        .btn-secondary {
            background-color: #F3F4F6;
            color: #1F2937;
            border: 1px solid #D1D5DB;
        }

        .btn-secondary:hover {
            background-color: #E5E7EB;
        }

        /* Footer Info */
        .message-footer {
            background-color: #F9FAFB;
            padding: 20px 24px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
        }

        .footer-text {
            font-size: 11px;
            color: #6B7280;
            line-height: 1.8;
        }

        .footer-text strong {
            color: #003580;
            font-weight: 700;
        }

        /* Test Credentials */
        .credentials-box {
            background-color: #F0F9FF;
            border: 1px solid #BAE6FD;
            border-radius: 8px;
            padding: 14px;
            margin-top: 12px;
        }

        .credentials-title {
            font-size: 11px;
            font-weight: 700;
            color: #0077BE;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .credential-item {
            font-size: 11px;
            color: #0077BE;
            padding: 4px 0;
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }

        .copy-btn {
            background: none;
            border: none;
            color: #0077BE;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 6px;
            margin-left: 8px;
            transition: all 0.2s ease;
        }

        .copy-btn:hover {
            background-color: rgba(0, 119, 190, 0.1);
            border-radius: 4px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .message-header {
                padding: 24px 16px;
            }

            .message-content {
                padding: 24px 16px;
            }

            .message-title {
                font-size: 16px;
            }

            .message-description {
                font-size: 13px;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
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
