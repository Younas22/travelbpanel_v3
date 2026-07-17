<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    private function isAgent(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'agent';
    }

    public function index(Request $request)
    {
        $search     = $request->input('search');
        $hotelId    = $request->input('hotel_id');
        $roomTypeId = $request->input('room_type_id');
        $status     = $request->input('status');

        $query = Room::with(['hotel', 'roomType']);

        if ($this->isAgent()) {
            $query->whereHas('hotel', fn($q) => $q->where('agent_id', auth()->id()));
        }

        if ($search) {
            $query->where('room_number', 'like', "%{$search}%");
        }

        if ($hotelId) {
            $query->where('hotel_id', $hotelId);
        }

        if ($roomTypeId) {
            $query->where('room_type_id', $roomTypeId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $rooms     = $query->latest()->paginate(50);
        $hotels    = $this->isAgent()
            ? Hotel::where('agent_id', auth()->id())->where('status', 1)->get()
            : Hotel::where('status', 1)->get();
        $roomTypes = RoomType::where('status', 1)->get();

        $stats = [
            'total'       => Room::count(),
            'available'   => Room::where('status', 'available')->count(),
            'occupied'    => Room::where('status', 'occupied')->count(),
            'maintenance' => Room::where('status', 'maintenance')->count(),
        ];

        return view('admin.hotels.rooms.index', compact('rooms', 'hotels', 'roomTypes', 'stats', 'search', 'hotelId', 'roomTypeId', 'status'));
    }

    public function create()
    {
        $hotels    = $this->isAgent()
            ? Hotel::where('agent_id', auth()->id())->get()
            : Hotel::all();
        $roomTypes = RoomType::where('status', 1)->get();

        if ($this->isAgent()) {
            return view('agent.hotels.rooms.create', [
                'hotels'     => $hotels,
                'roomTypes'  => $roomTypes,
                'formAction' => route('agent.hotels.rooms.store'),
                'backUrl'    => request()->has('hotel_id')
                    ? route('agent.hotels.edit', request('hotel_id'))
                    : route('agent.hotels.index'),
            ]);
        }

        return view('admin.hotels.rooms.create', compact('hotels', 'roomTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hotel_id'     => 'required|integer|exists:hotels,id',
            'room_type_id' => 'required|integer|exists:room_types,id',
            'room_number'  => 'required|string|max:20',
            'floor'        => 'nullable|string|max:20',
            'status'       => 'required|in:available,occupied,maintenance',
        ]);

        if ($this->isAgent()) {
            Hotel::where('agent_id', auth()->id())->findOrFail($validated['hotel_id']);
        }

        $exists = Room::where('hotel_id', $validated['hotel_id'])
                     ->where('room_number', $validated['room_number'])
                     ->exists();

        if ($exists) {
            return back()->withErrors(['room_number' => 'Room number already exists in this hotel.'])->withInput();
        }

        Room::create($validated);

        if ($this->isAgent()) {
            return redirect()->route('agent.hotels.edit', ['hotel' => $validated['hotel_id'], 'tab' => 'rooms'])
                ->with('success', 'Room created successfully!');
        }

        return redirect()->route('admin.hotels.edit', ['hotel' => $validated['hotel_id'], 'tab' => 'rooms'])
            ->with('success', 'Room created successfully!');
    }

    public function edit(Room $room)
    {
        if ($this->isAgent()) {
            Hotel::where('agent_id', auth()->id())->findOrFail($room->hotel_id);
        }

        $hotels    = $this->isAgent()
            ? Hotel::where('agent_id', auth()->id())->where('status', 1)->get()
            : Hotel::where('status', 1)->get();
        $roomTypes = RoomType::where('status', 1)->where('hotel_id', $room->hotel_id)->get();

        if ($this->isAgent()) {
            return view('agent.hotels.rooms.edit', compact('room', 'hotels', 'roomTypes'));
        }

        return view('admin.hotels.rooms.edit', compact('room', 'hotels', 'roomTypes'));
    }

    public function update(Request $request, Room $room)
    {
        if ($this->isAgent()) {
            Hotel::where('agent_id', auth()->id())->findOrFail($room->hotel_id);
        }

        $validated = $request->validate([
            'hotel_id'     => 'required|integer|exists:hotels,id',
            'room_type_id' => 'required|integer|exists:room_types,id',
            'room_number'  => 'required|string|max:20',
            'floor'        => 'nullable|string|max:20',
            'status'       => 'required|in:available,occupied,maintenance',
        ]);

        $exists = Room::where('hotel_id', $validated['hotel_id'])
                     ->where('room_number', $validated['room_number'])
                     ->where('id', '!=', $room->id)
                     ->exists();

        if ($exists) {
            return back()->withErrors(['room_number' => 'Room number already exists in this hotel.'])->withInput();
        }

        $room->update($validated);

        if ($this->isAgent()) {
            return redirect()->route('agent.hotels.edit', ['hotel' => $room->hotel_id, 'tab' => 'rooms'])
                ->with('success', 'Room updated successfully!');
        }

        return redirect()->route('admin.hotels.edit', ['hotel' => $room->hotel_id, 'tab' => 'rooms'])
            ->with('success', 'Room updated successfully!');
    }

    public function destroy(Room $room)
    {
        if ($this->isAgent()) {
            Hotel::where('agent_id', auth()->id())->findOrFail($room->hotel_id);
        }

        $hotelId = $room->hotel_id;
        $room->delete();

        if ($this->isAgent()) {
            return redirect()->route('agent.hotels.edit', ['hotel' => $hotelId, 'tab' => 'rooms'])
                ->with('success', 'Room deleted successfully!');
        }

        return redirect()->route('admin.hotels.rooms.index')
            ->with('success', 'Room deleted successfully!');
    }

    public function updateStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,maintenance',
        ]);

        $room->update($validated);

        return response()->json([
            'success' => true,
            'status'  => $room->status,
            'message' => 'Room status updated successfully!',
        ]);
    }
}
