@extends('admin.layouts.app')
@section('title', 'Agents Management')

@section('content')

    @php
        $avatarPalette = [
            ['bg'=>'#B5D4F4','text'=>'#0C447C'],
            ['bg'=>'#C0DD97','text'=>'#27500A'],
            ['bg'=>'#FAC775','text'=>'#633806'],
            ['bg'=>'#F4C0D1','text'=>'#72243E'],
            ['bg'=>'#CECBF6','text'=>'#3C3489'],
        ];
    @endphp

        <!-- ===== PAGE HEADER ===== -->
    <div class="ag-header">
        <div>
            <h2 class="ag-title">Agents Management</h2>
            <p class="ag-subtitle">Manage B2B travel agents</p>
        </div>
        <a href="{{ route('admin.agents.create') }}" class="ag-add-btn">
            <i class="bi bi-plus-circle"></i> Add Agent
        </a>
    </div>

    <!-- ===== STATS ===== -->
    <div class="ag-stats">
        <div class="ag-stat">
            <div class="ag-stat-icon ag-icon-blue"><i class="bi bi-people"></i></div>
            <div>
                <div class="ag-stat-value">{{ $stats['total'] }}</div>
                <div class="ag-stat-label">Total agents</div>
            </div>
        </div>
        <div class="ag-stat">
            <div class="ag-stat-icon ag-icon-green"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="ag-stat-value">{{ $stats['active'] }}</div>
                <div class="ag-stat-label">Active</div>
            </div>
        </div>
        <div class="ag-stat">
            <div class="ag-stat-icon ag-icon-amber"><i class="bi bi-clock"></i></div>
            <div>
                <div class="ag-stat-value">{{ $stats['pending'] }}</div>
                <div class="ag-stat-label">Pending</div>
            </div>
        </div>
        <div class="ag-stat">
            <div class="ag-stat-icon ag-icon-red"><i class="bi bi-slash-circle"></i></div>
            <div>
                <div class="ag-stat-value">{{ $stats['suspended'] }}</div>
                <div class="ag-stat-label">Suspended</div>
            </div>
        </div>
    </div>

    <!-- ===== FILTERS ===== -->
    <div class="ag-filter-card">
        <form method="GET" class="ag-filter-grid">
            <div class="ag-filter-search">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="form-control"
                       placeholder="Search by name, email, company, code..."
                       value="{{ request('search') }}">
            </div>
            <select name="status" class="form-select">
                <option value="">All status</option>
                <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
                <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
            <button type="submit" class="ag-btn ag-btn-primary">
                <i class="bi bi-funnel"></i> Filter
            </button>
            <a href="{{ route('admin.agents.index') }}" class="ag-btn ag-btn-outline">Reset</a>
        </form>
    </div>

    <!-- ===== TABLE ===== -->
    <div class="ag-table-card">
        <div class="table-responsive">
            <table class="ag-table">
                <thead>
                <tr>
                    <th>Agent</th>
                    <th>Code</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Wallet Balance</th>
                    <th>Commission</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($agents as $agent)
                    @php
                        $initials = collect(explode(' ', $agent->full_name))
                            ->map(fn($n) => strtoupper(substr($n, 0, 1)))
                            ->take(2)->implode('');
                        $avatar = $avatarPalette[crc32($agent->full_name) % count($avatarPalette)];
                    @endphp
                    <tr>
                        <td>
                            <div class="ag-agent">
                                <div class="ag-avatar" style="background:{{ $avatar['bg'] }}; color:{{ $avatar['text'] }}">
                                    {{ $initials }}
                                </div>
                                <div>
                                    <div class="ag-agent-name">{{ $agent->full_name }}</div>
                                    <div class="ag-meta">{{ $agent->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="ag-code">{{ $agent->agent_code }}</span></td>
                        <td>{{ $agent->company_name ?? '—' }}</td>
                        <td>{!! $agent->approval_status_badge !!}</td>
                        <td>
                            @if($agent->wallet)
                                <span class="ag-wallet">PKR {{ number_format($agent->wallet->balance, 0) }}</span>
                            @else
                                <span class="ag-meta">—</span>
                            @endif
                        </td>
                        <td><span class="ag-commission">{{ $agent->commission_rate ?? 0 }}%</span></td>
                        <td><span class="ag-meta">{{ $agent->created_at->format('d M Y') }}</span></td>
                        <td>
                            <div class="ag-actions">
                                <a href="{{ route('admin.agents.show', $agent) }}" class="ag-action-btn" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.agents.edit', $agent) }}" class="ag-action-btn" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('admin.agents.permissions', $agent) }}" class="ag-action-btn" title="Permissions">
                                    <i class="bi bi-shield-check"></i>
                                </a>
                                <a href="{{ route('admin.agents.wallet', $agent) }}" class="ag-action-btn" title="Wallet">
                                    <i class="bi bi-wallet2"></i>
                                </a>

                                @if($agent->approval_status === 'pending')
                                    <form method="POST" action="{{ route('admin.agents.approve', $agent) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="ag-action-btn ag-action-approve" title="Approve">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                @elseif($agent->approval_status === 'active')
                                    <button type="button" class="ag-action-btn ag-action-suspend" title="Suspend"
                                            data-bs-toggle="modal" data-bs-target="#suspendModal{{ $agent->id }}">
                                        <i class="bi bi-pause-circle"></i>
                                    </button>
                                @elseif($agent->approval_status === 'suspended')
                                    <form method="POST" action="{{ route('admin.agents.activate', $agent) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="ag-action-btn ag-action-activate" title="Activate">
                                            <i class="bi bi-play-circle"></i>
                                        </button>
                                    </form>
                                @endif

                                <button type="button" class="ag-action-btn ag-action-danger" title="Delete"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal{{ $agent->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteModal{{ $agent->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content ag-modal">
                                <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}">
                                    @csrf
                                    @method('DELETE')
                                    <div class="modal-header">
                                        <h5 class="modal-title ag-modal-title-danger">
                                            <i class="bi bi-exclamation-triangle"></i> Delete Agent
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete <strong>{{ $agent->full_name }}</strong>?</p>
                                        <p class="ag-modal-warning">This action cannot be undone. The agent and all associated data will be permanently deleted.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="ag-btn ag-btn-outline" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="ag-btn ag-btn-danger">Delete Agent</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Suspend Modal -->
                    <div class="modal fade" id="suspendModal{{ $agent->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content ag-modal">
                                <form method="POST" action="{{ route('admin.agents.suspend', $agent) }}">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Suspend {{ $agent->full_name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label">Reason (optional)</label>
                                        <textarea name="reason" class="form-control" rows="3" placeholder="Reason for suspension..."></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="ag-btn ag-btn-outline" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="ag-btn ag-btn-warning">Suspend Agent</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="ag-empty">
                                <i class="bi bi-people"></i>
                                <h5>No agents found</h5>
                                <p>Try adjusting your search or filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($agents->hasPages())
            <div class="ag-pagination">
                {{ $agents->withQueryString()->links() }}
            </div>
        @endif
    </div>

@endsection

@push('styles')
    <style>
        /* ===== PAGE HEADER ===== */
        .ag-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.25rem;
        }
        .ag-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--bs-body-color);
            margin: 0 0 4px;
        }
        .ag-subtitle {
            font-size: 13px;
            color: var(--bs-secondary-color);
            margin: 0;
        }
        .ag-add-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            background: #0C6DFD;
            color: #fff;
            text-decoration: none;
            border: 1px solid #0C6DFD;
            transition: opacity .15s;
        }
        .ag-add-btn:hover { opacity: .9; color: #fff; text-decoration: none; }

        /* ===== STATS ===== */
        .ag-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 1.25rem;
        }
        .ag-stat {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .ag-stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .ag-icon-blue,
        .ag-icon-green { background: #E3F0FF; color: #0C6DFD; }
        .ag-icon-amber { background: #FAEEDA; color: #633806; }
        .ag-icon-red   { background: #FCEBEB; color: #A32D2D; }
        [data-bs-theme="dark"] .ag-icon-blue,
        [data-bs-theme="dark"] .ag-icon-green { background: #0a2a4d; color: #6ba8ff; }
        [data-bs-theme="dark"] .ag-icon-amber { background: #2e1e05; color: #f0b054; }
        [data-bs-theme="dark"] .ag-icon-red   { background: #2e0a0a; color: #f08080; }

        .ag-stat-value {
            font-size: 22px;
            font-weight: 600;
            color: var(--bs-body-color);
            line-height: 1;
        }
        .ag-stat-label {
            font-size: 12px;
            color: var(--bs-secondary-color);
            margin-top: 3px;
        }

        /* ===== FILTER CARD ===== */
        .ag-filter-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
        }
        .ag-filter-grid {
            display: grid;
            grid-template-columns: 1fr 200px auto auto;
            gap: 10px;
            align-items: stretch;
        }
        .ag-filter-grid .form-control,
        .ag-filter-grid .form-select {
            border-radius: 8px;
            border-color: var(--bs-border-color);
            font-size: 13px;
            height: 100%;
        }
        .ag-filter-search { position: relative; }
        .ag-filter-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13px;
            color: var(--bs-secondary-color);
            pointer-events: none;
        }
        .ag-filter-search .form-control { padding-left: 34px; }

        /* ===== BUTTONS ===== */
        .ag-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid var(--bs-border-color);
            white-space: nowrap;
            transition: background .15s, color .15s, opacity .15s;
        }
        .ag-btn-primary {
            background: #0C6DFD;
            border-color: #0C6DFD;
            color: #fff;
        }
        .ag-btn-primary:hover { opacity: .9; color: #fff; }
        .ag-btn-outline {
            background: var(--bs-secondary-bg);
            color: var(--bs-secondary-color);
        }
        .ag-btn-outline:hover {
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            text-decoration: none;
        }
        .ag-btn-danger {
            background: #A32D2D;
            border-color: #A32D2D;
            color: #fff;
        }
        .ag-btn-danger:hover { opacity: .9; color: #fff; }
        .ag-btn-warning {
            background: #0C6DFD;
            border-color: #0C6DFD;
            color: #fff;
        }
        .ag-btn-warning:hover { opacity: .9; color: #fff; }

        /* ===== TABLE CARD ===== */
        .ag-table-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            overflow: hidden;
        }
        .ag-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .ag-table thead th {
            padding: 10px 14px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: var(--bs-secondary-color);
            border-bottom: 1px solid var(--bs-border-color);
            white-space: nowrap;
            text-align: left;
            background: var(--bs-secondary-bg);
        }
        .ag-table tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid var(--bs-border-color);
            vertical-align: middle;
            color: var(--bs-body-color);
        }
        .ag-table tbody tr:last-child td { border-bottom: none; }
        .ag-table tbody tr:hover td { background: var(--bs-tertiary-bg); }

        .ag-meta { font-size: 12px; color: var(--bs-secondary-color); }

        /* ===== AGENT CELL ===== */
        .ag-agent { display: flex; align-items: center; gap: 9px; }
        .ag-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
            flex-shrink: 0;
        }
        .ag-agent-name { font-size: 13px; font-weight: 500; color: var(--bs-body-color); }

        /* ===== CODE ===== */
        .ag-code {
            font-family: var(--bs-font-monospace, monospace);
            font-size: 12px;
            background: var(--bs-secondary-bg);
            padding: 2px 8px;
            border-radius: 6px;
            color: var(--bs-body-color);
        }

        /* ===== WALLET / COMMISSION ===== */
        .ag-wallet { font-size: 13px; font-weight: 600; color: #0C6DFD; }
        [data-bs-theme="dark"] .ag-wallet { color: #6ba8ff; }
        .ag-commission {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
        }

        /* ===== STATUS BADGES (from $agent->approval_status_badge) ===== */
        .ag-table .badge {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: .02em;
            text-transform: capitalize;
        }
        .ag-table .badge.bg-success { background: #E3F0FF !important; color: #0C6DFD !important; }
        .ag-table .badge.bg-warning { background: #FAEEDA !important; color: #633806 !important; }
        .ag-table .badge.bg-danger  { background: #FCEBEB !important; color: #A32D2D !important; }
        .ag-table .badge.bg-info,
        .ag-table .badge.bg-primary { background: #E3F0FF !important; color: #0C6DFD !important; }
        .ag-table .badge.bg-secondary { background: var(--bs-tertiary-bg) !important; color: var(--bs-secondary-color) !important; }

        [data-bs-theme="dark"] .ag-table .badge.bg-success { background: #0a2a4d !important; color: #6ba8ff !important; }
        [data-bs-theme="dark"] .ag-table .badge.bg-warning { background: #2e1e05 !important; color: #f0b054 !important; }
        [data-bs-theme="dark"] .ag-table .badge.bg-danger  { background: #2e0a0a !important; color: #f08080 !important; }
        [data-bs-theme="dark"] .ag-table .badge.bg-info,
        [data-bs-theme="dark"] .ag-table .badge.bg-primary { background: #0a2a4d !important; color: #6ba8ff !important; }

        /* ===== ACTION BUTTONS ===== */
        .ag-actions { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
        .ag-action-btn {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--bs-border-color);
            border-radius: 7px;
            background: var(--bs-secondary-bg);
            color: var(--bs-secondary-color);
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            transition: background .12s, color .12s, border-color .12s, opacity .12s;
        }
        .ag-action-btn:hover {
            background: #0C6DFD;
            color: #fff;
            border-color: #0C6DFD;
            text-decoration: none;
        }
        .ag-action-danger:hover {
            background: #A32D2D;
            border-color: #A32D2D;
            color: #fff;
        }

        /* Filled status-action buttons */
        .ag-action-approve,
        .ag-action-suspend,
        .ag-action-activate {
            border: none;
            color: #fff;
        }
        .ag-action-approve  { background: #0C6DFD; }
        .ag-action-suspend  { background: #0C6DFD; }
        .ag-action-activate { background: #0C6DFD; }
        .ag-action-approve:hover,
        .ag-action-suspend:hover,
        .ag-action-activate:hover {
            opacity: .85;
            color: #fff;
            border-color: transparent;
        }

        /* ===== EMPTY STATE ===== */
        .ag-empty {
            text-align: center;
            padding: 3rem 1rem;
        }
        .ag-empty i {
            font-size: 40px;
            color: var(--bs-border-color);
            margin-bottom: 12px;
            display: block;
        }
        .ag-empty h5 {
            font-size: 14px;
            font-weight: 600;
            color: var(--bs-body-color);
            margin-bottom: 4px;
        }
        .ag-empty p {
            font-size: 12px;
            color: var(--bs-secondary-color);
            margin: 0;
        }

        /* ===== PAGINATION ===== */
        .ag-pagination {
            padding: 14px 20px;
            border-top: 1px solid var(--bs-border-color);
        }
        .ag-pagination .pagination { margin: 0; }
        .ag-pagination .page-link {
            border-radius: 7px;
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
            font-size: 13px;
            margin: 0 2px;
            background: var(--bs-body-bg);
        }
        .ag-pagination .page-item.active .page-link {
            background: #0C6DFD;
            border-color: #0C6DFD;
            color: #fff;
        }
        .ag-pagination .page-item.disabled .page-link {
            color: var(--bs-secondary-color);
            background: var(--bs-secondary-bg);
        }

        /* ===== MODALS ===== */
        .ag-modal {
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
        }
        .ag-modal .modal-header,
        .ag-modal .modal-footer {
            border-color: var(--bs-border-color);
        }
        .ag-modal .form-control {
            border-radius: 8px;
            border-color: var(--bs-border-color);
            font-size: 13px;
        }
        .ag-modal-title-danger {
            color: #A32D2D;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }
        .ag-modal-warning {
            font-size: 12px;
            color: #A32D2D;
            margin-bottom: 0;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .ag-stats { grid-template-columns: repeat(2, 1fr); }
            .ag-filter-grid { grid-template-columns: 1fr 1fr; }
            .ag-filter-search { grid-column: 1 / -1; }
        }
        @media (max-width: 576px) {
            .ag-header { align-items: flex-start; }
            .ag-stats { grid-template-columns: 1fr 1fr; }
            .ag-filter-grid { grid-template-columns: 1fr; }
            .ag-filter-search { grid-column: auto; }
        }
    </style>
@endpush
