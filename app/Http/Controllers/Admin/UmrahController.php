<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Umrah;
use App\Models\UmrahPackageType;
use App\Models\UmrahInclusion;
use App\Models\UmrahExclusion;
use App\Models\UmrahImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UmrahController extends Controller
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

        $query = Umrah::with('images');

        if ($this->isAgent()) {
            $query->where('agent_id', auth()->id());
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('loaction', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($packageType) {
            $query->where('packege_type', $packageType);
        }

        $packages = $query->latest()->paginate(20);

        if ($this->isAgent()) {
            return view('agent.umrah.index', compact('packages'));
        }

        $packageTypes  = UmrahPackageType::where('status', '1')->get();
        $allInclusions = UmrahInclusion::all()->keyBy('id');
        $allExclusions = UmrahExclusion::all()->keyBy('id');
        $airports      = DB::table('flights_airports')->get()->keyBy('id');

        $stats = [
            'total'    => Umrah::count(),
            'active'   => Umrah::where('status', '1')->count(),
            'inactive' => Umrah::where('status', '0')->count(),
            'featured' => Umrah::where('featured', '1')->count(),
            'pending'  => Umrah::where('approval_status', 'pending')->count(),
        ];

        return view('admin.umrah.packages.index', compact(
            'packages', 'packageTypes', 'stats', 'search', 'status',
            'packageType', 'allInclusions', 'allExclusions', 'airports'
        ));
    }

    public function create()
    {
        $packageTypes = UmrahPackageType::where('status', '1')->get();
        $inclusions   = UmrahInclusion::all();
        $exclusions   = UmrahExclusion::all();
        $airports     = DB::table('flights_airports')->where('status', '1')->get();

        if ($this->isAgent()) {
            return view('admin.umrah.packages.create', [
                'packageTypes' => $packageTypes,
                'inclusions'   => $inclusions,
                'exclusions'   => $exclusions,
                'airports'     => $airports,
                'layout'       => 'agent.layouts.app',
                'formAction'   => route('agent.umrah.store'),
                'backUrl'      => route('agent.umrah.index'),
            ]);
        }

        return view('admin.umrah.packages.create', compact('packageTypes', 'inclusions', 'exclusions', 'airports'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:225',
            'packege_type'     => 'required|string|max:225',
            'currceny'         => 'required|string|max:225',
            'price'            => 'required|string|max:225',
            'duration'         => 'required|string|max:225',
            'loaction'         => 'required|string|max:225',
            'leaving_from'     => 'nullable|integer',
            'going_to'         => 'nullable|integer',
            'checkin_date'     => 'nullable|date',
            'checkout_date'    => 'nullable|date',
            'night_in_mekkah'  => 'nullable|integer',
            'night_in_madina'  => 'nullable|integer',
            'class'            => 'nullable|string|max:255',
            'desc'             => 'required|string',
            'inclusions'       => 'nullable|array',
            'inclusions.*'     => 'integer|exists:umrah_inclusions,id',
            'exclusions'       => 'nullable|array',
            'exclusions.*'     => 'integer|exists:umrah_exclusions,id',
            'policy'           => 'nullable|string',
            'featured'         => 'required|in:0,1',
            'status'           => 'required|in:0,1',
            'stars'            => 'nullable|string|max:225',
            'rating'           => 'nullable|string|max:225',
            'adults'           => 'nullable|string|max:225',
            'childs'           => 'nullable|string|max:225',
            'infants'          => 'nullable|string|max:225',
            'images.*'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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

        $package = Umrah::create($validated);

        if ($request->hasFile('images')) {
            $uploadPath = public_path('assets/images/umrah');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            foreach ($request->file('images') as $image) {
                $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move($uploadPath, $fileName);
                UmrahImage::create([
                    'umrah_id' => $package->id,
                    'image'    => 'umrah/' . $fileName,
                ]);
            }
        }

        if ($this->isAgent()) {
            return redirect()->route('agent.umrah.index')
                ->with('success', 'Umrah package submitted for approval!');
        }

        return redirect()->route('admin.umrah.packages.index')
            ->with('success', 'Umrah Package created successfully!');
    }

    public function edit(Umrah $umrah)
    {
        if ($this->isAgent() && $umrah->agent_id !== auth()->id()) {
            abort(403);
        }

        $packageTypes = UmrahPackageType::where('status', '1')->get();
        $inclusions   = UmrahInclusion::all();
        $exclusions   = UmrahExclusion::all();
        $airports     = DB::table('flights_airports')->where('status', '1')->get();
        $umrah->load('images');

        if ($this->isAgent()) {
            return view('admin.umrah.packages.edit', [
                'umrah'                => $umrah,
                'packageTypes'         => $packageTypes,
                'inclusions'           => $inclusions,
                'exclusions'           => $exclusions,
                'airports'             => $airports,
                'layout'               => 'agent.layouts.app',
                'formAction'           => route('agent.umrah.update', $umrah->id),
                'formMethod'           => 'PUT',
                'backUrl'              => route('agent.umrah.index'),
                'deleteImageRouteName' => 'agent.umrah.image.destroy',
            ]);
        }

        return view('admin.umrah.packages.edit', compact('umrah', 'packageTypes', 'inclusions', 'exclusions', 'airports'));
    }

    public function update(Request $request, Umrah $umrah)
    {
        if ($this->isAgent() && $umrah->agent_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name'             => 'required|string|max:225',
            'packege_type'     => 'required|string|max:225',
            'currceny'         => 'required|string|max:225',
            'price'            => 'required|string|max:225',
            'duration'         => 'required|string|max:225',
            'loaction'         => 'required|string|max:225',
            'leaving_from'     => 'nullable|integer',
            'going_to'         => 'nullable|integer',
            'checkin_date'     => 'nullable|date',
            'checkout_date'    => 'nullable|date',
            'night_in_mekkah'  => 'nullable|integer',
            'night_in_madina'  => 'nullable|integer',
            'class'            => 'nullable|string|max:255',
            'desc'             => 'required|string',
            'inclusions'       => 'nullable|array',
            'inclusions.*'     => 'integer|exists:umrah_inclusions,id',
            'exclusions'       => 'nullable|array',
            'exclusions.*'     => 'integer|exists:umrah_exclusions,id',
            'policy'           => 'nullable|string',
            'featured'         => 'required|in:0,1',
            'status'           => 'required|in:0,1',
            'stars'            => 'nullable|string|max:225',
            'rating'           => 'nullable|string|max:225',
            'adults'           => 'nullable|string|max:225',
            'childs'           => 'nullable|string|max:225',
            'infants'          => 'nullable|string|max:225',
            'images.*'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['inclusions'] = $request->inclusions ?? [];
        $validated['exclusions'] = $request->exclusions ?? [];

        if ($this->isAgent()) {
            $validated['approval_status'] = 'pending';
            $validated['status']          = '0';
        }

        $umrah->update($validated);

        if ($request->hasFile('images')) {
            $uploadPath = public_path('assets/images/umrah');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            foreach ($request->file('images') as $image) {
                $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move($uploadPath, $fileName);
                UmrahImage::create([
                    'umrah_id' => $umrah->id,
                    'image'    => 'umrah/' . $fileName,
                ]);
            }
        }

        if ($this->isAgent()) {
            return redirect()->route('agent.umrah.index')
                ->with('success', 'Umrah package updated and resubmitted for approval!');
        }

        return redirect()->route('admin.umrah.packages.index')
            ->with('success', 'Umrah Package updated successfully!');
    }

    public function destroy(Umrah $umrah)
    {
        if ($this->isAgent() && $umrah->agent_id !== auth()->id()) {
            abort(403);
        }

        foreach ($umrah->images as $image) {
            $filePath = public_path('assets/images/' . $image->image);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $image->delete();
        }

        $umrah->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Umrah Package deleted successfully!']);
        }

        if ($this->isAgent()) {
            return redirect()->route('agent.umrah.index')
                ->with('success', 'Umrah package deleted.');
        }

        return redirect()->route('admin.umrah.packages.index')
            ->with('success', 'Umrah Package deleted successfully!');
    }

    public function approve(Umrah $umrah)
    {
        $umrah->update([
            'approval_status' => 'approved',
            'status'          => '1',
        ]);

        return redirect()->back()->with('success', 'Umrah package approved and made active!');
    }

    public function reject(Umrah $umrah)
    {
        $umrah->update([
            'approval_status' => 'rejected',
            'status'          => '0',
        ]);

        return redirect()->back()->with('success', 'Umrah package submission rejected.');
    }

    public function toggleStatus(Umrah $umrah)
    {
        $umrah->update(['status' => $umrah->status == '1' ? '0' : '1']);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
        }

        return redirect()->route('admin.umrah.packages.index')
            ->with('success', 'Status updated successfully!');
    }

    public function toggleFeatured(Umrah $umrah)
    {
        $umrah->update(['featured' => $umrah->featured == '1' ? '0' : '1']);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Featured status updated successfully!']);
        }

        return redirect()->route('admin.umrah.packages.index')
            ->with('success', 'Featured status updated successfully!');
    }

    public function deleteImage(UmrahImage $image)
    {
        if ($this->isAgent()) {
            Umrah::where('agent_id', auth()->id())->where('id', $image->umrah_id)->firstOrFail();
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
