<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Display a listing of languages
     */
    public function index()
    {
        $languages = Language::orderBy('sort_order')->get();
        return view('admin.settings.languages.index', compact('languages'));
    }

    /**
     * Show the form for creating a new language
     */
    public function create()
    {
        return view('admin.settings.languages.create');
    }

    /**
     * Store a newly created language
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:languages,code',
            'name' => 'required|string|max:100',
            'native_name' => 'nullable|string|max:100',
            'status' => 'required|boolean',
            'is_default' => 'nullable|boolean',
            'direction' => 'required|in:ltr,rtl',
            'sort_order' => 'nullable|integer',
        ]);

        // If setting as default, remove default from all other languages
        if ($request->is_default) {
            Language::where('is_default', 1)->update(['is_default' => 0]);
        }

        Language::create($request->all());

        return redirect()->route('admin.settings.languages.index')
                       ->with('success', 'Language added successfully!');
    }

    /**
     * Show the form for editing language
     */
    public function edit(Language $language)
    {
        return view('admin.settings.languages.edit', compact('language'));
    }

    /**
     * Update the specified language
     */
    public function update(Request $request, Language $language)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:languages,code,' . $language->id,
            'name' => 'required|string|max:100',
            'native_name' => 'nullable|string|max:100',
            'status' => 'required|boolean',
            'is_default' => 'nullable|boolean',
            'direction' => 'required|in:ltr,rtl',
            'sort_order' => 'nullable|integer',
        ]);

        // If setting as default, remove default from all other languages
        if ($request->is_default) {
            Language::where('is_default', 1)
                    ->where('id', '!=', $language->id)
                    ->update(['is_default' => 0]);
        }

        $language->update($request->all());

        return redirect()->route('admin.settings.languages.index')
                       ->with('success', 'Language updated successfully!');
    }

    /**
     * Toggle language status
     */
    public function toggleStatus(Language $language)
    {
        $language->update(['status' => !$language->status]);

        return redirect()->route('admin.settings.languages.index')
                       ->with('success', 'Language status updated!');
    }

    /**
     * Toggle language default
     */
    public function toggleDefault(Language $language)
    {
        // If setting as default, remove default from all other languages
        if (!$language->is_default) {
            Language::where('is_default', 1)->update(['is_default' => 0]);
        }

        $language->update(['is_default' => !$language->is_default]);

        return redirect()->route('admin.settings.languages.index')
                       ->with('success', 'Default language updated!');
    }

    /**
     * Remove the specified language
     */
    public function destroy(Language $language)
    {
        $language->delete();

        return redirect()->route('admin.settings.languages.index')
                       ->with('success', 'Language deleted successfully!');
    }
}
