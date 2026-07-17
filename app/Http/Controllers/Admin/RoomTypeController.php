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
            'beds'           => 'required|integer|min:1',
            'max_adults'     => 'required|integer|min:1',
            'max_children'   => 'required|integer|min:0',
            'ac'             => 'required|in:0,1',
            'status'         => 'required|in:0,1',
            'amenities'      => 'nullable|array',
            'amenities.*'    => 'integer|exists:all_amenities,id',
            'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Verify hotel ownership if agent
        if ($this->isAgent()) {
            Hotel::where('agent_id', auth()->id())->findOrFail($validated['hotel_id']);
        }

        $roomType = RoomType::create($validated);

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

        return redirect()->route('admin.hotels.edit', ['hotel' => $validated['hotel_id'], 'tab' => 'room-types'])
            ->with('success', 'Room Type created successfully!');
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
        $roomType->load(['images', 'amenities']);

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
            'beds'           => 'required|integer|min:1',
            'max_adults'     => 'required|integer|min:1',
            'max_children'   => 'required|integer|min:0',
            'ac'             => 'required|in:0,1',
            'status'         => 'required|in:0,1',
            'amenities'      => 'nullable|array',
            'amenities.*'    => 'integer|exists:all_amenities,id',
            'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $roomType->update($validated);

        if ($request->has('amenities')) {
            $roomType->amenities()->sync($request->amenities);
        } else {
            $roomType->amenities()->detach();
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

        return redirect()->route('admin.hotels.edit', ['hotel' => $roomType->hotel_id, 'tab' => 'room-types'])
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
