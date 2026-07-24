{{-- Global floating theme switcher — available on every admin page --}}
@php
    $__qsPresets = app(\App\Services\ThemeService::class)->presets();
@endphp
<button type="button" id="themeQsToggle" class="theme-qs-toggle" title="Theme Settings" aria-label="Open theme switcher">
    <i class="bi bi-gear-fill"></i>
</button>

<div id="themeQsPanel" class="theme-qs-panel">
    <div class="theme-qs-header">
        <h6><i class="bi bi-palette"></i> Quick Theme Switch</h6>
        <button type="button" id="themeQsClose" class="theme-qs-close" aria-label="Close">&times;</button>
    </div>
    <div class="theme-qs-body">
        <div class="theme-qs-grid">
            @foreach($__qsPresets as $key => $preset)
                <div class="theme-qs-card {{ $__activeTheme->theme_name === $key ? 'active' : '' }}"
                     data-quick-preset="{{ $key }}"
                     data-values="{{ json_encode($preset['values']) }}"
                     data-dark="{{ !empty($preset['dark']) ? '1' : '0' }}"
                     title="{{ $preset['label'] }}">
                    <div class="theme-qs-check"><i class="bi bi-check-circle-fill"></i></div>
                    <div class="theme-qs-preview">
                        @foreach($preset['preview'] as $swatch)
                            <span style="background:{{ $swatch }}"></span>
                        @endforeach
                    </div>
                    <div class="theme-qs-label">{{ $preset['label'] }}</div>
                </div>
            @endforeach
        </div>
        <a href="{{ route('admin.settings.theme') }}" class="theme-qs-full-link">
            <i class="bi bi-sliders"></i> Full Theme Settings
        </a>
    </div>
</div>

<script id="theme-qs-state" type="application/json">{!! json_encode([
    'theme_name' => $__activeTheme->theme_name,
    'font_family' => $__activeTheme->font_family,
    'font_size' => $__activeTheme->font_size,
    'border_radius' => $__activeTheme->border_radius,
    'layout_options' => array_merge(\App\Models\ThemeSetting::defaultLayoutOptions(), $__activeTheme->layout_options ?? []),
    'colors' => $__activeTheme->only([
        'primary_color', 'secondary_color', 'success_color', 'warning_color', 'danger_color', 'info_color',
        'body_background', 'sidebar_background', 'navbar_background', 'card_background', 'text_color',
        'border_color', 'input_background', 'input_border', 'input_focus_color',
    ]),
    'routes' => [
        'update' => route('admin.settings.theme.update'),
    ],
]) !!}</script>

@once
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
@endonce
<script src="{{ asset('public/assets/js/theme-quick-switch.js') }}"></script>
