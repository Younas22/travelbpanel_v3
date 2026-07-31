{{-- Shared Tailwind CDN + scoped reset for every admin-nova/menus/* page. --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: { preflight: false },
        theme: {
            extend: {
                fontFamily: { jakarta: ['Plus Jakarta Sans', 'sans-serif'] },
                colors: {
                    novabg: '#F7F8FC', novablue: '#2563EB', novacyan: '#06B6D4',
                    novatext: '#000000', novamuted: '#000000', novaborder: '#E5E7EB',
                    novasuccess: '#22C55E', novawarning: '#F59E0B', novadanger: '#EF4444',
                },
            },
        },
    };
</script>
<style>
    #mnPage, #mnPage *, #mnPage *::before, #mnPage *::after { box-sizing: border-box; }
    #mnPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #mnPage h1, #mnPage h2, #mnPage h3, #mnPage p { margin: 0; padding: 0; }
    #mnPage a { text-decoration: none; color: inherit; }
    #mnPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #mnPage svg { display: block; }
    #mnPage .tt-fade-in { animation: mnFadeIn .5s ease both; }
    @keyframes mnFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    #mnPage .tt-row:hover { background: #F7F8FC; }

    #mnPage .mn-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000; background: #fff;
        white-space: nowrap; transition: background .2s ease, border-color .2s ease;
    }
    #mnPage .mn-btn-nova:hover { background: #F7F8FC; }
    #mnPage .mn-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
    #mnPage .mn-btn-primary:hover { background: #1D4ED8; border-color: #1D4ED8; }
    #mnPage .mn-btn-cyan { background: #06B6D4; color: #fff; border-color: #06B6D4; }
    #mnPage .mn-icon-btn {
        width: 30px; height: 30px; border-radius: 9999px; border: 1px solid #E5E7EB;
        display: inline-flex; align-items: center; justify-content: center; background: #fff;
        transition: background .2s ease, border-color .2s ease;
    }
    #mnPage .mn-icon-btn:hover { background: #F7F8FC; }
    #mnPage .mn-icon-warn:hover { background: #FFFBEB; border-color: #F59E0B; color: #F59E0B; }
    #mnPage .mn-icon-success:hover { background: #ECFDF5; border-color: #22C55E; color: #22C55E; }
    #mnPage .mn-icon-danger:hover { background: #FEF2F2; border-color: #EF4444; color: #EF4444; }

    #mnPage .mn-tab { border-radius: 9999px; border: 1px solid #E5E7EB; background: #fff; color: #000; }
    #mnPage .mn-tab.mn-tab-active { background: #2563EB; border-color: #2563EB; color: #fff; }
    #mnPage .mn-tab:hover:not(.mn-tab-active) { background: #F7F8FC; }

    #mnPage .mn-drag-handle { cursor: grab; color: #9CA3AF; }
    #mnPage .mn-item.dragging { opacity: .5; }
    #mnPage .sortable-ghost { opacity: .35; }
    #mnPage .sortable-chosen { box-shadow: 0 0 0 2px #2563EB inset; border-radius: 1rem; }

    #mnPage .mn-bulk-bar { display: none; }
    #mnPage .mn-bulk-bar.mn-visible { display: flex; }

    /* Dedicated show/hide toggle for the header bulk-action buttons —
       NOT Tailwind's "hidden" utility. Tailwind CDN injects its generated
       stylesheet the instant its <script> tag executes (synchronously,
       before the browser parses this later <style> block), so this block
       ends up AFTER Tailwind's own rules in the DOM and would silently win
       the display:none vs display:inline-flex tie against any element that
       also carries our own unconditional-display .mn-btn-nova class. */
    #mnPage .mn-bulk-only { display: none; }
    #mnPage .mn-bulk-only.mn-show { display: inline-flex; }

    #mnPage .mn-label { display: block; font-size: 11px; font-weight: 600; color: #000; margin-bottom: 6px; }
    #mnPage .mn-input, #mnPage select.mn-input, #mnPage textarea.mn-input {
        width: 100%; border: 1px solid #E5E7EB; border-radius: 14px; padding: 10px 14px;
        font-size: 13px; font-family: 'Plus Jakarta Sans', sans-serif; color: #000; background: #fff;
    }
    #mnPage .mn-input:focus { outline: none; border-color: #2563EB; }
    #mnPage textarea.mn-input { border-radius: 16px; resize: vertical; }
    #mnPage .mn-required { color: #EF4444; }
    #mnPage .mn-help { font-size: 11px; color: #6B7280; margin-top: 4px; }
    #mnPage .mn-error { font-size: 11px; color: #EF4444; margin-top: 4px; }

    #mnPage .mn-switch { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
    #mnPage .mn-switch input { opacity: 0; width: 0; height: 0; }
    #mnPage .mn-switch .mn-slider {
        position: absolute; inset: 0; background: #E5E7EB; border-radius: 9999px; cursor: pointer;
        transition: background .25s ease;
    }
    #mnPage .mn-switch .mn-slider::before {
        content: ''; position: absolute; width: 16px; height: 16px; left: 3px; top: 3px;
        background: #fff; border-radius: 50%; transition: transform .25s ease; box-shadow: 0 1px 3px rgba(0,0,0,.25);
    }
    #mnPage .mn-switch input:checked + .mn-slider { background: #2563EB; }
    #mnPage .mn-switch input:checked + .mn-slider::before { transform: translateX(18px); }

    #mnPage [data-tooltip] { position: relative; }
    #mnPage [data-tooltip]::after {
        content: attr(data-tooltip); position: absolute; bottom: calc(100% + 8px); left: 50%;
        transform: translateX(-50%) translateY(4px); background: #1F2937; color: #fff;
        font-size: 11px; font-weight: 600; line-height: 1; padding: 6px 10px; border-radius: 6px;
        white-space: nowrap; box-shadow: 0 6px 16px rgba(0,0,0,.18); opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, transform .15s ease, visibility .15s ease; z-index: 60;
    }
    #mnPage [data-tooltip]::before {
        content: ''; position: absolute; bottom: calc(100% + 3px); left: 50%; transform: translateX(-50%);
        border: 5px solid transparent; border-top-color: #1F2937; opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, visibility .15s ease; z-index: 60;
    }
    #mnPage [data-tooltip]:hover::after, #mnPage [data-tooltip]:focus-visible::after { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); transition-delay: .25s; }
    #mnPage [data-tooltip]:hover::before, #mnPage [data-tooltip]:focus-visible::before { opacity: 1; visibility: visible; transition-delay: .25s; }

    /* Toast notifications used by index.blade.php's showNotification(). */
    #mnPage .mn-toast {
        position: fixed; bottom: 20px; right: 20px; z-index: 9999; padding: 12px 18px; border-radius: 9999px;
        font-size: 13px; font-weight: 600; color: #fff; box-shadow: 0 8px 20px rgba(0,0,0,.15);
        display: flex; align-items: center; gap: 8px; animation: mnToastIn .25s ease both;
    }
    @keyframes mnToastIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
</style>
