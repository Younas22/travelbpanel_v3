{{-- Shared Tailwind CDN + scoped reset for every admin-nova/bookings/* page —
     identical setup to admin-nova/dashboard/index.blade.php (same fixed
     Plus Jakarta Sans / palette / radius design language), just scoped to
     #bkPage instead of #dashTT. Included via @push('styles') by each page. --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: { preflight: false },
        theme: {
            extend: {
                fontFamily: {
                    jakarta: ['Plus Jakarta Sans', 'sans-serif'],
                },
                colors: {
                    novabg: '#F7F8FC',
                    novablue: '#2563EB',
                    novablue2: '#3B82F6',
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
    #bkPage, #bkPage *, #bkPage *::before, #bkPage *::after { box-sizing: border-box; }
    #bkPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #bkPage h1, #bkPage h2, #bkPage h3, #bkPage h4, #bkPage h5, #bkPage h6,
    #bkPage p, #bkPage ul, #bkPage ol, #bkPage dl, #bkPage dd { margin: 0; padding: 0; }
    #bkPage ul, #bkPage ol { list-style: none; }
    #bkPage a { text-decoration: none; color: inherit; }
    #bkPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #bkPage svg { display: block; }

    #bkPage .tt-fade-in { animation: bkFadeIn .5s ease both; }
    @keyframes bkFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    #bkPage .tt-card { transition: box-shadow .3s cubic-bezier(.4,0,.2,1); }
    #bkPage .tt-row { transition: background .2s ease, border-color .2s ease, transform .2s ease; }
    #bkPage .tt-row:hover { background: #F7F8FC; border-color: #DBEAFE; transform: translateX(2px); }
    #bkPage .tt-btn { transition: background .2s ease, box-shadow .2s ease, transform .2s ease; }
    #bkPage .tt-btn:hover { transform: translateY(-1px); }

    #bkPage .bk-bulk-bar { display: none; }
    #bkPage .bk-bulk-bar.bk-visible { display: flex; }

    /* Plain CSS backing for the filter form's apply/reset buttons — the
       Tailwind CDN build compiles utility classes at runtime, so on a slow
       connection (or if cdn.tailwindcss.com is blocked by an ad-blocker)
       there's a window where bg-novablue/rounded-full/etc simply haven't
       been generated yet and the button has no shape or color at all. These
       two classes guarantee the button is always visibly a blue/white
       circle from first paint, Tailwind or not. */
    #bkPage .bk-filter-submit {
        width: 40px; height: 40px; border-radius: 9999px;
        background: #2563EB; color: #fff;
        display: flex; align-items: center; justify-content: center;
    }
    #bkPage .bk-filter-submit:hover { background: #1D4ED8; }
    #bkPage .bk-filter-reset {
        width: 40px; height: 40px; border-radius: 9999px;
        border: 1px solid #E5E7EB; color: #000;
        display: flex; align-items: center; justify-content: center;
    }
    #bkPage .bk-filter-reset:hover { background: #F7F8FC; }

    /* Bootstrap's default pagination markup ({{ $bookings->links('pagination::bootstrap-4') }})
       already reads the shared --primary-color/--text-color/--border-color/--radius-*
       tokens via admin-modern.css's own .page-item/.page-link rules, so it
       automatically re-skins to Nova's black-on-white palette + full radius
       with zero extra CSS here — this block only adds the rounded-full pill
       shape Nova's spec asks for everywhere else. */
    #bkPage .pagination { gap: 4px; }
    #bkPage .page-link { border-radius: 9999px !important; }
</style>
