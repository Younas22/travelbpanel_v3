<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\WebsiteTheme;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WebsiteThemeController extends Controller
{
    public function update(Request $request)
    {
        $hex = ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        $weight = ['nullable', Rule::in(array_keys(WebsiteTheme::weights()))];

        // Dropdowns / navbar buttons / search tabs: optional colors, px size, weight, corners.
        $groupRules = [];
        $optional = ['font_weight', 'heading_weight', 'button_weight'];
        foreach (WebsiteTheme::groups() as $group => $g) {
            foreach (array_keys($g['colors']) as $key) {
                $groupRules[$key] = ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'];
                $optional[] = $key;
            }
            $groupRules["{$group}_size"] = ['required', Rule::in(array_keys(WebsiteTheme::pxSizes($g['size'])))];
            $groupRules["{$group}_weight"] = $weight;
            $optional[] = "{$group}_weight";
            if ($g['radius']) {
                $groupRules["{$group}_radius"] = ['required', Rule::in(array_keys(WebsiteTheme::radii()))];
            }
        }

        $data = $request->validate($groupRules + [
            'preset' => ['required', Rule::in(array_merge(array_keys(WebsiteTheme::presets()), ['custom']))],
            'primary_color' => $hex,
            'primary_hover' => $hex,
            'bg_color' => $hex,
            'text_color' => $hex,
            'icon_color' => $hex,
            'font' => ['required', Rule::in(WebsiteTheme::fonts())],
            'font_arabic' => ['required', Rule::in(WebsiteTheme::arabicFonts())],
            'font_weight' => $weight,
            'heading_weight' => $weight,
            'button_weight' => $weight,
            'heading_scale' => ['required', Rule::in(array_keys(WebsiteTheme::scales()))],
            'button_scale' => ['required', Rule::in(array_keys(WebsiteTheme::scales()))],
            'icon_scale' => ['required', Rule::in(array_keys(WebsiteTheme::scales()))],
        ]);
        // Empty weight = keep each element's own weight; empty color = Auto (follows the primary).
        foreach ($optional as $key) {
            $data[$key] = (string) ($data[$key] ?? '');
        }

        if ($request->boolean('reset')) {
            $data = WebsiteTheme::defaults();
        }

        WebsiteTheme::save($data);

        return redirect()->route('admin.settings.theme', ['tab' => 'website'])
            ->with('success', $request->boolean('reset') ? 'Website theme reset to default.' : 'Website theme saved.');
    }
}
