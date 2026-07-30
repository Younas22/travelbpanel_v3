{{-- Shared Tailwind CDN + scoped reset for every admin-nova/agents/* page
     (create/edit/show/permissions/wallet/transactions) — same fixed Plus
     Jakarta Sans / palette / radius design language as agents/index.blade.php,
     just pulled into one include so the newer pages don't each redeclare the
     same config block. Included via @push('styles') by each page. --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: { preflight: false },
        theme: {
            extend: {
                fontFamily: { jakarta: ['Plus Jakarta Sans', 'sans-serif'] },
                colors: {
                    novabg: '#F7F8FC',
                    novablue: '#2563EB',
                    novacyan: '#06B6D4',
                    novatext: '#000000',
                    novamuted: '#000000',
                    novaborder: '#E5E7EB',
                    novasuccess: '#22C55E',
                    novawarning: '#F59E0B',
                    novadanger: '#EF4444',
                },
            },
        },
    };
</script>
<style>
    #agPage, #agPage *, #agPage *::before, #agPage *::after { box-sizing: border-box; }
    #agPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #agPage h1, #agPage h2, #agPage h3, #agPage h4, #agPage h5, #agPage p { margin: 0; padding: 0; }
    #agPage a { text-decoration: none; color: inherit; }
    #agPage button { font: inherit; color: inherit; }
    #agPage svg { display: block; }

    #agPage .tt-fade-in { animation: agFadeIn .5s ease both; }
    @keyframes agFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    #agPage .tt-card { transition: box-shadow .3s ease; }
    #agPage .tt-row { transition: background .2s ease, border-color .2s ease, transform .2s ease; }
    #agPage .tt-row:hover { background: #F7F8FC; border-color: #DBEAFE; transform: translateX(2px); }

    /* Plain CSS backing for every pill/icon button — Tailwind CDN utility
       classes compile at runtime and have a window where they simply aren't
       generated yet, leaving buttons shapeless/colorless. These classes give
       a correct baseline appearance immediately; Tailwind layers on top. */
    #agPage .ag-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000; background: #fff;
        white-space: nowrap; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif;
        transition: background .2s ease, border-color .2s ease;
    }
    #agPage .ag-btn-nova:hover { background: #F7F8FC; }
    #agPage .ag-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
    #agPage .ag-btn-primary:hover { background: #1D4ED8; border-color: #1D4ED8; }
    #agPage .ag-btn-danger { background: #EF4444; color: #fff; border-color: #EF4444; }
    #agPage .ag-btn-danger:hover { background: #DC2626; border-color: #DC2626; }
    #agPage .ag-icon-btn {
        width: 30px; height: 30px; border-radius: 9999px; border: 1px solid #E5E7EB;
        display: inline-flex; align-items: center; justify-content: center; background: #fff; cursor: pointer;
        transition: background .2s ease, border-color .2s ease;
    }
    #agPage .ag-icon-btn:hover { background: #F7F8FC; }
    #agPage .ag-icon-approve:hover { background: #ECFDF5; border-color: #22C55E; color: #22C55E; }
    #agPage .ag-icon-suspend:hover { background: #FFFBEB; border-color: #F59E0B; color: #F59E0B; }
    #agPage .ag-icon-danger:hover { background: #FEF2F2; border-color: #EF4444; color: #EF4444; }

    /* Material-style hover tooltip — replaces the native browser title=""
       tooltip (which is unstyled, slow, and inconsistent across browsers).
       Put the label in data-tooltip instead of title so only this custom
       tooltip shows, not both. Small dark pill above the trigger with a
       short show-delay and a fade + rise-in transition. */
    #agPage [data-tooltip] { position: relative; }
    #agPage [data-tooltip]::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%) translateY(4px);
        background: #1F2937;
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        line-height: 1;
        padding: 6px 10px;
        border-radius: 6px;
        white-space: nowrap;
        box-shadow: 0 6px 16px rgba(0,0,0,.18);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .15s ease, transform .15s ease, visibility .15s ease;
        z-index: 60;
    }
    #agPage [data-tooltip]::before {
        content: '';
        position: absolute;
        bottom: calc(100% + 3px);
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #1F2937;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .15s ease, visibility .15s ease;
        z-index: 60;
    }
    #agPage [data-tooltip]:hover::after, #agPage [data-tooltip]:focus-visible::after {
        opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); transition-delay: .25s;
    }
    #agPage [data-tooltip]:hover::before, #agPage [data-tooltip]:focus-visible::before {
        opacity: 1; visibility: visible; transition-delay: .25s;
    }

    /* Form fields — plain CSS so inputs are always pill/rounded and bordered
       correctly even if Tailwind's utilities haven't compiled yet. */
    #agPage .ag-label { display: block; font-size: 11px; font-weight: 600; color: #000; margin-bottom: 6px; }
    #agPage .ag-input, #agPage select.ag-input, #agPage textarea.ag-input {
        width: 100%; border: 1px solid #E5E7EB; border-radius: 14px; padding: 10px 14px;
        font-size: 13px; font-family: 'Plus Jakarta Sans', sans-serif; color: #000; background: #fff;
        transition: border-color .2s ease;
    }
    #agPage .ag-input:focus { outline: none; border-color: #2563EB; }
    #agPage textarea.ag-input { border-radius: 16px; resize: vertical; }
    #agPage .ag-field-help { font-size: 11px; color: #6B7280; margin-top: 4px; }
    #agPage .ag-required { color: #EF4444; }

    /* iOS-style toggle switch, reused for permission toggles. */
    #agPage .ag-switch { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
    #agPage .ag-switch input { opacity: 0; width: 0; height: 0; }
    #agPage .ag-switch .ag-slider {
        position: absolute; inset: 0; background: #E5E7EB; border-radius: 9999px; cursor: pointer;
        transition: background .25s ease;
    }
    #agPage .ag-switch .ag-slider::before {
        content: ''; position: absolute; width: 16px; height: 16px; left: 3px; top: 3px;
        background: #fff; border-radius: 50%; transition: transform .25s ease; box-shadow: 0 1px 3px rgba(0,0,0,.25);
    }
    #agPage .ag-switch input:checked + .ag-slider { background: #2563EB; }
    #agPage .ag-switch input:checked + .ag-slider::before { transform: translateX(18px); }

    /* Bootstrap nav-tabs (used on show.blade.php) restyled as Nova pills. */
    #agPage .ag-tabs { display: flex; flex-wrap: wrap; gap: 6px; border: none; margin-bottom: 20px; }
    #agPage .ag-tab {
        display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 9999px;
        font-size: 12px; font-weight: 600; color: #000; border: 1px solid #E5E7EB; background: #fff;
        transition: background .2s ease, color .2s ease, border-color .2s ease;
    }
    #agPage .ag-tab:hover { background: #F7F8FC; }
    #agPage .ag-tab.active { background: #2563EB !important; color: #fff !important; border-color: #2563EB !important; }

    #agPage .ag-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    #agPage .ag-table th {
        text-align: left; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
        color: #000; padding: 10px 12px; border-bottom: 1px solid #E5E7EB; white-space: nowrap;
    }
    #agPage .ag-table td { padding: 12px; border-bottom: 1px solid #F3F4F6; vertical-align: middle; }
    #agPage .ag-table tr:last-child td { border-bottom: none; }

    #agPage .pagination { gap: 4px; }
    #agPage .page-link { border-radius: 9999px !important; }
</style>
