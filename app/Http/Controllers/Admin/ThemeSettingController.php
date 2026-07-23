<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateThemeSettingRequest;
use App\Models\ThemeSetting;
use App\Services\ThemeService;
use Illuminate\Http\Request;

class ThemeSettingController extends Controller
{
    public function __construct(protected ThemeService $themeService)
    {
    }

    public function edit()
    {
        $theme = $this->themeService->active();
        $presets = $this->themeService->presets();

        return view('admin.settings.theme', compact('theme', 'presets'));
    }

    public function update(UpdateThemeSettingRequest $request)
    {
        $data = $request->validated();
        $data['layout_options'] = array_merge(
            ThemeSetting::defaultLayoutOptions(),
            $data['layout_options'] ?? []
        );

        // The client always sends the fully-resolved current field values
        // (preset colors already merged in client-side when a card was
        // clicked, plus any manual tweaks made afterward) — so the submitted
        // colors are authoritative here. "preset" only tags which label to
        // store; it must never re-derive colors server-side, or a manual
        // edit made after picking a preset would be silently overwritten.
        $data['theme_name'] = ($request->filled('preset') && $request->string('preset')->toString() !== 'custom')
            ? $request->string('preset')->toString()
            : ($data['theme_name'] ?? 'custom');

        $theme = $this->themeService->active();
        $theme->update($data);
        $this->themeService->forgetCache();

        $fresh = $theme->fresh();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Theme settings saved successfully.',
                'css_vars' => $fresh->toCssVariables(),
                'body_classes' => $fresh->layoutBodyClasses(),
            ]);
        }

        return back()->with('success', 'Theme settings saved successfully.');
    }

    public function reset(Request $request)
    {
        $theme = $this->themeService->active();
        $theme->update(ThemeSetting::defaults());
        $this->themeService->forgetCache();

        $fresh = $theme->fresh();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Theme reset to default.',
                'theme' => $fresh,
                'css_vars' => $fresh->toCssVariables(),
                'body_classes' => $fresh->layoutBodyClasses(),
            ]);
        }

        return back()->with('success', 'Theme reset to default.');
    }
}
