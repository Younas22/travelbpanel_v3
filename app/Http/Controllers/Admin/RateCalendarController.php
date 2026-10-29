<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\RoomTypeRate;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Hotels → Room Types → Rate calendar. Pick a hotel and room type; the next 12 months are shown and
 * the admin can set two nightly prices per date: the refundable price and the non-refundable price.
 * A date without a saved price uses the room type's base (price_per_night / non_refundable_price).
 */
class RateCalendarController extends Controller
{
    public const DAYS = 365;

    public function index(Request $request)
    {
        $hotels = Hotel::orderBy('name')->get(['id', 'name']);
        $roomTypes = RoomType::orderBy('name')->get(['id', 'hotel_id', 'name', 'price_per_night', 'non_refundable_price', 'status']);

        return view('admin-nova.hotels.rate-calendar.index', [
            'hotels'    => $hotels,
            'roomTypes' => $roomTypes,
            'hotelId'   => (string) $request->query('hotel_id', ''),
            'typeId'    => (string) $request->query('room_type_id', ''),
            'start'     => Carbon::today()->toDateString(),
            'days'      => self::DAYS,
        ]);
    }

    /** Saved prices for the next 12 months: { base_price, base_nr, rates: { 'Y-m-d': { p, n } } }. */
    public function rates(RoomType $roomType)
    {
        [$start, $end] = $this->window();

        return response()->json([
            'room_type'  => ['id' => $roomType->id, 'name' => $roomType->name, 'hotel' => optional($roomType->hotel)->name],
            'base_price' => (float) $roomType->price_per_night,
            'base_nr'    => $roomType->non_refundable_price === null ? null : (float) $roomType->non_refundable_price,
            'currency'   => 'USD',
            'start'      => $start,
            'end'        => $end,
            'rates'      => $this->savedRates($roomType, $start, $end),
        ]);
    }

    /**
     * Save changes: { rates: { 'Y-m-d': { p: price|null, n: nonRefundablePrice|null } } } — the full pair for
     * each changed date. null means "use the base price"; a date with both null is removed.
     */
    public function save(Request $request, RoomType $roomType)
    {
        [$start, $end] = $this->window();

        $request->validate([
            'rates' => 'required|array|max:' . (self::DAYS + 1),
        ]);

        $set = [];
        $clear = [];
        foreach ($request->input('rates') as $date => $pair) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date) || $date < $start || $date > $end) {
                return response()->json(['success' => false, 'message' => "Date {$date} is outside the calendar."], 422);
            }
            $pair = is_array($pair) ? $pair : ['p' => $pair, 'n' => null];
            $values = [];
            foreach (['p' => 'price', 'n' => 'non_refundable_price'] as $key => $column) {
                $v = $pair[$key] ?? null;
                if ($v === '' || $v === null) {
                    $values[$column] = null;
                    continue;
                }
                if (!is_numeric($v) || $v < 0 || $v > 9999999) {
                    return response()->json(['success' => false, 'message' => "Invalid price for {$date}."], 422);
                }
                $values[$column] = round((float) $v, 2);
            }
            if ($values['price'] === null && $values['non_refundable_price'] === null) {
                $clear[] = $date;
                continue;
            }
            $set[] = ['room_type_id' => $roomType->id, 'date' => $date] + $values + ['created_at' => now(), 'updated_at' => now()];
        }

        DB::transaction(function () use ($roomType, $set, $clear) {
            if ($clear) {
                $roomType->rates()->whereIn('date', $clear)->delete();
            }
            foreach (array_chunk($set, 200) as $chunk) {
                RoomTypeRate::upsert($chunk, ['room_type_id', 'date'], ['price', 'non_refundable_price', 'updated_at']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => count($set) + count($clear) . ' date(s) saved.',
            'rates'   => $this->savedRates($roomType, $start, $end),
        ]);
    }

    private function savedRates(RoomType $roomType, string $start, string $end): object
    {
        return (object) $roomType->rates()->whereBetween('date', [$start, $end])->orderBy('date')->get(['date', 'price', 'non_refundable_price'])
            ->mapWithKeys(fn ($r) => [$r->date->toDateString() => [
                'p' => $r->price === null ? null : (float) $r->price,
                'n' => $r->non_refundable_price === null ? null : (float) $r->non_refundable_price,
            ]])->all();
    }

    private function window(): array
    {
        $start = Carbon::today();

        return [$start->toDateString(), $start->copy()->addDays(self::DAYS - 1)->toDateString()];
    }
}
