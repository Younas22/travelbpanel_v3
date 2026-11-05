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
