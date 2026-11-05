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
