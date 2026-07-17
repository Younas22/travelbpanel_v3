@extends('admin.layouts.app')

@section('title', 'Visa Requests')

@section('content')
        <!-- Content Area -->
        <div class="content-area">
            <!-- Page Header with Stats -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="mb-1">Visa Requests Management</h2>
                        <p class="text-muted mb-0">Track and manage all visa applications</p>
                    </div>
                    <div class="col-md-6">
                        <div class="row g-3">
                            <div class="col-4 text-center">
                                <div class="h4 mb-0 text-primary">{{ number_format($stats['total'] ?? 0) }}</div>
                                <small class="text-muted">Total</small>
                            </div>
                            <div class="col-4 text-center">
                                <div class="h4 mb-0 text-success">{{ number_format($stats['uae'] ?? 0) }}</div>
                                <small class="text-muted">UAE Visas</small>
                            </div>
                            <div class="col-4 text-center">
                                <div class="h4 mb-0 text-warning">{{ number_format($stats['other'] ?? 0) }}</div>
                                <small class="text-muted">Other Visas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-card">
                <form method="GET" action="{{ route('admin.visa-requests.visaindex') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Search Visa Requests</label>
                            <div class="search-container">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" class="form-control search-input" 
                                       placeholder="Search by name, passport no, nationality..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Visa Category</label>
                            <select name="visa_category" class="form-select">
                                <option value="">All Categories</option>
                                <option value="uae" {{ request('visa_category') === 'uae' ? 'selected' : '' }}>UAE</option>
                                <option value="other" {{ request('visa_category') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-funnel"></i> Filter
                                </button>
                                <a href="{{ route('admin.visa-requests.visaindex') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Visa Requests Table -->
            <div class="bookings-table">
                <div class="table-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">All Visa Requests ({{ number_format($visaRequests->total()) }})</h5>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Request ID</th>
                                <th>Applicant</th>
                                <th>Visa Details</th>
                                <th>Passport Info</th>
                                <th>Nationality</th>
                                <th>Guarantor</th>
                                <th>Documents</th>
                                <th>Receipt</th>
                                <th>Applied Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($visaRequests as $request)
                            <tr>
                                <td>
                                    <div class="fw-semibold">#VR{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</div>
                                    <small class="text-muted">{{ $request->created_at->format('M j, Y') }}</small>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        @php
                                            $fullName = trim($request->first_name . ' ' . ($request->middle_name ?? '') . ' ' . $request->surname);
                                            $initials = collect(explode(' ', $fullName))->map(function($name) {
                                                return strtoupper(substr($name, 0, 1));
                                            })->take(2)->implode('');
                                            $colors = ['28a745', 'dc3545', '6f42c1', 'fd7e14', '20c997'];
                                            $color = $colors[crc32($fullName) % count($colors)];
                                        @endphp
                                        <img src="https://via.placeholder.com/32x32/{{ $color }}/ffffff?text={{ $initials }}" 
                                             alt="" class="rounded-circle me-2">
                                        <div>
                                            <div class="fw-semibold">{{ $fullName }}</div>
                                            <small class="text-muted">{{ $request->gender }} • {{ $request->marital_status }}</small>
                                        </div>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="fw-semibold text-capitalize">{{ $request->visa_category }} Visa</div>
                                    <small class="text-muted">
                                        @if($request->visa_type)
                                            {{ ucfirst($request->visa_type) }}
                                        @endif
                                        @if($request->visa_plan)
                                            • {{ ucfirst($request->visa_plan) }}
                                        @endif
                                    </small>
                                </td>
                                
                                <td>
                                    <div class="fw-semibold">{{ $request->passport_no }}</div>
                                    <small class="text-muted">
                                        Exp: {{ $request->passport_expiry_date ? $request->passport_expiry_date->format('M j, Y') : 'N/A' }}
                                    </small>
                                </td>
                                
                                <td>
                                    <span class="badge bg-light text-dark">{{ ucfirst($request->nationality) }}</span>
                                </td>
                                
                                <td>
                                    @if($request->guarantor_name)
                                        <div class="fw-semibold">{{ $request->guarantor_name }}</div>
                                        <small class="text-muted">{{ ucfirst($request->guarantor_relation ?? 'N/A') }}</small>
                                    @else
                                        <span class="text-muted">No Guarantor</span>
                                    @endif
                                </td>
                                
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($request->passport_front)
                                            <span class="badge bg-success">Passport</span>
                                        @endif
                                        @if($request->passport_photo)
                                            <span class="badge bg-info">Photo</span>
                                        @endif
                                        @if($request->other_document)
                                            <span class="badge bg-secondary">Other</span>
                                        @endif
                                    </div>
                                </td>
                                
                                <td>
                                    @if($request->receipt_amount)
                                        <div class="fw-semibold">AED {{ number_format($request->receipt_amount) }}</div>
                                        <small class="text-muted">{{ $request->receipt_no ?? 'N/A' }}</small>
                                    @else
                                        <span class="text-muted">No Receipt</span>
                                    @endif
                                </td>
                                
                                <td>
                                    <div class="fw-semibold">{{ $request->created_at->format('M j, Y') }}</div>
                                    <small class="text-muted">{{ $request->created_at->format('g:i A') }}</small>
                                </td>
                                
                                <td>
                                    <a href="{{ route('admin.visa-requests.show', $request->id) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                                        <h5 class="text-muted">No visa requests found</h5>
                                        <p class="text-muted mb-0">Try adjusting your search criteria or filters.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($visaRequests->hasPages())
                <div class="pagination-container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $visaRequests->firstItem() }} to {{ $visaRequests->lastItem() }} of {{ number_format($visaRequests->total()) }} entries
                        </div>
                        <nav>
                            {{ $visaRequests->links('pagination::bootstrap-4') }}
                        </nav>
                    </div>
                </div>
                @endif
            </div>
        </div>

@endsection