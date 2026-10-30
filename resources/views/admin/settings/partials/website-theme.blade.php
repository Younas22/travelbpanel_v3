{{-- Website tab of Settings > Theme: the public site's colors, fonts, weights,
     sizes, navbar, search tabs and dropdowns (App\Support\WebsiteTheme).
     Shared by the Classic, Modern and Nova pages. Settings sit in one card with
     small tabs on the left; the live preview stays in view on the right. --}}
@php
    [$site, $siteLinked] = \App\Support\WebsiteTheme::resolved(\App\Support\WebsiteTheme::current());
    $groups = \App\Support\WebsiteTheme::groups();
    $wtActive = 'colors'; // the JS reopens the last used section

    // A preset's full set of values, with its follow-the-theme colors worked out.
    $presetValues = fn ($p) => \App\Support\WebsiteTheme::resolved(array_merge(\App\Support\WebsiteTheme::defaults(), $p['values']))[0];

    $mainColors = [
        ['primary_color', 'Primary', 'Buttons, links, highlights', null],
        ['primary_hover', 'Hover', 'Buttons and links on mouse-over', 'primary-dark'],
        ['bg_color', 'Background', 'Page, cards, panels', null],
        ['text_color', 'Text', 'Headings and text', null],
        ['icon_color', 'Icons', 'Icons across the site', 'primary'],
    ];
    $sections = [
        'colors' => ['Colors', 'bi-palette'],
        'type' => ['Fonts & Sizes', 'bi-fonts'],
        'navbar' => ['Navbar', 'bi-window'],
        'tabs' => ['Search Tabs', 'bi-segmented-nav'],
        'dropdowns' => ['Dropdowns', 'bi-menu-down'],
    ];
@endphp

{{-- size / weight / corners row of one element group --}}
@php
    $groupControls = function ($group) use ($groups, $site) {
        $g = $groups[$group];
        $out = '<div class="wt-controls">';
        $out .= '<label>Text size<select class="form-select form-select-sm" name="' . $group . '_size" data-wt-var="--wt-' . $g['css'] . '-size" data-wt-kind="px" data-wt-default="' . $g['size'] . '">';
        foreach (\App\Support\WebsiteTheme::pxSizes($g['size']) as $v => $l) {
            $out .= '<option value="' . $v . '"' . ((string) $site["{$group}_size"] === (string) $v ? ' selected' : '') . '>' . e($l) . '</option>';
        }
        $out .= '</select></label>';
        $out .= '<label>Weight<select class="form-select form-select-sm" name="' . $group . '_weight" data-wt-var="--wt-' . $g['css'] . '-weight" data-wt-kind="weight">';
        foreach (\App\Support\WebsiteTheme::weights() as $v => $l) {
            $out .= '<option value="' . $v . '"' . ((string) $site["{$group}_weight"] === (string) $v ? ' selected' : '') . '>' . e($l) . '</option>';
        }
        $out .= '</select></label>';
        if ($g['radius']) {
            $out .= '<label>Corners<select class="form-select form-select-sm" name="' . $group . '_radius" data-wt-var="--wt-' . $g['css'] . '-radius" data-wt-kind="radius">';
            foreach (\App\Support\WebsiteTheme::radii() as $v => $l) {
                $out .= '<option value="' . $v . '"' . ((string) $site["{$group}_radius"] === (string) $v ? ' selected' : '') . '>' . e($l) . '</option>';
            }
            $out .= '</select></label>';
        }

        return $out . '</div>';
    };
@endphp

<div data-theme-pane="website" @if(request('tab') !== 'website') hidden @endif>
    <form method="POST" action="{{ route('admin.settings.theme.website.update') }}" id="websiteThemeForm" class="wt">
        @csrf
        <input type="hidden" name="preset" id="wtPreset" value="{{ $site['preset'] }}">

        {{-- Title + actions --}}
        <div class="wt-head">
            <div>
                <h2><i class="bi bi-globe2"></i> Website Theme</h2>
                <p>Colors, fonts and sizes of the public website. The preview updates as you change things; Save applies them to the live site.</p>
            </div>
            <div class="wt-actions">
                <button type="submit" name="reset" value="1" class="btn btn-sm btn-outline-danger modern-btn" onclick="return confirm('Reset the website theme to the default blue theme?')">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
                <button type="submit" class="btn btn-sm btn-primary modern-btn"><i class="bi bi-check-lg"></i> Save</button>
            </div>
        </div>

        @if(session('success') && request('tab') === 'website')
            <div class="alert alert-success py-2">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
        @endif

        {{-- Ready-made themes --}}
        <div class="wt-presets">
            <span class="wt-presets-label">Start from a theme</span>
            @foreach(\App\Support\WebsiteTheme::presets() as $key => $preset)
                @php $v = $presetValues($preset); @endphp
                <button type="button" class="wt-preset {{ $site['preset'] === $key ? 'active' : '' }}" data-wt-preset="{{ $key }}" data-values='@json($v)'>
                    <span class="wt-preset-dots">
                        <span style="background:{{ $v['primary_color'] }}"></span>
                        <span style="background:{{ $v['text_color'] }}"></span>
                        <span style="background:{{ $v['icon_color'] }}"></span>
                    </span>
                    <span class="wt-preset-text"><b>{{ $preset['label'] }}</b><small>{{ $v['font'] }}</small></span>
                </button>
            @endforeach
        </div>

        <div class="wt-layout">
            {{-- Settings --}}
            <div class="wt-card">
                <nav class="wt-nav" role="tablist">
                    @foreach($sections as $key => [$label, $icon])
                        <button type="button" class="wt-nav-btn {{ $wtActive === $key ? 'active' : '' }}" data-wt-section="{{ $key }}" role="tab">
                            <i class="bi {{ $icon }}"></i><span>{{ $label }}</span>
                        </button>
                    @endforeach
                </nav>

                <div class="wt-panes">
                    {{-- Colors --}}
                    <section class="wt-pane" data-wt-pane="colors" @if($wtActive !== 'colors') hidden @endif>
                        <h3>Main colors</h3>
                        <p class="wt-help">The site is built from these. Hover and Icons follow Primary until you change them.</p>
                        <div class="wt-grid">
                            @foreach($mainColors as [$name, $label, $hint, $source])
                                @include('admin.settings.partials.wt-color', ['name' => $name, 'label' => $label, 'hint' => $hint, 'value' => $site[$name], 'source' => $source, 'linked' => $source === 'primary' ? strcasecmp($site[$name], $site['primary_color']) === 0 : false])
                            @endforeach
                        </div>
                    </section>

                    {{-- Fonts & sizes --}}
                    <section class="wt-pane" data-wt-pane="type" @if($wtActive !== 'type') hidden @endif>
                        <h3>Fonts</h3>
                        <div class="wt-controls wt-controls-half">
                            <label>Website font
                                <select class="form-select form-select-sm" name="font">
                                    @foreach(\App\Support\WebsiteTheme::fonts() as $font)
                                        <option value="{{ $font }}" style="font-family:'{{ $font }}'" @selected($site['font'] === $font)>{{ $font }}{{ $font === 'Poppins' ? ' (default)' : '' }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>Arabic font
                                <select class="form-select form-select-sm" name="font_arabic">
                                    @foreach(\App\Support\WebsiteTheme::arabicFonts() as $font)
                                        <option value="{{ $font }}" style="font-family:'{{ $font }}'" @selected($site['font_arabic'] === $font)>{{ $font }}{{ $font === 'IBM Plex Sans Arabic' ? ' (default)' : '' }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <h3>Boldness</h3>
                        <div class="wt-controls">
                            @foreach(['font_weight' => 'Text', 'heading_weight' => 'Headings', 'button_weight' => 'Buttons'] as $name => $label)
                                <label>{{ $label }}
                                    <select class="form-select form-select-sm" name="{{ $name }}">
                                        @foreach(\App\Support\WebsiteTheme::weights() as $val => $l)
                                            <option value="{{ $val }}" @selected((string) $site[$name] === (string) $val)>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            @endforeach
                        </div>

                        <h3>Sizes <small>(% of each element's own size)</small></h3>
                        <div class="wt-controls">
                            @foreach(['heading_scale' => 'Headings', 'button_scale' => 'Buttons', 'icon_scale' => 'Icons'] as $name => $label)
                                <label>{{ $label }}
                                    <select class="form-select form-select-sm" name="{{ $name }}">
                                        @foreach(\App\Support\WebsiteTheme::scales() as $val => $l)
                                            <option value="{{ $val }}" @selected((string) $site[$name] === (string) $val)>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            @endforeach
                        </div>
                    </section>

                    {{-- Navbar: links + buttons --}}
                    <section class="wt-pane" data-wt-pane="navbar" @if($wtActive !== 'navbar') hidden @endif>
                        @foreach(['navlink' => ['Links', 'Our Services, About, Blog, Support'], 'navbtn' => ['Buttons', 'Sign In (outline) and Sign Up (solid)']] as $group => [$title, $help])
                            <h3>{{ $title }} <small>{{ $help }}</small></h3>
                            <div class="wt-grid">
                                @foreach($groups[$group]['colors'] as $name => [$suffix, $label, $hint, $source])
                                    @include('admin.settings.partials.wt-color', ['name' => $name, 'label' => $label, 'hint' => $hint, 'value' => $site[$name], 'source' => $source, 'linked' => $siteLinked[$name], 'cssVar' => '--wt-' . $groups[$group]['css'] . '-' . $suffix])
                                @endforeach
                            </div>
                            {!! $groupControls($group) !!}
                        @endforeach
                    </section>

                    {{-- Search tabs / dropdowns --}}
                    @foreach(['tabs' => ['tab', 'Stay / Flight / Tours / Umrah / Visa tabs above the homepage search form'], 'dropdowns' => ['dropdown', 'Our Services, account and sign up menus in the navbar']] as $pane => [$group, $help])
                        <section class="wt-pane" data-wt-pane="{{ $pane }}" @if($wtActive !== $pane) hidden @endif>
                            <h3>{{ $sections[$pane][0] }} <small>{{ $help }}</small></h3>
                            <div class="wt-grid">
                                @foreach($groups[$group]['colors'] as $name => [$suffix, $label, $hint, $source])
                                    @include('admin.settings.partials.wt-color', ['name' => $name, 'label' => $label, 'hint' => $hint, 'value' => $site[$name], 'source' => $source, 'linked' => $siteLinked[$name], 'cssVar' => '--wt-' . $groups[$group]['css'] . '-' . $suffix])
                                @endforeach
                            </div>
                            {!! $groupControls($group) !!}
                        </section>
                    @endforeach
                </div>
            </div>

            {{-- Live preview (mirrors the real site's classes in style.css) --}}
            <aside class="wt-preview-wrap">
                <div class="wt-preview-title"><i class="bi bi-eye"></i> Live preview</div>
                <div class="wt-preview" id="wtPreview">
                    <div class="wt-navlinks">
                        <span class="wt-nl is-on">Our Services</span>
                        <span class="wt-nl is-hover">About</span>
                        <span class="wt-nb wt-nb-out">Sign In</span>
                        <span class="wt-nb wt-nb-solid">Sign Up</span>
                    </div>
                    <h3>Find Your <span class="wt-accent">Perfect Hotel</span></h3>
                    <p>Search hundreds of hotels and book at the best price.</p>
                    <div class="wt-tabs">
                        <span class="wt-tab is-on"><i class="bi bi-building"></i> Stay</span>
                        <span class="wt-tab is-hover"><i class="bi bi-airplane"></i> Flight</span>
                        <span class="wt-tab"><i class="bi bi-suitcase-lg"></i> Tours</span>
                    </div>
                    <div class="wt-preview-row">
                        <ul class="wt-features">
                            <li><i class="bi bi-shield-check"></i> Secure booking</li>
                            <li><i class="bi bi-headset"></i> 24/7 support</li>
                        </ul>
                        <div class="wt-preview-actions">
                            <span class="wt-btn">Search</span>
                            <span class="wt-link">Learn more →</span>
                        </div>
                    </div>
                    <div class="wt-dds">
                        <div class="wt-dd">
                            <span class="wt-menu-link is-on"><i class="bi bi-building"></i> Stay</span>
                            <span class="wt-menu-link"><i class="bi bi-airplane"></i> Flight</span>
                            <span class="wt-menu-link"><i class="bi bi-suitcase-lg"></i> Tours</span>
                        </div>
                        <div class="wt-dd">
                            <span class="wt-menu-link"><i class="bi bi-speedometer2"></i> Dashboard</span>
                            <span class="wt-menu-link"><i class="bi bi-person-fill"></i> Profile</span>
                            <hr class="wt-menu-sep">
                            <span class="wt-menu-link"><i class="bi bi-box-arrow-right"></i> Logout</span>
                        </div>
                    </div>
                    <p class="wt-ar" dir="rtl">ابحث عن فندقك المثالي واحجز بأفضل سعر</p>
                </div>
            </aside>
        </div>
    </form>
</div>

@push('styles')
<link rel="stylesheet" href="{{ url('public/assets/fonts/fonts.css') }}?v={{ filemtime(public_path('assets/fonts/fonts.css')) }}">
<style>
    /* ---------- layout ---------- */
    .wt { --wt-ui-border: #E5E7EB; --wt-ui-muted: #6B7280; --wt-ui-accent: var(--primary-color, #2563EB); }
    .wt-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
    .wt-head h2 { margin: 0 0 2px; font-size: 18px; font-weight: 700; color: #111827; }
    .wt-head p { margin: 0; font-size: 13px; color: var(--wt-ui-muted); }
    .wt-actions { display: flex; gap: 8px; }
    .wt-presets { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 14px; }
    .wt-presets-label { font-size: 12px; font-weight: 600; color: var(--wt-ui-muted); margin-right: 4px; }
    .wt-preset { display: inline-flex; align-items: center; gap: 10px; padding: 7px 14px 7px 8px; background: #fff; border: 1px solid var(--wt-ui-border); border-radius: 9999px; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
    .wt-preset:hover { border-color: #93C5FD; }
    .wt-preset.active { border-color: var(--wt-ui-accent); box-shadow: 0 0 0 1px var(--wt-ui-accent); }
    .wt-preset-dots { display: flex; }
    .wt-preset-dots span { width: 18px; height: 18px; border-radius: 50%; border: 2px solid #fff; margin-left: -6px; box-shadow: 0 0 0 1px rgba(0,0,0,.08); }
    .wt-preset-dots span:first-child { margin-left: 0; }
    .wt-preset-text { display: flex; flex-direction: column; line-height: 1.15; text-align: left; }
    .wt-preset-text b { font-size: 13px; color: #111827; }
    .wt-preset-text small { font-size: 11px; color: var(--wt-ui-muted); }

    .wt-layout { display: grid; grid-template-columns: minmax(0, 1fr) 380px; gap: 16px; align-items: start; }
    @media (max-width: 1199px) { .wt-layout { grid-template-columns: 1fr; } }
    .wt-card { display: grid; grid-template-columns: 170px minmax(0, 1fr); background: #fff; border: 1px solid var(--wt-ui-border); border-radius: 16px; overflow: hidden; }
    @media (max-width: 767px) { .wt-card { grid-template-columns: 1fr; } .wt-nav { flex-direction: row !important; overflow-x: auto; border-right: 0 !important; border-bottom: 1px solid var(--wt-ui-border); } }
    .wt-nav { display: flex; flex-direction: column; gap: 2px; padding: 10px; background: #F9FAFB; border-right: 1px solid var(--wt-ui-border); }
    .wt-nav-btn { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border: 0; border-radius: 10px; background: transparent; color: #374151; font-size: 13px; font-weight: 600; text-align: left; white-space: nowrap; cursor: pointer; }
    .wt-nav-btn i { font-size: 15px; color: var(--wt-ui-muted); }
    .wt-nav-btn:hover { background: #F3F4F6; }
    .wt-nav-btn.active { background: #fff; color: var(--wt-ui-accent); box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    .wt-nav-btn.active i { color: var(--wt-ui-accent); }
    .wt-panes { padding: 18px 20px; min-width: 0; }
    .wt-pane[hidden] { display: none !important; }
    .wt-pane h3 { margin: 18px 0 10px; font-size: 13px; font-weight: 700; color: #111827; }
    .wt-pane h3:first-child { margin-top: 0; }
    .wt-pane h3 small { margin-left: 6px; font-size: 12px; font-weight: 400; color: var(--wt-ui-muted); }
    .wt-help { margin: -4px 0 12px; font-size: 12px; color: var(--wt-ui-muted); }

    /* ---------- color field: swatch + label + hex ---------- */
    .wt-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px; }
    .wt-cf { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border: 1px solid var(--wt-ui-border); border-radius: 12px; background: #fff; }
    .wt-cf:focus-within { border-color: var(--wt-ui-accent); }
    .wt-cf-swatch { flex-shrink: 0; width: 34px; height: 34px; padding: 0; border: 1px solid rgba(0,0,0,.1); border-radius: 10px; background: none; cursor: pointer; }
    .wt-cf-swatch::-webkit-color-swatch-wrapper { padding: 0; }
    .wt-cf-swatch::-webkit-color-swatch { border: 0; border-radius: 9px; }
    .wt-cf-swatch::-moz-color-swatch { border: 0; border-radius: 9px; }
    .wt-cf-label { flex: 1; min-width: 0; margin: 0; font-size: 13px; font-weight: 600; color: #111827; line-height: 1.2; cursor: pointer; }
    .wt-cf-label small { display: block; font-size: 11px; font-weight: 400; color: var(--wt-ui-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .wt-cf-hex { width: 84px; flex-shrink: 0; padding: 5px 8px; border: 1px solid var(--wt-ui-border); border-radius: 8px; font: 12px/1.2 ui-monospace, SFMono-Regular, Menlo, monospace; text-transform: uppercase; color: #111827; }
    .wt-cf-hex:focus { outline: none; border-color: var(--wt-ui-accent); }

    /* ---------- selects row ---------- */
    .wt-controls { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; margin-top: 10px; }
    .wt-controls.wt-controls-half { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    @media (max-width: 575px) { .wt-controls.wt-controls-half { grid-template-columns: 1fr; } }
    .wt-controls label { display: flex; flex-direction: column; gap: 4px; margin: 0; font-size: 12px; font-weight: 600; color: #374151; }
    .wt-controls label small { font-weight: 400; color: var(--wt-ui-muted); }

    /* ---------- preview ---------- */
    /* Sticky beside the settings. The admin layouts put overflow-x:hidden on
       <body>, which turns it into a scroll container and stops sticky from
       working; clip looks the same without that. */
    .wt-preview-wrap { position: sticky; top: 90px; max-height: calc(100vh - 110px); overflow-y: auto; padding: 14px; background: #fff; border: 1px solid var(--wt-ui-border); border-radius: 16px; }
    body:has(.wt-preview-wrap) { overflow-x: clip; }
    .wt-preview-title { margin-bottom: 10px; font-size: 12px; font-weight: 700; color: var(--wt-ui-muted); text-transform: uppercase; letter-spacing: .06em; }
    .wt-preview { padding: 16px; border: 1px solid var(--wt-ui-border); border-radius: 12px; background: var(--wt-bg); font-family: var(--wt-font); color: var(--wt-text); }
    .wt-preview h3 { font-family: inherit; color: var(--wt-text); font-size: calc(22px * var(--wt-heading-scale, 1)); font-weight: var(--wt-heading-weight, 700); margin: 0 0 4px; }
    .wt-preview p { font-weight: var(--wt-font-weight, 400); color: color-mix(in srgb, var(--wt-text) 65%, transparent); margin-bottom: 12px; font-size: 14px; }
    .wt-accent { color: var(--wt-primary); }
    .wt-preview-row { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 12px; }
    .wt-features { list-style: none; padding: 0; margin: 0; display: grid; gap: 4px; font-size: 13px; }
    .wt-features i { color: var(--wt-icon); width: 18px; font-size: calc(14px * var(--wt-icon-scale, 1)); }
    .wt-preview-actions { display: flex; gap: 10px; align-items: center; }
    .wt-btn { display: inline-block; padding: 8px 16px; border-radius: 10px; background: var(--wt-primary); color: #fff; font-weight: var(--wt-button-weight, 600); font-size: calc(14px * var(--wt-button-scale, 1)); transition: background .15s; cursor: pointer; }
    .wt-btn:hover { background: var(--wt-primary-hover); }
    .wt-link { color: var(--wt-primary); font-weight: 600; font-size: 13px; cursor: pointer; }
    .wt-link:hover { color: var(--wt-primary-hover); }
    .wt-preview .wt-ar { font-size: 15px; margin: 0; }
    /* navbar links (.nav-item) + buttons (.btn-signin-pill / .btn-signup-solid) */
    .wt-navlinks { display: flex; flex-wrap: wrap; align-items: center; gap: 2px; margin: -4px -4px 14px; padding: 6px; border-radius: 12px; background: var(--wt-bg); box-shadow: 0 2px 10px rgba(0,0,0,.06); }
    .wt-nl { position: relative; display: inline-flex; align-items: center; height: 34px; padding: 0 8px; border-radius: 10px; color: var(--wt-nlink-text, var(--wt-text)); font-size: var(--wt-nlink-size, 14px); font-weight: var(--wt-nlink-weight, 600); cursor: pointer; transition: color .2s, background-color .2s; }
    .wt-nl:hover, .wt-nl.is-hover { color: var(--wt-nlink-hover, var(--wt-primary)); background-color: var(--wt-nlink-hover-bg, transparent); }
    .wt-nl.is-on { color: var(--wt-nlink-active, var(--wt-primary)); }
    .wt-nl.is-on::after { content: ''; position: absolute; bottom: 2px; left: 50%; width: 18px; height: 3px; border-radius: 2px; background: var(--wt-nlink-active, var(--wt-primary)); transform: translateX(-50%); }
    .wt-nb { display: inline-flex; align-items: center; height: 32px; padding: 0 12px; border-radius: var(--wt-nbtn-radius, 999px); font-size: var(--wt-nbtn-size, calc(14px * var(--wt-button-scale, 1))); font-weight: var(--wt-nbtn-weight, var(--wt-button-weight, 600)); cursor: pointer; transition: background .15s, opacity .15s; }
    .wt-nb-out { margin-left: auto; border: 1.5px solid var(--wt-nbtn-outline, var(--wt-primary)); color: var(--wt-nbtn-outline, var(--wt-primary)); background: var(--wt-bg); }
    .wt-nb-out:hover { background: var(--wt-nbtn-hover-bg, color-mix(in srgb, var(--wt-primary) 6%, transparent)); }
    .wt-nb-solid { border: 1.5px solid var(--wt-nbtn-bg, var(--wt-primary)); background: var(--wt-nbtn-bg, var(--wt-primary)); color: var(--wt-nbtn-text, #fff); }
    .wt-nb-solid:hover { background: var(--wt-nbtn-hover-bg, var(--wt-nbtn-bg, var(--wt-primary))); border-color: var(--wt-nbtn-hover-bg, var(--wt-nbtn-bg, var(--wt-primary))); opacity: .92; }
    /* homepage search tabs (.tab-btn) */
    .wt-tabs { display: inline-flex; flex-wrap: wrap; gap: 2px; max-width: 100%; padding: 5px; margin-bottom: 12px; border: 1px solid rgba(0,0,0,.06); border-radius: calc(var(--wt-tab-radius, 9993px) + 5px); background: var(--wt-bg); box-shadow: 0 8px 24px rgba(0,0,0,.1); }
    .wt-tab { display: inline-flex; align-items: center; gap: 7px; height: 36px; padding: 0 12px; border-radius: var(--wt-tab-radius, 9999px); color: var(--wt-tab-text, var(--wt-text)); font-size: var(--wt-tab-size, calc(15px * var(--wt-button-scale, 1))); font-weight: var(--wt-tab-weight, var(--wt-button-weight, 500)); cursor: pointer; transition: background-color .2s, color .2s; }
    .wt-tab i { color: color-mix(in srgb, var(--wt-tab-text, var(--wt-text)) 70%, transparent); }
    .wt-tab:hover, .wt-tab.is-hover { color: var(--wt-tab-hover-text, var(--wt-primary)); background: var(--wt-tab-hover-bg, color-mix(in srgb, var(--wt-primary) 6%, transparent)); }
    .wt-tab:hover i, .wt-tab.is-hover i { color: var(--wt-tab-hover-text, var(--wt-icon)); }
    .wt-tab.is-on { color: var(--wt-tab-active-text, #fff); background: var(--wt-tab-active-bg, var(--wt-primary)); font-weight: var(--wt-tab-weight, var(--wt-button-weight, 600)); }
    .wt-tab.is-on i { color: var(--wt-tab-active-text, #fff); }
    /* dropdowns (.nav-dd-panel / .nav-menu-link) */
    .wt-dds { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px; }
    .wt-dd { display: grid; align-content: start; padding: 6px; background: var(--wt-bg); border: 1px solid rgba(0,0,0,.08); border-radius: 14px; box-shadow: 0 12px 28px rgba(0,0,0,.1); }
    .wt-menu-link { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; color: var(--wt-text); font-size: var(--wt-dd-size, 14px); font-weight: var(--wt-dd-weight, 500); cursor: pointer; transition: background-color .15s, color .15s; }
    .wt-menu-link i { width: 16px; text-align: center; color: color-mix(in srgb, var(--wt-text) 50%, transparent); }
    .wt-menu-link:hover, .wt-menu-link.is-on { background: var(--wt-dd-hover-bg, color-mix(in srgb, var(--wt-primary) 8%, transparent)); color: var(--wt-dd-hover-color, var(--wt-primary)); }
    .wt-menu-link:hover i, .wt-menu-link.is-on i { color: var(--wt-dd-hover-color, var(--wt-icon)); }
    .wt-menu-sep { margin: 4px; border: 0; border-top: 1px solid rgba(0,0,0,.08); opacity: 1; }
    /* Nova's theme page sets its own font on every div/span (#thPage …), which
       would hide the font being previewed. Icons keep their icon font. */
    .wt-preview, .wt-preview *:not(i) { font-family: var(--wt-font) !important; }
    .wt-preview .wt-ar { font-family: var(--wt-font-ar) !important; }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('websiteThemeForm');
        if (!form) return;
        const preview = document.getElementById('wtPreview');
        const field = (n) => form.querySelector('[name="' + n + '"]');
        const isHex = (v) => /^#[0-9A-Fa-f]{6}$/.test(v);

        // ---- sub-tabs (Colors / Fonts & Sizes / Navbar / …); the open one is
        // remembered so Save brings you back to it.
        const openSection = (name) => {
            const btn = form.querySelector('[data-wt-section="' + name + '"]');
            if (!btn) return;
            form.querySelectorAll('[data-wt-section]').forEach((b) => b.classList.toggle('active', b === btn));
            form.querySelectorAll('[data-wt-pane]').forEach((p) => { p.hidden = p.dataset.wtPane !== name; });
            try { sessionStorage.setItem('wtSection', name); } catch (e) {}
        };
        form.querySelectorAll('[data-wt-section]').forEach((btn) => btn.addEventListener('click', () => openSection(btn.dataset.wtSection)));
        try { const saved = sessionStorage.getItem('wtSection'); if (saved) openSection(saved); } catch (e) {}

        // ---- colors that follow the theme (same rules as \App\Support\WebsiteTheme::autoColor)
        const mixHex = (hex, base, w) => '#' + [1, 3, 5].map((i) => Math.round(parseInt(hex.substr(i, 2), 16) * w + parseInt(base.substr(i, 2), 16) * (1 - w)).toString(16).padStart(2, '0')).join('').toUpperCase();
        // A slightly darker shade (HSL lightness -6) for the hover color.
        const darken = (hex) => {
            let [r, g, b] = [1, 3, 5].map((i) => parseInt(hex.substr(i, 2), 16) / 255);
            const max = Math.max(r, g, b), min = Math.min(r, g, b);
            let h = 0, s = 0, l = (max + min) / 2;
            if (max !== min) {
                const d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                h = max === r ? (g - b) / d + (g < b ? 6 : 0) : max === g ? (b - r) / d + 2 : (r - g) / d + 4;
                h /= 6;
            }
            l = Math.max(0, l - 0.06);
            const f = (n) => {
                const k = (n + h * 12) % 12, a = s * Math.min(l, 1 - l);
                return Math.round((l - a * Math.max(-1, Math.min(k - 3, 9 - k, 1))) * 255).toString(16).padStart(2, '0');
            };
            return ('#' + f(0) + f(8) + f(4)).toUpperCase();
        };
        const sources = () => {
            const pick = (n, d) => (isHex(field(n).value) ? field(n).value.toUpperCase() : d);
            const primary = pick('primary_color', '#2563EB'), text = pick('text_color', '#000000'), bg = pick('bg_color', '#FFFFFF');
            return { primary, text, bg, white: '#FFFFFF', 'primary-dark': darken(primary), 'primary-tint': mixHex(primary, bg, 0.08), 'primary-tint-6': mixHex(primary, bg, 0.06) };
        };
        const setColor = (name, hex) => {
            field(name).value = hex.toUpperCase();
            const picker = form.querySelector('[data-wt-pair="' + name + '"]');
            if (picker) picker.value = hex.toLowerCase();
        };
        // Linked colors (never edited by hand) move with Primary / Text / Background.
        const updateLinked = () => {
            const src = sources();
            form.querySelectorAll('[data-wt-auto-src][data-wt-linked="1"]').forEach((p) => setColor(p.dataset.wtPair, src[p.dataset.wtAutoSrc]));
        };

        // ---- preview
        const render = () => {
            const s = preview.style;
            ['primary', 'bg', 'text', 'icon'].forEach((k) => {
                const v = field(k + '_color').value;
                if (isHex(v)) s.setProperty('--wt-' + k, v);
            });
            const hover = field('primary_hover').value;
            if (isHex(hover)) s.setProperty('--wt-primary-hover', hover);
            s.setProperty('--wt-font', "'" + field('font').value + "', sans-serif");
            s.setProperty('--wt-font-ar', "'" + field('font_arabic').value + "', sans-serif");
            ['font_weight', 'heading_weight', 'button_weight'].forEach((n) => {
                const v = field(n).value, prop = '--wt-' + n.replace('_', '-');
                if (v) s.setProperty(prop, v); else s.removeProperty(prop);
            });
            ['heading', 'button', 'icon'].forEach((k) => s.setProperty('--wt-' + k + '-scale', field(k + '_scale').value / 100));
            // navbar / tabs / dropdowns: each field names its preview variable
            form.querySelectorAll('[data-wt-var]').forEach((el) => {
                const v = el.value;
                let val = null;
                if (el.dataset.wtKind === 'color') val = isHex(v) ? v : null;
                if (el.dataset.wtKind === 'px') val = v && v !== el.dataset.wtDefault ? v + 'px' : null;
                if (el.dataset.wtKind === 'weight') val = v || null;
                if (el.dataset.wtKind === 'radius') val = v && v !== 'pill' ? v + 'px' : null;
                if (val) s.setProperty(el.dataset.wtVar, val); else s.removeProperty(el.dataset.wtVar);
            });
        };
        const markCustom = () => {
            document.getElementById('wtPreset').value = 'custom';
            form.querySelectorAll('[data-wt-preset]').forEach((b) => b.classList.remove('active'));
        };

        // ---- color swatch <-> hex box
        form.querySelectorAll('[data-wt-pair]').forEach((picker) => {
            const name = picker.dataset.wtPair, text = field(name);
            const changed = () => {
                picker.dataset.wtLinked = '0'; // edited by hand: stop following the theme
                if (['primary_color', 'text_color', 'bg_color'].includes(name)) updateLinked();
                markCustom();
                render();
            };
            picker.addEventListener('input', () => { text.value = picker.value.toUpperCase(); changed(); });
            text.addEventListener('input', () => { if (isHex(text.value)) { picker.value = text.value.toLowerCase(); changed(); } });
        });
        form.querySelectorAll('select').forEach((sel) => sel.addEventListener('change', () => { markCustom(); render(); }));

        // ---- ready-made themes: fill every field (with real hex codes)
        form.querySelectorAll('[data-wt-preset]').forEach((btn) => btn.addEventListener('click', () => {
            const v = JSON.parse(btn.dataset.values);
            Object.entries(v).forEach(([k, val]) => {
                const f = field(k);
                if (!f || k === 'preset') return;
                if (form.querySelector('[data-wt-pair="' + k + '"]')) setColor(k, val); else f.value = val ?? '';
            });
            form.querySelectorAll('[data-wt-auto-src]').forEach((p) => { p.dataset.wtLinked = '1'; });
            document.getElementById('wtPreset').value = btn.dataset.wtPreset;
            form.querySelectorAll('[data-wt-preset]').forEach((b) => b.classList.toggle('active', b === btn));
            render();
        }));

        // On load, a follow-the-theme color is linked if it still equals what the
        // theme gives it (the stock blue's hover is the hand-picked #1D4ED8).
        const src0 = sources();
        form.querySelectorAll('[data-wt-auto-src]').forEach((p) => {
            const v = field(p.dataset.wtPair).value.toUpperCase();
            const stockHover = p.dataset.wtPair === 'primary_hover' && src0.primary === '#2563EB' && v === '#1D4ED8';
            if (v === src0[p.dataset.wtAutoSrc] || stockHover) p.dataset.wtLinked = '1';
        });

        render();
    })();
</script>
@endpush
