@extends('admin-modern.layouts.app')

@section('title', 'Support Tickets')

@section('content')
    <div class="content-area">

        <!-- ===== PAGE HEADER ===== -->
        <div class="bk-header">
            <div>
                <h2 class="bk-title">Support Tickets</h2>
                <p class="bk-subtitle">Tickets raised by customers and agents</p>
            </div>
            <div class="bk-stats">
                <div class="bk-stat">
                    <div class="bk-stat-value">{{ number_format($stats['total']) }}</div>
                    <div class="bk-stat-label">Total</div>
                </div>
                <div class="bk-stat">
                    <div class="bk-stat-value bk-stat-warn">{{ number_format($stats['open']) }}</div>
                    <div class="bk-stat-label">Open</div>
                </div>
                <div class="bk-stat">
                    <div class="bk-stat-value">{{ number_format($stats['in_progress']) }}</div>
                    <div class="bk-stat-label">In Progress</div>
                </div>
                <div class="bk-stat">
                    <div class="bk-stat-value bk-stat-ok">{{ number_format($stats['resolved']) }}</div>
                    <div class="bk-stat-label">Resolved</div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <!-- ===== FILTERS ===== -->
        <div class="bk-filter-card">
            <form method="GET" action="{{ route('admin.support.index') }}">
                <div class="bk-filter-grid">
                    <div class="bk-field bk-field-search">
                        <label class="form-label">Search tickets</label>
                        <div class="bk-search">
                            <i class="bi bi-search"></i>
                            <input type="text" name="search" class="form-control" placeholder="Ticket #, subject..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="bk-field">
                        <label class="form-label">Source</label>
                        <select name="source" class="form-select">
                            <option value="">All</option>
                            <option value="user" {{ request('source') === 'user' ? 'selected' : '' }}>Customers</option>
                            <option value="agent" {{ request('source') === 'agent' ? 'selected' : '' }}>Agents</option>
                        </select>
                    </div>

                    <div class="bk-field">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All status</option>
                            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <div class="bk-field">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="">All priorities</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>

                    <div class="bk-field bk-field-actions">
                        <button type="submit" class="bk-icon-btn bk-icon-btn-primary" title="Apply filters"><i class="bi bi-funnel"></i></button>
                        <a href="{{ route('admin.support.index') }}" class="bk-icon-btn" title="Reset filters"><i class="bi bi-arrow-clockwise"></i></a>
                    </div>
                </div>
            </form>
        </div>

        <!-- ===== TICKETS TABLE ===== -->
        <div class="bk-table-card">
            <div class="bk-table-header">
                <h5>All tickets ({{ number_format($tickets->total()) }})</h5>
            </div>

            <div class="table-responsive">
                <table class="bk-table">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Raised by</th>
                            <th>Subject</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Last Activity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php
                        $statusColors = ['open' => 'bk-stat-warn', 'in_progress' => '', 'resolved' => 'bk-stat-ok', 'closed' => ''];
                    @endphp
                    @forelse($tickets as $ticket)
                        @php $submitter = $ticket->user ?? $ticket->agent; @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.support.show', $ticket) }}" class="bk-id-link">
                                    <div class="bk-id">#{{ $ticket->ticket_number }}</div>
                                    <div class="bk-meta">{{ $ticket->created_at->format('M j, Y') }}</div>
                                </a>
                            </td>
                            <td>
                                <div class="bk-detail">{{ $submitter ? trim($submitter->first_name . ' ' . $submitter->last_name) : 'Unknown' }}</div>
                                <span class="badge-status">{{ $ticket->agent_id ? 'Agent' : 'Customer' }}</span>
                            </td>
                            <td><div class="bk-detail">{{ \Illuminate\Support\Str::limit($ticket->subject, 40) }}</div></td>
                            <td><span class="badge-status">{{ ucfirst($ticket->priority) }}</span></td>
                            <td><span class="badge-status {{ $statusColors[$ticket->status] ?? '' }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td>
                            <td><span class="bk-meta">{{ $ticket->last_activity_at?->diffForHumans() ?? $ticket->created_at->diffForHumans() }}</span></td>
                            <td>
                                <div class="bk-actions">
                                    <a href="{{ route('admin.support.show', $ticket) }}" class="bk-action-btn" title="View ticket">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="bk-empty">
                                    <i class="bi bi-inbox"></i>
                                    <h5>No tickets found</h5>
                                    <p>No support tickets match these filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div class="bk-pagination">
                    <div class="bk-pagination-info">Showing {{ $tickets->firstItem() }} to {{ $tickets->lastItem() }} of {{ number_format($tickets->total()) }} entries</div>
                    <nav>{{ $tickets->links('pagination::bootstrap-4') }}</nav>
                </div>
            @endif
        </div>
    </div>
@endsection
