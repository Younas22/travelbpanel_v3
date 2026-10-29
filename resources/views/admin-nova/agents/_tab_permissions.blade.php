{{-- Expects: $agent, $agentPermissions (AgentPermission::getAgentPermissions()).
     Shared by show.blade.php's Permissions tab and the standalone permissions page.
     module.* keys default to enabled until saved (same rule as AgentPermission::hasPermission). --}}
@php
    $permGroups = [
        ['title' => 'Core Travel Modules', 'desc' => 'Control access to main travel services and modules.', 'icon' => 'bi-airplane', 'items' => [
            'module.hotels'  => ['bi-building', 'Access hotel search, booking and management'],
            'module.flights' => ['bi-airplane', 'Access flight search, booking and management'],
            'module.tours'   => ['bi-map', 'Access tour packages and booking'],
            'module.umrah'   => ['bi-moon-stars', 'Access umrah packages and booking'],
            'module.visa'    => ['bi-passport', 'Access visa services and booking'],
        ]],
        ['title' => 'Wallet & Financial', 'desc' => 'Control access to wallet and financial features.', 'icon' => 'bi-wallet2', 'items' => [
            'wallet.view'         => ['bi-wallet', 'Allow agent to view wallet balance'],
            'wallet.request'      => ['bi-cash-coin', 'Allow agent to request wallet top-up'],
            'wallet.transactions' => ['bi-receipt', 'Allow agent to view wallet transactions'],
            'wallet.invoices'     => ['bi-download', 'Allow agent to download invoices'],
        ]],
        ['title' => 'Booking Management', 'desc' => 'Control booking creation and management access.', 'icon' => 'bi-calendar3', 'items' => [
            'bookings.make' => ['bi-file-earmark-plus', 'Allow agent to make bookings through the website'],
            'bookings.view' => ['bi-file-earmark-text', 'Allow agent to view and manage their own bookings'],
        ]],
        ['title' => 'Manual Properties', 'desc' => 'Allow agent to manage their own manual properties.', 'icon' => 'bi-gear', 'items' => [
            'hotels.add' => ['bi-building-add', 'Can add/edit/delete manual hotels'],
            'tours.add'  => ['bi-map', 'Can add/edit/delete tour packages'],
            'umrah.add'  => ['bi-moon-stars', 'Can add/edit/delete umrah packages'],
        ]],
    ];
    $permEnabled = fn ($key) => str_starts_with($key, 'module.')
        ? (!isset($agentPermissions[$key]) || $agentPermissions[$key])
        : (isset($agentPermissions[$key]) && $agentPermissions[$key]);
    $permTotal = collect($permGroups)->sum(fn ($g) => count($g['items']));
    $permOn = collect($permGroups)->flatMap(fn ($g) => array_keys($g['items']))->filter($permEnabled)->count();
    $permColumns = [[$permGroups[0], $permGroups[2]], [$permGroups[1], $permGroups[3]]];
@endphp
<form method="POST" action="{{ route('admin.agents.permissions.save', $agent) }}" class="tbp-agent-permissions-page" id="tbpAgentPermForm">
    @csrf

    <div class="tbp-agent-card">
        <div class="tbp-agent-permissions-header">
            <span class="tbp-agent-icon-box"><i class="bi bi-shield"></i></span>
            <div>
                <h2>Agent Permissions</h2>
                <p>Manage which features and modules this agent can access.</p>
            </div>
            <button type="submit" class="tbp-agent-btn is-primary tbp-agent-save-permissions"><i class="bi bi-floppy"></i> <span>Save Permissions</span></button>
        </div>

        <div class="tbp-agent-permissions-summary">
            <div class="tbp-agent-summary-card">
                <span class="tbp-agent-icon-box"><i class="bi bi-grid"></i></span>
                <div><small>Total Modules</small><strong data-perm-total>{{ $permTotal }}</strong><em>All modules</em></div>
            </div>
            <div class="tbp-agent-summary-card">
                <span class="tbp-agent-icon-box"><i class="bi bi-check-circle"></i></span>
                <div><small>Enabled Modules</small><strong data-perm-on>{{ $permOn }}</strong><em>Currently enabled</em></div>
            </div>
            <div class="tbp-agent-summary-card">
                <span class="tbp-agent-icon-box"><i class="bi bi-x-lg"></i></span>
                <div><small>Disabled Modules</small><strong data-perm-off>{{ $permTotal - $permOn }}</strong><em>Currently disabled</em></div>
            </div>
        </div>
    </div>

    <div class="tbp-agent-permission-groups">
        @foreach($permColumns as $column)
            <div class="tbp-agent-permission-column">
                @foreach($column as $group)
                    <section class="tbp-agent-card tbp-agent-permission-group">
                        <header>
                            <i class="bi {{ $group['icon'] }}"></i>
                            <div><h3>{{ $group['title'] }}</h3><p>{{ $group['desc'] }}</p></div>
                        </header>
                        @foreach($group['items'] as $key => [$icon, $desc])
                            @php $id = 'perm_' . str_replace('.', '_', $key); @endphp
                            <div class="tbp-agent-permission-row">
                                <i class="bi {{ $icon }}"></i>
                                <label for="{{ $id }}"><strong>{{ \App\Models\AgentPermission::ALL_PERMISSIONS[$key] }}</strong><span>{{ $desc }}</span></label>
                                <span class="tbp-agent-permission-toggle">
                                    <input type="checkbox" name="permissions[{{ $key }}]" value="1" id="{{ $id }}" {{ $permEnabled($key) ? 'checked' : '' }}>
                                    <span aria-hidden="true"></span>
                                </span>
                            </div>
                        @endforeach
                    </section>
                @endforeach
            </div>
        @endforeach
    </div>
</form>

@push('scripts')
<script>
(function () {
    var form = document.getElementById('tbpAgentPermForm');
    if (!form) return;
    var boxes = form.querySelectorAll('.tbp-agent-permission-toggle input');
    function recount() {
        var on = Array.prototype.filter.call(boxes, function (b) { return b.checked; }).length;
        form.querySelector('[data-perm-on]').textContent = on;
        form.querySelector('[data-perm-off]').textContent = boxes.length - on;
    }
    boxes.forEach(function (b) { b.addEventListener('change', recount); });
    form.addEventListener('submit', function () {
        var btn = form.querySelector('.tbp-agent-save-permissions');
        btn.disabled = true;
        btn.querySelector('span').textContent = 'Saving…';
    });
})();
</script>
@endpush
