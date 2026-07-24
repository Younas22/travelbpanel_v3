<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateThemeSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Accepts #hex / #hexa / rgb()/rgba() so glass-style translucent
        // presets (which use rgba() surfaces) can round-trip through the form.
        $color = ['required', 'string', 'max:40', 'regex:/^(#[0-9A-Fa-f]{3,8}|rgba?\([0-9.,\s%]+\))$/'];

        return [
            'theme_name' => ['nullable', 'string', 'max:40'],
            'design_style' => ['nullable', 'string', 'in:classic,modern'],

            'primary_color' => $color,
            'secondary_color' => $color,
            'success_color' => $color,
            'warning_color' => $color,
            'danger_color' => $color,
            'info_color' => $color,
            'body_background' => $color,
            'sidebar_background' => $color,
            'navbar_background' => $color,
            'card_background' => $color,
            'text_color' => $color,
            'border_color' => $color,
            'input_background' => $color,
            'input_border' => $color,
            'input_focus_color' => $color,

            'font_family' => ['required', 'string', 'max:60'],
            'font_size' => ['required', 'string', 'max:10'],
            'border_radius' => ['required', 'string', 'in:small,medium,large,xl'],

            'layout_options' => ['nullable', 'array'],
            'layout_options.sidebar_fixed' => ['nullable', 'boolean'],
            'layout_options.navbar_fixed' => ['nullable', 'boolean'],
            'layout_options.box_shadow' => ['nullable', 'boolean'],
            'layout_options.rounded_cards' => ['nullable', 'boolean'],
            'layout_options.rounded_inputs' => ['nullable', 'boolean'],
            'layout_options.rounded_buttons' => ['nullable', 'boolean'],
            'layout_options.compact_mode' => ['nullable', 'boolean'],
            'layout_options.wide_layout' => ['nullable', 'boolean'],
            'layout_options.fluid_layout' => ['nullable', 'boolean'],
            'layout_options.animations' => ['nullable', 'boolean'],
        ];
    }
}
