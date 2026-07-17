<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UmrahInclusion;
use Illuminate\Http\Request;

class UmrahInclusionController extends Controller
{
    private function isAgent(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'agent';
    }

    private function routePrefix(): string
    {
        return $this->isAgent() ? 'agent.umrah.inclusions' : 'admin.umrah.inclusions';
    }

    private function layout(): string
    {
        return $this->isAgent() ? 'agent.layouts.app' : 'admin.layouts.app';
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $query  = UmrahInclusion::query();

        if ($this->isAgent()) {
            $query->where('agent_id', auth()->id());
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $inclusions  = $query->latest()->paginate(20);
        $routePrefix = $this->routePrefix();
        $layout      = $this->layout();

        return view('admin.umrah.inclusions.index', compact('inclusions', 'search', 'routePrefix', 'layout'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:225',
        ]);

        if ($this->isAgent()) {
            $validated['agent_id'] = auth()->id();
        }

        UmrahInclusion::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Inclusion created successfully!']);
        }

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Inclusion created successfully!');
    }

    public function update(Request $request, UmrahInclusion $inclusion)
    {
        if ($this->isAgent() && $inclusion->agent_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:225',
        ]);

        $inclusion->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Inclusion updated successfully!']);
        }

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Inclusion updated successfully!');
    }

    public function destroy(UmrahInclusion $inclusion)
    {
        if ($this->isAgent() && $inclusion->agent_id !== auth()->id()) {
            abort(403);
        }

        $inclusion->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Inclusion deleted successfully!']);
        }

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Inclusion deleted successfully!');
    }
}
