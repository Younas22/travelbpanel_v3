@extends('admin.layouts.app')

@section('title', 'Website Settings')

@section('content')
<div class="content-area p-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1">Website Settings</h2>
                <p class="text-muted mb-0">Configure your website settings and preferences</p>
            </div>
            <div class="col-md-4">
                <div class="text-end">
                    <button class="btn btn-success modern-btn" onclick="saveAllSettings()">
                        <i class="bi bi-check-lg"></i> Save All Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Container -->
    <div class="settings-container">
        <!-- Settings Header -->
        <div class="settings-header">
            <h4 class="mb-1">Configuration Panel</h4>
            <p class="text-muted mb-0">Manage all aspects of your travel booking platform</p>
        </div>

        <!-- Settings Tabs -->
        <ul class="nav nav-tabs settings-tabs" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link modern-btn active" id="main-tab" data-bs-toggle="tab" data-bs-target="#main" type="button" role="tab">
                    <i class="bi bi-gear me-2"></i>Main Settings
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link modern-btn" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab">
                    <i class="bi bi-search me-2"></i>SEO
                </button>
            </li>
            <!-- <li class="nav-item" role="presentation">
                <button class="nav-link modern-btn" id="accounts-tab" data-bs-toggle="tab" data-bs-target="#accounts" type="button" role="tab">
                    <i class="bi bi-people me-2"></i>Accounts
                </button>
            </li> -->
            <li class="nav-item" role="presentation">
                <button class="nav-link modern-btn" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
                    <i class="bi bi-cpu me-2"></i>System
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link modern-btn" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                    <i class="bi bi-envelope me-2"></i>Contact
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link modern-btn" id="branding-tab" data-bs-toggle="tab" data-bs-target="#branding" type="button" role="tab">
                    <i class="bi bi-palette me-2"></i>Branding
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link modern-btn" id="homepage-tab" data-bs-toggle="tab" data-bs-target="#homepage" type="button" role="tab">
                    <i class="bi bi-house me-2"></i>Home Page
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link modern-btn" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab">
                    <i class="bi bi-share me-2"></i>Social Links
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="settingsTabContent">
            <!-- Main Settings Tab -->
            <div class="tab-pane fade show active" id="main" role="tabpanel">
                <div class="settings-content">
                    <form id="mainSettingsForm" onsubmit="saveSettingsGroup(event, 'main')">
                        <div class="settings-section">
                            <div class="section-title">Application Name and Tags</div>
                            <div class="section-description">Basic information about your travel booking platform</div>
                            
                            <div class="form-group">
                                <label class="form-label">Business Name</label>
                                <input type="text" class="form-control" name="business_name" 
                                       value="{{ $settings['main']['business_name'] ?? 'SkyBooking Travel' }}" 
                                       placeholder="Enter your business name">
                            </div>
 
                            <div class="form-group">
                                <label class="form-label">Domain Name</label>
                                <input type="text" class="form-control" name="domain_name" 
                                       value="{{ $settings['main']['domain_name'] ?? 'skybooking.com' }}" 
                                       placeholder="Enter your domain name">
                            </div>

                            <div class="form-group">
                                <label class="form-label">License Key</label>
                                <input type="text" class="form-control" name="license_key" 
                                       value="{{ $settings['main']['license_key'] ?? '' }}" 
                                       placeholder="Enter your license key">
                                <div class="form-text">Contact support to get your license key</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Website Status</label>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="website_offline" 
                                           id="websiteOffline" value="1" 
                                           {{ ($settings['main']['website_offline'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="websiteOffline">
                                        Website Offline (Maintenance Mode)
                                    </label>
                                </div>
                                <div class="form-text">Enable this to put your website in maintenance mode</div>
                            </div>
                        </div>

                        <div class="save-section">
                            <button type="submit" class="btn btn-primary modern-btn">
                                <i class="bi bi-check-lg"></i> Save Main Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SEO Tab -->
            <div class="tab-pane fade" id="seo" role="tabpanel">
                <div class="settings-content">
                    <form id="seoSettingsForm" onsubmit="saveSettingsGroup(event, 'seo')">
                        <div class="settings-section">
                            <div class="section-title">SEO and Meta Tags</div>
                            <div class="section-description">Optimize your website for search engines</div>
                            
                            <div class="form-group">
                                <label class="form-label">Meta Title</label>
                                <input type="text" class="form-control" name="meta_title" 
                                       value="{{ $settings['seo']['meta_title'] ?? 'SkyBooking - Best Flight Booking Platform' }}" 
                                       placeholder="Enter meta title" maxlength="60">
                                <div class="form-text">Recommended length: 50-60 characters</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Meta Description</label>
                                <textarea class="form-control" rows="3" name="meta_description" 
                                          placeholder="Enter meta description" maxlength="160">{{ $settings['seo']['meta_description'] ?? 'Book flights at the best prices with SkyBooking. Compare flights from multiple airlines and save on your next trip. Easy booking, 24/7 support.' }}</textarea>
                                <div class="form-text">Recommended length: 150-160 characters</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Keywords</label>
                                <input type="text" class="form-control" name="meta_keywords" 
                                       value="{{ $settings['seo']['meta_keywords'] ?? 'flight booking, cheap flights, airline tickets, travel' }}" 
                                       placeholder="Enter keywords separated by commas">
                            </div>
                        </div>

                        <div class="save-section">
                            <button type="submit" class="btn btn-primary modern-btn">
                                <i class="bi bi-check-lg"></i> Save SEO Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Accounts Tab -->
            <div class="tab-pane fade" id="accounts" role="tabpanel">
                <div class="settings-content">
                    <form id="accountsSettingsForm" onsubmit="saveSettingsGroup(event, 'accounts')">
                        <div class="settings-section">
                            <div class="section-title">Users and Accounts Settings</div>
                            <div class="section-description">Configure user registration and booking permissions</div>
                            
                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="guest_booking" 
                                           id="guestBooking" value="1" 
                                           {{ ($settings['accounts']['guest_booking'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="guestBooking">
                                        Allow Guest Booking
                                    </label>
                                </div>
                                <div class="form-text">If disabled, only registered users can make bookings</div>
                            </div>

                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="user_registration" 
                                           id="userRegistration" value="1" 
                                           {{ ($settings['accounts']['user_registration'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="userRegistration">
                                        Enable User Registration
                                    </label>
                                </div>
                                <div class="form-text">Allow new users to create accounts</div>
                            </div>

                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="supplier_registration" 
                                           id="supplierRegistration" value="1" 
                                           {{ ($settings['accounts']['supplier_registration'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="supplierRegistration">
                                        Enable Suppliers Registration
                                    </label>
                                </div>
                                <div class="form-text">Allow suppliers to register and add their services</div>
                            </div>

                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="agent_registration" 
                                           id="agentRegistration" value="1" 
                                           {{ ($settings['accounts']['agent_registration'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="agentRegistration">
                                        Enable Agents Registration
                                    </label>
                                </div>
                                <div class="form-text">Allow travel agents to register and earn commissions</div>
                            </div>
                        </div>

                        <div class="save-section">
                            <button type="submit" class="btn btn-primary modern-btn">
                                <i class="bi bi-check-lg"></i> Save Account Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- System Settings Tab -->
            <div class="tab-pane fade" id="system" role="tabpanel">
                <div class="settings-content">
                    <form id="systemSettingsForm" onsubmit="saveSettingsGroup(event, 'system')">
                        <div class="settings-section">
                            <div class="section-title">System Settings and Configurations</div>
                            <div class="section-description">Configure system-level settings and integrations</div>

                            <div class="form-group">
                                <label class="form-label">Business Model</label>
                                <div class="form-text mb-2">Controls which signup options appear on the public website navbar</div>
                                <div class="d-flex flex-column gap-2">
                                    @php $bm = $settings['system']['business_model'] ?? 'both'; @endphp
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="business_model" id="bm_both" value="both" {{ $bm == 'both' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="bm_both">
                                            <strong>B2B + B2C</strong> &mdash; Both Customer &amp; Agent signup visible
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="business_model" id="bm_b2c" value="b2c" {{ $bm == 'b2c' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="bm_b2c">
                                            <strong>B2C Only</strong> &mdash; Customer signup only (Agent signup hidden)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="business_model" id="bm_b2b" value="b2b" {{ $bm == 'b2b' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="bm_b2b">
                                            <strong>B2B Only</strong> &mdash; Agent signup only (Customer signup hidden)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Agent Signup URL</label>
                                <div class="form-text mb-2">Where every "Agent Signup" link across the site points to. Leave blank to use the built-in page (/agent/register). Enter a relative path (e.g. agent/signup) or a full URL to point somewhere else.</div>
                                <input type="text" class="form-control" name="agent_signup_url"
                                       value="{{ $settings['system']['agent_signup_url'] ?? '' }}"
                                       placeholder="e.g. agent/signup or https://example.com/agent-signup">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Google Analytics Tracking ID</label>
                                <input type="text" class="form-control" name="google_analytics_id" 
                                       value="{{ $settings['system']['google_analytics_id'] ?? '' }}" 
                                       placeholder="G-XXXXXXXXXX">
                                <div class="form-text">Your Google Analytics 4 measurement ID</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Google Tag Manager ID</label>
                                <input type="text" class="form-control" name="google_tag_manager_id" 
                                       value="{{ $settings['system']['google_tag_manager_id'] ?? '' }}" 
                                       placeholder="GTM-XXXXXXX">
                                <div class="form-text">Your Google Tag Manager container ID</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Facebook Pixel ID</label>
                                <input type="text" class="form-control" name="facebook_pixel_id" 
                                       value="{{ $settings['system']['facebook_pixel_id'] ?? '' }}" 
                                       placeholder="Enter Facebook Pixel ID">
                                <div class="form-text">For tracking conversions and creating custom audiences</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Custom Tracking Code</label>
                                <textarea class="form-control" rows="4" name="custom_tracking_code" 
                                          placeholder="Enter custom tracking scripts">{{ $settings['system']['custom_tracking_code'] ?? '' }}</textarea>
                                <div class="form-text">Additional tracking codes (will be added to head section)</div>
                            </div>
                        </div>

                        <div class="save-section">
                            <button type="submit" class="btn btn-primary modern-btn">
                                <i class="bi bi-check-lg"></i> Save System Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contact Tab -->
            <div class="tab-pane fade" id="contact" role="tabpanel">
                <div class="settings-content">
                    <form id="contactSettingsForm" onsubmit="saveSettingsGroup(event, 'contact')">
                        <div class="settings-section">
                            <div class="section-title">Contact Details</div>
                            <div class="section-description">Your business contact information</div>
                            
                            <div class="form-group">
                                <label class="form-label">Business Address</label>
                                <textarea class="form-control" rows="3" name="business_address" 
                                          placeholder="Enter your business address">{{ $settings['contact']['business_address'] ?? '' }}</textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Address on Map (Embed Code)</label>
                                <textarea class="form-control" rows="3" name="map_embed_code" 
                                          placeholder="Enter Google Maps embed code">{{ $settings['contact']['map_embed_code'] ?? '' }}</textarea>
                                <div class="form-text">Get embed code from Google Maps</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Contact Email</label>
                                        <input type="email" class="form-control" name="contact_email" 
                                               value="{{ $settings['contact']['contact_email'] ?? '' }}" 
                                               placeholder="contact@yourcompany.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Contact Phone</label>
                                        <input type="tel" class="form-control" name="contact_phone" 
                                               value="{{ $settings['contact']['contact_phone'] ?? '' }}" 
                                               placeholder="+1 (555) 123-4567">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Support Email</label>
                                        <input type="email" class="form-control" name="support_email" 
                                               value="{{ $settings['contact']['support_email'] ?? '' }}" 
                                               placeholder="support@yourcompany.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Emergency Contact</label>
                                        <input type="tel" class="form-control" name="emergency_contact" 
                                               value="{{ $settings['contact']['emergency_contact'] ?? '' }}" 
                                               placeholder="+1 (555) 911-HELP">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="save-section">
                            <button type="submit" class="btn btn-primary modern-btn">
                                <i class="bi bi-check-lg"></i> Save Contact Information
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- Branding Tab -->
<div class="tab-pane fade" id="branding" role="tabpanel">
    <div class="settings-content">
        <form id="brandingSettingsForm" onsubmit="saveSettingsGroup(event, 'branding')" enctype="multipart/form-data">
            <div class="settings-section">
                <div class="section-title">Business Logo and Favicon</div>
                <div class="section-description">Upload your brand assets</div>
                
                <!-- Business Logo -->
                <div class="form-group">
                    <label class="form-label">Business Logo (Dark)</label>
                    <div class="logo-preview-container">
                        @if(!empty($settings['branding']['business_logo']))
                            <div class="current-image">
                                <img src="{{ asset('public/assets/images/' . $settings['branding']['business_logo']) }}" 
                                     alt="Current Logo" 
                                     class="image-preview logo-preview-60">
                                <div class="mt-2 small text-muted">Current Dark Logo</div>
                            </div>
                        @else
                            <div class="no-image">No logo uploaded</div>
                        @endif
                    </div>
                    <div class="file-upload-area" onclick="document.getElementById('logoUpload').click()">
                        <div class="upload-icon">
                            <i class="bi bi-cloud-upload"></i>
                        </div>
                        <div class="upload-text">Click to upload dark logo</div>
                        <div class="upload-hint">PNG, JPG, GIF supported, max size 2 MB</div>
                    </div>
                    <input type="file" id="logoUpload" name="business_logo" accept="image/*" class="d-none">
                </div>

                <!-- White Logo (NEW) -->
                <div class="form-group">
                    <label class="form-label">Business Logo (White)</label>
                    <div class="logo-preview-container">
                        @if(!empty($settings['branding']['business_logo_white']))
                            <div class="current-image">
                                <div class="white-logo-preview">
                                    <img src="{{ asset('public/assets/images/' . $settings['branding']['business_logo_white']) }}" 
                                         alt="Current White Logo" 
                                         class="image-preview logo-preview-60">
                                </div>
                                <div class="mt-2 small text-muted">Current White Logo</div>
                            </div>
                        @else
                            <div class="no-image">No white logo uploaded</div>
                        @endif
                    </div>
                    <div class="file-upload-area" onclick="document.getElementById('logoWhiteUpload').click()">
                        <div class="upload-icon">
                            <i class="bi bi-cloud-upload"></i>
                        </div>
                        <div class="upload-text">Click to upload white logo</div>
                        <div class="upload-hint">PNG recommended for transparency, max size 2 MB</div>
                    </div>
                    <input type="file" id="logoWhiteUpload" name="business_logo_white" accept="image/*" class="d-none">
                </div>

                <!-- Favicon -->
                <div class="form-group">
                    <label class="form-label">Favicon</label>
                    <div class="logo-preview-container">
                        @if(!empty($settings['branding']['favicon']))
                            <div class="current-image">
                                <img src="{{ asset('public/assets/images/' . $settings['branding']['favicon']) }}" 
                                     alt="Current Favicon"
                                     class="favicon-preview-32">
                                <div class="mt-2 small text-muted">Current Favicon (32x32)</div>
                            </div>
                        @else
                            <div class="no-image">No favicon uploaded</div>
                        @endif
                    </div>
                    <div class="file-upload-area" onclick="document.getElementById('faviconUpload').click()">
                        <div class="upload-icon">
                            <i class="bi bi-cloud-upload"></i>
                        </div>
                        <div class="upload-text">Click to upload favicon</div>
                        <div class="upload-hint">PNG, ICO supported, max size 1 MB, recommended 32x32px</div>
                    </div>
                    <input type="file" id="faviconUpload" name="favicon" accept="image/*,.ico" class="d-none">
                </div>

            </div>

            <div class="save-section">
                <button type="submit" class="btn btn-primary modern-btn">
                    <i class="bi bi-check-lg"></i> Save Branding Assets
                </button>
            </div>
        </form>
    </div>
</div>

            <!-- Home Page Tab -->
            <div class="tab-pane fade" id="homepage" role="tabpanel">
                <div class="settings-content">
                    <form id="homepageSettingsForm" onsubmit="saveSettingsGroup(event, 'homepage')" enctype="multipart/form-data">
                        <div class="settings-section">
                            <div class="section-title">Homepage Cover</div>
                            <div class="section-description">Upload homepage hero/cover image</div>
                            
                            <div class="form-group">
                                <label class="form-label">Homepage Cover Image</label>
                                @if(!empty($settings['homepage']['cover_image']))
                                    <div class="current-image">
                                        <img src="{{ asset('public/assets/images/' .  $settings['homepage']['cover_image']) }}" alt="Current Cover" class="image-preview cover-preview-300">
                                        <div class="mt-2 small text-muted">Current Cover Image</div>
                                    </div>
                                @endif
                                <div class="file-upload-area" onclick="document.getElementById('coverUpload').click()">
                                    <div class="upload-icon">
                                        <i class="bi bi-cloud-upload"></i>
                                    </div>
                                    <div class="upload-text">Click to upload homepage cover</div>
                                    <div class="upload-hint">PNG, JPG supported, max size 5 MB<br>Recommended size: 1920x800 pixels</div>
                                </div>
                                <input type="file" id="coverUpload" name="cover_image" accept="image/*" class="d-none">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Cover Title</label>
                                <input type="text" class="form-control" name="cover_title" 
                                       value="{{ $settings['homepage']['cover_title'] ?? 'Find Your Perfect Flight' }}" 
                                       placeholder="Enter cover title">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Cover Subtitle</label>
                                <input type="text" class="form-control" name="cover_subtitle" 
                                       value="{{ $settings['homepage']['cover_subtitle'] ?? 'Compare prices and book flights from top airlines worldwide' }}" 
                                       placeholder="Enter cover subtitle">
                            </div>
                        </div>

                        <div class="save-section">
                            <button type="submit" class="btn btn-primary modern-btn">
                                <i class="bi bi-check-lg"></i> Save Homepage Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Social Links Tab -->
            <div class="tab-pane fade" id="social" role="tabpanel">
                <div class="settings-content">
                    <form id="socialSettingsForm" onsubmit="saveSettingsGroup(event, 'social')">
                        <div class="settings-section">
                            <div class="section-title">Social Links</div>
                            <div class="section-description">Social media pages links</div>
                            
                            <div class="social-input-group">
                                <div class="social-icon social-facebook">
                                    <i class="bi bi-facebook"></i>
                                </div>
                                <input type="url" class="form-control" name="facebook_url" 
                                       value="{{ $settings['social']['facebook_url'] ?? '' }}" 
                                       placeholder="https://facebook.com/yourpage">
                            </div>

                            <div class="social-input-group">
                                <div class="social-icon social-twitter">
                                    <i class="bi bi-twitter"></i>
                                </div>
                                <input type="url" class="form-control" name="twitter_url" 
                                       value="{{ $settings['social']['twitter_url'] ?? '' }}" 
                                       placeholder="https://twitter.com/youraccount">
                            </div>

                            <div class="social-input-group">
                                <div class="social-icon social-linkedin">
                                    <i class="bi bi-linkedin"></i>
                                </div>
                                <input type="url" class="form-control" name="linkedin_url" 
                                       value="{{ $settings['social']['linkedin_url'] ?? '' }}" 
                                       placeholder="https://linkedin.com/company/yourcompany">
                            </div>

                            <div class="social-input-group">
                                <div class="social-icon social-instagram">
                                    <i class="bi bi-instagram"></i>
                                </div>
                                <input type="url" class="form-control" name="instagram_url" 
                                       value="{{ $settings['social']['instagram_url'] ?? '' }}" 
                                       placeholder="https://instagram.com/youraccount">
                            </div>

                            <div class="social-input-group">
                                <div class="social-icon social-google">
                                    <i class="bi bi-google"></i>
                                </div>
                                <input type="url" class="form-control" name="google_business_url" 
                                       value="{{ $settings['social']['google_business_url'] ?? '' }}" 
                                       placeholder="https://business.google.com/yourprofile">
                            </div>

                            <div class="social-input-group">
                                <div class="social-icon social-youtube">
                                    <i class="bi bi-youtube"></i>
                                </div>
                                <input type="url" class="form-control" name="youtube_url" 
                                       value="{{ $settings['social']['youtube_url'] ?? '' }}" 
                                       placeholder="https://youtube.com/c/yourchannel">
                            </div>

                            <div class="social-input-group">
                                <div class="social-icon social-whatsapp">
                                    <i class="bi bi-whatsapp"></i>
                                </div>
                                <input type="tel" class="form-control" name="whatsapp_number" 
                                       value="{{ $settings['social']['whatsapp_number'] ?? '' }}" 
                                       placeholder="+1-555-123-4567">
                            </div>
                        </div>

                        <div class="save-section">
                            <button type="submit" class="btn btn-primary modern-btn">
                                <i class="bi bi-check-lg"></i> Save Social Links
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Save individual settings group
function saveSettingsGroup(event, group) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Add group to form data
    formData.append('group', group);
    
    fetch('{{ route("admin.settings.website.update") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification('Settings saved successfully!', 'success');
            
            // Reload the page
            location.reload();
        } else {
            showNotification('Error saving settings: ' + data.message, 'error');
            
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while saving settings', 'error');
    });
}

// Save all settings at once
function saveAllSettings() {
    const forms = document.querySelectorAll('[id$="SettingsForm"]');
    let completedForms = 0;
    let totalForms = forms.length;
    let hasErrors = false;
    
    forms.forEach(form => {
        const formData = new FormData(form);
        const group = form.id.replace('SettingsForm', '').toLowerCase();
        formData.append('group', group);
        
        fetch('{{ route("admin.settings.website.update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            completedForms++;
            if (!data.success) {
                hasErrors = true;
            }
            
            // Check if all forms are completed
            if (completedForms === totalForms) {
                if (hasErrors) {
                    showNotification('Some settings could not be saved. Please check individual tabs.', 'warning');
                } else {
                    showNotification('All settings saved successfully!', 'success');
                }
            }
        })
        .catch(error => {
            completedForms++;
            hasErrors = true;
            console.error('Error:', error);
            
            if (completedForms === totalForms) {
                showNotification('Error occurred while saving some settings', 'error');
            }
        });
    });
}

// Show notification
function showNotification(message, type = 'success') {
    // You can customize this based on your notification system
    alert(message);
}

// File upload preview
function setupFilePreview(inputId, previewSelector) {
    document.getElementById(inputId).addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.querySelector(previewSelector);
                if (preview) {
                    preview.src = e.target.result;
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

// Setup file previews when page loads
document.addEventListener('DOMContentLoaded', function() {
    setupFilePreview('logoUpload', '.current-image img');
    setupFilePreview('faviconUpload', '.current-image img');
    setupFilePreview('coverUpload', '.current-image img');
});
</script>

@endsection

