<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourPackageType;
use Illuminate\Http\Request;

class TourPackageTypeController extends Controller
{
    private function isAgent(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'agent';
    }

    private function routePrefix(): string
    {
        return $this->isAgent() ? 'agent.tours.package-types' : 'admin.tours.package-types';
    }

    private function layout(): string
    {
        return $this->isAgent() ? 'agent.layouts.app' : 'admin.layouts.app';
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $query  = TourPackageType::query();

        if ($this->isAgent()) {
            $query->where('agent_id', auth()->id());
        }

        if ($search) {
            $query->where('packege_type', 'like', "%{$search}%");
        }

        $packageTypes = $query->latest()->paginate(20);
        $routePrefix  = $this->routePrefix();
        $layout       = $this->layout();

        return view('admin.tours.package-types.index', compact('packageTypes', 'search', 'routePrefix', 'layout'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'packege_type' => 'required|string|max:225',
            'status'       => 'required|in:0,1',
        ]);

        if ($this->isAgent()) {
            $validated['agent_id'] = auth()->id();
        }

        TourPackageType::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Package Type created successfully!']);
        }

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Package Type created successfully!');
    }

    public function update(Request $request, TourPackageType $packageType)
    {
        if ($this->isAgent() && $packageType->agent_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'packege_type' => 'required|string|max:225',
            'status'       => 'required|in:0,1',
        ]);

        $packageType->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Package Type updated successfully!']);
        }

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Package Type updated successfully!');
    }

    public function destroy(TourPackageType $packageType)
    {
        if ($this->isAgent() && $packageType->agent_id !== auth()->id()) {
            abort(403);
        }

        $packageType->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Package Type deleted successfully!']);
        }

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Package Type deleted successfully!');
    }

    public function toggleStatus(TourPackageType $packageType)
    {
        if ($this->isAgent() && $packageType->agent_id !== auth()->id()) {
            abort(403);
        }

        $packageType->update(['status' => $packageType->status == '1' ? '0' : '1']);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
        }

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Status updated successfully!');
    }
}
