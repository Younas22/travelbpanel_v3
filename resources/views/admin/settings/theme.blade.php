@extends('admin.layouts.app')

@section('title', 'Theme Settings')

@php
    $secondaryColorFields = [
        ['name' => 'text_color', 'label' => 'Text Color', 'value' => $theme->text_color],
        ['name' => 'secondary_color', 'label' => 'Secondary Color', 'value' => $theme->secondary_color],
        ['name' => 'body_background', 'label' => 'Body Background', 'value' => $theme->body_background],
        ['name' => 'card_background', 'label' => 'Card Background', 'value' => $theme->card_background],
        ['name' => 'sidebar_background', 'label' => 'Sidebar Background', 'value' => $theme->sidebar_background],
        ['name' => 'navbar_background', 'label' => 'Navbar Background', 'value' => $theme->navbar_background],
        ['name' => 'border_color', 'label' => 'Border Color', 'value' => $theme->border_color],
        ['name' => 'input_background', 'label' => 'Input Background', 'value' => $theme->input_background],
        ['name' => 'input_border', 'label' => 'Input Border', 'value' => $theme->input_border],
        ['name' => 'input_focus_color', 'label' => 'Input Focus Color', 'value' => $theme->input_focus_color],
        ['name' => 'success_color', 'label' => 'Success Color', 'value' => $theme->success_color],
        ['name' => 'warning_color', 'label' => 'Warning Color', 'value' => $theme->warning_color],
        ['name' => 'danger_color', 'label' => 'Danger Color', 'value' => $theme->danger_color],
        ['name' => 'info_color', 'label' => 'Info Color', 'value' => $theme->info_color],
    ];

    $primaryNamedColors = [
        'Blue' => '#0C6DFD', 'Green' => '#10B981', 'Red' => '#EF4444', 'Orange' => '#F59E0B',
        'Purple' => '#8B5CF6', 'Pink' => '#EC4899', 'Teal' => '#14B8A6', 'Cyan' => '#06B6D4',
        'Indigo' => '#6366F1', 'Yellow' => '#EAB308',
    ];

    $fontFamilies = ['Inter', 'Poppins', 'Roboto', 'Open Sans', 'Nunito', 'System Font'];

    $radiusOptions = [
        'small' => ['label' => 'Small', 'preview' => '2px'],
        'medium' => ['label' => 'Medium', 'preview' => '8px'],
        'large' => ['label' => 'Large', 'preview' => '14px'],
        'xl' => ['label' => 'Extra Large', 'preview' => '22px'],
    ];

    $layoutToggles = [
        ['key' => 'sidebar_fixed', 'label' => 'Sidebar Fixed', 'desc' => 'Keep the sidebar pinned while scrolling'],
        ['key' => 'navbar_fixed', 'label' => 'Navbar Fixed', 'desc' => 'Keep the top navbar pinned while scrolling'],
        ['key' => 'box_shadow', 'label' => 'Box Shadow', 'desc' => 'Soft shadows on cards and panels'],
        ['key' => 'rounded_cards', 'label' => 'Rounded Cards', 'desc' => 'Apply the border radius to cards'],
        ['key' => 'rounded_inputs', 'label' => 'Rounded Inputs', 'desc' => 'Apply the border radius to form inputs'],
        ['key' => 'rounded_buttons', 'label' => 'Rounded Buttons', 'desc' => 'Apply the border radius to buttons'],
        ['key' => 'compact_mode', 'label' => 'Compact Mode', 'desc' => 'Reduce spacing for a denser layout'],
        ['key' => 'wide_layout', 'label' => 'Wide Layout', 'desc' => 'Widen the main content container'],
        ['key' => 'fluid_layout', 'label' => 'Fluid Layout', 'desc' => 'Stretch content to fill the viewport'],
        ['key' => 'animations', 'label' => 'Animations', 'desc' => 'Enable hover / transition animations'],
    ];

    $layoutOptions = array_merge(\App\Models\ThemeSetting::defaultLayoutOptions(), $theme->layout_options ?? []);
@endphp

@section('content')
<div class="content-area p-4">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h2 class="mb-1"><i class="bi bi-palette"></i> Theme &amp; Appearance</h2>
                <p class="text-muted mb-0">Customize colors, typography, shape and layout for the entire admin panel — changes preview instantly.</p>
            </div>
            <div class="col-md-5">
                <div class="text-end d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary modern-btn" id="themeCancelBtn">
                        <i class="bi bi-x-lg"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-outline-danger modern-btn" id="themeResetBtn">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset to Default
                    </button>
                    <button type="button" class="btn btn-primary modern-btn" id="themeSaveBtn">
                        <i class="bi bi-check-lg"></i> Save Settings
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="theme-settings-container p-4">
        <form id="themeSettingsForm">
            @csrf
            <input type="hidden" name="preset" id="selectedPreset" value="{{ $theme->theme_name }}">
            <input type="hidden" name="design_style" id="selectedDesignStyle" value="{{ $theme->design_style }}">

            {{-- Design Style --}}
            <div class="settings-section">
                <div class="section-title">
                    <div class="section-icon"><i class="bi bi-layout-text-window-reverse"></i></div>
                    Design Style
                </div>
                <div class="section-description">Choose the overall visual design for the admin panel. Your colors, typography, radius and layout options below apply to either one.</div>

                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <div class="theme-card {{ $theme->design_style === 'classic' ? 'active' : '' }}"
                             data-design-card data-design="classic">
                            <div class="theme-card-check"><i class="bi bi-check-circle-fill"></i></div>
                            <div class="theme-card-preview">
                                <span style="background:#0C6DFD"></span>
                                <span style="background:#F8FAFC"></span>
                                <span style="background:#FFFFFF"></span>
                            </div>
                            <div class="theme-card-body">
                                <div class="theme-card-icon"><i class="bi bi-square"></i></div>
                                <div class="theme-card-label">Classic</div>
                                <div class="theme-card-desc">The original admin design.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="theme-card {{ $theme->design_style === 'modern' ? 'active' : '' }}"
                             data-design-card data-design="modern">
                            <div class="theme-card-check"><i class="bi bi-check-circle-fill"></i></div>
                            <div class="theme-card-preview">
                                <span style="background:#0C6DFD"></span>
                                <span style="background:#f7f8fa"></span>
                                <span style="background:#FFFFFF"></span>
                            </div>
                            <div class="theme-card-body">
                                <div class="theme-card-icon"><i class="bi bi-stars"></i></div>
                                <div class="theme-card-label">Modern</div>
                                <div class="theme-card-desc">A softer, premium SaaS-style redesign.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="theme-card {{ $theme->design_style === 'nova' ? 'active' : '' }}"
                             data-design-card data-design="nova">
                            <div class="theme-card-check"><i class="bi bi-check-circle-fill"></i></div>
                            <div class="theme-card-preview">
                                <span style="background:#0f172a"></span>
                                <span style="background:#2563eb"></span>
                                <span style="background:#f8fafc"></span>
                            </div>
                            <div class="theme-card-body">
                                <div class="theme-card-icon"><i class="bi bi-lightning-charge"></i></div>
                                <div class="theme-card-label">Nova</div>
                                <div class="theme-card-desc">Ultra-modern 2026 SaaS dashboard — currently Dashboard only, other pages stay Classic.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Theme Style --}}
            <div class="settings-section">
                <div class="section-title">
                    <div class="section-icon"><i class="bi bi-grid-3x3-gap"></i></div>
                    Theme Style
                </div>
                <div class="section-description">Pick a starting point. You can still fine-tune every color, font and layout option afterward.</div>

                <div class="row g-3">
                    @foreach($presets as $key => $preset)
                        <div class="col-6 col-md-4">
                            <div class="theme-card {{ $theme->theme_name === $key ? 'active' : '' }}"
                                 data-preset-card
                                 data-preset="{{ $key }}"
                                 data-values="{{ json_encode($preset['values']) }}"
                                 data-dark="{{ !empty($preset['dark']) ? '1' : '0' }}">
                                <div class="theme-card-check"><i class="bi bi-check-circle-fill"></i></div>
                                <div class="theme-card-preview">
                                    @foreach($preset['preview'] as $swatch)
                                        <span style="background:{{ $swatch }}"></span>
                                    @endforeach
                                </div>
                                <div class="theme-card-body">
                                    <div class="theme-card-icon"><i class="bi {{ $preset['icon'] }}"></i></div>
                                    <div class="theme-card-label">{{ $preset['label'] }}</div>
                                    <div class="theme-card-desc">{{ $preset['description'] }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Primary Color --}}
            <div class="settings-section">
                <div class="section-title">
                    <div class="section-icon"><i class="bi bi-droplet-fill"></i></div>
                    Primary Color
                </div>
                <div class="section-description">Drives buttons, links, the active sidebar item, navbar highlights, badges, switches, pagination, tabs, progress bars and focus states across the panel.</div>

                <div class="color-swatch-row" data-preset-swap>
                    @foreach($primaryNamedColors as $name => $hex)
                        <button type="button" class="color-swatch-btn" data-color="{{ $hex }}" title="{{ $name }}" style="background:{{ $hex }}"></button>
                    @endforeach
                </div>

                <div class="row align-items-end mt-3">
                    <div class="col-auto">
                        <label class="form-label d-block">Custom</label>
                        <input type="color" class="form-control form-control-color" id="primary_color_picker" value="{{ $theme->primary_color }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hex / RGBA Value</label>
                        <input type="text" class="form-control theme-var-input" id="primary_color" name="primary_color" data-css-var="--primary-color" data-bs-var="--bs-primary" value="{{ $theme->primary_color }}">
                    </div>
                </div>
            </div>

            {{-- Secondary Colors --}}
            <div class="settings-section">
                <div class="section-title">
                    <div class="section-icon"><i class="bi bi-palette2"></i></div>
                    Secondary Colors
                </div>
                <div class="section-description">Fine-grained surface, text and status colors — every field maps to a CSS variable, nothing is hardcoded.</div>

                <div class="row g-3">
                    @foreach($secondaryColorFields as $field)
                        <div class="col-md-3 col-sm-6">
                            <div class="color-field">
                                <label class="form-label">{{ $field['label'] }}</label>
                                <div class="color-field-row">
                                    <input type="color" class="color-field-swatch" data-paired="{{ $field['name'] }}" value="{{ str_starts_with($field['value'], '#') ? $field['value'] : '#ffffff' }}">
                                    <input type="text" class="form-control theme-var-input" id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ $field['value'] }}">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Typography --}}
            <div class="settings-section">
                <div class="section-title">
                    <div class="section-icon"><i class="bi bi-fonts"></i></div>
                    Typography
                </div>
                <div class="section-description">Applies to the entire admin panel via <code>--font-family</code> and <code>--font-size-base</code>.</div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Font Family</label>
                        <select class="form-select theme-var-input" id="font_family" name="font_family" data-css-var="--font-family-raw">
                            @foreach($fontFamilies as $font)
                                <option value="{{ $font }}" {{ $theme->font_family === $font ? 'selected' : '' }}>{{ $font }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Base Font Size</label>
                        <select class="form-select theme-var-input" id="font_size" name="font_size" data-css-var="--font-size-base">
                            @foreach(['12px', '13px', '14px', '15px', '16px', '18px'] as $size)
                                <option value="{{ $size }}" {{ $theme->font_size === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Border Radius --}}
            <div class="settings-section">
                <div class="section-title">
                    <div class="section-icon"><i class="bi bi-bezier2"></i></div>
                    Border Radius
                </div>
                <div class="section-description">Affects buttons, cards, inputs, tables, dropdowns, modals, badges and alerts.</div>

                <div class="row g-3">
                    @foreach($radiusOptions as $key => $opt)
                        <div class="col-6 col-md-3">
                            <label class="radius-option {{ $theme->border_radius === $key ? 'active' : '' }}">
                                <input type="radio" name="border_radius" value="{{ $key }}" {{ $theme->border_radius === $key ? 'checked' : '' }}>
                                <span class="radius-preview" style="border-radius:{{ $opt['preview'] }}"></span>
                                <span class="radius-label">{{ $opt['label'] }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Layout Options --}}
            <div class="settings-section">
                <div class="section-title">
                    <div class="section-icon"><i class="bi bi-layout-sidebar"></i></div>
                    Layout Options
                </div>
                <div class="section-description">Structural toggles for the admin shell — applied as body classes with no page reload.</div>

                <div class="row g-3">
                    @foreach($layoutToggles as $toggle)
                        <div class="col-md-6 col-lg-4">
                            <div class="layout-toggle-row">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="layout_options[{{ $toggle['key'] }}]" value="1" data-layout-key="{{ $toggle['key'] }}" {{ !empty($layoutOptions[$toggle['key']]) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <div>
                                    <div class="layout-toggle-label">{{ $toggle['label'] }}</div>
                                    <div class="layout-toggle-desc">{{ $toggle['desc'] }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="save-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="text-start">
                            <h6 class="mb-1">Theme Configuration</h6>
                            <small class="text-muted">Save to apply these settings for every admin, on every login. Reset restores factory defaults.</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-end d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-outline-danger modern-btn" id="themeResetBtnBottom">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                            </button>
                            <button type="button" class="btn btn-primary modern-btn" id="themeSaveBtnBottom">
                                <i class="bi bi-check-lg"></i> Save Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script id="theme-initial-state" type="application/json">{!! json_encode([
    'theme' => $theme->only([
        'theme_name', 'design_style', 'primary_color', 'secondary_color', 'success_color', 'warning_color',
        'danger_color', 'info_color', 'body_background', 'sidebar_background', 'navbar_background',
        'card_background', 'text_color', 'border_color', 'input_background', 'input_border',
        'input_focus_color', 'font_family', 'font_size', 'border_radius',
    ]),
    'design_stylesheets' => [
        'classic' => asset('public/assets/css/admin.css') . '?v=' . (@filemtime(public_path('assets/css/admin.css')) ?: 1),
        'modern' => asset('public/assets/css/admin-modern.css') . '?v=' . (@filemtime(public_path('assets/css/admin-modern.css')) ?: 1),
        // Nova has no dedicated stylesheet yet — it borrows admin-modern.css
        // for its shared chrome (sidebar/header) while its own pages use
        // Tailwind loaded per-page. See NovaAdminViewFinder.
        'nova' => asset('public/assets/css/admin-modern.css') . '?v=' . (@filemtime(public_path('assets/css/admin-modern.css')) ?: 1),
    ],
    'layout_options' => $layoutOptions,
    'css_vars' => $theme->toCssVariables(),
    'routes' => [
        'update' => route('admin.settings.theme.update'),
        'reset' => route('admin.settings.theme.reset'),
    ],
]) !!}</script>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="{{ asset('public/assets/js/admin-theme.js') }}?v={{ @filemtime(public_path('assets/js/admin-theme.js')) }}"></script>
@endpush
