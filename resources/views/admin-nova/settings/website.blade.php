@extends('admin-nova.layouts.app')

@section('title', 'Website Settings')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
@php
    $s = fn ($group, $key, $default = '') => $settings[$group][$key] ?? $default;
    $img = fn ($path) => $path ? asset('public/assets/images/' . $path) : null;
    $tabs = [
        'main' => ['bi-gear', 'Main Settings'], 'seo' => ['bi-search', 'SEO'], 'system' => ['bi-cpu', 'System'],
        'contact' => ['bi-envelope', 'Contact'], 'branding' => ['bi-palette', 'Branding'], 'homepage' => ['bi-house', 'Home Page'],
        'social' => ['bi-share', 'Social Links'], 'security' => ['bi-shield-lock', 'Security'],
    ];
    $bm = $s('system', 'business_model', 'both');
    $tf = $s('system', 'time_format', '12');
    $tz = $s('system', 'timezone', 'UTC');
    $socials = [
        ['facebook_url', 'bi-facebook', 'url', 'https://facebook.com/yourpage', 'Facebook'],
        ['twitter_url', 'bi-twitter-x', 'url', 'https://twitter.com/youraccount', 'Twitter / X'],
        ['linkedin_url', 'bi-linkedin', 'url', 'https://linkedin.com/company/yourcompany', 'LinkedIn'],
        ['instagram_url', 'bi-instagram', 'url', 'https://instagram.com/youraccount', 'Instagram'],
        ['google_business_url', 'bi-google', 'url', 'https://business.google.com/yourprofile', 'Google Business'],
        ['youtube_url', 'bi-youtube', 'url', 'https://youtube.com/c/yourchannel', 'YouTube'],
        ['whatsapp_number', 'bi-whatsapp', 'tel', '+1-555-123-4567', 'WhatsApp'],
    ];
    $uploads = [
        ['business_logo', 'logoUpload', 'Business Logo (Dark)', 'Click to upload dark logo', 'PNG, JPG, GIF supported, max size 2 MB', 'image/*', 'Current Dark Logo', ''],
        ['business_logo_white', 'logoWhiteUpload', 'Business Logo (White)', 'Click to upload white logo', 'PNG recommended for transparency, max size 2 MB', 'image/*', 'Current White Logo', 'is-dark'],
        ['favicon', 'faviconUpload', 'Favicon', 'Click to upload favicon', 'PNG, ICO supported, max size 1 MB, recommended 32x32px', 'image/*,.ico', 'Current Favicon (32x32)', 'is-favicon'],
    ];
@endphp

<div class="tbp-website-settings-page">
    <div class="tbp-website-settings-header">
        <div>
            <h1>Website Settings</h1>
            <p>Configure your website settings and preferences</p>
        </div>
        <button type="button" class="tbp-website-settings-btn tbp-website-settings-save-all" onclick="saveAllSettings(this)"><i class="bi bi-check-lg"></i> <span>Save All Changes</span></button>
    </div>

    <div class="tbp-website-settings-panel">
        <div class="tbp-website-settings-panel-head">
            <h2>Configuration Panel</h2>
            <p>Manage all aspects of your travel booking platform</p>
        </div>

        <nav class="tbp-website-settings-tabs" role="tablist">
            @foreach($tabs as $key => [$icon, $label])
                <button type="button" class="tbp-website-settings-tab {{ $loop->first ? 'active' : '' }}" id="{{ $key }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $key }}" role="tab" aria-controls="{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    <i class="bi {{ $icon }}"></i> {{ $label }}
                </button>
            @endforeach
        </nav>

        <div class="tab-content">

            {{-- ===== MAIN ===== --}}
            <div class="tab-pane fade show active" id="main" role="tabpanel">
                <form id="mainSettingsForm" class="tbp-website-settings-form" onsubmit="saveSettingsGroup(event, 'main')">
                    <section class="tbp-website-settings-card">
                        <header><i class="bi bi-person-badge"></i><div><h3>Application Name and Tags</h3><p>Basic information about your travel booking platform</p></div></header>
                        <label class="tbp-website-settings-field"><span>Business Name</span>
                            <input type="text" name="business_name" value="{{ $s('main', 'business_name', 'SkyBooking Travel') }}" placeholder="Enter your business name"></label>
                        <label class="tbp-website-settings-check">
                            <input type="checkbox" name="show_business_name_with_logo" value="1" @checked($s('main', 'show_business_name_with_logo', false))>
                            <span><strong>Show business name next to the logo</strong><small>When enabled, the business name above is shown alongside the logo everywhere it appears — the admin dashboard sidebar and the public website.</small></span>
                        </label>
                        <label class="tbp-website-settings-field"><span>Domain Name</span>
                            <input type="text" name="domain_name" value="{{ $s('main', 'domain_name', 'skybooking.com') }}" placeholder="Enter your domain name"></label>
                        <label class="tbp-website-settings-field"><span>License Key</span>
                            <input type="text" name="license_key" value="{{ $s('main', 'license_key') }}" placeholder="Enter your license key">
                            <small>Contact support to get your license key</small></label>
                        <div class="tbp-website-settings-field"><span>Website Status</span>
                            <label class="tbp-website-settings-switch">
                                <input type="checkbox" name="website_offline" value="1" @checked($s('main', 'website_offline', false))>
                                <i aria-hidden="true"></i>
                                <span><strong>Website Offline (Maintenance Mode)</strong><small>Enable this to put your website in maintenance mode</small></span>
                            </label>
                        </div>
                    </section>
                    <div class="tbp-website-settings-actions"><button type="submit" class="tbp-website-settings-btn tbp-website-settings-save"><i class="bi bi-check-lg"></i> <span>Save Main Settings</span></button></div>
                </form>
            </div>

            {{-- ===== SEO ===== --}}
            <div class="tab-pane fade" id="seo" role="tabpanel">
                <form id="seoSettingsForm" class="tbp-website-settings-form" onsubmit="saveSettingsGroup(event, 'seo')">
                    <section class="tbp-website-settings-card">
                        <header><i class="bi bi-search"></i><div><h3>SEO and Meta Tags</h3><p>Optimize your website for search engines</p></div></header>
                        <label class="tbp-website-settings-field"><span>Meta Title</span>
                            <input type="text" name="meta_title" maxlength="60" value="{{ $s('seo', 'meta_title', 'SkyBooking - Best Flight Booking Platform') }}" placeholder="Enter meta title">
                            <small>Recommended length: 50-60 characters</small></label>
                        <label class="tbp-website-settings-field"><span>Meta Description</span>
                            <textarea name="meta_description" rows="3" maxlength="160" placeholder="Enter meta description">{{ $s('seo', 'meta_description', 'Book flights at the best prices with SkyBooking. Compare flights from multiple airlines and save on your next trip. Easy booking, 24/7 support.') }}</textarea>
                            <small>Recommended length: 150-160 characters</small></label>
                        <label class="tbp-website-settings-field"><span>Keywords</span>
                            <input type="text" name="meta_keywords" value="{{ $s('seo', 'meta_keywords', 'flight booking, cheap flights, airline tickets, travel') }}" placeholder="Enter keywords separated by commas">
                            <small>Separate multiple keywords with commas</small></label>
                    </section>
                    <div class="tbp-website-settings-actions"><button type="submit" class="tbp-website-settings-btn tbp-website-settings-save"><i class="bi bi-check-lg"></i> <span>Save SEO Settings</span></button></div>
                </form>
            </div>

            {{-- ===== ACCOUNTS (tab hidden, as before; still saved by Save All) ===== --}}
            <div class="tab-pane fade" id="accounts" role="tabpanel">
                <form id="accountsSettingsForm" class="tbp-website-settings-form" onsubmit="saveSettingsGroup(event, 'accounts')">
                    @foreach(['guest_booking' => true, 'user_registration' => true, 'supplier_registration' => false, 'agent_registration' => true] as $key => $default)
                        <input type="checkbox" name="{{ $key }}" value="1" hidden @checked($s('accounts', $key, $default))>
                    @endforeach
                </form>
            </div>

            {{-- ===== SYSTEM ===== --}}
            <div class="tab-pane fade" id="system" role="tabpanel">
                <form id="systemSettingsForm" class="tbp-website-settings-form" onsubmit="saveSettingsGroup(event, 'system')">
                    <section class="tbp-website-settings-card">
                        <header><i class="bi bi-gear"></i><div><h3>System Settings and Configurations</h3><p>Configure system-level settings and integrations</p></div></header>
                        <div class="tbp-website-settings-field"><span>Business Model</span>
                            <small>Controls which signup options appear on the public website navbar</small>
                            <div class="tbp-website-settings-radios">
                                @foreach(['both' => ['B2B + B2C', 'Both Customer & Agent signup visible'], 'b2c' => ['B2C Only', 'Customer signup only (Agent signup hidden)'], 'b2b' => ['B2B Only', 'Agent signup only (Customer signup hidden)']] as $val => [$title, $desc])
                                    <label><input type="radio" name="business_model" value="{{ $val }}" @checked($bm == $val)> <strong>{{ $title }}</strong> — {{ $desc }}</label>
                                @endforeach
                            </div>
                        </div>
                        <label class="tbp-website-settings-field"><span>Agent Signup URL</span>
                            <small>Where every "Agent Signup" link across the site points to. Leave blank to use the built-in page (/agent/register). Enter a relative path (e.g. agent/signup) or a full URL to point somewhere else.</small>
                            <input type="text" name="agent_signup_url" value="{{ $s('system', 'agent_signup_url') }}" placeholder="e.g. agent/signup or https://example.com/agent-signup"></label>
                        <label class="tbp-website-settings-field"><span>Timezone</span>
                            <small>Used to display all dates and times across the site (bookings, invoices, transactions, flight times, etc.)</small>
                            <select name="timezone">
                                @foreach(\DateTimeZone::listIdentifiers() as $tzId)
                                    <option value="{{ $tzId }}" @selected($tz === $tzId)>{{ $tzId }} (UTC{{ \Carbon\Carbon::now(new \DateTimeZone($tzId))->format('P') }})</option>
                                @endforeach
                            </select></label>
                        <div class="tbp-website-settings-field"><span>Time Format</span>
                            <small>Controls whether times across the site show as 12-hour (06:09 PM) or 24-hour (18:09)</small>
                            <div class="tbp-website-settings-radios">
                                <label><input type="radio" name="time_format" value="12" @checked($tf == '12')> <strong>12-hour</strong> — e.g. 06:09 PM</label>
                                <label><input type="radio" name="time_format" value="24" @checked($tf == '24')> <strong>24-hour</strong> — e.g. 18:09</label>
                            </div>
                        </div>
                        <div class="tbp-website-settings-grid">
                            <label class="tbp-website-settings-field"><span>Google Analytics Tracking ID</span>
                                <input type="text" name="google_analytics_id" value="{{ $s('system', 'google_analytics_id') }}" placeholder="G-XXXXXXXXXX">
                                <small>Your Google Analytics 4 measurement ID</small></label>
                            <label class="tbp-website-settings-field"><span>Google Tag Manager ID</span>
                                <input type="text" name="google_tag_manager_id" value="{{ $s('system', 'google_tag_manager_id') }}" placeholder="GTM-XXXXXXX">
                                <small>Your Google Tag Manager container ID</small></label>
                        </div>
                        <label class="tbp-website-settings-field"><span>Facebook Pixel ID</span>
                            <input type="text" name="facebook_pixel_id" value="{{ $s('system', 'facebook_pixel_id') }}" placeholder="Enter Facebook Pixel ID">
                            <small>For tracking conversions and creating custom audiences</small></label>
                        <label class="tbp-website-settings-field"><span>Custom Tracking Code</span>
                            <textarea name="custom_tracking_code" rows="4" placeholder="Enter custom tracking scripts">{{ $s('system', 'custom_tracking_code') }}</textarea>
                            <small>Additional tracking codes (will be added to head section)</small></label>
                    </section>
                    <div class="tbp-website-settings-actions"><button type="submit" class="tbp-website-settings-btn tbp-website-settings-save"><i class="bi bi-check-lg"></i> <span>Save System Settings</span></button></div>
                </form>
            </div>

            {{-- ===== CONTACT ===== --}}
            <div class="tab-pane fade" id="contact" role="tabpanel">
                <form id="contactSettingsForm" class="tbp-website-settings-form" onsubmit="saveSettingsGroup(event, 'contact')">
                    <section class="tbp-website-settings-card">
                        <header><i class="bi bi-envelope"></i><div><h3>Contact Details</h3><p>Your business contact information</p></div></header>
                        <label class="tbp-website-settings-field"><span>Business Address</span>
                            <textarea name="business_address" rows="3" placeholder="Enter your business address">{{ $s('contact', 'business_address') }}</textarea></label>
                        <label class="tbp-website-settings-field"><span>Address on Map (Embed Code)</span>
                            <textarea name="map_embed_code" rows="3" placeholder="Enter Google Maps embed code">{{ $s('contact', 'map_embed_code') }}</textarea>
                            <small>Get embed code from Google Maps</small></label>
                        <div class="tbp-website-settings-grid">
                            <label class="tbp-website-settings-field"><span>Contact Email</span>
                                <input type="email" name="contact_email" value="{{ $s('contact', 'contact_email') }}" placeholder="contact@yourcompany.com"></label>
                            <label class="tbp-website-settings-field"><span>Contact Phone</span>
                                <input type="tel" name="contact_phone" value="{{ $s('contact', 'contact_phone') }}" placeholder="+1 (555) 123-4567"></label>
                            <label class="tbp-website-settings-field"><span>Support Email</span>
                                <input type="email" name="support_email" value="{{ $s('contact', 'support_email') }}" placeholder="support@yourcompany.com"></label>
                            <label class="tbp-website-settings-field"><span>Emergency Contact</span>
                                <input type="tel" name="emergency_contact" value="{{ $s('contact', 'emergency_contact') }}" placeholder="+1 (555) 911-HELP"></label>
                        </div>
                    </section>
                    <div class="tbp-website-settings-actions"><button type="submit" class="tbp-website-settings-btn tbp-website-settings-save"><i class="bi bi-check-lg"></i> <span>Save Contact Information</span></button></div>
                </form>
            </div>

            {{-- ===== BRANDING ===== --}}
            <div class="tab-pane fade" id="branding" role="tabpanel">
                <form id="brandingSettingsForm" class="tbp-website-settings-form" onsubmit="saveSettingsGroup(event, 'branding')" enctype="multipart/form-data">
                    <section class="tbp-website-settings-card">
                        <header><i class="bi bi-image"></i><div><h3>Business Logo and Favicon</h3><p>Upload your brand assets</p></div></header>
                        @foreach($uploads as [$name, $inputId, $label, $cta, $hint, $accept, $currentLabel, $variant])
                            @php $current = $img($s('branding', $name)); @endphp
                            <div class="tbp-website-settings-upload-row">
                                <div class="tbp-website-settings-field"><span>{{ $label }}</span>
                                    <label class="tbp-website-settings-upload" for="{{ $inputId }}">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                        <strong data-file-name>{{ $cta }}</strong>
                                        <small>{{ $hint }}</small>
                                    </label>
                                    <input type="file" id="{{ $inputId }}" name="{{ $name }}" accept="{{ $accept }}" hidden data-preview="{{ $inputId }}Preview">
                                </div>
                                <div class="tbp-website-settings-field"><span>{{ $currentLabel }}</span>
                                    <div class="tbp-website-settings-preview {{ $variant }}">
                                        <img id="{{ $inputId }}Preview" src="{{ $current }}" alt="{{ $currentLabel }}" @if(!$current) hidden @endif>
                                        @unless($current)<em data-empty>Nothing uploaded yet</em>@endunless
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </section>
                    <div class="tbp-website-settings-actions"><button type="submit" class="tbp-website-settings-btn tbp-website-settings-save"><i class="bi bi-check-lg"></i> <span>Save Branding Assets</span></button></div>
                </form>
            </div>

            {{-- ===== HOMEPAGE ===== --}}
            <div class="tab-pane fade" id="homepage" role="tabpanel">
                <form id="homepageSettingsForm" class="tbp-website-settings-form" onsubmit="saveSettingsGroup(event, 'homepage')" enctype="multipart/form-data">
                    <section class="tbp-website-settings-card">
                        <header><i class="bi bi-image"></i><div><h3>Homepage Cover</h3><p>Upload homepage hero/cover image</p></div></header>
                        @php $cover = $img($s('homepage', 'cover_image')); @endphp
                        <div class="tbp-website-settings-upload-row is-cover">
                            <div class="tbp-website-settings-preview is-cover">
                                <img id="coverUploadPreview" src="{{ $cover }}" alt="Current cover image" @if(!$cover) hidden @endif>
                                @unless($cover)<em data-empty>No cover image yet</em>@endunless
                            </div>
                            <div>
                                <label class="tbp-website-settings-upload" for="coverUpload">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    <strong data-file-name>Click to upload homepage cover</strong>
                                    <small>PNG, JPG supported, max size 5 MB<br>Recommended size: 1920x800 pixels</small>
                                </label>
                                <input type="file" id="coverUpload" name="cover_image" accept="image/*" hidden data-preview="coverUploadPreview">
                            </div>
                        </div>
                        <label class="tbp-website-settings-field"><span>Cover Title</span>
                            <input type="text" name="cover_title" value="{{ $s('homepage', 'cover_title', 'Find Your Perfect Flight') }}" placeholder="Enter cover title"></label>
                        <label class="tbp-website-settings-field"><span>Cover Subtitle</span>
                            <input type="text" name="cover_subtitle" value="{{ $s('homepage', 'cover_subtitle', 'Compare prices and book flights from top airlines worldwide') }}" placeholder="Enter cover subtitle"></label>
                    </section>
                    <div class="tbp-website-settings-actions"><button type="submit" class="tbp-website-settings-btn tbp-website-settings-save"><i class="bi bi-check-lg"></i> <span>Save Homepage Settings</span></button></div>
                </form>
            </div>

            {{-- ===== SOCIAL ===== --}}
            <div class="tab-pane fade" id="social" role="tabpanel">
                <form id="socialSettingsForm" class="tbp-website-settings-form" onsubmit="saveSettingsGroup(event, 'social')">
                    <section class="tbp-website-settings-card">
                        <header><i class="bi bi-share"></i><div><h3>Social Links</h3><p>Social media pages links</p></div></header>
                        @foreach($socials as [$name, $icon, $type, $placeholder, $label])
                            <label class="tbp-website-settings-social">
                                <span class="tbp-website-settings-social-icon" title="{{ $label }}"><i class="bi {{ $icon }}"></i></span>
                                <input type="{{ $type }}" name="{{ $name }}" value="{{ $s('social', $name) }}" placeholder="{{ $placeholder }}" aria-label="{{ $label }}">
                            </label>
                        @endforeach
                    </section>
                    <div class="tbp-website-settings-actions"><button type="submit" class="tbp-website-settings-btn tbp-website-settings-save"><i class="bi bi-check-lg"></i> <span>Save Social Links</span></button></div>
                </form>
            </div>

            {{-- ===== SECURITY ===== --}}
            <div class="tab-pane fade" id="security" role="tabpanel">
                <form id="securitySettingsForm" class="tbp-website-settings-form" onsubmit="saveSettingsGroup(event, 'security')">
                    <section class="tbp-website-settings-card">
                        <header><i class="bi bi-shield-check"></i><div><h3>Google Authenticator Login</h3>
                            <p>Turn this on per account type below. The first time it applies to an account, that person gets a QR code to scan into Google Authenticator right after their password — every login after that just asks for the 6-digit code. Each account has its own key; there's nothing to configure per-user here.</p></div></header>
                        @foreach(['admin_2fa_enabled' => 'Admin', 'agent_2fa_enabled' => 'Agent', 'user_2fa_enabled' => 'Customer'] as $key => $who)
                            <label class="tbp-website-settings-switch">
                                <input type="checkbox" name="{{ $key }}" value="1" @checked($s('security', $key, '0') === '1')>
                                <i aria-hidden="true"></i>
                                <span><strong>Require for {{ $who }} accounts</strong></span>
                            </label>
                        @endforeach
                    </section>
                    <div class="tbp-website-settings-actions"><button type="submit" class="tbp-website-settings-btn tbp-website-settings-save"><i class="bi bi-check-lg"></i> <span>Save Security Settings</span></button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="tbp-website-settings-toast" id="tbpSettingsToast" role="status" hidden></div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
var settingsUrl = @json(route('admin.settings.website.update'));

function postGroup(form, group) {
    var data = new FormData(form);
    data.append('group', group);
    return fetch(settingsUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: data
    }).then(function (r) { return r.json(); });
}

function setBusy(btn, busy) {
    if (!btn) return;
    var label = btn.querySelector('span');
    if (busy) { btn.dataset.label = label.textContent; label.textContent = 'Saving…'; }
    else if (btn.dataset.label) { label.textContent = btn.dataset.label; }
    btn.disabled = busy;
}

function showNotification(message, type) {
    var toast = document.getElementById('tbpSettingsToast');
    toast.textContent = message;
    toast.className = 'tbp-website-settings-toast is-' + (type || 'success');
    toast.hidden = false;
    clearTimeout(toast._t);
    toast._t = setTimeout(function () { toast.hidden = true; }, 3500);
}

function saveSettingsGroup(event, group) {
    event.preventDefault();
    var btn = event.target.querySelector('.tbp-website-settings-save');
    setBusy(btn, true);
    postGroup(event.target, group).then(function (data) {
        if (data.success) {
            showNotification('Settings saved successfully!', 'success');
            setTimeout(function () { location.reload(); }, 700);
        } else {
            setBusy(btn, false);
            showNotification('Error saving settings: ' + (data.message || 'please check the fields'), 'error');
        }
    }).catch(function (err) {
        console.error(err);
        setBusy(btn, false);
        showNotification('An error occurred while saving settings', 'error');
    });
}

function saveAllSettings(btn) {
    var forms = document.querySelectorAll('[id$="SettingsForm"]');
    setBusy(btn, true);
    Promise.all(Array.prototype.map.call(forms, function (form) {
        return postGroup(form, form.id.replace('SettingsForm', '').toLowerCase())
            .then(function (d) { return !!d.success; }, function () { return false; });
    })).then(function (results) {
        setBusy(btn, false);
        if (results.every(Boolean)) showNotification('All settings saved successfully!', 'success');
        else showNotification('Some settings could not be saved. Please check individual tabs.', 'warning');
    });
}

// Preview each picked file in its own preview box and show its name in the drop area.
document.querySelectorAll('.tbp-website-settings-page input[type=file][data-preview]').forEach(function (input) {
    input.addEventListener('change', function () {
        var file = input.files[0];
        if (!file) return;
        var area = document.querySelector('label[for="' + input.id + '"] [data-file-name]');
        if (area) area.textContent = file.name;
        var img = document.getElementById(input.dataset.preview);
        var reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result; img.hidden = false;
            var empty = img.parentNode.querySelector('[data-empty]');
            if (empty) empty.remove();
        };
        reader.readAsDataURL(file);
    });
});

$(function () {
    $('select[name="timezone"]').select2({ placeholder: 'Search and select a timezone...', allowClear: false, width: '100%' });
});
</script>
@endpush
