@extends('admin.layouts.app')

@section('title', 'Booking')

@section('content')
        <!-- Content Area -->
        <div class="content-area">
            <!-- Page Header with Stats -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="mb-1">Bookings Management</h2>
                        <p class="text-muted mb-0">Track and manage all flight bookings</p>
                    </div>
                    <div class="col-md-6">
                        <div class="row g-3">
                            <div class="col-4 text-center">
                                <div class="h4 mb-0 text-primary">2,486</div>
                                <small class="text-muted">Total</small>
                            </div>
                            <div class="col-4 text-center">
                                <div class="h4 mb-0 text-success">2,245</div>
                                <small class="text-muted">Confirmed</small>
                            </div>
                            <div class="col-4 text-center">
                                <div class="h4 mb-0 text-warning">241</div>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-card">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Search Bookings</label>
                        <div class="search-container">
                            <i class="bi bi-search"></i>
                            <input type="text" class="form-control search-input" placeholder="Search by booking ID, customer name, email...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select class="form-select">
                            <option value="">All Status</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                            <button class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="bookings-table">
                <div class="table-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">All Bookings (2,486)</h5>
                        <div class="d-flex gap-2">
                            <div class="dropdown export-dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
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
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Route</th>
                                <th>Flight Details</th>
                                <th>Travel Date</th>
                                <th>Passengers</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Partner</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td>
                                    <div class="fw-semibold">#BK-2025-001</div>
                                    <small class="text-muted">Jul 8, 2025</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32x32/28a745/ffffff?text=JD" alt="" class="rounded-circle me-2">
                                        <div>
                                            <div class="fw-semibold">John Doe</div>
                                            <small class="text-muted">john@email.com</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">NYC → LAX</div>
                                    <small class="text-muted">Non-stop</small>
                                </td>
                                <td>
                                    <div class="booking-details">
                                        <div class="fw-semibold">AA 1234</div>
                                        <small class="text-muted">American Airlines</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">Jul 15, 2025</div>
                                    <small class="text-muted">08:30 - 11:45</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">2 Adults</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">$850</div>
                                    <small class="text-muted">Total</small>
                                </td>
                                <td><span class="badge-status status-confirmed">Confirmed</span></td>
                                <td>
                                    <small class="text-muted">Amadeus API</small>
                                </td>
                                <td>
                                    <div class="action-buttons d-flex gap-1">
                                        <button class="btn btn-outline-primary btn-sm" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" title="Cancel">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td>
                                    <div class="fw-semibold">#BK-2025-002</div>
                                    <small class="text-muted">Jul 8, 2025</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32x32/dc3545/ffffff?text=SM" alt="" class="rounded-circle me-2">
                                        <div>
                                            <div class="fw-semibold">Sarah Miller</div>
                                            <small class="text-muted">sarah@email.com</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">LON → DXB</div>
                                    <small class="text-muted">1 Stop</small>
                                </td>
                                <td>
                                    <div class="booking-details">
                                        <div class="fw-semibold">EK 001</div>
                                        <small class="text-muted">Emirates</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">Jul 18, 2025</div>
                                    <small class="text-muted">14:20 - 22:45</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">1 Adult</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">$1,240</div>
                                    <small class="text-muted">Total</small>
                                </td>
                                <td><span class="badge-status status-pending">Pending</span></td>
                                <td>
                                    <small class="text-muted">Kiwi API</small>
                                </td>
                                <td>
                                    <div class="action-buttons d-flex gap-1">
                                        <button class="btn btn-outline-primary btn-sm" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" title="Cancel">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td>
                                    <div class="fw-semibold">#BK-2025-003</div>
                                    <small class="text-muted">Jul 7, 2025</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32x32/6f42c1/ffffff?text=MJ" alt="" class="rounded-circle me-2">
                                        <div>
                                            <div class="fw-semibold">Mike Johnson</div>
                                            <small class="text-muted">mike@email.com</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">PAR → TOK</div>
                                    <small class="text-muted">2 Stops</small>
                                </td>
                                <td>
                                    <div class="booking-details">
                                        <div class="fw-semibold">AF 292</div>
                                        <small class="text-muted">Air France</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">Jul 20, 2025</div>
                                    <small class="text-muted">11:15 - 15:30+1</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">1 Adult, 1 Child</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">$1,840</div>
                                    <small class="text-muted">Total</small>
                                </td>
                                <td><span class="badge-status status-confirmed">Confirmed</span></td>
                                <td>
                                    <small class="text-muted">Duffel API</small>
                                </td>
                                <td>
                                    <div class="action-buttons d-flex gap-1">
                                        <button class="btn btn-outline-primary btn-sm" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" title="Cancel">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td>
                                    <div class="fw-semibold">#BK-2025-004</div>
                                    <small class="text-muted">Jul 6, 2025</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32x32/fd7e14/ffffff?text=EA" alt="" class="rounded-circle me-2">
                                        <div>
                                            <div class="fw-semibold">Emma Anderson</div>
                                            <small class="text-muted">emma@email.com</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">SYD → SIN</div>
                                    <small class="text-muted">Non-stop</small>
                                </td>
                                <td>
                                    <div class="booking-details">
                                        <div class="fw-semibold">SQ 221</div>
                                        <small class="text-muted">Singapore Airlines</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">Jul 12, 2025</div>
                                    <small class="text-muted">09:20 - 14:30</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">3 Adults</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">$1,620</div>
                                    <small class="text-muted">Total</small>
                                </td>
                                <td><span class="badge-status status-cancelled">Cancelled</span></td>
                                <td>
                                    <small class="text-muted">Custom Partner</small>
                                </td>
                                <td>
                                    <div class="action-buttons d-flex gap-1">
                                        <button class="btn btn-outline-primary btn-sm" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" title="Refund">
                                            <i class="bi bi-arrow-left-circle"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" title="Archive">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td>
                                    <div class="fw-semibold">#BK-2025-005</div>
                                    <small class="text-muted">Jul 5, 2025</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32x32/20c997/ffffff?text=RW" alt="" class="rounded-circle me-2">
                                        <div>
                                            <div class="fw-semibold">Robert Wilson</div>
                                            <small class="text-muted">robert@email.com</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">BOM → FRA</div>
                                    <small class="text-muted">1 Stop</small>
                                </td>
                                <td>
                                    <div class="booking-details">
                                        <div class="fw-semibold">LH 756</div>
                                        <small class="text-muted">Lufthansa</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">Jul 25, 2025</div>
                                    <small class="text-muted">02:15 - 08:45</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">2 Adults</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">$1,500</div>
                                    <small class="text-muted">Total</small>
                                </td>
                                <td><span class="badge-status status-refunded">Refunded</span></td>
                                <td>
                                    <small class="text-muted">Amadeus API</small>
                                </td>
                                <td>
                                    <div class="action-buttons d-flex gap-1">
                                        <button class="btn btn-outline-primary btn-sm" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" title="Archive">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing 1 to 5 of 2,486 entries
                        </div>
                        <nav>
                            <ul class="pagination mb-0">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link" href="#">1</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <span class="page-link">...</span>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">497</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
@endsection