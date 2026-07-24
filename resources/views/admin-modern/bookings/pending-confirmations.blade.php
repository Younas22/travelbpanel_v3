@extends('admin-modern.layouts.app')

@section('title', 'Booking')

@section('content')

    @php
        $avatarPalette = [
            ['bg'=>'#B5D4F4','text'=>'#0C447C'],
            ['bg'=>'#C0DD97','text'=>'#27500A'],
            ['bg'=>'#FAC775','text'=>'#633806'],
            ['bg'=>'#F4C0D1','text'=>'#72243E'],
            ['bg'=>'#CECBF6','text'=>'#3C3489'],
        ];

        $typeThemes = [
            'flight' => ['icon' => 'bi-airplane', 'cls' => 'bk-type-flight'],
            'hotel'  => ['icon' => 'bi-building', 'cls' => 'bk-type-hotel'],
        ];
    @endphp

    <div class="bk-header">
        <div>
            <h2 class="bk-title">Bookings Management</h2>
            <p class="bk-subtitle">Track and manage all flight and hotel bookings</p>
        </div>
        <div class="bk-stats">
            <div class="bk-stat"><div class="bk-stat-value">{{ number_format($stats['total'] ?? 0) }}</div><div class="bk-stat-label">Total</div></div>
            <div class="bk-stat"><div class="bk-stat-value bk-stat-ok">{{ number_format($stats['confirmed'] ?? 0) }}</div><div class="bk-stat-label">Confirmed</div></div>
            <div class="bk-stat"><div class="bk-stat-value bk-stat-warn">{{ number_format($stats['pending'] ?? 0) }}</div><div class="bk-stat-label">Pending</div></div>
        </div>
    </div>

    <div class="bk-filter-card">
        <form method="GET" action="{{ route('admin.bookings.all') }}">
            <div class="bk-filter-grid">
                <div class="bk-field bk-field-search">
                    <label class="form-label">Search bookings</label>
                    <div class="bk-search">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" class="form-control" placeholder="Booking ID, customer name, email..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="bk-field">
                    <label class="form-label">Booking type</label>
                    <select name="type" class="form-select">
                        <option value="all" {{ ($bookingType ?? 'all') === 'all' ? 'selected' : '' }}>All types</option>
                        <option value="flight" {{ ($bookingType ?? 'all') === 'flight' ? 'selected' : '' }}>Flight</option>
                        <option value="hotel" {{ ($bookingType ?? 'all') === 'hotel' ? 'selected' : '' }}>Hotel</option>
                    </select>
                </div>

                <div class="bk-field">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All status</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="bk-field">
                    <label class="form-label">Date from</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="bk-field">
                    <label class="form-label">Date to</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <div class="bk-field bk-field-actions">
                    <button type="submit" class="bk-icon-btn bk-icon-btn-primary" title="Apply filters"><i class="bi bi-funnel"></i></button>
                    <a href="{{ route('admin.bookings.all') }}" class="bk-icon-btn" title="Reset filters"><i class="bi bi-arrow-clockwise"></i></a>
                </div>
            </div>
        </form>
    </div>

    <div class="bk-table-card">
        <div class="bk-table-header">
            <h5>Pending bookings ({{ number_format($bookings->total()) }})</h5>
            <button type="button" class="bk-bulk-btn bk-hidden" id="bulkDeleteBtn">
                <i class="bi bi-trash"></i> Delete selected (<span id="selectedCount">0</span>)
            </button>
        </div>

        <div class="table-responsive">
            <table class="bk-table">
                <thead>
                <tr>
                    <th width="30"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                    <th>Type</th><th>Booking ID</th><th>Customer</th><th>Details</th><th>Date/Stay</th>
                    <th>Passengers/Guests</th><th>Amount</th><th>Status</th><th>Payment</th><th>Partner</th><th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($bookings as $booking)
                    @php
                        $type = $booking->booking_type ?? 'flight';
                        $typeTheme = $typeThemes[$type] ?? $typeThemes['flight'];
                        $isHotel = $type === 'hotel';
                        $invoiceRoute = url(($isHotel ? 'hotel' : 'flight') . '/invoice', $booking->booking_code_ref);
                        $initials = collect(explode(' ', $booking->customer_name))
                            ->map(fn($n) => strtoupper(substr($n, 0, 1)))
                            ->take(2)->implode('');
                        $avatar = $avatarPalette[crc32($booking->customer_name) % count($avatarPalette)];
                    @endphp
                    <tr>
                        <td><input type="checkbox" class="form-check-input booking-checkbox" data-id="{{ $booking->id }}" data-type="{{ $type }}"></td>
                        <td><span class="bk-type {{ $typeTheme['cls'] }}"><i class="bi {{ $typeTheme['icon'] }}"></i> {{ ucfirst($type) }}</span></td>
                        <td>
                            <a href="{{ $invoiceRoute }}" class="bk-id-link">
                                <div class="bk-id">#{{ $booking->booking_code_ref }}</div>
                                <div class="bk-meta">{{ $booking->created_at->format('M j, Y') }}</div>
                            </a>
                        </td>
                        <td>
                            <div class="bk-customer">
                                <div class="bk-avatar" style="background:{{ $avatar['bg'] }}; color:{{ $avatar['text'] }}">{{ $initials }}</div>
                                <div><div class="bk-cust-name">{{ $booking->customer_name }}</div><div class="bk-meta">{{ $booking->customer_email }}</div></div>
                            </div>
                        </td>
                        <td>
                            @if($isHotel)
                                <div class="bk-detail">{{ $booking->hotel_info['name'] ?? 'N/A' }}</div>
                                <div class="bk-meta">{{ $booking->hotel_info['location'] ?? 'N/A' }}</div>
                            @else
                                <div class="bk-detail">{{ $booking->flight_route['route'] ?? 'N/A' }}</div>
                                <div class="bk-meta">{{ $booking->flight_route['stops'] ?? 'N/A' }}</div>
                            @endif
                        </td>
                        <td>
                            @if($isHotel)
                                <div class="bk-detail">{{ $booking->stay_dates['check_in'] ?? 'N/A' }}</div>
                                <div class="bk-meta">{{ $booking->stay_dates['nights'] ?? 'N/A' }}</div>
                            @else
                                <div class="bk-detail">{{ $booking->travel_date['date'] ?? 'N/A' }}</div>
                                <div class="bk-meta">{{ $booking->travel_date['time'] ?? 'N/A' }}</div>
                            @endif
                        </td>
                        <td>
                            @if($isHotel)
                                <span class="bk-count">{{ $booking->guest_count ?? 'N/A' }}</span>
                            @else
                                <span class="bk-count">{{ $booking->passenger_count ?? 'N/A' }}</span>
                            @endif
                        </td>
                        <td><div class="bk-amount">{{ $booking->formatted_amount }}</div><div class="bk-meta">Total</div></td>
                        <td><span class="badge-status {{ $booking->status_badge_class }}">{{ ucfirst($booking->booking_status_flag) }}</span></td>
                        <td><span class="badge-status {{ $booking->payment_status_badge_class }}">{{ ucfirst($booking->booking_payment_state) }}</span></td>
                        <td><span class="bk-meta">{{ ucfirst($booking->booking_supplier_name) }}</span></td>
                        <td>
                            <div class="bk-actions">
                                <a href="{{ route('admin.bookings.edit', ['type' => $type, 'id' => $booking->id]) }}" class="bk-action-btn" title="Edit booking"><i class="bi bi-pencil"></i></a>
                                <a href="{{ $invoiceRoute }}" class="bk-action-btn" title="View invoice" target="_blank"><i class="bi bi-file-text"></i></a>
                                <button type="button" class="bk-action-btn bk-action-danger delete-single-btn" data-id="{{ $booking->id }}" data-type="{{ $type }}" data-ref="{{ $booking->booking_code_ref }}" title="Delete booking"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12">
                            <div class="bk-empty">
                                <i class="bi bi-inbox"></i>
                                <h5>No bookings found</h5>
                                <p>Try adjusting your search criteria or filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="bk-pagination">
                <div class="bk-pagination-info">Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ number_format($bookings->total()) }} entries</div>
                <nav>{{ $bookings->links('pagination::bootstrap-4') }}</nav>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const bookingCheckboxes = document.querySelectorAll('.booking-checkbox');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const selectedCountSpan = document.getElementById('selectedCount');

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    bookingCheckboxes.forEach(checkbox => { checkbox.checked = this.checked; });
                    updateBulkDeleteButton();
                });
            }

            bookingCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateBulkDeleteButton();
                    const allChecked = Array.from(bookingCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(bookingCheckboxes).some(cb => cb.checked);
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = allChecked;
                        selectAllCheckbox.indeterminate = someChecked && !allChecked;
                    }
                });
            });

            function updateBulkDeleteButton() {
                const checkedBoxes = document.querySelectorAll('.booking-checkbox:checked');
                const count = checkedBoxes.length;
                if (count > 0) { bulkDeleteBtn.style.display = 'inline-flex'; selectedCountSpan.textContent = count; }
                else { bulkDeleteBtn.style.display = 'none'; }
            }

            if (bulkDeleteBtn) {
                bulkDeleteBtn.addEventListener('click', function() {
                    const checkedBoxes = document.querySelectorAll('.booking-checkbox:checked');
                    const bookings = [];
                    checkedBoxes.forEach(checkbox => { bookings.push({ id: checkbox.dataset.id, type: checkbox.dataset.type }); });
                    if (bookings.length === 0) { alert('Please select at least one booking to delete.'); return; }
                    if (confirm(`Are you sure you want to delete ${bookings.length} booking(s)? This action cannot be undone.`)) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("admin.bookings.bulk-delete") }}';
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden'; csrfInput.name = '_token'; csrfInput.value = csrfToken;
                        form.appendChild(csrfInput);
                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden'; methodInput.name = '_method'; methodInput.value = 'DELETE';
                        form.appendChild(methodInput);
                        const bookingsInput = document.createElement('input');
                        bookingsInput.type = 'hidden'; bookingsInput.name = 'bookings'; bookingsInput.value = JSON.stringify(bookings);
                        form.appendChild(bookingsInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }

            document.querySelectorAll('.delete-single-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id, type = this.dataset.type, ref = this.dataset.ref;
                    if (confirm(`Are you sure you want to delete booking #${ref}? This action cannot be undone.`)) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `{{ url('admin/bookings') }}/${type}/${id}`;
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden'; csrfInput.name = '_token'; csrfInput.value = csrfToken;
                        form.appendChild(csrfInput);
                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden'; methodInput.name = '_method'; methodInput.value = 'DELETE';
                        form.appendChild(methodInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
