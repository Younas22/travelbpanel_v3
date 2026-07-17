<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourPackageType;
use App\Models\TourInclusion;
use App\Models\TourExclusion;
use App\Models\TourImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TourController extends Controller
{
    private function isAgent(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'agent';
    }

    public function index(Request $request)
    {
        $search      = $request->input('search');
        $status      = $request->input('status');
        $packageType = $request->input('package_type');

        $query = Tour::with(['images', 'location']);

        if ($this->isAgent()) {
            $query->where('agent_id', auth()->id());
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereIn('loaction', function ($query) use ($search) {
                        $query->select('id')
                            ->from('locations')
                            ->where('city', 'like', "%{$search}%")
                            ->orWhere('country', 'like', "%{$search}%");
                    });
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($packageType) {
            $query->where('packege_type', $packageType);
        }

        $packages      = $query->latest()->paginate(20);
        $packageTypes  = TourPackageType::where('status', '1')->get();
        $allInclusions = TourInclusion::all()->keyBy('id');
        $allExclusions = TourExclusion::all()->keyBy('id');
        $airports      = DB::table('flights_airports')->get()->keyBy('id');

        if ($this->isAgent()) {
            return view('agent.tours.index', compact('packages'));
        }

        $stats = [
            'total'    => Tour::count(),
            'active'   => Tour::where('status', '1')->count(),
            'inactive' => Tour::where('status', '0')->count(),
            'featured' => Tour::where('featured', '1')->count(),
            'pending'  => Tour::where('approval_status', 'pending')->count(),
        ];

        return view('admin.tours.packages.index', compact(
            'packages', 'packageTypes', 'stats', 'search', 'status',
            'packageType', 'allInclusions', 'allExclusions', 'airports'
        ));
    }

    public function create()
    {
        $packageTypes = TourPackageType::where('status', '1')->get();
        $inclusions   = TourInclusion::all();
        $exclusions   = TourExclusion::all();
        $airports     = DB::table('flights_airports')->where('status', '1')->get();

        if ($this->isAgent()) {
            return view('admin.tours.packages.create', [
                'packageTypes' => $packageTypes,
                'inclusions'   => $inclusions,
                'exclusions'   => $exclusions,
                'airports'     => $airports,
                'layout'       => 'agent.layouts.app',
                'formAction'   => route('agent.tours.store'),
                'backUrl'      => route('agent.tours.index'),
            ]);
        }

        return view('admin.tours.packages.create', compact('packageTypes', 'inclusions', 'exclusions', 'airports'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:225',
            'packege_type'  => 'required|string|max:225',
            'currceny'      => 'required|string|max:225',
            'price'         => 'required|string|max:225',
            'duration'      => 'required|string|max:225',
            'loaction'      => 'required|integer|exists:locations,id',
            'leaving_from'  => 'nullable|integer',
            'going_to'      => 'nullable|integer',
            'checkin_date'  => 'nullable|date',
            'checkout_date' => 'nullable|date',
            'days'          => 'nullable|integer',
            'nights'        => 'nullable|integer',
            'class'         => 'nullable|string|max:255',
            'desc'          => 'required|string',
            'inclusions'    => 'nullable|array',
            'inclusions.*'  => 'integer|exists:tour_inclusions,id',
            'exclusions'    => 'nullable|array',
            'exclusions.*'  => 'integer|exists:tour_exclusions,id',
            'policy'        => 'nullable|string',
            'featured'      => 'required|in:0,1',
            'status'        => 'required|in:0,1',
            'stars'         => 'nullable|string|max:225',
            'rating'        => 'nullable|string|max:225',
            'adults'        => 'nullable|string|max:225',
            'childs'        => 'nullable|string|max:225',
            'infants'       => 'nullable|string|max:225',
            'images.*'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['inclusions'] = $request->inclusions ?? [];
        $validated['exclusions'] = $request->exclusions ?? [];

        if ($this->isAgent()) {
            $validated['agent_id']        = auth()->id();
            $validated['added_by']        = 'agent';
            $validated['approval_status'] = 'pending';
            $validated['status']          = '0';
        } else {
            $validated['added_by']        = 'admin';
            $validated['approval_status'] = 'approved';
        }

        $package = Tour::create($validated);

        if ($request->hasFile('images')) {
            $uploadPath = public_path('assets/images/tours');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            foreach ($request->file('images') as $image) {
                $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move($uploadPath, $fileName);
                TourImage::create([
                    'tour_id' => $package->id,
                    'image'   => 'tours/' . $fileName,
                ]);
            }
        }

        if ($this->isAgent()) {
            return redirect()->route('agent.tours.index')
                ->with('success', 'Tour package submitted for approval!');
        }

        return redirect()->route('admin.tours.packages.index')
            ->with('success', 'Tour Package created successfully!');
    }

    public function edit(Tour $tour)
    {
        if ($this->isAgent() && $tour->agent_id !== auth()->id()) {
            abort(403);
        }

        $packageTypes = TourPackageType::where('status', '1')->get();
        $inclusions   = TourInclusion::all();
        $exclusions   = TourExclusion::all();
        $airports     = DB::table('flights_airports')->where('status', '1')->get();
        $tour->load('images');

        if ($this->isAgent()) {
            return view('admin.tours.packages.edit', [
                'package'              => $tour,
                'packageTypes'         => $packageTypes,
                'inclusions'           => $inclusions,
                'exclusions'           => $exclusions,
                'airports'             => $airports,
                'layout'               => 'agent.layouts.app',
                'formAction'           => route('agent.tours.update', $tour->id),
                'formMethod'           => 'PUT',
                'backUrl'              => route('agent.tours.index'),
                'deleteImageRouteName' => 'agent.tours.image.destroy',
            ]);
        }

        return view('admin.tours.packages.edit', compact('tour', 'packageTypes', 'inclusions', 'exclusions', 'airports'));
    }

    public function update(Request $request, Tour $tour)
    {
        if ($this->isAgent() && $tour->agent_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:225',
            'packege_type'  => 'required|string|max:225',
            'currceny'      => 'required|string|max:225',
            'price'         => 'required|string|max:225',
            'duration'      => 'required|string|max:225',
            'loaction'      => 'required|integer|exists:locations,id',
            'leaving_from'  => 'nullable|integer',
            'going_to'      => 'nullable|integer',
            'checkin_date'  => 'nullable|date',
            'checkout_date' => 'nullable|date',
            'days'          => 'nullable|integer',
            'nights'        => 'nullable|integer',
            'class'         => 'nullable|string|max:255',
            'desc'          => 'required|string',
            'inclusions'    => 'nullable|array',
            'inclusions.*'  => 'integer|exists:tour_inclusions,id',
            'exclusions'    => 'nullable|array',
            'exclusions.*'  => 'integer|exists:tour_exclusions,id',
            'policy'        => 'nullable|string',
            'featured'      => 'required|in:0,1',
            'status'        => 'required|in:0,1',
            'stars'         => 'nullable|string|max:225',
            'rating'        => 'nullable|string|max:225',
            'adults'        => 'nullable|string|max:225',
            'childs'        => 'nullable|string|max:225',
            'infants'       => 'nullable|string|max:225',
            'images.*'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['inclusions'] = $request->inclusions ?? [];
        $validated['exclusions'] = $request->exclusions ?? [];

        if ($this->isAgent()) {
            $validated['approval_status'] = 'pending';
            $validated['status']          = '0';
        }

        $tour->update($validated);

        if ($request->hasFile('images')) {
            $uploadPath = public_path('assets/images/tours');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            foreach ($request->file('images') as $image) {
                $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move($uploadPath, $fileName);
                TourImage::create([
                    'tour_id' => $tour->id,
                    'image'   => 'tours/' . $fileName,
                ]);
            }
        }

        if ($this->isAgent()) {
            return redirect()->route('agent.tours.index')
                ->with('success', 'Tour package updated and resubmitted for approval!');
        }

        return redirect()->route('admin.tours.packages.index')
            ->with('success', 'Tour Package updated successfully!');
    }

    public function destroy(Tour $tour)
    {
        if ($this->isAgent() && $tour->agent_id !== auth()->id()) {
            abort(403);
        }

        foreach ($tour->images as $image) {
            $filePath = public_path('assets/images/' . $image->image);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $image->delete();
        }

        $tour->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Tour Package deleted successfully!']);
        }

        if ($this->isAgent()) {
            return redirect()->route('agent.tours.index')
                ->with('success', 'Tour package deleted.');
        }

        return redirect()->route('admin.tours.packages.index')
            ->with('success', 'Tour Package deleted successfully!');
    }

    public function approve(Tour $tour)
    {
        $tour->update([
            'approval_status' => 'approved',
            'status'          => '1',
        ]);

        return redirect()->back()->with('success', 'Tour package approved and made active!');
    }

    public function reject(Tour $tour)
    {
        $tour->update([
            'approval_status' => 'rejected',
            'status'          => '0',
        ]);

        return redirect()->back()->with('success', 'Tour package submission rejected.');
    }

    public function toggleStatus(Tour $tour)
    {
        $tour->update(['status' => $tour->status == '1' ? '0' : '1']);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
        }

        return redirect()->route('admin.tours.packages.index')
            ->with('success', 'Status updated successfully!');
    }

    public function toggleFeatured(Tour $tour)
    {
        $tour->update(['featured' => $tour->featured == '1' ? '0' : '1']);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Featured status updated successfully!']);
        }

        return redirect()->route('admin.tours.packages.index')
            ->with('success', 'Featured status updated successfully!');
    }

    public function deleteImage(TourImage $image)
    {
        // Verify ownership if agent
        if ($this->isAgent()) {
            Tour::where('agent_id', auth()->id())->where('id', $image->tour_id)->firstOrFail();
        }

        $filePath = public_path('assets/images/' . $image->image);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $image->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Image deleted successfully!']);
        }

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}
