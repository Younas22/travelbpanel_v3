/**
 * Global floating theme quick-switcher — present on every admin page.
 *
 * Clicking a preset card instantly re-colors the current page (live) and
 * persists the choice via AJAX, so the next page load (and every other
 * page right now) reflects it too. Mirrors ThemeSetting::toCssVariables()
 * (see app/Models/ThemeSetting.php) for the subset this widget controls —
 * colors only; font/radius/layout stay whatever they already were.
 */
(function () {
    'use strict';

    const toggleBtn = document.getElementById('themeQsToggle');
    const panel = document.getElementById('themeQsPanel');
    const closeBtn = document.getElementById('themeQsClose');
    const stateEl = document.getElementById('theme-qs-state');
    if (!toggleBtn || !panel || !stateEl) return;

    const state = JSON.parse(stateEl.textContent);

    function openPanel() { panel.classList.add('open'); }
    function closePanel() { panel.classList.remove('open'); }

    toggleBtn.addEventListener('click', () => {
        panel.classList.contains('open') ? closePanel() : openPanel();
    });
    closeBtn.addEventListener('click', closePanel);
    document.addEventListener('click', (e) => {
        if (panel.classList.contains('open') && !panel.contains(e.target) && !toggleBtn.contains(e.target)) {
            closePanel();
        }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closePanel();
    });

    function hexToRgbTriplet(hex) {
        if (!hex) return null;
        const m = /^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/.exec(hex.trim());
        if (!m) return null;
        let v = m[1];
        if (v.length === 3) v = v.split('').map((c) => c + c).join('');
        return `${parseInt(v.substr(0, 2), 16)},${parseInt(v.substr(2, 2), 16)},${parseInt(v.substr(4, 2), 16)}`;
    }

    const CSS_VAR_MAP = {
        primary_color: '--primary-color', secondary_color: '--secondary-color', success_color: '--success-color',
        warning_color: '--warning-color', danger_color: '--danger-color', info_color: '--info-color',
        body_background: '--body-bg', sidebar_background: '--sidebar-bg', navbar_background: '--navbar-bg',
        card_background: '--card-bg', text_color: '--text-color', border_color: '--border-color',
        input_background: '--input-bg', input_border: '--input-border', input_focus_color: '--input-focus-color',
    };

    const LAYOUT_CLASSES = [
        'theme-sidebar-fixed', 'theme-navbar-fixed', 'theme-shadow',
        'theme-compact', 'theme-wide', 'theme-fluid', 'theme-animated',
    ];

    function applyColors(values, presetKey, isDark) {
        const root = document.documentElement.style;
        Object.entries(CSS_VAR_MAP).forEach(([field, varName]) => {
            if (values[field]) root.setProperty(varName, values[field]);
        });

        root.setProperty('--bs-primary', values.primary_color);
        root.setProperty('--bs-link-color', values.primary_color);
        root.setProperty('--bs-link-hover-color', values.primary_color);
        root.setProperty('--bs-success', values.success_color);
        root.setProperty('--bs-warning', values.warning_color);
        root.setProperty('--bs-danger', values.danger_color);
        root.setProperty('--bs-info', values.info_color);
        root.setProperty('--bs-card-bg', values.card_background);

        ['primary', 'success', 'warning', 'danger', 'info'].forEach((name) => {
            const rgb = hexToRgbTriplet(values[`${name}_color`]);
            if (rgb) root.setProperty(`--bs-${name}-rgb`, rgb);
        });

        document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');

        const body = document.body;
        Array.from(body.classList)
            .filter((cls) => cls.startsWith('theme-') && !LAYOUT_CLASSES.includes(cls))
            .forEach((cls) => body.classList.remove(cls));
        body.classList.add(`theme-${presetKey}`);
    }

    function toast(message, isError) {
        if (typeof Toastify === 'function') {
            Toastify({
                text: message,
                duration: isError ? 5000 : 2500,
                close: true,
                gravity: 'top',
                position: 'right',
                backgroundColor: isError ? '#ff6b6b' : '#4fbe87',
            }).showToast();
        }
    }

    function saveTheme(values, presetKey) {
        const payload = {
            theme_name: presetKey,
            preset: presetKey,
            font_family: state.font_family,
            font_size: state.font_size,
            border_radius: state.border_radius,
            layout_options: state.layout_options,
            ...values,
        };

        return fetch(state.routes.update, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload),
        }).then((res) => res.json().then((data) => ({ ok: res.ok, data })));
    }

    document.querySelectorAll('[data-quick-preset]').forEach((card) => {
        card.addEventListener('click', () => {
            const presetKey = card.dataset.quickPreset;
            const values = JSON.parse(card.dataset.values);
            const isDark = card.dataset.dark === '1';

            applyColors(values, presetKey, isDark);
            document.querySelectorAll('[data-quick-preset]').forEach((c) => c.classList.remove('active'));
            card.classList.add('active');

            toggleBtn.classList.add('spinning');
            setTimeout(() => toggleBtn.classList.remove('spinning'), 600);

            saveTheme(values, presetKey)
                .then(({ ok, data }) => {
                    if (ok && data.success) {
                        state.theme_name = presetKey;
                        toast(`Theme switched to ${card.querySelector('.theme-qs-label').textContent}.`);
                    } else {
                        toast((data && data.message) || 'Could not save theme.', true);
                    }
                })
                .catch(() => toast('An error occurred while saving the theme.', true));
        });
    });
})();
