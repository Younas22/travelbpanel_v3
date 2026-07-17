<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UmrahExclusion;
use Illuminate\Http\Request;

class UmrahExclusionController extends Controller
{
    private function isAgent(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'agent';
    }

    private function routePrefix(): string
    {
        return $this->isAgent() ? 'agent.umrah.exclusions' : 'admin.umrah.exclusions';
    }

    private function layout(): string
    {
        return $this->isAgent() ? 'agent.layouts.app' : 'admin.layouts.app';
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $query  = UmrahExclusion::query();

        if ($this->isAgent()) {
            $query->where('agent_id', auth()->id());
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $exclusions  = $query->latest()->paginate(20);
        $routePrefix = $this->routePrefix();
        $layout      = $this->layout();

        return view('admin.umrah.exclusions.index', compact('exclusions', 'search', 'routePrefix', 'layout'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:225',
        ]);

        if ($this->isAgent()) {
            $validated['agent_id'] = auth()->id();
        }

        UmrahExclusion::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Exclusion created successfully!']);
        }

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Exclusion created successfully!');
    }

    public function update(Request $request, UmrahExclusion $exclusion)
    {
        if ($this->isAgent() && $exclusion->agent_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:225',
        ]);

        $exclusion->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Exclusion updated successfully!']);
        }

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Exclusion updated successfully!');
    }

    public function destroy(UmrahExclusion $exclusion)
    {
        if ($this->isAgent() && $exclusion->agent_id !== auth()->id()) {
            abort(403);
        }

        $exclusion->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Exclusion deleted successfully!']);
        }

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Exclusion deleted successfully!');
    }
}
