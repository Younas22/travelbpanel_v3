<?php

namespace App\Http\Controllers\Agent;

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
        $theme = $this->themeService->active(auth()->id());
        $presets = $this->themeService->presets();

        return view('agent.settings.theme', compact('theme', 'presets'));
    }

    public function update(UpdateThemeSettingRequest $request)
    {
        $data = $request->validated();
        $data['layout_options'] = array_merge(
            ThemeSetting::defaultLayoutOptions(),
            $data['layout_options'] ?? []
        );

        // Same rule as the Admin panel: the client always sends the fully
        // resolved current field values, so submitted colors are
        // authoritative — "preset" only tags the stored label.
        $data['theme_name'] = ($request->filled('preset') && $request->string('preset')->toString() !== 'custom')
            ? $request->string('preset')->toString()
            : ($data['theme_name'] ?? 'custom');

        $theme = ThemeSetting::forUser(auth()->id());
        $theme->update($data);
        $this->themeService->forgetCache(auth()->id());

        $fresh = $theme->fresh();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your theme has been saved.',
                'css_vars' => $fresh->toCssVariables(),
                'body_classes' => $fresh->layoutBodyClasses(),
            ]);
        }

        return back()->with('success', 'Your theme has been saved.');
    }

    public function reset(Request $request)
    {
        $theme = ThemeSetting::forUser(auth()->id());
        $theme->update(ThemeSetting::defaults(auth()->id()));
        $this->themeService->forgetCache(auth()->id());

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
