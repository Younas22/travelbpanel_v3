@extends('agent-modern.layouts.app')
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

    <div class="ap-page-header">
        <div>
            <h4 class="ap-page-title"><i class="bi bi-palette"></i> Theme &amp; Appearance</h4>
            <p class="ap-page-sub">Customize colors, typography, shape and layout for your own view of the panel — changes preview instantly and only affect your account.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="ap-btn-outline" id="themeCancelBtn">
                <i class="bi bi-x-lg"></i> Cancel
            </button>
            <button type="button" class="ap-btn-danger" id="themeResetBtn">
                <i class="bi bi-arrow-counterclockwise"></i> Reset to Default
            </button>
            <button type="button" class="ap-btn-primary" id="themeSaveBtn">
                <i class="bi bi-check-lg"></i> Save Settings
            </button>
        </div>
    </div>

    <form id="themeSettingsForm">
        @csrf
        <input type="hidden" name="preset" id="selectedPreset" value="{{ $theme->theme_name }}">
        <input type="hidden" name="design_style" id="selectedDesignStyle" value="{{ $theme->design_style }}">

        {{-- Design Style --}}
        <div class="am-card mb-4">
            <div class="am-card-body">
                <div class="ap-page-title" style="font-size: 14px; display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <i class="bi bi-layout-text-window-reverse"></i> Design Style
                </div>
                <p class="form-text mb-3">Choose the overall visual style for your panel. Your colors, typography, radius and layout options below apply to either one.</p>

                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <div class="theme-card {{ $theme->design_style === 'classic' ? 'active' : '' }}" data-design-card data-design="classic">
                            <div class="theme-card-check"><i class="bi bi-check-circle-fill"></i></div>
                            <div class="theme-card-preview">
                                <span style="background:#0C6DFD"></span>
                                <span style="background:#F8FAFC"></span>
                                <span style="background:#FFFFFF"></span>
                            </div>
                            <div class="theme-card-body">
                                <div class="theme-card-icon"><i class="bi bi-square"></i></div>
                                <div class="theme-card-label">Classic</div>
                                <div class="theme-card-desc">The original agent panel design.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="theme-card {{ $theme->design_style === 'modern' ? 'active' : '' }}" data-design-card data-design="modern">
                            <div class="theme-card-check"><i class="bi bi-check-circle-fill"></i></div>
                            <div class="theme-card-preview">
                                <span style="background:#0C6DFD"></span>
                                <span style="background:#f7f8fa"></span>
                                <span style="background:#FFFFFF"></span>
                            </div>
                            <div class="theme-card-body">
                                <div class="theme-card-icon"><i class="bi bi-stars"></i></div>
                                <div class="theme-card-label">Modern</div>
                                <div class="theme-card-desc">A premium SaaS-style redesign.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Theme Style --}}
        <div class="am-card mb-4">
            <div class="am-card-body">
                <div class="ap-page-title" style="font-size: 14px; display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <i class="bi bi-grid-3x3-gap"></i> Theme Style
                </div>
                <p class="form-text mb-3">Pick a starting point. You can still fine-tune every color, font and layout option afterward.</p>

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
        </div>

        {{-- Primary Color --}}
        <div class="am-card mb-4">
            <div class="am-card-body">
                <div class="ap-page-title" style="font-size: 14px; display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <i class="bi bi-droplet-fill"></i> Primary Color
                </div>
                <p class="form-text mb-3">Drives buttons, links, the active sidebar item, badges, switches, pagination, tabs and focus states across your panel.</p>

                <div class="color-swatch-row" data-preset-swap>
                    @foreach($primaryNamedColors as $name => $hex)
                        <button type="button" class="color-swatch-btn" data-color="{{ $hex }}" title="{{ $name }}" style="background:{{ $hex }}"></button>
                    @endforeach
                </div>

                <div class="row align-items-end mt-3 g-3">
                    <div class="col-auto">
                        <label class="form-label d-block">Custom</label>
                        <input type="color" class="form-control form-control-color" id="primary_color_picker" value="{{ $theme->primary_color }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hex / RGBA Value</label>
                        <input type="text" class="form-control theme-var-input" id="primary_color" name="primary_color" data-css-var="--primary-color" value="{{ $theme->primary_color }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Secondary Colors --}}
        <div class="am-card mb-4">
            <div class="am-card-body">
                <div class="ap-page-title" style="font-size: 14px; display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <i class="bi bi-palette2"></i> Secondary Colors
                </div>
                <p class="form-text mb-3">Fine-grained surface, text and status colors — every field maps to a CSS variable, nothing is hardcoded.</p>

                <div class="row g-3">
                    @foreach($secondaryColorFields as $field)
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label">{{ $field['label'] }}</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="color" class="color-field-swatch" data-paired="{{ $field['name'] }}" value="{{ str_starts_with($field['value'], '#') ? $field['value'] : '#ffffff' }}">
                                <input type="text" class="form-control theme-var-input" id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ $field['value'] }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Typography --}}
        <div class="am-card mb-4">
            <div class="am-card-body">
                <div class="ap-page-title" style="font-size: 14px; display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <i class="bi bi-fonts"></i> Typography
                </div>
                <p class="form-text mb-3">Applies across your panel via <code>--font-family</code> and <code>--font-size-base</code>.</p>

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
        </div>

        {{-- Border Radius --}}
        <div class="am-card mb-4">
            <div class="am-card-body">
                <div class="ap-page-title" style="font-size: 14px; display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <i class="bi bi-bezier2"></i> Border Radius
                </div>
                <p class="form-text mb-3">Affects buttons, cards, inputs, tables, dropdowns, modals and badges.</p>

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
        </div>

        {{-- Layout Options --}}
        <div class="am-card mb-4">
            <div class="am-card-body">
                <div class="ap-page-title" style="font-size: 14px; display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <i class="bi bi-layout-sidebar"></i> Layout Options
                </div>
                <p class="form-text mb-3">Structural toggles for your panel shell — applied as body classes with no page reload.</p>

                <div class="row g-3">
                    @foreach($layoutToggles as $toggle)
                        <div class="col-md-6 col-lg-4">
                            <div class="layout-toggle-row">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="layout_options[{{ $toggle['key'] }}]" value="1" data-layout-key="{{ $toggle['key'] }}" {{ !empty($layoutOptions[$toggle['key']]) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <div>
                                    <div style="font-size: 13px; font-weight: 650;">{{ $toggle['label'] }}</div>
                                    <div style="font-size: 11px; color: color-mix(in srgb, var(--text-color) 50%, transparent);">{{ $toggle['desc'] }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="am-card">
            <div class="am-card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h6 class="mb-1">Theme Configuration</h6>
                    <small class="form-text">This is your personal preference — it only changes how the panel looks for you. Reset restores factory defaults.</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="ap-btn-danger" id="themeResetBtnBottom">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                    <button type="button" class="ap-btn-primary" id="themeSaveBtnBottom">
                        <i class="bi bi-check-lg"></i> Save Settings
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script id="theme-initial-state" type="application/json">{!! json_encode([
        'theme' => $theme->only([
            'theme_name', 'design_style', 'primary_color', 'secondary_color', 'success_color', 'warning_color',
            'danger_color', 'info_color', 'body_background', 'sidebar_background', 'navbar_background',
            'card_background', 'text_color', 'border_color', 'input_background', 'input_border',
            'input_focus_color', 'font_family', 'font_size', 'border_radius',
        ]),
        'design_stylesheets' => [
            'classic' => asset('public/assets/css/style.css'),
            'modern' => asset('public/assets/css/agent-modern.css') . '?v=' . (@filemtime(public_path('assets/css/agent-modern.css')) ?: 1),
        ],
        'layout_options' => $layoutOptions,
        'css_vars' => $theme->toCssVariables(),
        'routes' => [
            'update' => route('agent.settings.theme.update'),
            'reset' => route('agent.settings.theme.reset'),
        ],
    ]) !!}</script>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="{{ asset('public/assets/js/admin-theme.js') }}?v={{ @filemtime(public_path('assets/js/admin-theme.js')) }}"></script>
@endpush
