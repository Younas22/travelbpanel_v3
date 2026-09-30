<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlightBooking;
use App\Models\HotelBooking;
use App\Models\TourBooking;
use App\Models\UmrahBooking;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Resend\Laravel\Facades\Resend;

class BookingController extends Controller
{


    /**
     * Display all bookings with filters and search
     */
    public function allBookings(Request $request)
    {
        // Get booking type filter (default: all)
        $bookingType = $request->get('type', 'all');

        // Determine which models to query
        if ($bookingType === 'flight') {
            $bookings = $this->getFlightBookings($request);
        } elseif ($bookingType === 'hotel') {
            $bookings = $this->getHotelBookings($request);
        } elseif ($bookingType === 'tour') {
            $bookings = $this->getTourBookings($request);
        } elseif ($bookingType === 'umrah') {
            $bookings = $this->getUmrahBookings($request);
        } else {
            // Merge all bookings (flight, hotel, tour, umrah)
            $bookings = $this->getAllBookings($request);
        }

        // Commission is computed from each booking's own agent (fare x that
        // agent's commission_rate%) — load it once here rather than in the
        // view, and rely on it being the same relation name on every booking
        // model regardless of which type actually produced the row (the
        // union query in getAllBookings() always hydrates as FlightBooking,
        // but all 4 booking models define an identical agent() relation).
        $bookings->getCollection()->load('agent:id,first_name,last_name,company_name,commission_rate');

        // Agents list for the filter dropdown
        $agents = User::where('user_type', User::TYPE_AGENT)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'company_name']);

        // Calculate statistics
        $stats = $this->getBookingStats($bookingType);

        return view('admin.bookings.all', compact('bookings', 'stats', 'bookingType', 'agents'));
    }

    /**
     * Resolve the effective [date_from, date_to] filter range. A "month"
     * filter (YYYY-MM, from a single month picker) takes precedence over the
     * separate date_from/date_to fields when both are present.
     */
    private function resolveDateRange(Request $request): array
    {
        if ($request->filled('month')) {
            $monthStart = Carbon::parse($request->month . '-01')->startOfMonth();
            return [$monthStart->copy()->startOfDay(), $monthStart->copy()->endOfMonth()->endOfDay()];
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            return [
                $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null,
                $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null,
            ];
        }

        return [null, null];
    }

    /**
     * Get flight bookings with filters
     */
    private function getFlightBookings(Request $request)
    {
        $query = FlightBooking::query();

        // Apply search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Apply payment state filter
        if ($request->filled('payment_state')) {
            $query->byPaymentState($request->payment_state);
        }

        // Apply agent filter
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        // Apply date range filter (a "month" picker takes precedence over date_from/date_to)
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        if ($dateFrom || $dateTo) {
            $query->byDateRange($dateFrom, $dateTo);
        }

        // Get paginated results and add booking_type to each
        $bookings = $query->latest()->paginate(15)->withQueryString();

        // Add booking_type to each item in the collection
        $bookings->getCollection()->transform(function($booking) {
            $booking->booking_type = 'flight';
            return $booking;
        });

        return $bookings;
    }

    /**
     * Get hotel bookings with filters
     */
    private function getHotelBookings(Request $request)
    {
        $query = HotelBooking::query();

        // Apply search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Apply payment state filter
        if ($request->filled('payment_state')) {
            $query->byPaymentState($request->payment_state);
        }

        // Apply agent filter
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        // Apply date range filter (a "month" picker takes precedence over date_from/date_to)
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        if ($dateFrom || $dateTo) {
            $query->byDateRange($dateFrom, $dateTo);
        }

        // Get paginated results and add booking_type to each
        $bookings = $query->latest()->paginate(15)->withQueryString();

        // Add booking_type to each item in the collection
        $bookings->getCollection()->transform(function($booking) {
            $booking->booking_type = 'hotel';
            return $booking;
        });

        return $bookings;
    }

    /**
     * Get tour bookings with filters
     */
    private function getTourBookings(Request $request)
    {
        $query = TourBooking::query();

        // Apply search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Apply payment state filter
        if ($request->filled('payment_state')) {
            $query->byPaymentState($request->payment_state);
        }

        // Apply agent filter
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        // Apply date range filter (a "month" picker takes precedence over date_from/date_to)
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        if ($dateFrom || $dateTo) {
            $query->byDateRange($dateFrom, $dateTo);
        }

        // Get paginated results and add booking_type to each
        $bookings = $query->latest()->paginate(15)->withQueryString();

        // Add booking_type to each item in the collection
        $bookings->getCollection()->transform(function($booking) {
            $booking->booking_type = 'tour';
            return $booking;
        });

        return $bookings;
    }

    /**
     * Get umrah bookings with filters
     */
    private function getUmrahBookings(Request $request)
    {
        $query = UmrahBooking::query();

        // Apply search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Apply payment state filter
        if ($request->filled('payment_state')) {
            $query->byPaymentState($request->payment_state);
        }

        // Apply agent filter
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        // Apply date range filter (a "month" picker takes precedence over date_from/date_to)
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        if ($dateFrom || $dateTo) {
            $query->byDateRange($dateFrom, $dateTo);
        }

        // Get paginated results and add booking_type to each
        $bookings = $query->latest()->paginate(15)->withQueryString();

        // Add booking_type to each item in the collection
        $bookings->getCollection()->transform(function($booking) {
            $booking->booking_type = 'umrah';
            return $booking;
        });

        return $bookings;
    }

    /**
     * Get all bookings (flight + hotel + tour + umrah) merged and paginated
     */
    private function getAllBookings(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $applyCommonFilters = function ($q) use ($request, $dateFrom, $dateTo) {
            $q->when($request->filled('search'), fn($q) => $q->search($request->search))
              ->when($request->filled('status'), fn($q) => $q->byStatus($request->status))
              ->when($request->filled('payment_state'), fn($q) => $q->byPaymentState($request->payment_state))
              ->when($request->filled('agent_id'), fn($q) => $q->where('agent_id', $request->agent_id))
              ->when($dateFrom || $dateTo, fn($q) => $q->byDateRange($dateFrom, $dateTo));
        };

        // Flight bookings query
        $flightQuery = FlightBooking::select(
            'id',
            'agent_id',
            DB::raw('CAST(booking_code_ref AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_code_ref'),
            DB::raw('CAST(booking_status_flag AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_status_flag'),
            DB::raw('CAST(booking_payment_state AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_payment_state'),
            DB::raw('CAST(booking_user_data AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_user_data'),
            DB::raw('CAST(booking_guest AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_guest'),
            DB::raw('CAST(booking_currency_origin AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_currency_origin'),
            DB::raw('CAST(booking_fare_base AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_fare_base'),
            DB::raw('CAST(booking_supplier_name AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_supplier_name'),
            'created_at'
        )->tap($applyCommonFilters)
        ->addSelect(DB::raw("'flight' COLLATE utf8mb4_unicode_ci as booking_type"));

        // Hotel bookings query
        $hotelQuery = HotelBooking::select(
            'id',
            'agent_id',
            DB::raw('CAST(booking_code_ref AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_code_ref'),
            DB::raw('CAST(booking_status_flag AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_status_flag'),
            DB::raw('CAST(booking_payment_state AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_payment_state'),
            DB::raw('CAST(booking_user_data AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_user_data'),
            DB::raw('CAST(booking_guest AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_guest'),
            DB::raw('CAST(booking_currency_origin AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_currency_origin'),
            DB::raw('CAST(booking_fare_base AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_fare_base'),
            DB::raw('CAST(booking_supplier_name AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_supplier_name'),
            'created_at'
        )->tap($applyCommonFilters)
        ->addSelect(DB::raw("'hotel' COLLATE utf8mb4_unicode_ci as booking_type"));

        // Tour bookings query
        $tourQuery = TourBooking::select(
            'id',
            'agent_id',
            DB::raw('CAST(booking_code_ref AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_code_ref'),
            DB::raw('CAST(booking_status_flag AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_status_flag'),
            DB::raw('CAST(booking_payment_state AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_payment_state'),
            DB::raw('CAST(booking_user_data AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_user_data'),
            DB::raw('CAST(booking_guest AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_guest'),
            DB::raw('CAST(booking_currency_origin AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_currency_origin'),
            DB::raw('CAST(booking_fare_base AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_fare_base'),
            DB::raw('CAST(booking_supplier_name AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_supplier_name'),
            'created_at'
        )->tap($applyCommonFilters)
        ->addSelect(DB::raw("'tour' COLLATE utf8mb4_unicode_ci as booking_type"));

        // Umrah bookings query
        $umrahQuery = UmrahBooking::select(
            'id',
            'agent_id',
            DB::raw('CAST(booking_code_ref AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_code_ref'),
            DB::raw('CAST(booking_status_flag AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_status_flag'),
            DB::raw('CAST(booking_payment_state AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_payment_state'),
            DB::raw('CAST(booking_user_data AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_user_data'),
            DB::raw('CAST(booking_guest AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_guest'),
            DB::raw('CAST(booking_currency_origin AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_currency_origin'),
            DB::raw('CAST(booking_fare_base AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_fare_base'),
            DB::raw('CAST(booking_supplier_name AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as booking_supplier_name'),
            'created_at'
        )->tap($applyCommonFilters)
        ->addSelect(DB::raw("'umrah' COLLATE utf8mb4_unicode_ci as booking_type"));

        // Union all and order by created_at
        $allBookings = $flightQuery->unionAll($hotelQuery)
            ->unionAll($tourQuery)
            ->unionAll($umrahQuery)
            ->orderByDesc('created_at')
            ->paginate(15);

        return $allBookings;
    }


    /**
     * Display cancelled and refunded bookings
     */
    public function cancelledRefunds(Request $request)
    {
        $bookingType = $request->get('type', 'all');

        if ($bookingType === 'flight') {
            $bookings = $this->getCancelledFlightBookings($request);
        } elseif ($bookingType === 'hotel') {
            $bookings = $this->getCancelledHotelBookings($request);
        } elseif ($bookingType === 'tour') {
            $bookings = $this->getCancelledTourBookings($request);
        } elseif ($bookingType === 'umrah') {
            $bookings = $this->getCancelledUmrahBookings($request);
        } else {
            $bookings = $this->getAllCancelledBookings($request);
        }

        $stats = $this->getBookingStats($bookingType);
        return view('admin.bookings.cancelled-refunds', compact('bookings', 'stats', 'bookingType'));
    }

    /**
     * Get cancelled flight bookings
     */
    private function getCancelledFlightBookings(Request $request)
    {
        $query = FlightBooking::query()
            ->whereIn('booking_status_flag', ['cancelled'])
            ->orWhere('booking_payment_state', 'refunded');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $query->byDateRange($dateFrom, $dateTo);
        }

        return $query->latest()->paginate(15)->withQueryString();
    }

    /**
     * Get cancelled hotel bookings
     */
    private function getCancelledHotelBookings(Request $request)
    {
        $query = HotelBooking::query()
            ->whereIn('booking_status_flag', ['cancelled'])
            ->orWhere('booking_payment_state', 'refunded');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $query->byDateRange($dateFrom, $dateTo);
        }

        return $query->latest()->paginate(15)->withQueryString();
    }

    /**
     * Get cancelled tour bookings
     */
    private function getCancelledTourBookings(Request $request)
    {
        $query = TourBooking::query()
            ->whereIn('booking_status_flag', ['cancelled'])
            ->orWhere('booking_payment_state', 'refunded');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $query->byDateRange($dateFrom, $dateTo);
        }

        return $query->latest()->paginate(15)->withQueryString();
    }

    /**
     * Get cancelled umrah bookings
     */
    private function getCancelledUmrahBookings(Request $request)
    {
        $query = UmrahBooking::query()
            ->whereIn('booking_status_flag', ['cancelled'])
            ->orWhere('booking_payment_state', 'refunded');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $query->byDateRange($dateFrom, $dateTo);
        }

        return $query->latest()->paginate(15)->withQueryString();
    }

    /**
     * Get all cancelled bookings
     */
    private function getAllCancelledBookings(Request $request)
    {
        // Flight cancelled/refunded bookings
        $flightQuery = FlightBooking::select(
            'id',
            'booking_code_ref',
            'booking_status_flag',
            'booking_payment_state',
            'booking_user_data',
            'booking_guest',
            'booking_currency_origin',
            'booking_fare_base',
            'booking_supplier_name',
            'created_at'
        )
        ->whereIn('booking_status_flag', ['cancelled'])
        ->orWhere('booking_payment_state', 'refunded')
        ->when($request->filled('search'), fn($q) => $q->search($request->search))
        ->when($request->filled('date_from') || $request->filled('date_to'), function($q) use ($request) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $q->byDateRange($dateFrom, $dateTo);
        })
        ->addSelect(\DB::raw("'flight' as booking_type"));

        // Hotel cancelled/refunded bookings
        $hotelQuery = HotelBooking::select(
            'id',
            'booking_code_ref',
            'booking_status_flag',
            'booking_payment_state',
            'booking_user_data',
            'booking_guest',
            'booking_currency_origin',
            'booking_fare_base',
            'booking_supplier_name',
            'created_at'
        )
        ->whereIn('booking_status_flag', ['cancelled'])
        ->orWhere('booking_payment_state', 'refunded')
        ->when($request->filled('search'), fn($q) => $q->search($request->search))
        ->when($request->filled('date_from') || $request->filled('date_to'), function($q) use ($request) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $q->byDateRange($dateFrom, $dateTo);
        })
        ->addSelect(\DB::raw("'hotel' as booking_type"));

        // Tour cancelled/refunded bookings
        $tourQuery = TourBooking::select(
            'id',
            'booking_code_ref',
            'booking_status_flag',
            'booking_payment_state',
            'booking_user_data',
            'booking_guest',
            'booking_currency_origin',
            'booking_fare_base',
            'booking_supplier_name',
            'created_at'
        )
        ->whereIn('booking_status_flag', ['cancelled'])
        ->orWhere('booking_payment_state', 'refunded')
        ->when($request->filled('search'), fn($q) => $q->search($request->search))
        ->when($request->filled('date_from') || $request->filled('date_to'), function($q) use ($request) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $q->byDateRange($dateFrom, $dateTo);
        })
        ->addSelect(\DB::raw("'tour' as booking_type"));

        // Umrah cancelled/refunded bookings
        $umrahQuery = UmrahBooking::select(
            'id',
            'booking_code_ref',
            'booking_status_flag',
            'booking_payment_state',
            'booking_user_data',
            'booking_guest',
            'booking_currency_origin',
            'booking_fare_base',
            'booking_supplier_name',
            'created_at'
        )
        ->whereIn('booking_status_flag', ['cancelled'])
        ->orWhere('booking_payment_state', 'refunded')
        ->when($request->filled('search'), fn($q) => $q->search($request->search))
        ->when($request->filled('date_from') || $request->filled('date_to'), function($q) use ($request) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $q->byDateRange($dateFrom, $dateTo);
        })
        ->addSelect(\DB::raw("'umrah' as booking_type"));

        // Union all and order by created_at
        $allBookings = $flightQuery->unionAll($hotelQuery)
            ->unionAll($tourQuery)
            ->unionAll($umrahQuery)
            ->orderByDesc('created_at')
            ->paginate(15);

        return $allBookings;
    }


    /**
     * Display pending confirmations
     */
    public function pendingConfirmations(Request $request)
    {
        $bookingType = $request->get('type', 'all');

        if ($bookingType === 'flight') {
            $bookings = $this->getPendingFlightBookings($request);
        } elseif ($bookingType === 'hotel') {
            $bookings = $this->getPendingHotelBookings($request);
        } elseif ($bookingType === 'tour') {
            $bookings = $this->getPendingTourBookings($request);
        } elseif ($bookingType === 'umrah') {
            $bookings = $this->getPendingUmrahBookings($request);
        } else {
            $bookings = $this->getAllPendingBookings($request);
        }

        $stats = $this->getBookingStats($bookingType);
        return view('admin.bookings.pending-confirmations', compact('bookings', 'stats', 'bookingType'));
    }

    /**
     * Get pending flight bookings
     */
    private function getPendingFlightBookings(Request $request)
    {
        $query = FlightBooking::query()->byStatus('pending');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $query->byDateRange($dateFrom, $dateTo);
        }

        return $query->latest()->paginate(15)->withQueryString();
    }

    /**
     * Get pending hotel bookings
     */
    private function getPendingHotelBookings(Request $request)
    {
        $query = HotelBooking::query()->byStatus('pending');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $query->byDateRange($dateFrom, $dateTo);
        }

        return $query->latest()->paginate(15)->withQueryString();
    }

    /**
     * Get pending tour bookings
     */
    private function getPendingTourBookings(Request $request)
    {
        $query = TourBooking::query()->byStatus('pending');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $query->byDateRange($dateFrom, $dateTo);
        }

        return $query->latest()->paginate(15)->withQueryString();
    }

    /**
     * Get pending umrah bookings
     */
    private function getPendingUmrahBookings(Request $request)
    {
        $query = UmrahBooking::query()->byStatus('pending');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $query->byDateRange($dateFrom, $dateTo);
        }

        return $query->latest()->paginate(15)->withQueryString();
    }

    /**
     * Get all pending bookings
     */
    private function getAllPendingBookings(Request $request)
    {
        // Flight pending bookings
        $flightQuery = FlightBooking::select(
            'id',
            'booking_code_ref',
            'booking_status_flag',
            'booking_payment_state',
            'booking_user_data',
            'booking_guest',
            'booking_currency_origin',
            'booking_fare_base',
            'booking_supplier_name',
            'created_at'
        )->byStatus('pending')
        ->when($request->filled('search'), fn($q) => $q->search($request->search))
        ->when($request->filled('date_from') || $request->filled('date_to'), function($q) use ($request) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $q->byDateRange($dateFrom, $dateTo);
        })
        ->addSelect(\DB::raw("'flight' as booking_type"));

        // Hotel pending bookings
        $hotelQuery = HotelBooking::select(
            'id',
            'booking_code_ref',
            'booking_status_flag',
            'booking_payment_state',
            'booking_user_data',
            'booking_guest',
            'booking_currency_origin',
            'booking_fare_base',
            'booking_supplier_name',
            'created_at'
        )->byStatus('pending')
        ->when($request->filled('search'), fn($q) => $q->search($request->search))
        ->when($request->filled('date_from') || $request->filled('date_to'), function($q) use ($request) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $q->byDateRange($dateFrom, $dateTo);
        })
        ->addSelect(\DB::raw("'hotel' as booking_type"));

        // Tour pending bookings
        $tourQuery = TourBooking::select(
            'id',
            'booking_code_ref',
            'booking_status_flag',
            'booking_payment_state',
            'booking_user_data',
            'booking_guest',
            'booking_currency_origin',
            'booking_fare_base',
            'booking_supplier_name',
            'created_at'
        )->byStatus('pending')
        ->when($request->filled('search'), fn($q) => $q->search($request->search))
        ->when($request->filled('date_from') || $request->filled('date_to'), function($q) use ($request) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $q->byDateRange($dateFrom, $dateTo);
        })
        ->addSelect(\DB::raw("'tour' as booking_type"));

        // Umrah pending bookings
        $umrahQuery = UmrahBooking::select(
            'id',
            'booking_code_ref',
            'booking_status_flag',
            'booking_payment_state',
            'booking_user_data',
            'booking_guest',
            'booking_currency_origin',
            'booking_fare_base',
            'booking_supplier_name',
            'created_at'
        )->byStatus('pending')
        ->when($request->filled('search'), fn($q) => $q->search($request->search))
        ->when($request->filled('date_from') || $request->filled('date_to'), function($q) use ($request) {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
            $q->byDateRange($dateFrom, $dateTo);
        })
        ->addSelect(\DB::raw("'umrah' as booking_type"));

        // Union all and order by created_at
        $allBookings = $flightQuery->unionAll($hotelQuery)
            ->unionAll($tourQuery)
            ->unionAll($umrahQuery)
            ->orderByDesc('created_at')
            ->paginate(15);

        return $allBookings;
    }



    /**
     * Get booking statistics
     */
    private function getBookingStats($bookingType = 'all')
    {
        if ($bookingType === 'flight') {
            return [
                'total' => FlightBooking::count(),
                'confirmed' => FlightBooking::byStatus('confirmed')->count(),
                'pending' => FlightBooking::byStatus('pending')->count(),
                'cancelled' => FlightBooking::byStatus('cancelled')->count(),
                'paid' => FlightBooking::byPaymentState('paid')->count(),
                'unpaid' => FlightBooking::byPaymentState('unpaid')->count(),
                'refunded' => FlightBooking::byPaymentState('refunded')->count(),
            ];
        } elseif ($bookingType === 'hotel') {
            return [
                'total' => HotelBooking::count(),
                'confirmed' => HotelBooking::byStatus('confirmed')->count(),
                'pending' => HotelBooking::byStatus('pending')->count(),
                'cancelled' => HotelBooking::byStatus('cancelled')->count(),
                'paid' => HotelBooking::byPaymentState('paid')->count(),
                'unpaid' => HotelBooking::byPaymentState('unpaid')->count(),
                'refunded' => HotelBooking::byPaymentState('refunded')->count(),
            ];
        } elseif ($bookingType === 'tour') {
            return [
                'total' => TourBooking::count(),
                'confirmed' => TourBooking::byStatus('confirmed')->count(),
                'pending' => TourBooking::byStatus('pending')->count(),
                'cancelled' => TourBooking::byStatus('cancelled')->count(),
                'paid' => TourBooking::byPaymentState('paid')->count(),
                'unpaid' => TourBooking::byPaymentState('unpaid')->count(),
                'refunded' => TourBooking::byPaymentState('refunded')->count(),
            ];
        } elseif ($bookingType === 'umrah') {
            return [
                'total' => UmrahBooking::count(),
                'confirmed' => UmrahBooking::byStatus('confirmed')->count(),
                'pending' => UmrahBooking::byStatus('pending')->count(),
                'cancelled' => UmrahBooking::byStatus('cancelled')->count(),
                'paid' => UmrahBooking::byPaymentState('paid')->count(),
                'unpaid' => UmrahBooking::byPaymentState('unpaid')->count(),
                'refunded' => UmrahBooking::byPaymentState('refunded')->count(),
            ];
        } else {
            // Combined stats
            return [
                'total' => FlightBooking::count() + HotelBooking::count() + TourBooking::count() + UmrahBooking::count(),
                'confirmed' => FlightBooking::byStatus('confirmed')->count() + HotelBooking::byStatus('confirmed')->count() + TourBooking::byStatus('confirmed')->count() + UmrahBooking::byStatus('confirmed')->count(),
                'pending' => FlightBooking::byStatus('pending')->count() + HotelBooking::byStatus('pending')->count() + TourBooking::byStatus('pending')->count() + UmrahBooking::byStatus('pending')->count(),
                'cancelled' => FlightBooking::byStatus('cancelled')->count() + HotelBooking::byStatus('cancelled')->count() + TourBooking::byStatus('cancelled')->count() + UmrahBooking::byStatus('cancelled')->count(),
                'paid' => FlightBooking::byPaymentState('paid')->count() + HotelBooking::byPaymentState('paid')->count() + TourBooking::byPaymentState('paid')->count() + UmrahBooking::byPaymentState('paid')->count(),
                'unpaid' => FlightBooking::byPaymentState('unpaid')->count() + HotelBooking::byPaymentState('unpaid')->count() + TourBooking::byPaymentState('unpaid')->count() + UmrahBooking::byPaymentState('unpaid')->count(),
                'refunded' => FlightBooking::byPaymentState('refunded')->count() + HotelBooking::byPaymentState('refunded')->count() + TourBooking::byPaymentState('refunded')->count() + UmrahBooking::byPaymentState('refunded')->count(),
            ];
        }
    }

    /**
     * Show edit form for a booking
     */
    public function edit($type, $id)
    {
        // Validate booking type
        if (!in_array($type, ['flight', 'hotel', 'tour', 'umrah'])) {
            return redirect()->route('admin.bookings.all')->with('error', 'Invalid booking type');
        }

        // Get the booking
        $booking = match($type) {
            'flight' => FlightBooking::findOrFail($id),
            'hotel' => HotelBooking::findOrFail($id),
            'tour' => TourBooking::findOrFail($id),
            'umrah' => UmrahBooking::findOrFail($id),
        };

        return view('admin.bookings.edit', [
            'booking' => $booking,
            'bookingType' => $type
        ]);
    }

    /**
     * Update booking status
     */
public function update(Request $request, $type, $id)
{
    if (!in_array($type, ['flight', 'hotel', 'tour', 'umrah'])) {
        return redirect()->route('admin.bookings.all')
            ->with('error', 'Invalid booking type');
    }

    $request->validate([
        'booking_status' => 'required|in:pending,confirmed,cancelled',
        'payment_status' => 'required|in:unpaid,paid,refunded',
    ]);

    $booking = match($type) {
        'flight' => FlightBooking::findOrFail($id),
        'hotel' => HotelBooking::findOrFail($id),
        'tour' => TourBooking::findOrFail($id),
        'umrah' => UmrahBooking::findOrFail($id),
    };

    // ðŸ"¹ Save old status
    $oldStatus = $booking->booking_status_flag;

    // ðŸ"¹ Update status
    $booking->booking_status_flag = $request->booking_status;
    $booking->booking_payment_state = $request->payment_status;
    $booking->save();

    // ðŸ”¹ Send email ONLY if status changed
    if ($oldStatus !== $request->booking_status) {

        $customerEmail = $booking->customer_email;

        if (!empty($customerEmail) && $customerEmail !== 'N/A') {

            try {
                // Resend API
                $apiKey = Setting::getValue('resend_api_key', 'email');
                config(['services.resend.key' => $apiKey]);

                $senderEmail = getSetting('sender_email', 'email', 'noreply@travelbookingpanel.com');
                $senderName  = getSetting('sender_name', 'email', 'Travel Booking Panel');
                $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');

                // Status text
                $statusText = ucfirst($request->booking_status);

                // Invoice / booking link
                $bookingRef = $booking->booking_code_ref;
                $detailsUrl = match($type) {
                    'flight' => route('flight.invoice', ['booking_ref' => $bookingRef]),
                    'hotel' => route('hotel.invoice', ['booking_ref' => $bookingRef]),
                    'tour' => route('tour.invoice', ['bookingRef' => $bookingRef]),
                    'umrah' => route('umrah.invoice', ['bookingRef' => $bookingRef]),
                };

                Resend::emails()->send([
                    'from' => "{$senderName} <{$senderEmail}>",
                    'to' => [$customerEmail],
                    'subject' => "Booking {$statusText} - {$bookingRef}",
                    'html' => $this->getBookingStatusEmailHtml(
                        $booking,
                        $businessName,
                        $statusText,
                        $detailsUrl,
                        $type
                    ),
                ]);

                \Log::info('Booking status email sent', [
                    'email' => $customerEmail,
                    'status' => $statusText
                ]);

            } catch (\Exception $e) {
                \Log::error('Booking status email failed', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    return redirect()->route('admin.bookings.all')
        ->with('success', 'Booking status updated successfully!');
}



private function getBookingStatusEmailHtml($booking, $businessName, $status, $url, $type)
{
    $bookingText = str_replace(
        [':type', ':reference', ':status'],
        [
            t("booking_mail_templates.$type"),
            $booking->booking_code_ref,
            t("booking_mail_templates.$status")
        ],
        t('booking_mail_templates.booking_text')
    );

    return "
        <h2>{$businessName}</h2>

        <p>" . t('booking_mail_templates.dear_customer') . "</p>

        <p>{$bookingText}</p>

        <p>
            <a href='{$url}' style='
                display:inline-block;
                padding:10px 20px;
                background:#2563eb;
                color:#fff;
                text-decoration:none;
                border-radius:5px;
            '>" . t('booking_mail_templates.view_details') . "</a>
        </p>

        <p>" . str_replace(
            ':business',
            $businessName,
            t('booking_mail_templates.thank_you')
        ) . "</p>
    ";
}





    /**
     * Delete a single booking
     */
    public function destroy($type, $id)
    {
        // Validate booking type
        if (!in_array($type, ['flight', 'hotel', 'tour', 'umrah'])) {
            return redirect()->route('admin.bookings.all')
                ->with('error', 'Invalid booking type');
        }

        try {
            // Get the booking based on type
            $booking = match($type) {
                'flight' => FlightBooking::findOrFail($id),
                'hotel' => HotelBooking::findOrFail($id),
                'tour' => TourBooking::findOrFail($id),
                'umrah' => UmrahBooking::findOrFail($id),
            };

            $bookingRef = $booking->booking_code_ref;

            // Delete the booking
            $booking->delete();

            return redirect()->route('admin.bookings.all')
                ->with('success', "Booking #{$bookingRef} has been deleted successfully!");

        } catch (\Exception $e) {
            \Log::error('Booking deletion failed', [
                'type' => $type,
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.bookings.all')
                ->with('error', 'Failed to delete booking. Please try again.');
        }
    }

    /**
     * Bulk delete bookings
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'bookings' => 'required|json'
        ]);

        try {
            $bookings = json_decode($request->bookings, true);
            $deletedCount = 0;
            $flightIds = [];
            $hotelIds = [];
            $tourIds = [];
            $umrahIds = [];

            // Separate bookings by type
            foreach ($bookings as $booking) {
                if ($booking['type'] === 'flight') {
                    $flightIds[] = $booking['id'];
                } elseif ($booking['type'] === 'hotel') {
                    $hotelIds[] = $booking['id'];
                } elseif ($booking['type'] === 'tour') {
                    $tourIds[] = $booking['id'];
                } elseif ($booking['type'] === 'umrah') {
                    $umrahIds[] = $booking['id'];
                }
            }

            // Delete flight bookings
            if (!empty($flightIds)) {
                $deletedCount += FlightBooking::whereIn('id', $flightIds)->delete();
            }

            // Delete hotel bookings
            if (!empty($hotelIds)) {
                $deletedCount += HotelBooking::whereIn('id', $hotelIds)->delete();
            }

            // Delete tour bookings
            if (!empty($tourIds)) {
                $deletedCount += TourBooking::whereIn('id', $tourIds)->delete();
            }

            // Delete umrah bookings
            if (!empty($umrahIds)) {
                $deletedCount += UmrahBooking::whereIn('id', $umrahIds)->delete();
            }

            return redirect()->route('admin.bookings.all')
                ->with('success', "{$deletedCount} booking(s) have been deleted successfully!");

        } catch (\Exception $e) {
            \Log::error('Bulk booking deletion failed', [
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.bookings.all')
                ->with('error', 'Failed to delete bookings. Please try again.');
        }
    }

}
