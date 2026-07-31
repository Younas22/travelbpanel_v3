{{-- Shared Tailwind CDN + scoped reset for every admin-nova/currencies/*
     page — same fixed Plus Jakarta Sans / palette / radius design language
     as the rest of Nova. Included via @push('styles') by each page. --}}
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
    #crPage, #crPage *, #crPage *::before, #crPage *::after { box-sizing: border-box; }
    #crPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #crPage h1, #crPage h2, #crPage p { margin: 0; padding: 0; }
    #crPage a { text-decoration: none; color: inherit; }
    #crPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #crPage svg { display: block; }
    #crPage .tt-fade-in { animation: crFadeIn .5s ease both; }
    @keyframes crFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    #crPage .tt-row:hover { background: #F7F8FC; }

    #crPage .cr-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000; background: #fff;
        white-space: nowrap; transition: background .2s ease, border-color .2s ease;
    }
    #crPage .cr-btn-nova:hover { background: #F7F8FC; }
    #crPage .cr-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
    #crPage .cr-btn-primary:hover { background: #1D4ED8; border-color: #1D4ED8; }
    #crPage .cr-btn-danger { background: #EF4444; color: #fff; border-color: #EF4444; }
    #crPage .cr-icon-btn {
        width: 30px; height: 30px; border-radius: 9999px; border: 1px solid #E5E7EB;
        display: inline-flex; align-items: center; justify-content: center; background: #fff;
        transition: background .2s ease, border-color .2s ease;
    }
    #crPage .cr-icon-btn:hover { background: #F7F8FC; }
    #crPage .cr-icon-danger:hover { background: #FEF2F2; border-color: #EF4444; color: #EF4444; }

    #crPage .cr-label { display: block; font-size: 11px; font-weight: 600; color: #000; margin-bottom: 6px; }
    #crPage .cr-input, #crPage select.cr-input {
        width: 100%; border: 1px solid #E5E7EB; border-radius: 14px; padding: 10px 14px;
        font-size: 13px; font-family: 'Plus Jakarta Sans', sans-serif; color: #000; background: #fff;
    }
    #crPage .cr-input:focus { outline: none; border-color: #2563EB; }
    #crPage .cr-input.is-invalid { border-color: #EF4444; }
    #crPage .cr-error { font-size: 11px; color: #EF4444; margin-top: 4px; }
    #crPage .cr-required { color: #EF4444; }

    /* iOS-style toggle switch. */
    #crPage .cr-switch { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
    #crPage .cr-switch input { opacity: 0; width: 0; height: 0; }
    #crPage .cr-switch .cr-slider {
        position: absolute; inset: 0; background: #E5E7EB; border-radius: 9999px; cursor: pointer;
        transition: background .25s ease;
    }
    #crPage .cr-switch .cr-slider::before {
        content: ''; position: absolute; width: 16px; height: 16px; left: 3px; top: 3px;
        background: #fff; border-radius: 50%; transition: transform .25s ease; box-shadow: 0 1px 3px rgba(0,0,0,.25);
    }
    #crPage .cr-switch input:checked + .cr-slider { background: #2563EB; }
    #crPage .cr-switch input:checked + .cr-slider::before { transform: translateX(18px); }

    #crPage .cr-bulk-bar { display: none; }
    #crPage .cr-bulk-bar.cr-visible { display: flex; }

    #crPage [data-tooltip] { position: relative; }
    #crPage [data-tooltip]::after {
        content: attr(data-tooltip); position: absolute; bottom: calc(100% + 8px); left: 50%;
        transform: translateX(-50%) translateY(4px); background: #1F2937; color: #fff;
        font-size: 11px; font-weight: 600; line-height: 1; padding: 6px 10px; border-radius: 6px;
        white-space: nowrap; box-shadow: 0 6px 16px rgba(0,0,0,.18); opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, transform .15s ease, visibility .15s ease; z-index: 60;
    }
    #crPage [data-tooltip]::before {
        content: ''; position: absolute; bottom: calc(100% + 3px); left: 50%; transform: translateX(-50%);
        border: 5px solid transparent; border-top-color: #1F2937; opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, visibility .15s ease; z-index: 60;
    }
    #crPage [data-tooltip]:hover::after, #crPage [data-tooltip]:focus-visible::after { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); transition-delay: .25s; }
    #crPage [data-tooltip]:hover::before, #crPage [data-tooltip]:focus-visible::before { opacity: 1; visibility: visible; transition-delay: .25s; }

    #crPage .pagination { gap: 4px; }
    #crPage .page-link { border-radius: 9999px !important; }
</style>
