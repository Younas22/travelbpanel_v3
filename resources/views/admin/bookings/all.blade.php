@extends('admin.layouts.app')

@section('title', 'Booking')

@section('content')
    <div class="content-area">

        @php
            $pageTitle = match($bookingType ?? 'all') {
                'hotel' => 'Hotel Bookings',
                'tour' => 'Tour Bookings',
                'umrah' => 'Umrah Bookings',
                'flight' => 'Flight Bookings',
                default => 'Bookings Management',
            };

            $avatarPalette = [
                ['bg'=>'#B5D4F4','text'=>'#0C447C'],
                ['bg'=>'#C0DD97','text'=>'#27500A'],
                ['bg'=>'#FAC775','text'=>'#633806'],
                ['bg'=>'#F4C0D1','text'=>'#72243E'],
                ['bg'=>'#CECBF6','text'=>'#3C3489'],
            ];

            $typeThemes = [
                'flight' => ['icon' => 'bi-airplane',    'cls' => 'bk-type-flight'],
                'hotel'  => ['icon' => 'bi-building',    'cls' => 'bk-type-hotel'],
                'tour'   => ['icon' => 'bi-map',         'cls' => 'bk-type-tour'],
                'umrah'  => ['icon' => 'bi-moon-stars',  'cls' => 'bk-type-umrah'],
            ];
        @endphp

            <!-- ===== PAGE HEADER ===== -->
        <div class="bk-header">
            <div>
                <h2 class="bk-title">{{ $pageTitle }}</h2>
                <p class="bk-subtitle">Track and manage {{ ($bookingType ?? 'all') === 'all' ? 'all' : $bookingType }} bookings</p>
            </div>
            <div class="bk-stats">
                <div class="bk-stat">
                    <div class="bk-stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
                    <div class="bk-stat-label">Total</div>
                </div>
                <div class="bk-stat">
                    <div class="bk-stat-value bk-stat-ok">{{ number_format($stats['confirmed'] ?? 0) }}</div>
                    <div class="bk-stat-label">Confirmed</div>
                </div>
                <div class="bk-stat">
                    <div class="bk-stat-value bk-stat-warn">{{ number_format($stats['pending'] ?? 0) }}</div>
                    <div class="bk-stat-label">Pending</div>
                </div>
            </div>
        </div>

        <!-- ===== FILTERS ===== -->
        <div class="bk-filter-card">
            <form method="GET" action="{{ route('admin.bookings.all') }}">
                <div class="bk-filter-grid">
                    <div class="bk-field bk-field-search">
                        <label class="form-label">Search bookings</label>
                        <div class="bk-search">
                            <i class="bi bi-search"></i>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Booking ID, customer name, email..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>

                    @if(($bookingType ?? 'all') === 'all')
                        <div class="bk-field">
                            <label class="form-label">Booking type</label>
                            <select name="type" class="form-select">
                                <option value="all">All types</option>
                                <option value="flight">Flight</option>
                                <option value="hotel">Hotel</option>
                                <option value="tour">Tour</option>
                                <option value="umrah">Umrah</option>
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="type" value="{{ $bookingType }}">
                    @endif

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
                        <button type="submit" class="bk-icon-btn bk-icon-btn-primary" title="Apply filters">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <a href="{{ route('admin.bookings.all') }}" class="bk-icon-btn" title="Reset filters">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- ===== BOOKINGS TABLE ===== -->
        <div class="bk-table-card">
            <div class="bk-table-header">
                <h5>All bookings ({{ number_format($bookings->total()) }})</h5>
                <button type="button" class="bk-bulk-btn" id="bulkDeleteBtn" style="display: none;">
                    <i class="bi bi-trash"></i> Delete selected (<span id="selectedCount">0</span>)
                </button>
            </div>

            <div class="table-responsive">
                <table class="bk-table">
                    <thead>
                    <tr>
                        <th width="30">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th>Type</th>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Details</th>
                        <th>Date/Stay</th>
                        <th>Passengers/Guests</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Phone</th>
                        <th>Partner</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($bookings as $booking)
                        @php
                            $get_phone = is_string($booking->booking_user_data)
                                ? json_decode($booking->booking_user_data, true)
                                : $booking->booking_user_data;

                            $type = $booking->booking_type ?? 'flight';
                            $typeTheme = $typeThemes[$type] ?? ['icon' => 'bi-question-circle', 'cls' => 'bk-type-default'];

                            $invoiceRoute = match($type) {
                                'flight' => url('flight/invoice', $booking->booking_code_ref),
                                'hotel' => url('hotel/invoice', $booking->booking_code_ref),
                                'tour' => route('tour.invoice', ['booking_ref' => $booking->booking_code_ref]),
                                'umrah' => route('umrah.invoice', ['booking_ref' => $booking->booking_code_ref]),
                                default => url('flight/invoice', $booking->booking_code_ref),
                            };

                            $initials = collect(explode(' ', $booking->customer_name))
                                ->map(fn($n) => strtoupper(substr($n, 0, 1)))
                                ->take(2)->implode('');
                            $avatar = $avatarPalette[crc32($booking->customer_name) % count($avatarPalette)];
                        @endphp
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input booking-checkbox"
                                       data-id="{{ $booking->id }}"
                                       data-type="{{ $type }}">
                            </td>
                            <td>
                                    <span class="bk-type {{ $typeTheme['cls'] }}">
                                        <i class="bi {{ $typeTheme['icon'] }}"></i>
                                        {{ ucfirst($type) }}
                                    </span>
                            </td>
                            <td>
                                <a target="_blank" href="{{ $invoiceRoute }}" class="bk-id-link">
                                    <div class="bk-id">#{{ $booking->booking_code_ref }}</div>
                                    <div class="bk-meta">{{ $booking->created_at->format('M j, Y') }}</div>
                                </a>
                            </td>
                            <td>
                                <div class="bk-customer">
                                    <div class="bk-avatar" style="background:{{ $avatar['bg'] }}; color:{{ $avatar['text'] }}">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <div class="bk-cust-name">{{ $booking->customer_name }}</div>
                                        <div class="bk-meta">{{ $booking->customer_email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($type === 'hotel')
                                    <div class="bk-detail">{{ $booking->hotel_info['name'] ?? 'N/A' }}</div>
                                    <div class="bk-meta">{{ $booking->hotel_info['location'] ?? 'N/A' }}</div>
                                @elseif($type === 'tour')
                                    <div class="bk-detail">{{ $booking->tour_info['name'] ?? 'N/A' }}</div>
                                    <div class="bk-meta">{{ $booking->tour_info['location'] ?? 'N/A' }}</div>
                                @elseif($type === 'umrah')
                                    <div class="bk-detail">{{ $booking->umrah_info['name'] ?? 'N/A' }}</div>
                                    <div class="bk-meta">{{ $booking->umrah_info['location'] ?? 'N/A' }}</div>
                                @else
                                    <div class="bk-detail">{{ $booking->flight_route['route'] ?? 'N/A' }}</div>
                                    <div class="bk-meta">{{ $booking->flight_route['stops'] ?? 'N/A' }}</div>
                                @endif
                            </td>
                            <td>
                                @if($type === 'hotel')
                                    <div class="bk-detail">{{ $booking->stay_dates['check_in'] ?? 'N/A' }}</div>
                                    <div class="bk-meta">{{ $booking->stay_dates['nights'] ?? 'N/A' }}</div>
                                @else
                                    <div class="bk-detail">{{ $booking->travel_date['date'] ?? 'N/A' }}</div>
                                    <div class="bk-meta">{{ $booking->travel_date['time'] ?? 'N/A' }}</div>
                                @endif
                            </td>
                            <td>
                                @if($type === 'hotel')
                                    <span class="bk-count">{{ $booking->guest_count ?? 'N/A' }}</span>
                                @else
                                    <span class="bk-count">{{ $booking->passenger_count ?? 'N/A' }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="bk-amount">{{ $booking->formatted_amount }}</div>
                                <div class="bk-meta">Total</div>
                            </td>
                            <td>
                                    <span class="badge-status {{ $booking->status_badge_class }}">
                                        {{ ucfirst($booking->booking_status_flag) }}
                                    </span>
                            </td>
                            <td>
                                    <span class="badge-status {{ $booking->payment_status_badge_class }}">
                                        {{ ucfirst($booking->booking_payment_state) }}
                                    </span>
                            </td>
                            <td>
                                <span class="bk-meta">{{ $get_phone['user_phone'] ?? $get_phone->user_phone ?? 'No number' }}</span>
                            </td>
                            <td>
                                <span class="bk-meta">{{ ucfirst($booking->booking_supplier_name) }}</span>
                            </td>
                            <td>
                                <div class="bk-actions">
                                    <a href="{{ route('admin.bookings.edit', ['type' => $type, 'id' => $booking->id]) }}"
                                       class="bk-action-btn" title="Edit booking">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="{{ $invoiceRoute }}"
                                       class="bk-action-btn" title="View invoice" target="_blank">
                                        <i class="bi bi-file-text"></i>
                                    </a>
                                    <button type="button" class="bk-action-btn bk-action-danger delete-single-btn"
                                            data-id="{{ $booking->id }}"
                                            data-type="{{ $type }}"
                                            data-ref="{{ $booking->booking_code_ref }}"
                                            title="Delete booking">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13">
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
                    <div class="bk-pagination-info">
                        Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ number_format($bookings->total()) }} entries
                    </div>
                    <nav>
                        {{ $bookings->links('pagination::bootstrap-4') }}
                    </nav>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            /* ===== PAGE HEADER ===== */
            .bk-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 16px;
                margin-bottom: 1.25rem;
            }
            .bk-title {
                font-size: 20px;
                font-weight: 600;
                color: var(--bs-body-color);
                margin: 0 0 4px;
            }
            .bk-subtitle {
                font-size: 13px;
                color: var(--bs-secondary-color);
                margin: 0;
            }

            .bk-stats { display: flex; gap: 10px; }
            .bk-stat {
                background: var(--bs-secondary-bg);
                border-radius: 12px;
                padding: 10px 22px;
                text-align: center;
                min-width: 84px;
            }
            .bk-stat-value {
                font-size: 20px;
                font-weight: 600;
                color: var(--bs-body-color);
                line-height: 1;
            }
            .bk-stat-label {
                font-size: 11px;
                color: var(--bs-secondary-color);
                margin-top: 4px;
                text-transform: uppercase;
                letter-spacing: .05em;
            }
            .bk-stat-ok   { color: #3B6D11; }
            .bk-stat-warn { color: #854F0B; }
            [data-bs-theme="dark"] .bk-stat-ok   { color: #97C459; }
            [data-bs-theme="dark"] .bk-stat-warn { color: #FAC775; }

            /* ===== FILTER CARD ===== */
            .bk-filter-card {
                background: var(--bs-body-bg);
                border: 1px solid var(--bs-border-color);
                border-radius: 14px;
                padding: 1.1rem 1.25rem;
                margin-bottom: 1.25rem;
            }
            .bk-filter-grid {
                display: grid;
                grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
                gap: 12px;
                align-items: end;
            }
            .bk-field label {
                font-size: 12px;
                font-weight: 500;
                color: var(--bs-secondary-color);
                margin-bottom: 4px;
                display: block;
            }
            .bk-filter-card .form-control,
            .bk-filter-card .form-select {
                border-radius: 8px;
                border-color: var(--bs-border-color);
                font-size: 13px;
            }
            .bk-search { position: relative; }
            .bk-search i {
                position: absolute;
                left: 12px;
                top: 50%;
                transform: translateY(-50%);
                font-size: 13px;
                color: var(--bs-secondary-color);
                pointer-events: none;
            }
            .bk-search .form-control { padding-left: 34px; }

            .bk-field-actions { display: flex; gap: 8px; }
            .bk-icon-btn {
                width: 38px;
                height: 38px;
                flex-shrink: 0;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--bs-border-color);
                border-radius: 8px;
                background: var(--bs-secondary-bg);
                color: var(--bs-secondary-color);
                font-size: 15px;
                text-decoration: none;
                transition: background .15s, color .15s, border-color .15s;
            }
            .bk-icon-btn:hover {
                background: var(--bs-tertiary-bg);
                color: var(--bs-body-color);
                text-decoration: none;
            }
            .bk-icon-btn-primary {
                background: var(--bs-primary);
                border-color: var(--bs-primary);
                color: #fff;
            }
            .bk-icon-btn-primary:hover {
                opacity: .9;
                color: #fff;
            }

            /* ===== TABLE CARD ===== */
            .bk-table-card {
                background: var(--bs-body-bg);
                border: 1px solid var(--bs-border-color);
                border-radius: 14px;
                overflow: hidden;
            }
            .bk-table-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 16px 20px;
                border-bottom: 1px solid var(--bs-border-color);
                background: var(--bs-secondary-bg);
            }
            .bk-table-header h5 {
                font-size: 15px;
                font-weight: 600;
                color: var(--bs-body-color);
                margin: 0;
            }
            .bk-bulk-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 6px 14px;
                font-size: 12px;
                font-weight: 500;
                color: #A32D2D;
                background: #FCEBEB;
                border: 1px solid transparent;
                border-radius: 8px;
                cursor: pointer;
                transition: opacity .15s;
            }
            .bk-bulk-btn:hover { opacity: .85; }
            [data-bs-theme="dark"] .bk-bulk-btn { background: #2e0a0a; color: #f08080; }

            /* ===== TABLE ===== */
            .bk-table { width: 100%; border-collapse: collapse; font-size: 13px; }
            .bk-table thead th {
                padding: 10px 14px;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: .4px;
                color: var(--bs-secondary-color);
                border-bottom: 1px solid var(--bs-border-color);
                white-space: nowrap;
                text-align: left;
                background: transparent;
            }
            .bk-table tbody td {
                padding: 11px 14px;
                border-bottom: 1px solid var(--bs-border-color);
                vertical-align: middle;
                color: var(--bs-body-color);
            }
            .bk-table tbody tr:last-child td { border-bottom: none; }
            .bk-table tbody tr:hover td { background: var(--bs-tertiary-bg); }

            .bk-meta { font-size: 11px; color: var(--bs-secondary-color); margin-top: 2px; }
            .bk-detail { font-size: 13px; font-weight: 500; color: var(--bs-body-color); }
            .bk-amount { font-size: 13px; font-weight: 600; color: var(--bs-body-color); }

            /* ===== TYPE BADGE ===== */
            .bk-type {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                font-size: 11px;
                font-weight: 600;
                padding: 3px 10px;
                border-radius: 20px;
                white-space: nowrap;
            }
            .bk-type-flight  { background: #E6F1FB; color: #0C447C; }
            .bk-type-hotel   { background: #EAF3DE; color: #27500A; }
            .bk-type-tour    { background: #FBEAF0; color: #72243E; }
            .bk-type-umrah   { background: #FAEEDA; color: #633806; }
            .bk-type-default { background: var(--bs-tertiary-bg); color: var(--bs-secondary-color); }

            [data-bs-theme="dark"] .bk-type-flight { background: #0c2f4d; color: #7db8f0; }
            [data-bs-theme="dark"] .bk-type-hotel  { background: #0a2e1a; color: #6dd499; }
            [data-bs-theme="dark"] .bk-type-tour   { background: #2e0a18; color: #f4a8c4; }
            [data-bs-theme="dark"] .bk-type-umrah  { background: #2e1e05; color: #f0b054; }

            /* ===== BOOKING ID LINK ===== */
            .bk-id-link { text-decoration: none; display: block; }
            .bk-id-link:hover .bk-id { text-decoration: underline; }
            .bk-id { font-size: 13px; font-weight: 600; color: var(--bs-primary); }

            /* ===== CUSTOMER ===== */
            .bk-customer { display: flex; align-items: center; gap: 9px; }
            .bk-avatar {
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
            .bk-cust-name { font-size: 13px; font-weight: 500; color: var(--bs-body-color); }

            /* ===== COUNT BADGE ===== */
            .bk-count {
                display: inline-block;
                padding: 2px 9px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 500;
                background: var(--bs-tertiary-bg);
                color: var(--bs-body-color);
            }

            /* ===== STATUS / PAYMENT BADGES ===== */
            .badge-status {
                display: inline-block;
                padding: 3px 10px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 600;
                letter-spacing: .02em;
                text-transform: capitalize;
            }
            .badge-status.bg-success { background: #EAF3DE !important; color: #27500A !important; }
            .badge-status.bg-warning { background: #FAEEDA !important; color: #633806 !important; }
            .badge-status.bg-danger  { background: #FCEBEB !important; color: #A32D2D !important; }
            .badge-status.bg-info,
            .badge-status.bg-primary { background: #E6F1FB !important; color: #0C447C !important; }
            .badge-status.bg-secondary,
            .badge-status.bg-light   { background: var(--bs-tertiary-bg) !important; color: var(--bs-secondary-color) !important; }

            [data-bs-theme="dark"] .badge-status.bg-success { background: #0a2e1a !important; color: #6dd499 !important; }
            [data-bs-theme="dark"] .badge-status.bg-warning { background: #2e1e05 !important; color: #f0b054 !important; }
            [data-bs-theme="dark"] .badge-status.bg-danger  { background: #2e0a0a !important; color: #f08080 !important; }
            [data-bs-theme="dark"] .badge-status.bg-info,
            [data-bs-theme="dark"] .badge-status.bg-primary { background: #0c2f4d !important; color: #7db8f0 !important; }

            /* ===== ACTION BUTTONS ===== */
            .bk-actions { display: flex; align-items: center; gap: 4px; }
            .bk-action-btn {
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
                transition: background .12s, color .12s, border-color .12s;
            }
            .bk-action-btn:hover {
                background: var(--bs-primary);
                color: #fff;
                border-color: var(--bs-primary);
                text-decoration: none;
            }
            .bk-action-danger:hover {
                background: #A32D2D;
                border-color: #A32D2D;
                color: #fff;
            }

            /* ===== EMPTY STATE ===== */
            .bk-empty {
                text-align: center;
                padding: 3rem 1rem;
            }
            .bk-empty i {
                font-size: 40px;
                color: var(--bs-border-color);
                margin-bottom: 12px;
                display: block;
            }
            .bk-empty h5 {
                font-size: 14px;
                font-weight: 600;
                color: var(--bs-body-color);
                margin-bottom: 4px;
            }
            .bk-empty p {
                font-size: 12px;
                color: var(--bs-secondary-color);
                margin: 0;
            }

            /* ===== PAGINATION ===== */
            .bk-pagination {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 12px;
                padding: 14px 20px;
                border-top: 1px solid var(--bs-border-color);
            }
            .bk-pagination-info {
                font-size: 12px;
                color: var(--bs-secondary-color);
            }
            .bk-pagination .pagination { margin: 0; }
            .bk-pagination .page-link {
                border-radius: 7px;
                border-color: var(--bs-border-color);
                color: var(--bs-body-color);
                font-size: 13px;
                margin: 0 2px;
                background: var(--bs-body-bg);
            }
            .bk-pagination .page-item.active .page-link {
                background: var(--bs-primary);
                border-color: var(--bs-primary);
                color: #fff;
            }
            .bk-pagination .page-item.disabled .page-link {
                color: var(--bs-secondary-color);
                background: var(--bs-secondary-bg);
            }

            /* ===== RESPONSIVE ===== */
            @media (max-width: 1200px) {
                .bk-filter-grid {
                    grid-template-columns: 1fr 1fr;
                }
                .bk-field-search { grid-column: 1 / -1; }
                .bk-field-actions { grid-column: 1 / -1; justify-content: flex-end; }
            }
            @media (max-width: 768px) {
                .bk-header { align-items: flex-start; }
                .bk-stats { width: 100%; }
                .bk-stat { flex: 1; }
                .bk-filter-grid { grid-template-columns: 1fr; }
                .bk-field-search { grid-column: auto; }
                .bk-field-actions { grid-column: auto; }
            }
        </style>
    @endpush

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
                    bookingCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
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

                if (count > 0) {
                    bulkDeleteBtn.style.display = 'inline-flex';
                    selectedCountSpan.textContent = count;
                } else {
                    bulkDeleteBtn.style.display = 'none';
                }
            }

            if (bulkDeleteBtn) {
                bulkDeleteBtn.addEventListener('click', function() {
                    const checkedBoxes = document.querySelectorAll('.booking-checkbox:checked');
                    const bookings = [];

                    checkedBoxes.forEach(checkbox => {
                        bookings.push({
                            id: checkbox.dataset.id,
                            type: checkbox.dataset.type
                        });
                    });

                    if (bookings.length === 0) {
                        alert('Please select at least one booking to delete.');
                        return;
                    }

                    if (confirm(`Are you sure you want to delete ${bookings.length} booking(s)? This action cannot be undone.`)) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("admin.bookings.bulk-delete") }}';

                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken;
                        form.appendChild(csrfInput);

                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'DELETE';
                        form.appendChild(methodInput);

                        const bookingsInput = document.createElement('input');
                        bookingsInput.type = 'hidden';
                        bookingsInput.name = 'bookings';
                        bookingsInput.value = JSON.stringify(bookings);
                        form.appendChild(bookingsInput);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }

            const deleteSingleBtns = document.querySelectorAll('.delete-single-btn');
            deleteSingleBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const type = this.dataset.type;
                    const ref = this.dataset.ref;

                    if (confirm(`Are you sure you want to delete booking #${ref}? This action cannot be undone.`)) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `{{ url('admin/bookings') }}/${type}/${id}`;

                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken;
                        form.appendChild(csrfInput);

                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'DELETE';
                        form.appendChild(methodInput);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
