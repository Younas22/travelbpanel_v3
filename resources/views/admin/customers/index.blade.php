@extends('admin.layouts.app')

@section('title', 'Customers')

@section('content')

<div class="content-area p-4">
    <!-- Page Header with Stats -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="mb-1">Customer Management</h2>
                <p class="text-muted mb-0">Manage customer accounts and analyze user behavior</p>
            </div>
            <div class="col-md-6 d-none">
                <div class="text-end">
                    <button class="btn btn-primary modern-btn" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="bi bi-person-plus"></i> Add New Customer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-blue">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="ms-3">
                        <div class="small text-muted">Total Customers</div>
                        <div class="h4 mb-0">{{ number_format($stats['total_customers']) }}</div>
                        <div class="small text-success">
                            <i class="bi bi-arrow-up"></i> 156 this month
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-green">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div class="ms-3">
                        <div class="small text-muted">Active Customers</div>
                        <div class="h4 mb-0">{{ number_format($stats['active_customers']) }}</div>
                        <div class="small text-success">
                            <i class="bi bi-arrow-up"></i> 
                            {{ round(($stats['active_customers']/$stats['total_customers'])*100, 1) }}% active rate
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-orange">
                        <i class="bi bi-star"></i>
                    </div>
                    <div class="ms-3">
                        <div class="small text-muted">VIP Customers</div>
                        <div class="h4 mb-0">{{ number_format($stats['vip_customers']) }}</div>
                        <div class="small text-info">
                            <i class="bi bi-arrow-right"></i> 
                            {{ round(($stats['vip_customers']/$stats['total_customers'])*100, 1) }}% of total
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-purple">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="ms-3">
                        <div class="small text-muted">Avg. Customer Value</div>
                        <div class="h4 mb-0">${{ number_format($stats['avg_customer_value'], 2) }}</div>
                        <div class="small text-success">
                            <i class="bi bi-arrow-up"></i> 8.2% from last month
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Filters -->
        <div class="col-12">
            <div class="filter-card">
                <form method="GET" action="{{ route('admin.customers.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
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
                        <!-- <div class="col-md-2">
                            <label class="form-label">Customer Tier</label>
                            <select name="tier" class="form-select">
                                <option value="">All Tiers</option>
                                <option value="bronze" {{ request('tier') == 'bronze' ? 'selected' : '' }}>Bronze</option>
                                <option value="silver" {{ request('tier') == 'silver' ? 'selected' : '' }}>Silver</option>
                                <option value="gold" {{ request('tier') == 'gold' ? 'selected' : '' }}>Gold</option>
                                <option value="platinum" {{ request('tier') == 'platinum' ? 'selected' : '' }}>Platinum</option>
                            </select>
                        </div> -->
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
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary modern-btn">
                                    <i class="bi bi-funnel"></i> Filter
                                </button>
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                                <button class="btn btn-success modern-btn">
                                    <i class="bi bi-envelope"></i> Bulk Email
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="col-xl-12">
            <div class="customers-table">
                <div class="table-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Customer List ({{ $customers->total() }})</h5>
                        <div class="d-flex gap-2 d-none">
                            <div class="dropdown export-dropdown">
                                <button class="btn btn-outline-primary modern-btn dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-download"></i> Export
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-excel me-2"></i>Export as Excel</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-text me-2"></i>Export as CSV</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-pdf me-2"></i>Export as PDF</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input type="checkbox" class="form-check-input">
                                </th>
                                <th>Customer</th>
                                <th>Registration</th>
                                <th>Bookings</th>
                                <th>Total Spent</th>
                                <th>Last Activity</th>
                                <!-- <th>Tier</th> -->
                                <th>Status</th>
                                <!-- <th>Actions</th> -->
                            </tr>
                        </thead>

                        <!-- ... previous code remains the same until the table ... -->

<tbody>
    @forelse ($customers as $customer)
    <tr>
        <td><input type="checkbox" class="form-check-input"></td>
        <td>
            <div class="customer-profile">
                <div class="customer-avatar" style="background: {{ getRandomColor() }};">
                    {{ $customer->initials }}
                </div>
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
        <!-- <td>
            <span class="customer-tier {{ $customer->getTierBadgeClass() }}">
                {{ ucfirst($customer->customer_tier) }}
            </span>
        </td> -->
        <td>
            <span class="badge-status {{ $customer->getStatusBadgeClass() }}">
                {{ ucfirst($customer->status) }}
            </span>
        </td>
        <td class="d-none">
            <div class="action-buttons d-flex gap-1">
                <a href="{{ route('admin.customers.show', $customer->id) }}" 
                   class="btn btn-outline-primary btn-sm" title="View Profile">
                    <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('admin.customers.edit', $customer->id) }}" 
                   class="btn btn-outline-secondary btn-sm" title="Edit">
                    <i class="bi bi-pencil"></i>
                </a>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                            data-bs-toggle="dropdown" title="More">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-envelope"></i>Send Email</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-telephone"></i>Call Customer</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gift"></i>Add Reward</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-warning" href="#"><i class="bi bi-pause-circle"></i>Suspend</a></li>
                        <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash"></i>Delete</a></li>
                    </ul>
                </div>
            </div>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="9" class="text-center py-4">No customers found matching your criteria</td>
    </tr>
    @endforelse
</tbody>

<!-- ... rest of the view remains the same ... -->

                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} entries
                        </div>
                        <nav>
                            {{ $customers->withQueryString()->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Add your custom CSS styles here */
    .customer-tier {
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .tier-bronze { background-color: #cd7f32; color: white; }
    .tier-silver { background-color: #c0c0c0; color: white; }
    .tier-gold { background-color: #ffd700; color: black; }
    .tier-platinum { background-color: #e5e4e2; color: black; }
    
    .badge-status {
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .status-active { background-color: #d1fae5; color: #065f46; }
    .status-inactive { background-color: #fef3c7; color: #92400e; }
    .status-suspended { background-color: #fee2e2; color: #b91c1c; }
    .status-vip { background-color: #e0e7ff; color: #4338ca; }
</style>
@endpush

@push('scripts')
<script>
    // Add any custom JavaScript here
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush


    <!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCustomerModalLabel">
                    <i class="bi bi-person-plus me-2"></i>Add New Customer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="modern-form">
                    <!-- Personal Information Section -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-person-circle"></i>
                            Personal Information
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" placeholder="Enter first name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" placeholder="Enter last name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" placeholder="customer@email.com" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-telephone"></i>
                                    </span>
                                    <input type="tel" class="form-control" id="phone" placeholder="+1 (555) 123-4567" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="profileImage" class="form-label">Profile Image</label>
                                <div class="file-upload-area" onclick="document.getElementById('profileImage').click()">
                                    <i class="bi bi-cloud-upload upload-icon"></i>
                                    <div class="upload-text">Click to upload profile image</div>
                                    <div class="upload-hint">PNG, JPG up to 2MB</div>
                                    <input type="file" id="profileImage" accept="image/*" style="display: none;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Settings Section -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-gear"></i>
                            Account Settings
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="customerTier" class="form-label">Customer Tier</label>
                                <select class="form-select" id="customerTier" required>
                                    <option value="">Select tier...</option>
                                    <option value="bronze">Bronze</option>
                                    <option value="silver">Silver</option>
                                    <option value="gold">Gold</option>
                                    <option value="platinum">Platinum</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="customerStatus" class="form-label">Status</label>
                                <select class="form-select" id="customerStatus" required>
                                    <option value="">Select status...</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="vip">VIP</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="dateOfBirth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dateOfBirth">
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender">
                                    <option value="">Select gender...</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                    <option value="prefer-not-to-say">Prefer not to say</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information Section -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-geo-alt"></i>
                            Address Information
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="address" class="form-label">Street Address</label>
                                <textarea class="form-control" id="address" rows="2" placeholder="Enter full address"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" placeholder="City">
                            </div>
                            <div class="col-md-4">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" placeholder="State/Province">
                            </div>
                            <div class="col-md-4">
                                <label for="zipCode" class="form-label">ZIP/Postal Code</label>
                                <input type="text" class="form-control" id="zipCode" placeholder="ZIP Code">
                            </div>
                            <div class="col-md-6">
                                <label for="country" class="form-label">Country</label>
                                <select class="form-select" id="country">
                                    <option value="">Select country...</option>
                                    <option value="US">United States</option>
                                    <option value="CA">Canada</option>
                                    <option value="GB">United Kingdom</option>
                                    <option value="AU">Australia</option>
                                    <option value="DE">Germany</option>
                                    <option value="FR">France</option>
                                    <option value="JP">Japan</option>
                                    <option value="CN">China</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Settings Section -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-sliders"></i>
                            Additional Settings
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                    <label class="form-check-label" for="emailNotifications">
                                        Send email notifications
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="smsNotifications">
                                    <label class="form-check-label" for="smsNotifications">
                                        Send SMS notifications
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="marketingEmails">
                                    <label class="form-check-label" for="marketingEmails">
                                        Send marketing emails
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="notes" class="form-label">Internal Notes</label>
                                <textarea class="form-control" id="notes" rows="3" placeholder="Add any internal notes about this customer..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modern-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary modern-btn" onclick="addCustomer()">
                    <i class="bi bi-plus-circle me-2"></i>Add Customer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection