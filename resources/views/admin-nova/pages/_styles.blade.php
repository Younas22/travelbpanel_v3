{{-- Shared Tailwind CDN + scoped reset for every admin-nova/pages/* page. --}}
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
    #pgPage, #pgPage *, #pgPage *::before, #pgPage *::after { box-sizing: border-box; }
    #pgPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #pgPage h1, #pgPage h2, #pgPage h3, #pgPage p { margin: 0; padding: 0; }
    #pgPage a { text-decoration: none; color: inherit; }
    #pgPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #pgPage svg { display: block; }
    #pgPage .tt-fade-in { animation: pgFadeIn .5s ease both; }
    @keyframes pgFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    #pgPage .tt-row:hover { background: #F7F8FC; }

    #pgPage .pg-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000; background: #fff;
        white-space: nowrap; transition: background .2s ease, border-color .2s ease;
    }
    #pgPage .pg-btn-nova:hover { background: #F7F8FC; }
    #pgPage .pg-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
    #pgPage .pg-btn-primary:hover { background: #1D4ED8; border-color: #1D4ED8; }
    #pgPage .pg-icon-btn {
        width: 30px; height: 30px; border-radius: 9999px; border: 1px solid #E5E7EB;
        display: inline-flex; align-items: center; justify-content: center; background: #fff;
        transition: background .2s ease, border-color .2s ease;
    }
    #pgPage .pg-icon-btn:hover { background: #F7F8FC; }
    #pgPage .pg-icon-warn:hover { background: #FFFBEB; border-color: #F59E0B; color: #F59E0B; }
    #pgPage .pg-icon-success:hover { background: #ECFDF5; border-color: #22C55E; color: #22C55E; }
    #pgPage .pg-icon-danger:hover { background: #FEF2F2; border-color: #EF4444; color: #EF4444; }

    #pgPage .pg-bulk-only { display: none; }
    #pgPage .pg-bulk-only.pg-show { display: inline-flex; }
    #pgPage .pg-bulk-bar { display: none; }
    #pgPage .pg-bulk-bar.pg-visible { display: flex; }

    #pgPage .pg-label { display: block; font-size: 11px; font-weight: 600; color: #000; margin-bottom: 6px; }
    #pgPage .pg-input, #pgPage select.pg-input, #pgPage textarea.pg-input {
        width: 100%; border: 1px solid #E5E7EB; border-radius: 14px; padding: 10px 14px;
        font-size: 13px; font-family: 'Plus Jakarta Sans', sans-serif; color: #000; background: #fff;
    }
    #pgPage .pg-input:focus { outline: none; border-color: #2563EB; }
    #pgPage textarea.pg-input { border-radius: 16px; resize: vertical; }
    #pgPage .pg-required { color: #EF4444; }
    #pgPage .pg-help { font-size: 11px; color: #6B7280; margin-top: 4px; }
    #pgPage .pg-error { font-size: 11px; color: #EF4444; margin-top: 4px; }

    #pgPage .pg-switch { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
    #pgPage .pg-switch input { opacity: 0; width: 0; height: 0; }
    #pgPage .pg-switch .pg-slider {
        position: absolute; inset: 0; background: #E5E7EB; border-radius: 9999px; cursor: pointer;
        transition: background .25s ease;
    }
    #pgPage .pg-switch .pg-slider::before {
        content: ''; position: absolute; width: 16px; height: 16px; left: 3px; top: 3px;
        background: #fff; border-radius: 50%; transition: transform .25s ease; box-shadow: 0 1px 3px rgba(0,0,0,.25);
    }
    #pgPage .pg-switch input:checked + .pg-slider { background: #2563EB; }
    #pgPage .pg-switch input:checked + .pg-slider::before { transform: translateX(18px); }

    /* Language tabs — Bootstrap tab JS kept (data-bs-toggle="tab"), pills restyled. */
    #pgPage .pg-tab { border-radius: 9999px; padding: 9px 18px; font-size: 12px; font-weight: 600; border: 1px solid #E5E7EB; background: #fff; color: #000; }
    #pgPage .pg-tab.active { background: #2563EB; border-color: #2563EB; color: #fff; }
    #pgPage .pg-tab:hover:not(.active) { background: #F7F8FC; }

    /* CKEditor container re-skin so the toolbar/editable area match the
       rounded, bordered Nova look instead of CKEditor's square default. */
    #pgPage .ck.ck-editor__main>.ck-editor__editable { border-radius: 0 0 14px 14px !important; border-color: #E5E7EB !important; }
    #pgPage .ck.ck-toolbar { border-radius: 14px 14px 0 0 !important; border-color: #E5E7EB !important; background: #F7F8FC !important; }
    #pgPage .ck.ck-editor__editable.ck-focused { border-color: #2563EB !important; box-shadow: none !important; }

    #pgPage [data-tooltip] { position: relative; }
    #pgPage [data-tooltip]::after {
        content: attr(data-tooltip); position: absolute; bottom: calc(100% + 8px); left: 50%;
        transform: translateX(-50%) translateY(4px); background: #1F2937; color: #fff;
        font-size: 11px; font-weight: 600; line-height: 1; padding: 6px 10px; border-radius: 6px;
        white-space: nowrap; box-shadow: 0 6px 16px rgba(0,0,0,.18); opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, transform .15s ease, visibility .15s ease; z-index: 60;
    }
    #pgPage [data-tooltip]::before {
        content: ''; position: absolute; bottom: calc(100% + 3px); left: 50%; transform: translateX(-50%);
        border: 5px solid transparent; border-top-color: #1F2937; opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, visibility .15s ease; z-index: 60;
    }
    #pgPage [data-tooltip]:hover::after, #pgPage [data-tooltip]:focus-visible::after { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); transition-delay: .25s; }
    #pgPage [data-tooltip]:hover::before, #pgPage [data-tooltip]:focus-visible::before { opacity: 1; visibility: visible; transition-delay: .25s; }

    #pgPage .pagination { gap: 4px; }
    #pgPage .page-link { border-radius: 9999px !important; }
</style>
