{{-- Shared Tailwind CDN + scoped reset for admin-nova/settings/languages/*. --}}
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
    #lgPage, #lgPage *, #lgPage *::before, #lgPage *::after { box-sizing: border-box; }
    #lgPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #lgPage h1, #lgPage h2, #lgPage p { margin: 0; padding: 0; }
    #lgPage a { text-decoration: none; color: inherit; }
    #lgPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #lgPage svg { display: block; }
    #lgPage .tt-fade-in { animation: lgFadeIn .5s ease both; }
    @keyframes lgFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    #lgPage .tt-row:hover { background: #F7F8FC; }

    #lgPage .lg-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000; background: #fff;
        white-space: nowrap; transition: background .2s ease, border-color .2s ease;
    }
    #lgPage .lg-btn-nova:hover { background: #F7F8FC; }
    #lgPage .lg-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
    #lgPage .lg-btn-primary:hover { background: #1D4ED8; border-color: #1D4ED8; }
    #lgPage .lg-btn-cyan { background: #06B6D4; color: #fff; border-color: #06B6D4; }
    #lgPage .lg-icon-btn {
        width: 30px; height: 30px; border-radius: 9999px; border: 1px solid #E5E7EB;
        display: inline-flex; align-items: center; justify-content: center; background: #fff;
        transition: background .2s ease, border-color .2s ease;
    }
    #lgPage .lg-icon-btn:hover { background: #F7F8FC; }
    #lgPage .lg-icon-danger:hover { background: #FEF2F2; border-color: #EF4444; color: #EF4444; }
    #lgPage .lg-icon-active { background: #2563EB; border-color: #2563EB; color: #fff; }

    #lgPage .lg-label { display: block; font-size: 11px; font-weight: 600; color: #000; margin-bottom: 6px; }
    #lgPage .lg-input, #lgPage select.lg-input {
        width: 100%; border: 1px solid #E5E7EB; border-radius: 14px; padding: 10px 14px;
        font-size: 13px; font-family: 'Plus Jakarta Sans', sans-serif; color: #000; background: #fff;
    }
    #lgPage .lg-input:focus { outline: none; border-color: #2563EB; }
    #lgPage .lg-input.is-invalid { border-color: #EF4444; }
    #lgPage .lg-error { font-size: 11px; color: #EF4444; margin-top: 4px; }
    #lgPage .lg-help { font-size: 11px; color: #6B7280; margin-top: 4px; }
    #lgPage .lg-required { color: #EF4444; }

    #lgPage .lg-switch { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
    #lgPage .lg-switch input { opacity: 0; width: 0; height: 0; }
    #lgPage .lg-switch .lg-slider {
        position: absolute; inset: 0; background: #E5E7EB; border-radius: 9999px; cursor: pointer;
        transition: background .25s ease;
    }
    #lgPage .lg-switch .lg-slider::before {
        content: ''; position: absolute; width: 16px; height: 16px; left: 3px; top: 3px;
        background: #fff; border-radius: 50%; transition: transform .25s ease; box-shadow: 0 1px 3px rgba(0,0,0,.25);
    }
    #lgPage .lg-switch input:checked + .lg-slider { background: #2563EB; }
    #lgPage .lg-switch input:checked + .lg-slider::before { transform: translateX(18px); }

    #lgPage [data-tooltip] { position: relative; }
    #lgPage [data-tooltip]::after {
        content: attr(data-tooltip); position: absolute; bottom: calc(100% + 8px); left: 50%;
        transform: translateX(-50%) translateY(4px); background: #1F2937; color: #fff;
        font-size: 11px; font-weight: 600; line-height: 1; padding: 6px 10px; border-radius: 6px;
        white-space: nowrap; box-shadow: 0 6px 16px rgba(0,0,0,.18); opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, transform .15s ease, visibility .15s ease; z-index: 60;
    }
    #lgPage [data-tooltip]::before {
        content: ''; position: absolute; bottom: calc(100% + 3px); left: 50%; transform: translateX(-50%);
        border: 5px solid transparent; border-top-color: #1F2937; opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, visibility .15s ease; z-index: 60;
    }
    #lgPage [data-tooltip]:hover::after, #lgPage [data-tooltip]:focus-visible::after { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); transition-delay: .25s; }
    #lgPage [data-tooltip]:hover::before, #lgPage [data-tooltip]:focus-visible::before { opacity: 1; visibility: visible; transition-delay: .25s; }
</style>
