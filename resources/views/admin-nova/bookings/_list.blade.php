{{-- Shared bookings table + pagination for every admin-nova/bookings/* page.
     Expects $bookings (paginated; each item's booking_type set/aliased by the controller —
     see BookingController::allBookings and friends) and $listTitle. Same fields, routes and
     accessors as the Classic table (resources/views/admin/bookings/all.blade.php). --}}
@php
    $bkTypeConfig = [
        'flight' => ['Flight', 'bi-airplane'],
        'hotel'  => ['Hotel', 'bi-building'],
        'tour'   => ['Tour', 'bi-map'],
        'umrah'  => ['Umrah', 'bi-moon-stars'],
    ];
    // Commission is computed on the fly (fare x the booking's own agent's commission rate) —
    // there's no stored per-booking commission column — and shown in the site's active currency.
    $__activeCurrencyForCommission = activeCurrency();
@endphp

<div class="tbp-bookings-list-head">
    <h2>{{ $listTitle ?? 'Bookings' }} <span>{{ number_format($bookings->total()) }} total</span></h2>
    <button type="button" id="bulkDeleteBtn" class="tbp-bookings-btn is-danger bk-bulk-bar"><i class="bi bi-trash"></i> Delete selected (<span id="selectedCount">0</span>)</button>
</div>

<div class="tbp-bookings-table-wrap">
    <table class="tbp-bookings-table">
        <thead>
            <tr>
                <th class="is-check"><input type="checkbox" id="bkSelectAll" aria-label="Select all bookings"></th>
                <th>Booking</th>
                <th>Customer</th>
                <th>Details</th>
                <th>Date / Pax</th>
                <th>Partner / Agent</th>
                <th>Amount / Status</th>
                <th class="is-actions">Actions</th>
            </tr>
        </thead>
        <tbody id="bkList">
        @forelse($bookings as $booking)
            @php
                $type = $booking->booking_type ?? 'flight';
                [$typeLabel, $typeIcon] = $bkTypeConfig[$type] ?? [ucfirst($type), 'bi-journal'];

                $invoiceRoute = match($type) {
                    'hotel' => url('hotel/invoice', $booking->booking_code_ref),
                    'tour'  => route('tour.invoice', ['booking_ref' => $booking->booking_code_ref]),
                    'umrah' => route('umrah.invoice', ['booking_ref' => $booking->booking_code_ref]),
                    default => url('flight/invoice', $booking->booking_code_ref),
                };
                $serviceName = match($type) {
                    'hotel' => $booking->hotel_info['name'] ?? 'N/A',
                    'tour'  => $booking->tour_info['name'] ?? 'N/A',
                    'umrah' => $booking->umrah_info['name'] ?? 'N/A',
                    default => $booking->flight_route['route'] ?? 'N/A',
                };
                $serviceLocation = match($type) {
                    'hotel' => $booking->hotel_info['location'] ?? 'N/A',
                    'tour'  => $booking->tour_info['location'] ?? 'N/A',
                    'umrah' => $booking->umrah_info['location'] ?? 'N/A',
                    default => $booking->flight_route['stops'] ?? 'N/A',
                };
                $dateLine = $type === 'hotel' ? ($booking->stay_dates['check_in'] ?? 'N/A') : ($booking->travel_date['date'] ?? 'N/A');
                $dateSub  = $type === 'hotel' ? ($booking->stay_dates['nights'] ?? 'N/A') : ($booking->travel_date['time'] ?? 'N/A');
                $paxCount = $type === 'hotel' ? ($booking->guest_count ?? 'N/A') : ($booking->passenger_count ?? 'N/A');

                $initials = collect(explode(' ', $booking->customer_name ?? ''))->filter()->map(fn ($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') ?: '?';
                $avatarTone = crc32($booking->customer_name ?? 'guest') % 5;

                $status = $booking->booking_status_flag ?: 'pending';
                $payment = $booking->booking_payment_state ?: 'unpaid';

                $userData = is_string($booking->booking_user_data) ? json_decode($booking->booking_user_data, true) : $booking->booking_user_data;
                $phone = is_array($userData) ? ($userData['user_phone'] ?? null) : ($userData->user_phone ?? null);

                $bookingAgent = $booking->relationLoaded('agent') ? $booking->agent : null;
                $commissionAmount = null;
                if ($bookingAgent && $bookingAgent->commission_rate) {
                    $rawCommission = $bookingAgent->calculateCommission($booking->booking_fare_base ?? 0);
                    $commissionAmount = $__activeCurrencyForCommission
                        ? convertCurrency($rawCommission, $booking->booking_currency_origin ?? 'USD', $__activeCurrencyForCommission->currency_name)
                        : $rawCommission;
                }
            @endphp
            <tr data-type="{{ $type }}" data-status="{{ $status }}">
                <td class="is-check"><input type="checkbox" class="booking-checkbox" data-id="{{ $booking->id }}" data-type="{{ $type }}" aria-label="Select booking {{ $booking->booking_code_ref }}"></td>
                <td>
                    <div class="tbp-bookings-ref">
                        <span class="tbp-bookings-type-icon is-{{ $type }}"><i class="bi {{ $typeIcon }}"></i></span>
                        <div>
                            <a href="{{ $invoiceRoute }}" target="_blank">#{{ $booking->booking_code_ref }}</a>
                            <small>{{ $booking->created_at->format('M j, Y') }}</small>
                            <span class="tbp-bookings-type is-{{ $type }}">{{ $typeLabel }}</span>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="tbp-bookings-customer">
                        <span class="tbp-bookings-avatar is-tone-{{ $avatarTone }}">{{ $initials }}</span>
                        <div>
                            <strong>{{ $booking->customer_name ?? 'N/A' }}</strong>
                            <small>{{ $booking->customer_email ?? 'N/A' }}</small>
                            @if($phone)<small>{{ $phone }}</small>@endif
                        </div>
                    </div>
                </td>
                <td class="is-details" data-label="Details"><strong>{{ \Illuminate\Support\Str::limit($serviceName, 34) }}</strong><small>{{ $serviceLocation }}</small></td>
                <td data-label="Date / Pax"><strong>{{ $dateLine }}</strong><small>{{ $dateSub }}</small><small>{{ $paxCount }}</small></td>
                <td data-label="Partner / Agent">
                    <strong>{{ ucfirst($booking->booking_supplier_name ?? 'Manual') }}</strong>
                    <small>{{ $bookingAgent ? trim($bookingAgent->first_name . ' ' . $bookingAgent->last_name) : 'Direct' }}</small>
                    @if($commissionAmount !== null)
                        <small>Commission {{ $__activeCurrencyForCommission->currency_name ?? $booking->booking_currency_origin }} {{ number_format($commissionAmount, 2) }}</small>
                    @endif
                </td>
                <td class="is-amount" data-label="Amount / Status">
                    <strong>{{ $booking->formatted_amount }}</strong>
                    <span class="tbp-bookings-status is-{{ $status }}">{{ ucfirst($status) }}</span>
                    <small class="tbp-bookings-payment is-{{ $payment }}">{{ ucfirst($payment) }}</small>
                </td>
                <td class="is-actions">
                    <div class="tbp-bookings-actions">
                        <a href="{{ $invoiceRoute }}" target="_blank" title="View invoice"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('admin.bookings.edit', ['type' => $type, 'id' => $booking->id]) }}" title="Edit booking"><i class="bi bi-pencil"></i></a>
                        <button type="button" class="delete-single-btn is-danger" data-id="{{ $booking->id }}" data-type="{{ $type }}" data-ref="{{ $booking->booking_code_ref }}" title="Delete booking"><i class="bi bi-trash"></i></button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="tbp-bookings-empty">
                    <i class="bi bi-calendar-x"></i>
                    <strong>No bookings found</strong>
                    <span>Try adjusting your search criteria or filters.</span>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($bookings->hasPages())
    <div class="tbp-bookings-pagination">
        <span>Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ number_format($bookings->total()) }} entries</span>
        {{ $bookings->links('pagination::bootstrap-4') }}
    </div>
@endif
