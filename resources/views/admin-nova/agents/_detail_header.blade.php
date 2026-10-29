{{-- Profile header + tabs shared by the agent Overview (show) and Permissions pages.
     Expects: $agent, $activeTab ('overview'|'permissions'|'bookings'),
     $inPageTabs (true on show.blade.php: Overview/Permissions/Bookings switch Bootstrap panes). --}}
@php
    $initials = collect(explode(' ', $agent->full_name))->filter()->map(fn ($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') ?: '?';
    $inPageTabs = $inPageTabs ?? false;
    $detailTabs = [
        'overview'    => ['label' => 'Overview', 'icon' => 'bi-person', 'url' => route('admin.agents.show', $agent), 'pane' => true],
        'permissions' => ['label' => 'Permissions', 'icon' => 'bi-shield', 'url' => route('admin.agents.permissions', $agent), 'pane' => true],
        'wallet'      => ['label' => 'Wallet', 'icon' => 'bi-wallet2', 'url' => route('admin.agents.wallet', $agent), 'pane' => false],
        'bookings'    => ['label' => 'Bookings', 'icon' => 'bi-calendar3', 'url' => route('admin.agents.show', $agent) . '#bookings', 'pane' => true],
    ];
@endphp
<div class="tbp-agent-profile-header">
    <div class="tbp-agent-profile-main">
        <span class="tbp-agent-profile-avatar">{{ $initials }}</span>
        <div class="tbp-agent-profile-text">
            <div class="tbp-agent-profile-name">
                <h1>{{ $agent->full_name }}</h1>
                <span class="tbp-agent-status is-{{ $agent->approval_status }}">{{ ucfirst($agent->approval_status ?? 'unknown') }}</span>
            </div>
            <p>{{ $agent->agent_code }} · {{ $agent->email }}</p>
        </div>
    </div>
    <div class="tbp-agent-profile-actions">
        <a href="{{ route('admin.agents.edit', $agent) }}" class="tbp-agent-btn"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('admin.agents.index') }}" class="tbp-agent-btn"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<nav class="tbp-agent-navigation-tabs" role="tablist">
    @foreach($detailTabs as $key => $tab)
        @if($inPageTabs && $tab['pane'])
            <a class="tbp-agent-tab {{ $activeTab === $key ? 'is-active active' : '' }}" id="{{ $key }}-tab" data-bs-toggle="tab"
               href="#{{ $key }}" role="tab" aria-controls="{{ $key }}" aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}">
                <i class="bi {{ $tab['icon'] }}"></i> {{ $tab['label'] }}
            </a>
        @else
            <a class="tbp-agent-tab {{ $activeTab === $key ? 'is-active' : '' }}" href="{{ $tab['url'] }}">
                <i class="bi {{ $tab['icon'] }}"></i> {{ $tab['label'] }}
            </a>
        @endif
    @endforeach
</nav>
