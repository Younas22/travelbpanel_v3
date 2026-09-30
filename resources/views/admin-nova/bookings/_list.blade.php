{{-- Shared row-card list + pagination for every admin-nova/bookings/* page.
     Expects $bookings (a paginated collection, each item's booking_type
     already set/aliased by the controller — see BookingController::allBookings
     and friends). Same real fields/routes/accessors as the Classic table
     view (resources/views/admin/bookings/all.blade.php), just re-skinned as
     Nova's row-card style instead of a <table>. --}}
@php
    $avatarPalette = [
        ['bg' => '#DBEAFE', 'text' => '#1D4ED8'],
        ['bg' => '#DCFCE7', 'text' => '#15803D'],
        ['bg' => '#FEF3C7', 'text' => '#B45309'],
        ['bg' => '#FCE7F3', 'text' => '#BE185D'],
        ['bg' => '#EDE9FE', 'text' => '#6D28D9'],
    ];
    $bkTypeConfig = [
        'flight' => ['label' => 'Flight', 'badge' => 'bg-blue-50 text-novablue', 'icon' => 'bg-blue-50 text-novablue'],
        'hotel'  => ['label' => 'Hotel',  'badge' => 'bg-emerald-50 text-emerald-600', 'icon' => 'bg-emerald-50 text-emerald-600'],
        'tour'   => ['label' => 'Tour',   'badge' => 'bg-amber-50 text-amber-600', 'icon' => 'bg-amber-50 text-amber-600'],
        'umrah'  => ['label' => 'Umrah',  'badge' => 'bg-violet-50 text-violet-600', 'icon' => 'bg-violet-50 text-violet-600'],
    ];

    // Commission is computed on the fly (fare x the booking's own agent's
    // commission_rate%) — there's no stored per-booking commission column,
    // and shown converted into whichever currency is active site-wide
    // (admin/currencies).
    $__activeCurrencyForCommission = activeCurrency();
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex items-center gap-2">
        <h2 class="text-sm font-semibold text-novatext">{{ $listTitle ?? 'Bookings' }}</h2>
        <span class="text-xs text-novamuted">{{ number_format($bookings->total()) }} total</span>
    </div>
    <button type="button" id="bulkDeleteBtn" class="bk-bulk-bar tt-btn items-center gap-1.5 rounded-full bg-novadanger hover:bg-red-600 text-white px-4 py-2 text-xs font-semibold">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
        Delete selected (<span id="selectedCount">0</span>)
    </button>
</div>

{{-- Column headings — only shown once the row-cards below lock into the
     single-line lg: layout; below that breakpoint every field already
     carries its own small inline label, so a separate heading row would
     just duplicate them. --}}
{{-- Plain "gap-3"/"px-4" would collide with Bootstrap's own
     .gap-3{gap:1rem!important} (16px vs Tailwind's 12px) and
     .px-4{padding:1.5rem!important} (24px vs Tailwind's 16px) — Bootstrap's
     !important wins on the unprefixed class names, quietly shifting every
     column right of "Booking" out of alignment with the data rows below.
     "lg:gap-3"/"lg:px-4" sidestep the collision entirely (Bootstrap has no
     such classes) and match the data rows, which already use
     "lg:gap-3"/"lg:p-4" for the same reason. --}}
<div class="hidden lg:flex lg:items-center lg:gap-3 lg:px-4 pb-2 mb-1">
    <span class="w-4 flex-shrink-0"></span>
    <span class="w-10 flex-shrink-0"></span>
    <span class="w-32 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Booking</span>
    <span class="w-40 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Customer</span>
    <span class="flex-1 min-w-0 px-2 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Details</span>
    <span class="w-24 flex-shrink-0 text-center text-[10px] font-semibold uppercase tracking-wide text-novamuted">Date / Stay</span>
    <span class="w-16 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Pax</span>
    <span class="w-24 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Phone</span>
    <span class="w-20 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Partner</span>
    <span class="w-24 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Agent</span>
    <span class="w-24 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Commission</span>
    <span class="w-28 flex-shrink-0 text-right text-[10px] font-semibold uppercase tracking-wide text-novamuted">Amount / Status</span>
    <span class="flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted" style="width:96px;">Actions</span>
</div>

<div class="space-y-3" id="bkList">
    @forelse($bookings as $booking)
        @php
            $type = $booking->booking_type ?? 'flight';
            $tc = $bkTypeConfig[$type] ?? ['label' => ucfirst($type), 'badge' => 'bg-slate-100 text-novatext', 'icon' => 'bg-slate-100 text-novatext'];

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
                default => $booking->flight_route['stops'] ?? 'N/A',
            };

            $dateLine = $type === 'hotel' ? ($booking->stay_dates['check_in'] ?? 'N/A') : ($booking->travel_date['date'] ?? 'N/A');
            $dateSub  = $type === 'hotel' ? ($booking->stay_dates['nights'] ?? 'N/A') : ($booking->travel_date['time'] ?? 'N/A');
            $paxCount = $type === 'hotel' ? ($booking->guest_count ?? 'N/A') : ($booking->passenger_count ?? 'N/A');

            $initials = collect(explode(' ', $booking->customer_name ?? ''))
                ->filter()->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') ?: '?';
            $avatar = $avatarPalette[crc32($booking->customer_name ?? 'guest') % count($avatarPalette)];

            $statusColor = match($booking->booking_status_flag) {
                'confirmed' => 'text-novasuccess',
                'cancelled' => 'text-novadanger',
                default => 'text-novawarning',
            };
            $paymentColor = match($booking->booking_payment_state) {
                'paid' => 'text-novasuccess',
                'refunded' => 'text-novablue',
                default => 'text-novawarning',
            };

            $userData = is_string($booking->booking_user_data) ? json_decode($booking->booking_user_data, true) : $booking->booking_user_data;
            $phone = (is_array($userData) ? ($userData['user_phone'] ?? null) : ($userData->user_phone ?? null)) ?: 'No number';

            $bookingAgent = $booking->relationLoaded('agent') ? $booking->agent : null;
            $commissionAmount = null;
            if ($bookingAgent && $bookingAgent->commission_rate) {
                $rawCommission = ($booking->booking_fare_base ?? 0) * ($bookingAgent->commission_rate / 100);
                $commissionAmount = $__activeCurrencyForCommission
                    ? convertCurrency($rawCommission, $booking->booking_currency_origin ?? 'USD', $__activeCurrencyForCommission->currency_name)
                    : $rawCommission;
            }
        @endphp
        <div class="tt-row flex flex-col lg:flex-row lg:items-center gap-2.5 lg:gap-3 rounded-2xl border border-novaborder p-3 lg:p-4"
             data-type="{{ $type }}" data-status="{{ $booking->booking_status_flag }}">

            {{-- Top line on mobile (checkbox, icon, booking id, amount);
                 first three segments of the single row from lg: up. --}}
            <div class="flex items-center gap-2.5 lg:gap-3">
                <input type="checkbox" class="booking-checkbox w-4 h-4 rounded border-novaborder text-novablue flex-shrink-0"
                       data-id="{{ $booking->id }}" data-type="{{ $type }}">

                <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-full {{ $tc['icon'] }} flex items-center justify-center flex-shrink-0">
                    @switch($type)
                        @case('hotel')
                            <svg class="w-4 h-4 lg:w-4.5 lg:h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="1"/><path d="M9 22v-4h6v4M9 7h1M9 11h1M14 7h1M14 11h1"/></svg>
                            @break
                        @case('tour')
                            <svg class="w-4 h-4 lg:w-4.5 lg:h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8 6 6 9.5 6 13a6 6 0 0 0 12 0c0-3.5-2-7-6-11Z"/></svg>
                            @break
                        @case('umrah')
                            <svg class="w-4 h-4 lg:w-4.5 lg:h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
                            @break
                        @default
                            <svg class="w-4 h-4 lg:w-4.5 lg:h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 19.5 21 12 2.5 4.5 5 11l-2.5.5L5 12l-2.5.5Z"/></svg>
                    @endswitch
                </div>

                <a href="{{ $invoiceRoute }}" target="_blank" class="min-w-0 flex-1 lg:flex-none lg:w-32">
                    <p class="text-xs lg:text-sm font-semibold text-novatext truncate">#{{ $booking->booking_code_ref }}</p>
                    <p class="text-[10px] lg:text-xs text-novamuted truncate mt-0.5">{{ $booking->created_at->format('M j, Y') }}</p>
                    <span class="inline-flex mt-1 lg:mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $tc['badge'] }}">{{ $tc['label'] }}</span>
                </a>

                {{-- Amount shows up here on mobile/tablet (top-right of the
                     card); hidden again from lg: where it moves to its own
                     column at the end of the single-line row instead. --}}
                <div class="lg:hidden text-right flex-shrink-0">
                    <p class="text-xs font-bold text-novatext truncate">{{ $booking->formatted_amount }}</p>
                    <p class="text-[10px] {{ $statusColor }} font-semibold mt-0.5 truncate">{{ ucfirst($booking->booking_status_flag) }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 lg:w-40 lg:flex-shrink-0">
                <div class="w-7 h-7 lg:w-8 lg:h-8 rounded-full flex items-center justify-center text-[10px] lg:text-[11px] font-bold flex-shrink-0"
                     style="background:{{ $avatar['bg'] }}; color:{{ $avatar['text'] }}">{{ $initials }}</div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-novatext truncate">{{ $booking->customer_name ?? 'N/A' }}</p>
                    <p class="text-[11px] text-novamuted truncate">{{ $booking->customer_email ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="lg:flex-1 min-w-0 lg:px-2">
                <p class="text-xs font-semibold text-novatext truncate">{{ Str::limit($serviceName, 28) }}</p>
                <p class="text-[11px] text-novamuted truncate mt-0.5">{{ $serviceLocation }}</p>
            </div>

            {{-- Date/Stay, Passengers, Phone, and Partner — a compact strip
                 that wraps freely on mobile/tablet and locks to one line
                 with fixed column widths from lg: up. --}}
            <div class="flex flex-wrap items-start gap-x-4 gap-y-1.5 lg:flex-nowrap lg:gap-4">
                <div class="w-20 lg:w-24 flex-shrink-0 lg:text-center">
                    <p class="text-[9px] text-novamuted">Date / Stay</p>
                    <p class="text-xs font-semibold text-novatext mt-0.5">{{ $dateLine }}</p>
                    <p class="text-[10px] text-novamuted">{{ $dateSub }}</p>
                </div>
                <div class="w-20 lg:w-16 flex-shrink-0 min-w-0">
                    <p class="text-[9px] text-novamuted">{{ $type === 'hotel' ? 'Guests' : 'Passengers' }}</p>
                    <p class="text-xs font-semibold text-novatext truncate mt-0.5">{{ $paxCount }}</p>
                </div>
                <div class="w-24 flex-shrink-0 min-w-0">
                    <p class="text-[9px] text-novamuted">Phone</p>
                    <p class="text-xs text-novatext truncate mt-0.5">{{ $phone }}</p>
                </div>
                <div class="w-20 flex-shrink-0 min-w-0">
                    <p class="text-[9px] text-novamuted">Partner</p>
                    <p class="text-xs text-novatext truncate mt-0.5">{{ ucfirst($booking->booking_supplier_name ?? 'Manual') }}</p>
                </div>
                <div class="w-24 flex-shrink-0 min-w-0">
                    <p class="text-[9px] text-novamuted">Agent</p>
                    @if($bookingAgent)
                        <p class="text-xs text-novatext truncate mt-0.5">{{ trim($bookingAgent->first_name . ' ' . $bookingAgent->last_name) }}</p>
                    @else
                        <p class="text-xs text-novamuted mt-0.5">Direct</p>
                    @endif
                </div>
                <div class="w-24 flex-shrink-0 min-w-0">
                    <p class="text-[9px] text-novamuted">Commission</p>
                    @if($commissionAmount !== null)
                        <p class="text-xs font-semibold text-novatext truncate mt-0.5">{{ $__activeCurrencyForCommission->currency_name ?? $booking->booking_currency_origin }} {{ number_format($commissionAmount, 2) }}</p>
                    @else
                        <p class="text-xs text-novamuted mt-0.5">—</p>
                    @endif
                </div>
            </div>

            {{-- Full amount/status/payment column — desktop only (mobile's
                 compact version is in the top line above). --}}
            <div class="hidden lg:block w-28 flex-shrink-0 text-right lg:ml-auto">
                <p class="text-sm font-bold text-novatext truncate">{{ $booking->formatted_amount }}</p>
                <p class="text-[11px] {{ $statusColor }} font-semibold mt-0.5 truncate">{{ ucfirst($booking->booking_status_flag) }}</p>
                <p class="text-[10px] {{ $paymentColor }} font-medium">{{ ucfirst($booking->booking_payment_state) }}</p>
            </div>

            <div class="flex items-center gap-1 flex-shrink-0 self-end lg:self-auto">
                <a href="{{ route('admin.bookings.edit', ['type' => $type, 'id' => $booking->id]) }}"
                   class="tt-btn w-7 h-7 lg:w-8 lg:h-8 rounded-full border border-novaborder flex items-center justify-center hover:bg-novabg" title="Edit booking">
                    <svg class="w-3 h-3 lg:w-3.5 lg:h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                </a>
                <a href="{{ $invoiceRoute }}" target="_blank"
                   class="tt-btn w-7 h-7 lg:w-8 lg:h-8 rounded-full border border-novaborder flex items-center justify-center hover:bg-novabg" title="View invoice">
                    <svg class="w-3 h-3 lg:w-3.5 lg:h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg>
                </a>
                <button type="button"
                        class="tt-btn delete-single-btn w-7 h-7 lg:w-8 lg:h-8 rounded-full border border-red-200 flex items-center justify-center text-novadanger hover:bg-red-50"
                        data-id="{{ $booking->id }}" data-type="{{ $type }}" data-ref="{{ $booking->booking_code_ref }}" title="Delete booking">
                    <svg class="w-3 h-3 lg:w-3.5 lg:h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                </button>
            </div>
        </div>
    @empty
        <div class="text-center py-16">
            <svg class="w-10 h-10 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4"/></svg>
            <p class="text-sm font-semibold text-novatext">No bookings found</p>
            <p class="text-xs text-novamuted mt-1">Try adjusting your search criteria or filters.</p>
        </div>
    @endforelse
</div>

@if($bookings->hasPages())
    <div class="flex flex-wrap items-center justify-between gap-3 mt-6 pt-5 border-t border-novaborder">
        <p class="text-xs text-novamuted">Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ number_format($bookings->total()) }} entries</p>
        {{ $bookings->links('pagination::bootstrap-4') }}
    </div>
@endif
