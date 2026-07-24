@extends('admin-modern.layouts.app')
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

    <div class="ag-header">
        <div>
            <h2 class="ag-title">Agents Management</h2>
            <p class="ag-subtitle">
                Manage B2B travel agents
                @if(($pendingTopups ?? 0) > 0)
                    &middot; <strong>{{ $pendingTopups }}</strong> pending top-up{{ $pendingTopups == 1 ? '' : 's' }}
                @endif
            </p>
        </div>
        <a href="{{ route('admin.agents.create') }}" class="ag-add-btn">
            <i class="bi bi-plus-circle"></i> Add Agent
        </a>
    </div>

    <div class="ag-stats">
        <div class="ag-stat">
            <div class="ag-stat-icon ag-icon-blue"><i class="bi bi-people"></i></div>
            <div><div class="ag-stat-value">{{ $stats['total'] }}</div><div class="ag-stat-label">Total agents</div></div>
        </div>
        <div class="ag-stat">
            <div class="ag-stat-icon ag-icon-green"><i class="bi bi-check-circle"></i></div>
            <div><div class="ag-stat-value">{{ $stats['active'] }}</div><div class="ag-stat-label">Active</div></div>
        </div>
        <div class="ag-stat">
            <div class="ag-stat-icon ag-icon-amber"><i class="bi bi-clock"></i></div>
            <div><div class="ag-stat-value">{{ $stats['pending'] }}</div><div class="ag-stat-label">Pending</div></div>
        </div>
        <div class="ag-stat">
            <div class="ag-stat-icon ag-icon-red"><i class="bi bi-slash-circle"></i></div>
            <div><div class="ag-stat-value">{{ $stats['suspended'] }}</div><div class="ag-stat-label">Suspended</div></div>
        </div>
    </div>

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
                                <div class="ag-avatar" style="background:{{ $avatar['bg'] }}; color:{{ $avatar['text'] }}">{{ $initials }}</div>
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
                                <a href="{{ route('admin.agents.show', $agent) }}" class="ag-action-btn" title="View"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.agents.edit', $agent) }}" class="ag-action-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                                <a href="{{ route('admin.agents.permissions', $agent) }}" class="ag-action-btn" title="Permissions"><i class="bi bi-shield-check"></i></a>
                                <a href="{{ route('admin.agents.wallet', $agent) }}" class="ag-action-btn" title="Wallet"><i class="bi bi-wallet2"></i></a>

                                @if($agent->approval_status === 'pending')
                                    <form method="POST" action="{{ route('admin.agents.approve', $agent) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="ag-action-btn ag-action-approve" title="Approve"><i class="bi bi-check-lg"></i></button>
                                    </form>
                                @elseif($agent->approval_status === 'active')
                                    <button type="button" class="ag-action-btn ag-action-suspend" title="Suspend" data-bs-toggle="modal" data-bs-target="#suspendModal{{ $agent->id }}"><i class="bi bi-pause-circle"></i></button>
                                @elseif($agent->approval_status === 'suspended')
                                    <form method="POST" action="{{ route('admin.agents.activate', $agent) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="ag-action-btn ag-action-activate" title="Activate"><i class="bi bi-play-circle"></i></button>
                                    </form>
                                @endif

                                <button type="button" class="ag-action-btn ag-action-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $agent->id }}"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade" id="deleteModal{{ $agent->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content ag-modal">
                                <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}">
                                    @csrf
                                    @method('DELETE')
                                    <div class="modal-header">
                                        <h5 class="modal-title ag-modal-title-danger"><i class="bi bi-exclamation-triangle"></i> Delete Agent</h5>
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
