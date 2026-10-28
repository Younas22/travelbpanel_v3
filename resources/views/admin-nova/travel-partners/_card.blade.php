{{-- Expects: $partner, $module, $isManual, $modIcon, $modKey, $displayName, $isConnected, $logoUrl, $avatar — from index.blade.php. --}}
<div class="tbp-supplier-integrations-card"
     data-module-id="{{ $module->id }}"
     data-supplier-type="{{ $isManual ? 'manual' : 'api' }}"
     data-connected="{{ $isConnected ? '1' : '0' }}"
     data-name="{{ strtolower($displayName) }}"
     data-commission="{{ (float) $partner->commission_rate }}"
     data-created="{{ optional($partner->created_at)->timestamp ?? 0 }}">

    <div class="tbp-supplier-integrations-card-header">
        @if($logoUrl)
            <div class="tbp-supplier-integrations-logo"><img src="{{ $logoUrl }}" alt="{{ $displayName }}"></div>
        @else
            <div class="tbp-supplier-integrations-logo is-initials" style="background:{{ $avatar['bg'] }};color:{{ $avatar['text'] }};"
                 title="{{ $isManual ? 'Added manually — no third-party API connected' : $displayName }}">
                {{ strtoupper(substr($displayName, 0, 2)) }}
            </div>
        @endif
        <div class="tbp-supplier-integrations-name-wrap">
            <div class="tbp-supplier-integrations-name" title="{{ $displayName }}">{{ $displayName }}</div>
            <div class="tbp-supplier-integrations-type">{{ $isManual ? 'Manual entry' : $displayName.' API' }}</div>
        </div>
        <label class="tbp-supplier-integrations-switch" title="{{ $partner->status === 'active' ? 'Active' : 'Inactive' }}">
            <input type="checkbox" class="partner-switch-input" data-partner-id="{{ $partner->id }}" data-module-id="{{ $module->id }}"
                   {{ $partner->status === 'active' ? 'checked' : '' }}
                   onchange="togglePartnerStatus(event, {{ $partner->id }}, {{ $module->id }})">
            <span></span>
        </label>
    </div>

    <div class="tbp-supplier-integrations-badges">
        <span class="tbp-supplier-integrations-badge tbp-supplier-integrations-badge-{{ $modKey }}"><i class="bi {{ $modIcon }}"></i> {{ $module->name }}</span>
        @if($isManual)
            <span class="tbp-supplier-integrations-badge tbp-supplier-integrations-badge-neutral">Manual</span>
        @else
            <span class="tbp-supplier-integrations-badge {{ $isConnected ? 'tbp-supplier-integrations-badge-connected' : 'tbp-supplier-integrations-badge-neutral' }}">{{ $isConnected ? 'Connected' : 'Disconnected' }}</span>
            <span class="tbp-supplier-integrations-badge {{ $partner->development_mode ? 'tbp-supplier-integrations-badge-sandbox' : 'tbp-supplier-integrations-badge-live' }}">{{ $partner->development_mode ? 'Sandbox' : 'Live' }}</span>
        @endif
    </div>

    <div class="tbp-supplier-integrations-commission">
        <span>Commission</span>
        <span>{{ !$isManual && $partner->commission_rate !== null ? number_format($partner->commission_rate, 1).'%' : '-' }}</span>
    </div>

    @if($module->id != 3)
        <a href="{{ route('admin.travel-partners.edit', $partner->id) }}" class="tbp-supplier-integrations-edit-button">
            <i class="bi bi-pencil"></i> Edit
        </a>
    @endif
</div>
