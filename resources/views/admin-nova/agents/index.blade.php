@extends('admin-nova.layouts.app')

@section('title', 'Agents Management')

@php
    $avatarPalette = [['#EDE9FE', '#6D28D9'], ['#DBEAFE', '#1D4ED8'], ['#DCFCE7', '#15803D'], ['#FCE7F3', '#BE185D'], ['#FEF3C7', '#B45309']];
    $statusLabels = ['active' => 'Active', 'pending' => 'Pending', 'suspended' => 'Suspended'];
    $kpis = [
        ['key' => 'total', 'label' => 'Total Agents', 'icon' => 'bi-people', 'tone' => 'blue'],
        ['key' => 'active', 'label' => 'Active Agents', 'icon' => 'bi-person-check', 'tone' => 'green'],
        ['key' => 'pending', 'label' => 'Pending Agents', 'icon' => 'bi-clock', 'tone' => 'amber'],
        ['key' => 'suspended', 'label' => 'Suspended Agents', 'icon' => 'bi-slash-circle', 'tone' => 'red'],
    ];
    $spark = function (array $values) {
        $n = count($values); $max = max($values ?: [0]); $min = min($values ?: [0]); $span = ($max - $min) ?: 1;
        $pts = [];
        foreach ($values as $i => $v) {
            $pts[] = [$n > 1 ? $i * 120 / ($n - 1) : 60, $max === $min ? 26 : 34 - ($v - $min) / $span * 28];
        }
        $d = 'M' . implode(' L', array_map(fn ($p) => round($p[0], 1) . ',' . round($p[1], 1), $pts));
        return ['line' => $d, 'area' => $d . ' L120,40 L0,40 Z'];
    };
    $tabs = ['' => ['All Agents', null], 'active' => ['Active', 'green'], 'pending' => ['Pending', 'amber'], 'suspended' => ['Suspended', 'red']];
    $currentStatus = (string) request('status', '');
    $exportUrl = route('admin.agents.export', request()->only(['search', 'status', 'company', 'joined', 'sort']));
@endphp

@section('content')
<div class="tbp-agents-page" id="tbpAgents">

    {{-- ============ HEADER ============ --}}
    <div class="tbp-agents-header">
        <div>
            <h1 class="tbp-agents-title">Agents Management</h1>
            <p class="tbp-agents-subtitle">Manage B2B travel agents, their permissions, commissions and wallet.</p>
        </div>
        <a href="{{ route('admin.agents.create') }}" class="tbp-agents-add-button"><i class="bi bi-plus-lg"></i> Add Agent</a>
    </div>

    {{-- ============ KPI CARDS ============ --}}
    <div class="tbp-agents-stats-grid">
        @foreach($kpis as $k)
            @php $t = $trends[$k['key']]; $sp = $spark($t['spark']); $chg = $t['change']; @endphp
            <div class="tbp-agents-stat-card tbp-agents-tone-{{ $k['tone'] }}">
                <div class="tbp-agents-stat-icon"><i class="bi {{ $k['icon'] }}"></i></div>
                <div class="tbp-agents-stat-body">
                    <div class="tbp-agents-stat-label">{{ $k['label'] }}</div>
                    <div class="tbp-agents-stat-value">{{ number_format($stats[$k['key']]) }}</div>
                    <div class="tbp-agents-stat-change {{ $chg === null || $chg > 0 ? 'is-up' : ($chg < 0 ? 'is-down' : 'is-flat') }}"
                         title="New agents in the last 30 days compared with the 30 days before ({{ $t['new'] }} new)">
                        @if($chg === null)
                            <i class="bi bi-arrow-up"></i> New
                        @else
                            <i class="bi {{ $chg < 0 ? 'bi-arrow-down' : 'bi-arrow-up' }}"></i> {{ $chg > 0 ? '+' : '' }}{{ number_format($chg, 1) }}%
                        @endif
                    </div>
                </div>
                <svg class="tbp-agents-sparkline" viewBox="0 0 120 40" preserveAspectRatio="none" aria-hidden="true">
                    <path d="{{ $sp['area'] }}" class="tbp-agents-sparkline-area"/>
                    <path d="{{ $sp['line'] }}" class="tbp-agents-sparkline-line"/>
                </svg>
            </div>
        @endforeach
    </div>

    {{-- ============ FILTERS (server-side GET) ============ --}}
    <form method="GET" action="{{ route('admin.agents.index') }}" class="tbp-agents-filters" id="tbpAgentsFilters">
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        <label class="tbp-agents-field tbp-agents-field-search">
            <span>Search Agents</span>
            <span class="tbp-agents-search">
                <i class="bi bi-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, company, code...">
            </span>
        </label>
        <label class="tbp-agents-field">
            <span>Status</span>
            <select name="status">
                <option value="">All status</option>
                @foreach($statusLabels as $val => $label)
                    <option value="{{ $val }}" {{ $currentStatus === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="tbp-agents-field">
            <span>Company</span>
            <select name="company">
                <option value="">All companies</option>
                @foreach($companies as $company)
                    <option value="{{ $company }}" {{ request('company') === $company ? 'selected' : '' }}>{{ $company }}</option>
                @endforeach
            </select>
        </label>
        <label class="tbp-agents-field">
            <span>Date Joined</span>
            <span class="tbp-agents-date">
                <i class="bi bi-calendar3"></i>
                <input type="date" name="joined" value="{{ request('joined') }}" title="Show agents who joined on or after this date">
            </span>
        </label>
        <div class="tbp-agents-filter-actions">
            <button type="submit" class="tbp-agents-filter-btn"><i class="bi bi-funnel"></i> Filter</button>
            <a href="{{ route('admin.agents.index') }}" class="tbp-agents-reset-btn" title="Reset filters"><i class="bi bi-arrow-clockwise"></i></a>
        </div>
    </form>

    {{-- ============ TABS + TABLE ============ --}}
    <div class="tbp-agents-table-container">
        <div class="tbp-agents-toolbar">
            <div class="tbp-agents-status-tabs" role="tablist">
                @foreach($tabs as $val => [$label, $tone])
                    <a href="{{ request()->fullUrlWithQuery(['status' => $val ?: null, 'page' => null]) }}"
                       class="{{ $currentStatus === $val ? 'is-active' : '' }}" role="tab">
                        @if($tone)<span class="tbp-agents-dot tbp-agents-dot-{{ $tone }}"></span>@else<i class="bi bi-people"></i>@endif
                        {{ $label }}
                        <span class="tbp-agents-tab-count">{{ $tabCounts[$val] }}</span>
                    </a>
                @endforeach
            </div>
            <div class="tbp-agents-toolbar-right">
                <a href="{{ $exportUrl }}" class="tbp-agents-export-btn" id="tbpAgentsExport" data-base="{{ $exportUrl }}">
                    <i class="bi bi-download"></i> <span id="tbpAgentsExportLabel">Export</span>
                </a>
                <label class="tbp-agents-sort">
                    Sort by
                    <select id="tbpAgentsSort">
                        @foreach($sorts as $key => $label)
                            <option value="{{ request()->fullUrlWithQuery(['sort' => $key, 'page' => null]) }}" {{ request('sort', 'newest') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>

        <div class="tbp-agents-table-scroll">
            <table class="tbp-agents-table">
                <thead>
                    <tr>
                        <th class="tbp-agents-check-col"><input type="checkbox" id="tbpAgentsCheckAll" aria-label="Select all agents"></th>
                        <th>Agent</th><th>Company</th><th>Status</th><th>Wallet</th><th>Commission</th><th>Joined</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agents as $agent)
                        @php
                            $initials = collect(explode(' ', $agent->full_name))->filter()->map(fn ($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') ?: '?';
                            [$avBg, $avFg] = $avatarPalette[crc32($agent->full_name . $agent->id) % count($avatarPalette)];
                            $status = $agent->approval_status;
                            $walletCur = $agent->wallet ? (($agent->wallet->currency && $agent->wallet->currency !== 'PKR') ? $agent->wallet->currency : 'USD') : null;
                        @endphp
                        <tr class="tbp-agents-row">
                            <td class="tbp-agents-check-col"><input type="checkbox" class="tbp-agents-check" value="{{ $agent->id }}" aria-label="Select {{ $agent->full_name }}"></td>
                            <td>
                                <div class="tbp-agents-agent">
                                    <span class="tbp-agents-avatar" style="background:{{ $avBg }};color:{{ $avFg }}">{{ $initials }}</span>
                                    <span class="tbp-agents-agent-text">
                                        <strong>{{ $agent->full_name }}</strong>
                                        <small>{{ $agent->email }}</small>
                                        @if($agent->agent_code)<span class="tbp-agents-code">#{{ $agent->agent_code }}</span>@endif
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="tbp-agents-company">
                                    <span class="tbp-agents-company-icon"><i class="bi bi-building"></i></span>
                                    <span>{{ $agent->company_name ?: '—' }}</span>
                                </span>
                            </td>
                            <td><span class="tbp-agents-status-badge is-{{ $status }}">{{ $statusLabels[$status] ?? ucfirst($status) }}</span></td>
                            <td class="tbp-agents-strong">{{ $walletCur ? $walletCur . ' ' . number_format($agent->wallet->balance, 0) : '—' }}</td>
                            <td class="tbp-agents-strong">{{ $agent->commission_rate !== null ? number_format($agent->commission_rate, 2) . '%' : '0%' }}</td>
                            <td><span class="tbp-agents-joined"><i class="bi bi-calendar3"></i> {{ $agent->created_at->format('d M Y') }}</span></td>
                            <td>
                                <div class="tbp-agents-actions">
                                    <a href="{{ route('admin.agents.show', $agent) }}" title="View"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('admin.agents.edit', $agent) }}" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <a href="{{ route('admin.agents.permissions', $agent) }}" title="Permissions"><i class="bi bi-shield"></i></a>
                                    <a href="{{ route('admin.agents.wallet', $agent) }}" title="Wallet"><i class="bi bi-wallet2"></i></a>
                                    @if($status === 'pending')
                                        <form method="POST" action="{{ route('admin.agents.approve', $agent) }}" onsubmit="return confirm('Approve {{ addslashes($agent->full_name) }}?');">
                                            @csrf
                                            <button type="submit" class="tbp-agents-label-btn is-approve"><i class="bi bi-check-lg"></i> Approve</button>
                                        </form>
                                    @elseif($status === 'active')
                                        <button type="button" class="tbp-agents-label-btn is-suspend" data-bs-toggle="modal" data-bs-target="#suspendModal{{ $agent->id }}"><i class="bi bi-pause-fill"></i> Suspend</button>
                                    @elseif($status === 'suspended')
                                        <form method="POST" action="{{ route('admin.agents.activate', $agent) }}" onsubmit="return confirm('Activate {{ addslashes($agent->full_name) }}?');">
                                            @csrf
                                            <button type="submit" class="tbp-agents-label-btn is-approve"><i class="bi bi-play-fill"></i> Activate</button>
                                        </form>
                                    @endif
                                    <button type="button" class="is-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $agent->id }}"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="tbp-agents-empty"><i class="bi bi-people"></i><strong>No agents found</strong><span>Try adjusting your search or filters.</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="tbp-agents-pagination">
            <span>
                @if($agents->total())
                    Showing {{ $agents->firstItem() }} to {{ $agents->lastItem() }} of {{ number_format($agents->total()) }} {{ Str::plural('agent', $agents->total()) }}
                @else
                    Showing 0 agents
                @endif
            </span>
            <nav class="tbp-agents-pages" aria-label="Pagination">
                <a href="{{ $agents->previousPageUrl() ?: '#' }}" class="{{ $agents->onFirstPage() ? 'is-disabled' : '' }}" aria-label="Previous"><i class="bi bi-chevron-left"></i></a>
                @foreach($agents->getUrlRange(max(1, $agents->currentPage() - 2), min($agents->lastPage(), $agents->currentPage() + 2)) as $page => $url)
                    <a href="{{ $url }}" class="{{ $page === $agents->currentPage() ? 'is-active' : '' }}">{{ $page }}</a>
                @endforeach
                <a href="{{ $agents->nextPageUrl() ?: '#' }}" class="{{ $agents->hasMorePages() ? '' : 'is-disabled' }}" aria-label="Next"><i class="bi bi-chevron-right"></i></a>
            </nav>
        </div>
    </div>

    {{-- ============ MODALS (same routes/forms as before) ============ --}}
    @foreach($agents as $agent)
        <div class="modal fade" id="deleteModal{{ $agent->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content tbp-agents-modal">
                    <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}">
                        @csrf
                        @method('DELETE')
                        <div class="modal-header"><h5 class="modal-title is-danger">Delete Agent</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete <strong>{{ $agent->full_name }}</strong>?</p>
                            <p class="tbp-agents-modal-warn">This action cannot be undone. The agent and all associated data will be permanently deleted.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="tbp-agents-modal-btn" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="tbp-agents-modal-btn is-danger">Delete Agent</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @if($agent->approval_status === 'active')
            <div class="modal fade" id="suspendModal{{ $agent->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content tbp-agents-modal">
                        <form method="POST" action="{{ route('admin.agents.suspend', $agent) }}">
                            @csrf
                            <div class="modal-header"><h5 class="modal-title">Suspend {{ $agent->full_name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <label class="form-label">Reason (optional)</label>
                                <textarea name="reason" class="form-control" rows="3" placeholder="Reason for suspension..."></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="tbp-agents-modal-btn" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="tbp-agents-modal-btn is-warning">Suspend Agent</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>
@endsection

@push('scripts')
<script>
(function () {
    const root = document.getElementById('tbpAgents');
    if (!root) return;

    document.getElementById('tbpAgentsSort').addEventListener('change', e => { window.location = e.target.value; });
    root.querySelectorAll('.tbp-agents-pages a.is-disabled').forEach(a => a.addEventListener('click', e => e.preventDefault()));

    // Selection → "Export selected" (CSV of just the checked agents).
    const all = document.getElementById('tbpAgentsCheckAll');
    const checks = Array.from(root.querySelectorAll('.tbp-agents-check'));
    const exportBtn = document.getElementById('tbpAgentsExport');
    const exportLabel = document.getElementById('tbpAgentsExportLabel');
    function sync() {
        const ids = checks.filter(c => c.checked).map(c => c.value);
        all.checked = ids.length > 0 && ids.length === checks.length;
        all.indeterminate = ids.length > 0 && ids.length < checks.length;
        const url = new URL(exportBtn.dataset.base, window.location.href);
        ids.forEach(id => url.searchParams.append('ids[]', id));
        exportBtn.href = url.toString();
        exportLabel.textContent = ids.length ? `Export selected (${ids.length})` : 'Export';
        checks.forEach(c => c.closest('tr').classList.toggle('is-selected', c.checked));
    }
    all.addEventListener('change', () => { checks.forEach(c => { c.checked = all.checked; }); sync(); });
    checks.forEach(c => c.addEventListener('change', sync));
})();
</script>
@endpush
