/**
 * Admin Theme Settings — live preview, AJAX save/reset.
 *
 * Every color/typography/radius/layout field on the Theme Settings page
 * mirrors ThemeSetting::toCssVariables() (see app/Models/ThemeSetting.php)
 * so the in-browser preview matches exactly what gets rendered server-side
 * on the next page load.
 */
(function () {
    'use strict';

    const COLOR_FIELDS = [
        'primary_color', 'secondary_color', 'success_color', 'warning_color',
        'danger_color', 'info_color', 'body_background', 'sidebar_background',
        'navbar_background', 'card_background', 'text_color', 'border_color',
        'input_background', 'input_border', 'input_focus_color',
    ];

    const LAYOUT_KEYS = [
        'sidebar_fixed', 'navbar_fixed', 'box_shadow', 'rounded_cards',
        'rounded_inputs', 'rounded_buttons', 'compact_mode', 'wide_layout',
        'fluid_layout', 'animations',
    ];

    const LAYOUT_CLASS_MAP = {
        sidebar_fixed: 'theme-sidebar-fixed',
        navbar_fixed: 'theme-navbar-fixed',
        box_shadow: 'theme-shadow',
        compact_mode: 'theme-compact',
        wide_layout: 'theme-wide',
        fluid_layout: 'theme-fluid',
        animations: 'theme-animated',
    };

    const RADIUS_SCALE = {
        small: { sm: '2px', md: '4px', lg: '6px' },
        medium: { sm: '4px', md: '8px', lg: '12px' },
        large: { sm: '8px', md: '12px', lg: '16px' },
        xl: { sm: '12px', md: '18px', lg: '24px' },
    };

    const form = document.getElementById('themeSettingsForm');
    if (!form) return;

    const initialState = JSON.parse(document.getElementById('theme-initial-state').textContent);
    const routes = initialState.routes;
    const designStylesheets = initialState.design_stylesheets || {};
    let baseline = buildStateFromTheme(initialState.theme, initialState.layout_options);

    function buildStateFromTheme(theme, layoutOptions) {
        const state = { ...theme, layout_options: { ...layoutOptions } };
        return state;
    }

    function hexToRgbTriplet(hex) {
        if (!hex) return null;
        const m = /^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/.exec(hex.trim());
        if (!m) return null;
        let v = m[1];
        if (v.length === 3) v = v.split('').map((c) => c + c).join('');
        const r = parseInt(v.substr(0, 2), 16);
        const g = parseInt(v.substr(2, 2), 16);
        const b = parseInt(v.substr(4, 2), 16);
        return `${r},${g},${b}`;
    }

    function computeCssVars(state) {
        const radius = RADIUS_SCALE[state.border_radius] || RADIUS_SCALE.medium;
        const layout = state.layout_options || {};

        const vars = {
            '--primary-color': state.primary_color,
            '--secondary-color': state.secondary_color,
            '--success-color': state.success_color,
            '--warning-color': state.warning_color,
            '--danger-color': state.danger_color,
            '--info-color': state.info_color,
            '--body-bg': state.body_background,
            '--sidebar-bg': state.sidebar_background,
            '--navbar-bg': state.navbar_background,
            '--card-bg': state.card_background,
            '--text-color': state.text_color,
            '--border-color': state.border_color,
            '--input-bg': state.input_background,
            '--input-border': state.input_border,
            '--input-focus-color': state.input_focus_color,
            '--font-family': state.font_family ? `'${state.font_family}', 'Inter', sans-serif` : null,
            '--font-size-base': state.font_size,
            '--radius-sm': radius.sm,
            '--radius-md': radius.md,
            '--radius-lg': radius.lg,
            '--card-radius': layout.rounded_cards ? radius.lg : '0px',
            '--input-radius': layout.rounded_inputs ? radius.md : '0px',
            '--btn-radius': layout.rounded_buttons ? radius.md : '0px',
            '--shadow-toggle': layout.box_shadow ? '1' : '0',
            '--transition-speed': layout.animations ? '0.2s' : '0s',
            '--bs-primary': state.primary_color,
            '--bs-link-color': state.primary_color,
            '--bs-link-hover-color': state.primary_color,
            '--bs-success': state.success_color,
            '--bs-warning': state.warning_color,
            '--bs-danger': state.danger_color,
            '--bs-info': state.info_color,
            '--bs-card-bg': state.card_background,
        };

        ['primary', 'success', 'warning', 'danger', 'info'].forEach((name) => {
            const rgb = hexToRgbTriplet(state[`${name}_color`]);
            if (rgb) vars[`--bs-${name}-rgb`] = rgb;
        });

        return vars;
    }

    function applyVars(vars) {
        const root = document.documentElement.style;
        Object.entries(vars).forEach(([name, value]) => {
            if (value === null || value === undefined || value === '') return;
            root.setProperty(name, value);
        });
    }

    function applyBodyClasses(layout, themeName) {
        const body = document.body;
        Object.values(LAYOUT_CLASS_MAP).forEach((cls) => body.classList.remove(cls));
        Object.entries(LAYOUT_CLASS_MAP).forEach(([key, cls]) => {
            if (layout[key]) body.classList.add(cls);
        });

        Array.from(body.classList)
            .filter((cls) => cls.startsWith('theme-') && !Object.values(LAYOUT_CLASS_MAP).includes(cls))
            .forEach((cls) => body.classList.remove(cls));
        body.classList.add(`theme-${themeName || 'custom'}`);
    }

    function applyGoogleFont(fontFamily) {
        const skip = ['system font', 'system', 'system-ui'].includes((fontFamily || '').toLowerCase());
        let link = document.getElementById('theme-google-font');
        if (skip) {
            if (link) link.remove();
            return;
        }
        const href = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(fontFamily).replace(/%20/g, '+')}:wght@400;500;600;700&display=swap`;
        if (!link) {
            link = document.createElement('link');
            link.id = 'theme-google-font';
            link.rel = 'stylesheet';
            document.head.appendChild(link);
        }
        link.href = href;
    }

    function applyState(state) {
        applyVars(computeCssVars(state));
        applyBodyClasses(state.layout_options || {}, state.theme_name);
        applyGoogleFont(state.font_family);
        document.documentElement.setAttribute('data-bs-theme', state._dark ? 'dark' : 'light');
    }

    function applyDesignStyle(designStyle) {
        const link = document.getElementById('admin-design-css');
        const href = designStylesheets[designStyle];
        if (link && href) link.href = href;

        form.querySelectorAll('[data-design-card]').forEach((c) => {
            c.classList.toggle('active', c.dataset.design === designStyle);
        });
    }

    form.querySelectorAll('[data-design-card]').forEach((card) => {
        card.addEventListener('click', () => {
            const design = card.dataset.design;
            document.getElementById('selectedDesignStyle').value = design;
            applyDesignStyle(design);
        });
    });

    function collectFormState() {
        const state = {
            theme_name: document.getElementById('selectedPreset').value || 'custom',
            design_style: document.getElementById('selectedDesignStyle').value || 'classic',
        };

        COLOR_FIELDS.forEach((name) => {
            const el = document.getElementById(name);
            if (el) state[name] = el.value;
        });

        state.font_family = document.getElementById('font_family').value;
        state.font_size = document.getElementById('font_size').value;

        const radiusEl = form.querySelector('input[name="border_radius"]:checked');
        state.border_radius = radiusEl ? radiusEl.value : 'medium';

        const layout = {};
        LAYOUT_KEYS.forEach((key) => {
            const cb = form.querySelector(`[data-layout-key="${key}"]`);
            layout[key] = !!(cb && cb.checked);
        });
        state.layout_options = layout;

        return state;
    }

    function syncColorField(name, value) {
        const textInput = document.getElementById(name);
        if (textInput) textInput.value = value;
        const swatch = form.querySelector(`.color-field-swatch[data-paired="${name}"]`);
        if (swatch && /^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/.test(value)) swatch.value = value;
        if (name === 'primary_color') {
            const picker = document.getElementById('primary_color_picker');
            if (picker && /^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/.test(value)) picker.value = value;
            updateActiveSwatchButton(value);
        }
    }

    function updateActiveSwatchButton(hex) {
        form.querySelectorAll('.color-swatch-btn').forEach((btn) => {
            btn.classList.toggle('active', btn.dataset.color.toLowerCase() === (hex || '').toLowerCase());
        });
    }

    function refreshAndPreview() {
        applyState(collectFormState());
    }

    // Paired color-picker <-> hex text input for every secondary color field.
    form.querySelectorAll('.color-field-swatch').forEach((swatch) => {
        swatch.addEventListener('input', () => {
            const name = swatch.dataset.paired;
            const textInput = document.getElementById(name);
            if (textInput) textInput.value = swatch.value;
            refreshAndPreview();
        });
    });

    // Primary color: swatch <-> hex text input <-> custom color picker.
    const primaryPicker = document.getElementById('primary_color_picker');
    const primaryText = document.getElementById('primary_color');
    primaryPicker.addEventListener('input', () => {
        primaryText.value = primaryPicker.value;
        updateActiveSwatchButton(primaryPicker.value);
        refreshAndPreview();
    });
    primaryText.addEventListener('input', () => {
        if (/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/.test(primaryText.value)) {
            primaryPicker.value = primaryText.value;
        }
        updateActiveSwatchButton(primaryText.value);
        refreshAndPreview();
    });

    form.querySelectorAll('.color-swatch-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            syncColorField('primary_color', btn.dataset.color);
            refreshAndPreview();
        });
    });

    // Every remaining plain text/select input that drives a CSS variable.
    form.querySelectorAll('.theme-var-input').forEach((el) => {
        el.addEventListener('input', () => {
            if (COLOR_FIELDS.includes(el.id)) syncColorField(el.id, el.value);
            refreshAndPreview();
        });
        el.addEventListener('change', refreshAndPreview);
    });

    // Border radius option cards.
    form.querySelectorAll('.radius-option').forEach((label) => {
        label.querySelector('input').addEventListener('change', () => {
            form.querySelectorAll('.radius-option').forEach((l) => l.classList.remove('active'));
            label.classList.add('active');
            refreshAndPreview();
        });
    });

    // Layout toggle switches.
    form.querySelectorAll('[data-layout-key]').forEach((cb) => {
        cb.addEventListener('change', refreshAndPreview);
    });

    // Theme preset cards — apply the preset's full color set into the form,
    // then preview it, without touching typography / radius / layout options.
    form.querySelectorAll('[data-preset-card]').forEach((card) => {
        card.addEventListener('click', () => {
            const values = JSON.parse(card.dataset.values);
            document.getElementById('selectedPreset').value = card.dataset.preset;

            Object.entries(values).forEach(([name, value]) => {
                const el = document.getElementById(name);
                if (el) el.value = value;
                syncColorField(name, value);
            });

            form.querySelectorAll('[data-preset-card]').forEach((c) => c.classList.remove('active'));
            card.classList.add('active');

            const state = collectFormState();
            state._dark = card.dataset.dark === '1';
            applyState(state);
        });
    });

    function toast(message, isError) {
        if (typeof Toastify === 'function') {
            Toastify({
                text: message,
                duration: isError ? 5000 : 3000,
                close: true,
                gravity: 'top',
                position: 'right',
                backgroundColor: isError ? '#ff6b6b' : '#4fbe87',
            }).showToast();
        } else {
            window.alert(message);
        }
    }

    function setButtonsBusy(busy) {
        ['themeSaveBtn', 'themeSaveBtnBottom', 'themeResetBtn', 'themeResetBtnBottom', 'themeCancelBtn'].forEach((id) => {
            const btn = document.getElementById(id);
            if (btn) btn.disabled = busy;
        });
    }

    function postJson(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(body || {}),
        }).then((res) => res.json().then((data) => ({ ok: res.ok, data })));
    }

    function saveSettings() {
        const state = collectFormState();
        setButtonsBusy(true);

        postJson(routes.update, state)
            .then(({ ok, data }) => {
                if (ok && data.success) {
                    baseline = state;
                    toast(data.message || 'Theme settings saved successfully.');
                } else {
                    toast(data.message || 'Could not save theme settings.', true);
                }
            })
            .catch(() => toast('An error occurred while saving theme settings.', true))
            .finally(() => setButtonsBusy(false));
    }

    function resetSettings() {
        if (!window.confirm('Reset the theme to factory defaults? This applies immediately for every admin.')) return;
        setButtonsBusy(true);

        postJson(routes.reset, {})
            .then(({ ok, data }) => {
                if (ok && data.success) {
                    populateForm(data.theme);
                    baseline = collectFormState();
                    applyState(baseline);
                    toast(data.message || 'Theme reset to default.');
                } else {
                    toast((data && data.message) || 'Could not reset theme.', true);
                }
            })
            .catch(() => toast('An error occurred while resetting the theme.', true))
            .finally(() => setButtonsBusy(false));
    }

    function populateForm(theme) {
        document.getElementById('selectedPreset').value = theme.theme_name || 'default';
        document.getElementById('selectedDesignStyle').value = theme.design_style || 'classic';
        applyDesignStyle(theme.design_style || 'classic');

        COLOR_FIELDS.forEach((name) => {
            const el = document.getElementById(name);
            if (el && theme[name] !== undefined) syncColorField(name, theme[name]);
        });

        document.getElementById('font_family').value = theme.font_family;
        document.getElementById('font_size').value = theme.font_size;

        form.querySelectorAll('input[name="border_radius"]').forEach((r) => {
            r.checked = r.value === theme.border_radius;
            r.closest('.radius-option').classList.toggle('active', r.checked);
        });

        const layout = theme.layout_options || {};
        LAYOUT_KEYS.forEach((key) => {
            const cb = form.querySelector(`[data-layout-key="${key}"]`);
            if (cb) cb.checked = !!layout[key];
        });

        form.querySelectorAll('[data-preset-card]').forEach((c) => {
            c.classList.toggle('active', c.dataset.preset === (theme.theme_name || 'default'));
        });
    }

    function cancelChanges() {
        populateForm(baseline);
        applyState(baseline);
        toast('Changes discarded.');
    }

    document.getElementById('themeSaveBtn').addEventListener('click', saveSettings);
    document.getElementById('themeSaveBtnBottom').addEventListener('click', saveSettings);
    document.getElementById('themeResetBtn').addEventListener('click', resetSettings);
    document.getElementById('themeResetBtnBottom').addEventListener('click', resetSettings);
    document.getElementById('themeCancelBtn').addEventListener('click', cancelChanges);
})();
