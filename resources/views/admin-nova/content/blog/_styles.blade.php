{{-- Shared Tailwind CDN + scoped reset for every admin-nova/content/blog/* page. --}}
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
    #blPage, #blPage *, #blPage *::before, #blPage *::after { box-sizing: border-box; }
    #blPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #blPage h1, #blPage h2, #blPage h3, #blPage p { margin: 0; padding: 0; }
    #blPage a { text-decoration: none; color: inherit; }
    #blPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #blPage svg { display: block; }
    #blPage .tt-fade-in { animation: blFadeIn .5s ease both; }
    @keyframes blFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    #blPage .tt-row:hover { background: #F7F8FC; }

    #blPage .bl-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000; background: #fff;
        white-space: nowrap; transition: background .2s ease, border-color .2s ease;
    }
    #blPage .bl-btn-nova:hover { background: #F7F8FC; }
    #blPage .bl-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
    #blPage .bl-btn-primary:hover { background: #1D4ED8; border-color: #1D4ED8; }
    #blPage .bl-icon-btn {
        width: 30px; height: 30px; border-radius: 9999px; border: 1px solid #E5E7EB;
        display: inline-flex; align-items: center; justify-content: center; background: #fff;
        transition: background .2s ease, border-color .2s ease;
    }
    #blPage .bl-icon-btn:hover { background: #F7F8FC; }
    #blPage .bl-icon-success:hover { background: #ECFDF5; border-color: #22C55E; color: #22C55E; }
    #blPage .bl-icon-warn:hover { background: #FFFBEB; border-color: #F59E0B; color: #F59E0B; }
    #blPage .bl-icon-danger:hover { background: #FEF2F2; border-color: #EF4444; color: #EF4444; }

    #blPage .bl-label { display: block; font-size: 11px; font-weight: 600; color: #000; margin-bottom: 6px; }
    #blPage .bl-input, #blPage select.bl-input, #blPage textarea.bl-input {
        width: 100%; border: 1px solid #E5E7EB; border-radius: 14px; padding: 10px 14px;
        font-size: 13px; font-family: 'Plus Jakarta Sans', sans-serif; color: #000; background: #fff;
    }
    #blPage .bl-input:focus { outline: none; border-color: #2563EB; }
    #blPage textarea.bl-input { border-radius: 16px; resize: vertical; }
    #blPage .bl-required { color: #EF4444; }
    #blPage .bl-help { font-size: 11px; color: #6B7280; margin-top: 4px; }
    #blPage .bl-error { font-size: 11px; color: #EF4444; margin-top: 4px; }

    #blPage .bl-switch { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
    #blPage .bl-switch input { opacity: 0; width: 0; height: 0; }
    #blPage .bl-switch .bl-slider {
        position: absolute; inset: 0; background: #E5E7EB; border-radius: 9999px; cursor: pointer;
        transition: background .25s ease;
    }
    #blPage .bl-switch .bl-slider::before {
        content: ''; position: absolute; width: 16px; height: 16px; left: 3px; top: 3px;
        background: #fff; border-radius: 50%; transition: transform .25s ease; box-shadow: 0 1px 3px rgba(0,0,0,.25);
    }
    #blPage .bl-switch input:checked + .bl-slider { background: #2563EB; }
    #blPage .bl-switch input:checked + .bl-slider::before { transform: translateX(18px); }

    #blPage .bl-dropzone {
        border: 2px dashed #E5E7EB; border-radius: 16px; padding: 24px; text-align: center; cursor: pointer;
        transition: border-color .2s ease, background .2s ease;
    }
    #blPage .bl-dropzone:hover { border-color: #2563EB; background: #F7F8FC; }

    #blPage .bl-bulk-only { display: none; }
    #blPage .bl-bulk-only.bl-show { display: inline-flex; }

    /* CKEditor container re-skin. */
    #blPage .ck.ck-editor__main>.ck-editor__editable { border-radius: 0 0 14px 14px !important; border-color: #E5E7EB !important; }
    #blPage .ck.ck-toolbar { border-radius: 14px 14px 0 0 !important; border-color: #E5E7EB !important; background: #F7F8FC !important; }
    #blPage .ck.ck-editor__editable.ck-focused { border-color: #2563EB !important; box-shadow: none !important; }

    #blPage [data-tooltip] { position: relative; }
    #blPage [data-tooltip]::after {
        content: attr(data-tooltip); position: absolute; bottom: calc(100% + 8px); left: 50%;
        transform: translateX(-50%) translateY(4px); background: #1F2937; color: #fff;
        font-size: 11px; font-weight: 600; line-height: 1; padding: 6px 10px; border-radius: 6px;
        white-space: nowrap; box-shadow: 0 6px 16px rgba(0,0,0,.18); opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, transform .15s ease, visibility .15s ease; z-index: 60;
    }
    #blPage [data-tooltip]::before {
        content: ''; position: absolute; bottom: calc(100% + 3px); left: 50%; transform: translateX(-50%);
        border: 5px solid transparent; border-top-color: #1F2937; opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, visibility .15s ease; z-index: 60;
    }
    #blPage [data-tooltip]:hover::after, #blPage [data-tooltip]:focus-visible::after { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); transition-delay: .25s; }
    #blPage [data-tooltip]:hover::before, #blPage [data-tooltip]:focus-visible::before { opacity: 1; visibility: visible; transition-delay: .25s; }

    #blPage .pagination { gap: 4px; }
    #blPage .page-link { border-radius: 9999px !important; }
</style>
