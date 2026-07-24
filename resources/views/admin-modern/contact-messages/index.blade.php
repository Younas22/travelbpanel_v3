@extends('admin-modern.layouts.app')

@section('title', 'Contact Messages')

@section('content')
        <!-- Content Area -->
        <div class="content-area">
            <!-- Page Header with Stats -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="mb-1">Contact Messages Management</h2>
                        <p class="text-muted mb-0">Track and manage all customer inquiries</p>
                    </div>
                    <div class="col-md-6">
                        <div class="row g-3">
                            <div class="col-3 text-center">
                                <div class="h4 mb-0 text-primary">{{ number_format($stats['total'] ?? 0) }}</div>
                                <small class="text-muted">Total</small>
                            </div>
                            <div class="col-3 text-center">
                                <div class="h4 mb-0 text-warning">{{ number_format($stats['new'] ?? 0) }}</div>
                                <small class="text-muted">New</small>
                            </div>
                            <div class="col-3 text-center">
                                <div class="h4 mb-0 text-info">{{ number_format($stats['read'] ?? 0) }}</div>
                                <small class="text-muted">Read</small>
                            </div>
                            <div class="col-3 text-center">
                                <div class="h4 mb-0 text-success">{{ number_format($stats['replied'] ?? 0) }}</div>
                                <small class="text-muted">Replied</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-card">
                <form method="GET" action="{{ route('admin.contact-messages.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Search Messages</label>
                            <div class="search-container">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" class="form-control search-input" 
                                       placeholder="Search by name, email, booking ref..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Subject</label>
                            <select name="subject" class="form-select">
                                <option value="">All Subjects</option>
                                <option value="booking" {{ request('subject') === 'booking' ? 'selected' : '' }}>Booking Assistance</option>
                                <option value="cancellation" {{ request('subject') === 'cancellation' ? 'selected' : '' }}>Cancellation/Refund</option>
                                <option value="modification" {{ request('subject') === 'modification' ? 'selected' : '' }}>Flight Modification</option>
                                <option value="complaint" {{ request('subject') === 'complaint' ? 'selected' : '' }}>Complaint</option>
                                <option value="feedback" {{ request('subject') === 'feedback' ? 'selected' : '' }}>Feedback</option>
                                <option value="partnership" {{ request('subject') === 'partnership' ? 'selected' : '' }}>Business Partnership</option>
                                <option value="other" {{ request('subject') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                                <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read</option>
                                <option value="replied" {{ request('status') === 'replied' ? 'selected' : '' }}>Replied</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-funnel"></i> Filter
                                </button>
                                <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Contact Messages Table -->
            <div class="bookings-table">
                <div class="table-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">All Contact Messages ({{ number_format($contactMessages->total()) }})</h5>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Message ID</th>
                                <th>Contact Info</th>
                                <th>Subject</th>
                                <th>Booking Ref</th>
                                <th>Message Preview</th>
                                <th>Status</th>
                                <th>Received Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contactMessages as $message)
                            <tr class="{{ $message->status === 'new' ? 'table-warning' : '' }}">
                                <td>
                                    <div class="fw-semibold">#CM{{ str_pad($message->id, 6, '0', STR_PAD_LEFT) }}</div>
                                    @if($message->status === 'new')
                                        <span class="badge bg-warning text-dark">NEW</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        @php
                                            $fullName = $message->full_name;
                                            $initials = collect(explode(' ', $fullName))->map(function($name) {
                                                return strtoupper(substr($name, 0, 1));
                                            })->take(2)->implode('');
                                            $colors = ['007bff', '28a745', 'dc3545', '6f42c1', 'fd7e14'];
                                            $color = $colors[crc32($fullName) % count($colors)];
                                        @endphp
                                        <img src="https://via.placeholder.com/32x32/{{ $color }}/ffffff?text={{ $initials }}" 
                                             alt="" class="rounded-circle me-2">
                                        <div>
                                            <div class="fw-semibold">{{ $fullName }}</div>
                                            <small class="text-muted">
                                                <i class="bi bi-envelope"></i> {{ $message->email }}
                                            </small>
                                            @if($message->phone)
                                                <br>
                                                <small class="text-muted">
                                                    <i class="bi bi-telephone"></i> {{ $message->phone }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                
                                <td>
                                    @php
                                        $subjectLabels = [
                                            'booking' => 'Booking Assistance',
                                            'cancellation' => 'Cancellation/Refund',
                                            'modification' => 'Flight Modification',
                                            'complaint' => 'Complaint',
                                            'feedback' => 'Feedback',
                                            'partnership' => 'Business Partnership',
                                            'other' => 'Other'
                                        ];
                                        $subjectText = $subjectLabels[$message->subject] ?? ucfirst($message->subject);
                                        
                                        $subjectColors = [
                                            'booking' => 'primary',
                                            'cancellation' => 'danger',
                                            'modification' => 'warning',
                                            'complaint' => 'danger',
                                            'feedback' => 'success',
                                            'partnership' => 'info',
                                            'other' => 'secondary'
                                        ];
                                        $badgeColor = $subjectColors[$message->subject] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }}">{{ $subjectText }}</span>
                                </td>
                                
                                <td>
                                    @if($message->booking_ref)
                                        <span class="badge bg-light text-dark">{{ $message->booking_ref }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                
                                <td>
                                    <div class="msg-preview-300">
                                        <small class="text-muted">
                                            {{ Str::limit($message->message, 80) }}
                                        </small>
                                    </div>
                                </td>
                                
                                <td>
                                    @php
                                        $statusColors = [
                                            'new' => 'warning',
                                            'read' => 'info',
                                            'replied' => 'success'
                                        ];
                                        $statusColor = $statusColors[$message->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">{{ ucfirst($message->status) }}</span>
                                </td>
                                
                                <td>
                                    <div class="fw-semibold">{{ $message->created_at->format('M j, Y') }}</div>
                                    <small class="text-muted">{{ $message->created_at->format('g:i A') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $message->created_at->diffForHumans() }}</small>
                                </td>
                                
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.contact-messages.show', $message->id) }}" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="mailto:{{ $message->email }}" 
                                           class="btn btn-sm btn-outline-success"
                                           title="Reply via Email">
                                            <i class="bi bi-reply"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                                        <h5 class="text-muted">No contact messages found</h5>
                                        <p class="text-muted mb-0">Try adjusting your search criteria or filters.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($contactMessages->hasPages())
                <div class="pagination-container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $contactMessages->firstItem() }} to {{ $contactMessages->lastItem() }} of {{ number_format($contactMessages->total()) }} entries
                        </div>
                        <nav>
                            {{ $contactMessages->links('pagination::bootstrap-4') }}
                        </nav>
                    </div>
                </div>
                @endif
            </div>
        </div>

@endsection