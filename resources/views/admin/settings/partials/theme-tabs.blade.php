{{-- Admin / Website switch at the top of Settings > Theme. Shared by the
     Classic, Modern and Nova theme pages; panes are [data-theme-pane]. --}}
@php $themeTab = request('tab') === 'website' ? 'website' : 'admin'; @endphp
<div class="theme-scope-tabs" role="tablist">
    <button type="button" class="theme-scope-tab {{ $themeTab === 'admin' ? 'active' : '' }}" data-theme-tab="admin" role="tab">
        <i class="bi bi-speedometer2"></i> Admin Panel
    </button>
    <button type="button" class="theme-scope-tab {{ $themeTab === 'website' ? 'active' : '' }}" data-theme-tab="website" role="tab">
        <i class="bi bi-globe2"></i> Website
    </button>
</div>

@push('styles')
<style>
    .theme-scope-tabs { display: inline-flex; gap: 4px; padding: 4px; margin-bottom: 20px; background: #fff; border: 1px solid #E5E7EB; border-radius: 9999px; }
    .theme-scope-tab { display: inline-flex; align-items: center; gap: 8px; padding: 8px 18px; border: 0; border-radius: 9999px; background: transparent; color: #374151; font-weight: 600; font-size: 13px; cursor: pointer; }
    .theme-scope-tab:hover { background: #F3F4F6; }
    .theme-scope-tab.active { background: var(--primary-color, #2563EB); color: #fff; }
    [data-theme-pane][hidden] { display: none !important; }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const tabs = document.querySelectorAll('[data-theme-tab]');
        const show = (name) => {
            tabs.forEach((t) => t.classList.toggle('active', t.dataset.themeTab === name));
            document.querySelectorAll('[data-theme-pane]').forEach((p) => { p.hidden = p.dataset.themePane !== name; });
            const url = new URL(window.location.href);
            if (name === 'website') url.searchParams.set('tab', 'website'); else url.searchParams.delete('tab');
            history.replaceState(null, '', url);
        };
        tabs.forEach((t) => t.addEventListener('click', () => show(t.dataset.themeTab)));
    })();
</script>
@endpush
