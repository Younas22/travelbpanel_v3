@php
    // Invoice-link email for tour / umrah bookings. Email clients ignore
    // stylesheets, so this template is styled inline (same look as hotel_invoice).
    // Params: $title, $intro, $contactName, $businessName, $invoiceUrl, $rows [label => value]
    $logo = getSettingImage('business_logo', 'branding');
    $contactEmail = getSetting('contact_email', 'contact', '');
    $contactPhone = getSetting('contact_phone', 'contact', '');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#F3F4F6;font-family:Arial,Helvetica,sans-serif;color:#000000;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F3F4F6;padding:24px 12px;">
    <tr><td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#FFFFFF;border:1px solid #E5E7EB;border-radius:16px;overflow:hidden;">
            <tr><td style="padding:24px 28px;border-bottom:1px solid #E5E7EB;">
                @if($logo)<img src="{{ $logo }}" alt="{{ $businessName }}" style="height:40px;">@else<strong>{{ $businessName }}</strong>@endif
            </td></tr>
            <tr><td style="padding:28px;">
                <h1 style="margin:0 0 8px;font-size:22px;color:#000000;">{{ t('hotelinvoice.confirmationTitle') }}</h1>
                <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#4B5563;">
                    {{ t('hotelinvoice.emailGreeting') }} {{ $contactName }},<br>
                    {{ $intro }}
                </p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E5E7EB;border-radius:12px;font-size:14px;">
                    @foreach($rows as $label => $value)
                        <tr>
                            <td style="padding:10px 14px;color:#6B7280;{{ $loop->last ? 'background:#EFF4FF;' : 'border-bottom:1px solid #F3F4F6;' }}">{{ $label }}</td>
                            <td style="padding:10px 14px;text-align:right;font-weight:bold;{{ $loop->last ? 'background:#EFF4FF;color:#2563EB;' : 'border-bottom:1px solid #F3F4F6;' }}">{{ $value }}</td>
                        </tr>
                    @endforeach
                </table>
                <p style="margin:24px 0;text-align:center;">
                    <a href="{{ $invoiceUrl }}" style="display:inline-block;padding:14px 32px;background:#2563EB;color:#FFFFFF;border-radius:10px;font-weight:bold;text-decoration:none;">{{ t('hotelinvoice.emailViewInvoice') }}</a>
                </p>
                <p style="margin:0;font-size:12px;color:#6B7280;word-break:break-all;">{{ $invoiceUrl }}</p>
            </td></tr>
            <tr><td style="padding:18px 28px;background:#2563EB;color:#FFFFFF;font-size:12px;line-height:1.6;">
                <strong>{{ $businessName }}</strong>
                @if($contactEmail) · {{ $contactEmail }}@endif
                @if($contactPhone) · {{ $contactPhone }}@endif
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
