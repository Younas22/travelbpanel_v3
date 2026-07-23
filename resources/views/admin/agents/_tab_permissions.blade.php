<form method="POST" action="{{ route('admin.agents.permissions.save', $agent) }}">
    @csrf

    <div class="as-card perm-card">
        <div class="perm-header">
            <h5><i class="bi bi-shield-check"></i> Agent Permissions</h5>
            <button type="submit" class="as-btn as-btn-primary">Save Permissions</button>
        </div>

        <div class="perm-grid">

            {{-- Wallet Permissions --}}
            <div>
                <div class="perm-section-label"><i class="bi bi-wallet2"></i> Wallet</div>
                @foreach([
                    'wallet.view'    => 'View Wallet Balance',
                    'wallet.request' => 'Request Wallet Top-up',
                ] as $key => $label)
                    <div class="perm-toggle-row">
                        <label class="perm-toggle-label" for="perm_{{ str_replace('.', '_', $key) }}">{{ $label }}</label>
                        <label class="perm-switch">
                            <input class="perm-switch-input" type="checkbox" name="permissions[{{ $key }}]" value="1"
                                   id="perm_{{ str_replace('.', '_', $key) }}"
                                {{ isset($agentPermissions[$key]) && $agentPermissions[$key] ? 'checked' : '' }}>
                            <span class="perm-switch-slider"></span>
                        </label>
                    </div>
                @endforeach
            </div>

            {{-- Booking Permissions --}}
            <div>
                <div class="perm-section-label"><i class="bi bi-calendar-check"></i> Bookings</div>
                @foreach([
                    'bookings.make' => 'Make Bookings (via Website)',
                    'bookings.view' => 'View Own Bookings',
                ] as $key => $label)
                    <div class="perm-toggle-row">
                        <label class="perm-toggle-label" for="perm_{{ str_replace('.', '_', $key) }}">{{ $label }}</label>
                        <label class="perm-switch">
                            <input class="perm-switch-input" type="checkbox" name="permissions[{{ $key }}]" value="1"
                                   id="perm_{{ str_replace('.', '_', $key) }}"
                                {{ isset($agentPermissions[$key]) && $agentPermissions[$key] ? 'checked' : '' }}>
                            <span class="perm-switch-slider"></span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="perm-sep"></div>

        {{-- Add Property Permissions --}}
        <div class="perm-section-label">
            Add Manual Properties
            <span class="perm-section-hint">— agent can add/edit/delete these from their panel</span>
        </div>

        <div class="perm-property-grid">
            @foreach([
                'hotels.add' => ['icon' => 'bi-building',     'label' => 'Add Hotels',         'desc' => 'Can add/edit/delete manual hotels'],
                'tours.add'  => ['icon' => 'bi-map',          'label' => 'Add Tour Packages',  'desc' => 'Can add/edit/delete tour packages'],
                'umrah.add'  => ['icon' => 'bi-moon-stars',   'label' => 'Add Umrah Packages', 'desc' => 'Can add/edit/delete umrah packages'],
            ] as $key => $perm)
                @php $isActive = isset($agentPermissions[$key]) && $agentPermissions[$key]; @endphp
                <div class="perm-property-card {{ $isActive ? 'perm-property-active' : '' }}" data-perm-card>
                    <div class="perm-property-head">
                        <span class="perm-property-title">
                            <i class="bi {{ $perm['icon'] }}"></i> {{ $perm['label'] }}
                        </span>
                        <label class="perm-switch">
                            <input class="perm-switch-input" type="checkbox" name="permissions[{{ $key }}]" value="1"
                                   id="perm_{{ str_replace('.', '_', $key) }}"
                                   data-perm-toggle
                                {{ $isActive ? 'checked' : '' }}>
                            <span class="perm-switch-slider"></span>
                        </label>
                    </div>
                    <div class="perm-property-desc">{{ $perm['desc'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</form>

@push('scripts')
    <script>
        document.querySelectorAll('[data-perm-toggle]').forEach(function (input) {
            input.addEventListener('change', function () {
                const card = this.closest('[data-perm-card]');
                if (card) card.classList.toggle('perm-property-active', this.checked);
            });
        });
    </script>
@endpush
