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

@push('styles')
    <style>
        /* ===== HEADER ===== */
        .perm-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: .85rem;
            border-bottom: 1px solid var(--bs-border-color);
            margin-bottom: 1.25rem;
        }
        .perm-header h5 {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--bs-body-color);
            margin: 0;
        }
        .perm-header h5 i {
            font-size: 16px;
            color: var(--bs-primary);
        }

        /* ===== GRID ===== */
        .perm-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .perm-section-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--bs-secondary-color);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .perm-section-label i { font-size: 13px; }
        .perm-section-hint {
            font-weight: 400;
            text-transform: none;
            letter-spacing: normal;
            color: var(--bs-secondary-color);
            font-size: 12px;
            margin-left: 4px;
        }

        /* ===== TOGGLE ROWS ===== */
        .perm-toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--bs-border-color);
        }
        .perm-toggle-row:last-child { border-bottom: none; }
        .perm-toggle-label {
            font-size: 13px;
            font-weight: 500;
            color: var(--bs-body-color);
            cursor: pointer;
        }

        /* ===== SEPARATOR ===== */
        .perm-sep {
            height: 1px;
            background: var(--bs-border-color);
            margin: 1.5rem 0;
        }

        /* ===== PROPERTY CARDS ===== */
        .perm-property-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .perm-property-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 12px;
            padding: 1rem;
            background: var(--bs-body-bg);
            transition: border-color .15s, background .15s;
        }
        .perm-property-active {
            border-color: var(--bs-primary);
            background: #E6F1FB;
        }
        [data-bs-theme="dark"] .perm-property-active {
            background: #0c2f4d;
        }

        .perm-property-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .perm-property-title {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            font-weight: 600;
            color: var(--bs-body-color);
        }
        .perm-property-title i {
            font-size: 15px;
            color: var(--bs-primary);
        }
        .perm-property-desc {
            font-size: 12px;
            color: var(--bs-secondary-color);
            margin-top: 8px;
        }
        .perm-property-active .perm-property-desc { color: #0C447C; }
        [data-bs-theme="dark"] .perm-property-active .perm-property-desc { color: #7db8f0; }

        /* ===== TOGGLE SWITCH ===== */
        .perm-switch {
            position: relative;
            display: inline-block;
            width: 38px;
            height: 22px;
            flex-shrink: 0;
            cursor: pointer;
        }
        .perm-switch input {
            opacity: 0;
            width: 0;
            height: 0;
            position: absolute;
        }
        .perm-switch-slider {
            position: absolute;
            inset: 0;
            background: var(--bs-border-color);
            border-radius: 22px;
            transition: background .2s;
        }
        .perm-switch-slider::before {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            left: 3px;
            top: 3px;
            background: var(--bs-body-bg);
            border-radius: 50%;
            transition: transform .2s;
        }
        .perm-switch input:checked + .perm-switch-slider { background: #0C6DFD; }
        .perm-switch input:checked + .perm-switch-slider::before { transform: translateX(16px); }
        [data-bs-theme="dark"] .perm-switch input:checked + .perm-switch-slider { background: #97C459; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .perm-grid { grid-template-columns: 1fr; gap: 0; }
            .perm-property-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .perm-header { flex-direction: column; align-items: stretch; }
            .perm-header .as-btn { justify-content: center; }
        }
    </style>
@endpush

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
