<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllAmenity;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    private function isAgent(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'agent';
    }

    private function routePrefix(): string
    {
        return $this->isAgent() ? 'agent.hotels.amenities' : 'admin.hotels.amenities';
    }

    private function layout(): string
    {
        return $this->isAgent() ? 'agent.layouts.app' : 'admin.layouts.app';
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $query  = AllAmenity::query();

        if ($this->isAgent()) {
            $query->where('agent_id', auth()->id());
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $amenities   = $query->latest('id')->paginate(20);
        $routePrefix = $this->routePrefix();
        $layout      = $this->layout();
        $baseUrl     = $this->isAgent() ? url('agent/hotels/amenities') : url('admin/hotels/amenities');

        $stats = [
            'total' => $this->isAgent()
                ? AllAmenity::where('agent_id', auth()->id())->count()
                : AllAmenity::count(),
        ];

        if ($this->isAgent()) {
            return view('agent.hotels.amenities.index', compact('amenities', 'stats', 'search'));
        }

        return view('admin.hotels.amenities.index', compact('amenities', 'stats', 'search', 'routePrefix', 'layout', 'baseUrl'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:100',
        ]);

        if ($this->isAgent()) {
            $validated['agent_id'] = auth()->id();
        }

        AllAmenity::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Amenity created successfully!'
        ]);
    }

    public function update(Request $request, AllAmenity $amenity)
    {
        if ($this->isAgent() && $amenity->agent_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:100',
        ]);

        $amenity->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Amenity updated successfully!'
        ]);
    }

    public function destroy(AllAmenity $amenity)
    {
        if ($this->isAgent() && $amenity->agent_id !== auth()->id()) {
            abort(403);
        }

        $hotelCount    = $amenity->hotels()->count();
        $roomTypeCount = $amenity->roomTypes()->count();

        if ($hotelCount > 0 || $roomTypeCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete amenity as it is being used by ' . ($hotelCount + $roomTypeCount) . ' hotels/room types.'
            ], 422);
        }

        $amenity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Amenity deleted successfully!'
        ]);
    }
}
