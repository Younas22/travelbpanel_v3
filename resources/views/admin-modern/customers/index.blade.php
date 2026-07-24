@extends('admin-modern.layouts.app')

@section('title', 'Customers')

@section('content')
    <div class="page-header">
        <h2 class="mb-1">Customer Management</h2>
        <p class="text-muted mb-0">Manage customer accounts and analyze user behavior</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-blue"><i class="bi bi-people"></i></div>
                    <div class="ms-3">
                        <div class="small text-muted">Total Customers</div>
                        <div class="h4 mb-0">{{ number_format($stats['total_customers']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-green"><i class="bi bi-person-check"></i></div>
                    <div class="ms-3">
                        <div class="small text-muted">Active Customers</div>
                        <div class="h4 mb-0">{{ number_format($stats['active_customers']) }}</div>
                        <div class="small text-success">
                            @if($stats['total_customers'] > 0)
                                {{ round(($stats['active_customers']/$stats['total_customers'])*100, 1) }}% active rate
                            @else
                                0% active rate
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-orange"><i class="bi bi-star"></i></div>
                    <div class="ms-3">
                        <div class="small text-muted">VIP Customers</div>
                        <div class="h4 mb-0">{{ number_format($stats['vip_customers']) }}</div>
                        <div class="small text-info">
                            @if($stats['total_customers'] > 0)
                                {{ round(($stats['vip_customers']/$stats['total_customers'])*100, 1) }}% of total
                            @else
                                0% of total
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-purple"><i class="bi bi-currency-dollar"></i></div>
                    <div class="ms-3">
                        <div class="small text-muted">Avg. Customer Value</div>
                        <div class="h4 mb-0">${{ number_format($stats['avg_customer_value'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-card mb-4">
        <form method="GET" action="{{ route('admin.customers.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search Customers</label>
                    <div class="search-container">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" class="form-control search-input"
                               placeholder="Search by name, email, phone..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="vip" {{ request('status') == 'vip' ? 'selected' : '' }}>VIP</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Registration Date</label>
                    <select name="date_filter" class="form-select">
                        <option value="">All Time</option>
                        <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="year" {{ request('date_filter') == 'year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary modern-btn w-100"><i class="bi bi-funnel"></i> Filter</button>
                </div>
            </div>
        </form>
    </div>

    <div class="customers-table">
        <div class="table-header">
            <h5 class="mb-0">Customer List ({{ $customers->total() }})</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                <tr>
                    <th>Customer</th>
                    <th>Registration</th>
                    <th>Bookings</th>
                    <th>Total Spent</th>
                    <th>Last Activity</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td>
                            <div class="customer-profile">
                                <div class="customer-avatar" style="background: {{ getRandomColor() }};">{{ $customer->initials }}</div>
                                <div class="customer-details">
                                    <div class="customer-name">{{ $customer->full_name }}</div>
                                    <div class="customer-email">{{ $customer->email }}</div>
                                    <div class="customer-email">{{ $customer->phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="registration-info">{{ $customer->created_at->format('M d, Y') }}</div>
                            <div class="last-activity">{{ $customer->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="booking-stats">
                                <div class="booking-count">{{ $customer->total_bookings ?? 0 }}</div>
                                <div class="booking-label">bookings</div>
                            </div>
                        </td>
                        <td>
                            <div class="amount-spent">${{ number_format($customer->total_spent ?? 0, 2) }}</div>
                            <div class="last-activity">
                                @if($customer->total_bookings > 0)
                                    Avg: ${{ number_format($customer->average_spending ?? 0, 2) }}
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="registration-info">{{ $customer->last_activity?->format('M d, Y') ?? 'Never' }}</div>
                            <div class="last-activity">{{ $customer->last_activity?->diffForHumans() ?? 'No activity' }}</div>
                        </td>
                        <td>
                            <span class="badge-status {{ $customer->getStatusBadgeClass() }}">{{ ucfirst($customer->status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="empty-state">
                                <i class="bi bi-people"></i>
                                <p class="mb-0">No customers found matching your criteria</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} entries
                </div>
                <nav>{{ $customers->withQueryString()->links() }}</nav>
            </div>
        </div>
    </div>
@endsection
