{{-- Shared Tailwind CDN + scoped reset + form-field CSS for umrah/packages
     create.blade.php and edit.blade.php — same fixed Plus Jakarta Sans /
     palette / radius design language used across the rest of Nova. --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: { preflight: false },
        theme: {
            extend: {
                fontFamily: { jakarta: ['var(--font-family)'] },
                colors: {
                    novabg: 'var(--body-bg)', novablue: 'var(--primary-color)', novacyan: 'var(--info-color)',
                    novatext: 'var(--text-color)', novamuted: 'var(--text-color)', novaborder: 'var(--border-color)', novacard: 'var(--card-bg)',
                    novasuccess: 'var(--success-color)', novawarning: 'var(--warning-color)', novadanger: 'var(--danger-color)',
                },
            },
        },
    };
</script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    #umfPage, #umfPage *, #umfPage *::before, #umfPage *::after { box-sizing: border-box; }
    #umfPage { font-family: var(--font-family); }
    #umfPage h1, #umfPage h2, #umfPage p { margin: 0; padding: 0; }
    #umfPage a { text-decoration: none; color: inherit; }
    #umfPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #umfPage svg { display: block; }
    #umfPage .tt-fade-in { animation: umfFadeIn .5s ease both; }
    @keyframes umfFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    #umfPage .umf-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid var(--border-color); border-radius: 9999px; color: var(--text-color); background: var(--card-bg);
        white-space: nowrap; transition: background .2s ease, border-color .2s ease;
    }
    #umfPage .umf-btn-nova:hover { background: var(--body-bg); }
    #umfPage .umf-btn-primary { background: var(--primary-color); color: #fff; border-color: var(--primary-color); }
    #umfPage .umf-btn-primary:hover { background: color-mix(in srgb, var(--primary-color) 85%, #000); border-color: color-mix(in srgb, var(--primary-color) 85%, #000); }

    #umfPage .umf-label { display: block; font-size: 11px; font-weight: 600; color: var(--text-color); margin-bottom: 6px; }
    #umfPage .umf-input, #umfPage select.umf-input, #umfPage textarea.umf-input {
        width: 100%; border: 1px solid var(--border-color); border-radius: 14px; padding: 10px 14px;
        font-size: 13px; font-family: var(--font-family); color: var(--text-color); background: var(--card-bg);
    }
    #umfPage .umf-input:focus { outline: none; border-color: var(--primary-color); }
    #umfPage textarea.umf-input { border-radius: 16px; resize: vertical; }
    #umfPage .umf-required { color: var(--danger-color); }
    #umfPage .umf-checklist { border: 1px solid var(--border-color); border-radius: 14px; padding: 12px; max-height: 180px; overflow-y: auto; }
    #umfPage .umf-checklist label { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-color); cursor: pointer; padding: 4px 0; }

    /* select2 re-skin so the airport picker matches the rest of the Nova
       form fields instead of its own default blue-focus-ring look. */
    #umfPage .select2-container .select2-selection--single {
        height: auto; border: 1px solid var(--border-color); border-radius: 14px; padding: 8px 14px;
    }
    #umfPage .select2-container .select2-selection--single .select2-selection__rendered { padding: 0; font-size: 13px; color: var(--text-color); }
    #umfPage .select2-container .select2-selection--single .select2-selection__arrow { height: 100%; }
    #umfPage .select2-dropdown { border-radius: 14px; border-color: var(--border-color); overflow: hidden; }
    #umfPage .select2-container--default .select2-results__option--highlighted[aria-selected] { background: var(--primary-color); }
</style>
