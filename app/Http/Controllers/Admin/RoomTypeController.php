<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use App\Models\RoomTypeImage;
use App\Models\Hotel;
use App\Models\AllAmenity;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    private function isAgent(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'agent';
    }

    public function index(Request $request)
    {
        $search  = $request->input('search');
        $hotelId = $request->input('hotel_id');
        $status  = $request->input('status');

        $query = RoomType::with(['hotel', 'images']);

        if ($this->isAgent()) {
            // Only show room types for hotels owned by this agent
            $query->whereHas('hotel', fn($q) => $q->where('agent_id', auth()->id()));
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($hotelId) {
            $query->where('hotel_id', $hotelId);
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $roomTypes = $query->latest()->paginate(20);
        $hotels    = $this->isAgent()
            ? Hotel::where('agent_id', auth()->id())->where('status', 1)->get()
            : Hotel::where('status', 1)->get();

        $stats = [
            'total'    => RoomType::count(),
            'active'   => RoomType::where('status', 1)->count(),
            'inactive' => RoomType::where('status', 0)->count(),
        ];

        return view('admin.hotels.room-types.index', compact('roomTypes', 'hotels', 'stats', 'search', 'hotelId', 'status'));
    }

    public function create()
    {
        $hotels    = $this->isAgent()
            ? Hotel::where('agent_id', auth()->id())->get()
            : Hotel::all();
        $amenities = AllAmenity::all();

        if ($this->isAgent()) {
            return view('agent.hotels.room-types.create', [
                'hotels'     => $hotels,
                'amenities'  => $amenities,
                'formAction' => route('agent.hotels.room-types.store'),
                'backUrl'    => request()->has('hotel_id')
                    ? route('agent.hotels.edit', request('hotel_id'))
                    : route('agent.hotels.index'),
            ]);
        }

        return view('admin.hotels.room-types.create', compact('hotels', 'amenities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hotel_id'       => 'required|integer|exists:hotels,id',
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string',
            'price_per_night'=> 'required|numeric|min:0',
            'non_refundable_price' => 'nullable|numeric|min:0',
            'beds'           => 'required|integer|min:1',
            'max_adults'     => 'required|integer|min:1',
            'max_children'   => 'required|integer|min:0',
            'ac'             => 'required|in:0,1',
            'status'         => 'required|in:0,1',
            'cancellation_type'     => 'nullable|in:refundable,non_refundable',
            'free_cancel_days'      => 'required_if:cancellation_type,refundable|nullable|integer|min:0|max:365',
            'free_cancel_time'      => 'required_if:cancellation_type,refundable|nullable|date_format:H:i',
            'cancellation_fee'      => 'nullable|numeric|min:0',
            'cancellation_fee_type' => 'nullable|in:percent,fixed',
            'amenities'      => 'nullable|array',
            'amenities.*'    => 'integer|exists:all_amenities,id',
            'room_amenities'   => 'nullable|array',
            'room_amenities.*' => 'integer|exists:hotel_catalog_items,id',
            'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated = $this->cancellationPolicy($validated);

        // Verify hotel ownership if agent
        if ($this->isAgent()) {
            Hotel::where('agent_id', auth()->id())->findOrFail($validated['hotel_id']);
        }

        $roomType = RoomType::create($validated);
        $roomType->roomAmenities()->sync($request->input('room_amenities', []));

        if ($request->has('amenities')) {
            $roomType->amenities()->attach($request->amenities);
        }

        if ($request->hasFile('images')) {
            $uploadPath = public_path('assets/images/hotel-rooms');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            foreach ($request->file('images') as $image) {
                $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move($uploadPath, $fileName);
                RoomTypeImage::create([
                    'room_type_id' => $roomType->id,
                    'image_path'   => 'hotel-rooms/' . $fileName,
                ]);
            }
        }

        if ($this->isAgent()) {
            return redirect()->route('agent.hotels.edit', ['hotel' => $validated['hotel_id'], 'tab' => 'room-types'])
                ->with('success', 'Room Type created successfully!');
        }

        // next step of the workflow: add rooms of this type to the hotel
        return redirect()->route('admin.hotels.rooms.create', ['hotel_id' => $validated['hotel_id'], 'room_type_id' => $roomType->id])
            ->with('success', 'Room Type created successfully! Now add its rooms.');
    }

    public function edit(RoomType $roomType)
    {
        if ($this->isAgent()) {
            Hotel::where('agent_id', auth()->id())->findOrFail($roomType->hotel_id);
        }

        $hotels    = $this->isAgent()
            ? Hotel::where('agent_id', auth()->id())->where('status', 1)->get()
            : Hotel::where('status', 1)->get();
        $amenities = AllAmenity::all();
        $roomType->load(['images', 'amenities', 'roomAmenities']);

        if ($this->isAgent()) {
            return view('agent.hotels.room-types.edit', compact('roomType', 'hotels', 'amenities'));
        }

        return view('admin.hotels.room-types.edit', compact('roomType', 'hotels', 'amenities'));
    }

    public function update(Request $request, RoomType $roomType)
    {
        if ($this->isAgent()) {
            Hotel::where('agent_id', auth()->id())->findOrFail($roomType->hotel_id);
        }

        $validated = $request->validate([
            'hotel_id'       => 'required|integer|exists:hotels,id',
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string',
            'price_per_night'=> 'required|numeric|min:0',
            'non_refundable_price' => 'nullable|numeric|min:0',
            'beds'           => 'required|integer|min:1',
            'max_adults'     => 'required|integer|min:1',
            'max_children'   => 'required|integer|min:0',
            'ac'             => 'required|in:0,1',
            'status'         => 'required|in:0,1',
            'cancellation_type'     => 'nullable|in:refundable,non_refundable',
            'free_cancel_days'      => 'required_if:cancellation_type,refundable|nullable|integer|min:0|max:365',
            'free_cancel_time'      => 'required_if:cancellation_type,refundable|nullable|date_format:H:i',
            'cancellation_fee'      => 'nullable|numeric|min:0',
            'cancellation_fee_type' => 'nullable|in:percent,fixed',
            'amenities'      => 'nullable|array',
            'amenities.*'    => 'integer|exists:all_amenities,id',
            'room_amenities'   => 'nullable|array',
            'room_amenities.*' => 'integer|exists:hotel_catalog_items,id',
            'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated = $this->cancellationPolicy($validated);

        $roomType->update($validated);

        if ($request->has('amenities')) {
            $roomType->amenities()->sync($request->amenities);
        } elseif (!$request->has('room_amenities_present')) {
            $roomType->amenities()->detach(); // classic/agent forms: an empty amenities list means "none"
        }
        if ($request->has('room_amenities_present')) {
            $roomType->roomAmenities()->sync($request->input('room_amenities', []));
        }

        if ($request->hasFile('images')) {
            $uploadPath = public_path('assets/images/hotel-rooms');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            foreach ($request->file('images') as $image) {
                $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move($uploadPath, $fileName);
                RoomTypeImage::create([
                    'room_type_id' => $roomType->id,
                    'image_path'   => 'hotel-rooms/' . $fileName,
                ]);
            }
        }

        if ($this->isAgent()) {
            return redirect()->route('agent.hotels.edit', ['hotel' => $roomType->hotel_id, 'tab' => 'room-types'])
                ->with('success', 'Room Type updated successfully!');
        }

        return redirect()->route('admin.hotels.room-types.index', ['hotel_id' => $roomType->hotel_id])
            ->with('success', 'Room Type updated successfully!');
    }

    public function destroy(RoomType $roomType)
    {
        if ($this->isAgent()) {
            Hotel::where('agent_id', auth()->id())->findOrFail($roomType->hotel_id);
        }

        $hotelId = $roomType->hotel_id;

        foreach ($roomType->images as $image) {
            $filePath = public_path('assets/images/' . $image->image_path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $image->delete();
        }

        $roomType->delete();

        if ($this->isAgent()) {
            return redirect()->route('agent.hotels.edit', ['hotel' => $hotelId, 'tab' => 'room-types'])
                ->with('success', 'Room Type deleted successfully!');
        }

        return redirect()->route('admin.hotels.room-types.index')
            ->with('success', 'Room Type deleted successfully!');
    }

    /** Forms without the policy fields leave it unchanged (new rows get the refundable defaults). Percent fees are capped at 100. */
    private function cancellationPolicy(array $data): array
    {
        if (empty($data['cancellation_type'])) {
            return array_diff_key($data, array_flip(['cancellation_type', 'free_cancel_days', 'free_cancel_time', 'cancellation_fee', 'cancellation_fee_type']));
        }
        $data['cancellation_fee_type'] = $data['cancellation_fee_type'] ?? 'percent';
        $data['cancellation_fee'] = (float) ($data['cancellation_fee'] ?? 0);
        if ($data['cancellation_fee_type'] === 'percent') {
            $data['cancellation_fee'] = min($data['cancellation_fee'], 100);
        }
        if ($data['cancellation_type'] === 'non_refundable') {
            $data['free_cancel_days'] = null;
            $data['free_cancel_time'] = null;
        }

        return $data;
    }

    public function toggleStatus(RoomType $roomType)
    {
        $roomType->status = !$roomType->status;
        $roomType->save();

        return response()->json([
            'success' => true,
            'status'  => $roomType->status,
            'message' => 'Status updated successfully!',
        ]);
    }

    public function deleteImage($imageId)
    {
        $image    = RoomTypeImage::findOrFail($imageId);
        $filePath = public_path('assets/images/' . $image->image_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully!',
        ]);
    }

    public function reorderImages(Request $request)
    {
        $request->validate([
            'images'              => 'required|array',
            'images.*.id'         => 'required|integer|exists:room_type_images,id',
            'images.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->images as $item) {
            RoomTypeImage::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image order updated successfully!',
        ]);
    }
}
