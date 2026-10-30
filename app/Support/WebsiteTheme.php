<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Theme of the public website (not the admin panel — that's ThemeSetting).
 *
 * The site's CSS (style.css / rtlstyle.css) reads its colors, font, weights
 * and sizes from CSS variables; common/header.blade.php sets them on <html>
 * from the values saved here (settings table, group "website_theme").
 *
 *   --site-primary / --site-primary-dark      buttons, links, accents (+ hover)
 *   --site-bg                                 white backgrounds (page, cards, panels)
 *   --site-text                               headings / body text
 *   --site-icon                               icons that use the brand color
 *   --site-font                               the whole site's font
 *   --site-{font,heading,button,dropdown}-weight  font weights (unset = each element's own)
 *   --site-{heading,button,dropdown,icon}-scale  size multipliers (unset = 1)
 *   --site-dd-*, --site-nlink-*, --site-nbtn-*,   dropdowns, navbar links/buttons,
 *   --site-tab-*                                  search
 *                                               tabs (see groups(); unset = today's look)
 *
 * Fonts are self-hosted in public/assets/fonts (fonts.css, imported at the
 * top of style.css).
 */
class WebsiteTheme
{
    public const GROUP = 'website_theme';

    protected static ?array $current = null;

    /** English (Latin) fonts offered in the dropdown. */
    public static function fonts(): array
    {
        return ['Poppins', 'Inter', 'Roboto', 'Open Sans', 'Montserrat', 'Lato'];
    }

    /** Arabic fonts, used when the site language is Arabic. */
    public static function arabicFonts(): array
    {
        return ['IBM Plex Sans Arabic', 'Cairo', 'Tajawal', 'Almarai', 'Noto Kufi Arabic'];
    }

    /** Font weight choices; '' keeps each element's own weight. */
    public static function weights(): array
    {
        return ['' => 'Default', '300' => 'Light (300)', '400' => 'Regular (400)', '500' => 'Medium (500)',
            '600' => 'Semi Bold (600)', '700' => 'Bold (700)', '800' => 'Extra Bold (800)'];
    }

    /** Size choices in percent of each element's own size. */
    public static function scales(): array
    {
        return ['80' => '80%', '90' => '90%', '100' => '100% (Default)', '110' => '110%', '120' => '120%', '130' => '130%'];
    }

    /** Text sizes 12–18px; $default is the element's own size today. */
    public static function pxSizes(int $default): array
    {
        $sizes = [];
        foreach (range(12, 18) as $px) {
            $sizes[(string) $px] = $px . 'px' . ($px === $default ? ' (Default)' : '');
        }

        return $sizes;
    }

    /** Corner shapes for buttons and tabs. */
    public static function radii(): array
    {
        return ['pill' => 'Pill (Default)', '12' => 'Rounded (12px)', '8' => 'Soft (8px)', '4' => 'Slight (4px)', '0' => 'Square'];
    }

    /**
     * Groups of settings for one kind of element (Admin > Theme > Website):
     * optional colors ('' = Auto, follows the primary), a px size, a weight and
     * a corner shape. 'css' is the CSS-variable prefix used in style.css.
     * Each color is [css suffix, label, help, auto source] — the source says
     * what an "Auto" color follows (see autoColor()).
     */
    public static function groups(): array
    {
        return [
            'dropdown' => ['css' => 'dd', 'size' => 14, 'radius' => false, 'colors' => [
                'dropdown_hover_bg' => ['hover-bg', 'Hover Background', 'Background of the item under the mouse (and of the current page).', 'primary-tint'],
                'dropdown_hover_color' => ['hover-color', 'Hover Text & Icon', 'Text and icon color of that item.', 'primary'],
            ]],
            'navlink' => ['css' => 'nlink', 'size' => 14, 'radius' => false, 'colors' => [
                'navlink_text' => ['text', 'Link Text', 'Our Services, About, Blog, Support.', 'text'],
                'navlink_hover' => ['hover', 'Hover Text', 'A link under the mouse.', 'primary'],
                'navlink_hover_bg' => ['hover-bg', 'Hover Background', 'Behind a link under the mouse (none by default).', 'bg'],
                'navlink_active' => ['active', 'Active Link', "The current page's link and its underline.", 'primary'],
            ]],
            'navbtn' => ['css' => 'nbtn', 'size' => 14, 'radius' => true, 'colors' => [
                'navbtn_bg' => ['bg', 'Sign Up Background', 'The solid button.', 'primary'],
                'navbtn_text' => ['text', 'Sign Up Text', 'Text on the solid button.', 'white'],
                'navbtn_outline' => ['outline', 'Sign In Text & Border', 'The outline button.', 'primary'],
                'navbtn_hover_bg' => ['hover-bg', 'Hover Background', 'Both buttons when the mouse is over them.', 'primary-tint-6'],
            ]],
            'tab' => ['css' => 'tab', 'size' => 15, 'radius' => true, 'colors' => [
                'tab_active_bg' => ['active-bg', 'Active Tab Background', 'The selected tab (e.g. Stay).', 'primary'],
                'tab_active_text' => ['active-text', 'Active Tab Text', 'Text and icon of the selected tab.', 'white'],
                'tab_text' => ['text', 'Tab Text', 'The other tabs.', 'text'],
                'tab_hover_bg' => ['hover-bg', 'Hover Background', 'A tab under the mouse.', 'primary-tint-6'],
                'tab_hover_text' => ['hover-text', 'Hover Text', 'Text of a tab under the mouse.', 'primary'],
            ]],
        ];
    }

    /**
     * The color an "Auto" field currently resolves to, for showing it in the
     * admin form: 'primary' | 'text' | 'bg' | 'white' | 'primary-tint' (the
     * light primary wash the CSS puts behind hovered items; -6 = the 6% one).
     */
    public static function autoColor(string $source, array $theme): string
    {
        return match ($source) {
            'primary' => $theme['primary_color'],
            'text' => $theme['text_color'],
            'bg' => $theme['bg_color'],
            'white' => '#FFFFFF',
            'primary-tint' => static::mix($theme['primary_color'], $theme['bg_color'], 0.08),
            'primary-tint-6' => static::mix($theme['primary_color'], $theme['bg_color'], 0.06),
        };
    }

    /**
     * $theme with every empty group color filled in with the color it shows
     * today, plus which ones were empty ("linked" — they still follow the theme
     * in the admin form until edited by hand).
     *
     * @return array{0: array, 1: array<string,bool>}
     */
    public static function resolved(array $theme): array
    {
        $linked = [];
        foreach (static::groups() as $g) {
            foreach ($g['colors'] as $key => [, , , $source]) {
                $auto = static::autoColor($source, $theme);
                $linked[$key] = empty($theme[$key]) || strcasecmp($theme[$key], $auto) === 0;
                $theme[$key] = $theme[$key] ?: $auto;
            }
        }

        return [$theme, $linked];
    }

    /** $weight of $hex over $base, as #RRGGBB. */
    protected static function mix(string $hex, string $base, float $weight): string
    {
        $a = array_map('hexdec', str_split(ltrim($hex, '#'), 2));
        $b = array_map('hexdec', str_split(ltrim($base, '#'), 2));

        return vsprintf('#%02X%02X%02X', array_map(fn ($x, $y) => round($x * $weight + $y * (1 - $weight)), $a, $b));
    }

    public static function defaults(): array
    {
        return [
            'preset' => 'default',
            'primary_color' => '#2563EB',
            'primary_hover' => '#1D4ED8',
            'bg_color' => '#FFFFFF',
            'text_color' => '#000000',
            'icon_color' => '#2563EB',
            'font' => 'Poppins',
            'font_arabic' => 'IBM Plex Sans Arabic',
            // Weights ('' = unchanged) and sizes (% of today's size)
            'font_weight' => '',
            'heading_weight' => '',
            'heading_scale' => '100',
            'button_weight' => '',
            'button_scale' => '100',
            // Dropdown menus ('' hover colors = follow the primary color)
            'dropdown_weight' => '',
            'dropdown_size' => '14',
            'dropdown_hover_bg' => '',
            'dropdown_hover_color' => '',
            // Navbar links (Our Services, About, Blog, Support)
            'navlink_text' => '',
            'navlink_hover' => '',
            'navlink_hover_bg' => '',
            'navlink_active' => '',
            'navlink_size' => '14',
            'navlink_weight' => '',
            // Navbar Sign In / Sign Up buttons
            'navbtn_bg' => '',
            'navbtn_text' => '',
            'navbtn_outline' => '',
            'navbtn_hover_bg' => '',
            'navbtn_size' => '14',
            'navbtn_weight' => '',
            'navbtn_radius' => 'pill',
            // Homepage search tabs (Stay, Flight, Tours…)
            'tab_active_bg' => '',
            'tab_active_text' => '',
            'tab_text' => '',
            'tab_hover_bg' => '',
            'tab_hover_text' => '',
            'tab_size' => '15',
            'tab_weight' => '',
            'tab_radius' => 'pill',
            'icon_scale' => '100',
        ];
    }

    /** Ready-made themes; picking one fills the form, which can still be tweaked. */
    public static function presets(): array
    {
        return [
            'default' => ['label' => 'Default Blue', 'values' => static::defaults()],
            'emerald' => ['label' => 'Emerald', 'values' => [
                'primary_color' => '#059669', 'primary_hover' => '#047857', 'bg_color' => '#FFFFFF', 'text_color' => '#0F172A', 'icon_color' => '#0D9488',
                'font' => 'Inter', 'font_arabic' => 'Cairo',
            ]],
            'sunset' => ['label' => 'Sunset', 'values' => [
                'primary_color' => '#EA580C', 'primary_hover' => '#C2410C', 'bg_color' => '#FFFCF8', 'text_color' => '#1C1917', 'icon_color' => '#EA580C',
                'font' => 'Montserrat', 'font_arabic' => 'Tajawal', 'heading_weight' => '800', 'button_weight' => '700',
            ]],
            'royal' => ['label' => 'Royal Purple', 'values' => [
                'primary_color' => '#7C3AED', 'primary_hover' => '#6D28D9', 'bg_color' => '#FFFFFF', 'text_color' => '#111827', 'icon_color' => '#7C3AED',
                'font' => 'Lato', 'font_arabic' => 'Almarai', 'heading_scale' => '110',
            ]],
        ];
    }

    public static function current(): array
    {
        if (static::$current === null) {
            $saved = Setting::where('group', static::GROUP)->where('is_active', true)->pluck('value', 'key')->toArray();
            static::$current = array_merge(static::defaults(), array_intersect_key($saved, static::defaults()));
        }

        return static::$current;
    }

    public static function save(array $values): void
    {
        foreach (array_intersect_key($values, static::defaults()) as $key => $value) {
            Setting::updateOrCreate(
                ['group' => static::GROUP, 'key' => $key],
                ['value' => $value, 'type' => 'string', 'is_active' => true]
            );
        }
        static::$current = null;
    }

    /** CSS variables for the <html style="…"> attribute. */
    public static function inlineVars(?string $locale = null): string
    {
        $t = static::current();
        $isArabic = in_array($locale ?? app()->getLocale(), ['ar'], true);

        $font = "'{$t['font']}', sans-serif";
        if ($isArabic) {
            $font = "'{$t['font_arabic']}', '{$t['font']}', sans-serif";
        }

        $vars = [
            "--site-primary:{$t['primary_color']}",
            "--site-primary-dark:{$t['primary_hover']}",
            "--site-bg:{$t['bg_color']}",
            "--site-text:{$t['text_color']}",
            "--site-icon:{$t['icon_color']}",
            "--site-font:{$font}",
        ];

        // Weights/sizes are only emitted when changed: the CSS falls back to
        // each element's own value (var(--site-heading-weight, 700) etc).
        foreach (['font' => 'font_weight', 'heading' => 'heading_weight', 'button' => 'button_weight', 'dropdown' => 'dropdown_weight'] as $css => $key) {
            if ($t[$key] !== '' && $t[$key] !== null) {
                $vars[] = "--site-{$css}-weight:{$t[$key]}";
            }
        }
        foreach (['heading', 'button', 'icon'] as $part) {
            $scale = (int) $t["{$part}_scale"];
            if ($scale && $scale !== 100) {
                $vars[] = "--site-{$part}-scale:" . ($scale / 100);
            }
        }
        // Dropdown size is picked in px; the CSS scales from the 14px menus.
        $ddSize = (int) $t['dropdown_size'];
        if ($ddSize && $ddSize !== 14) {
            $vars[] = '--site-dropdown-scale:' . round($ddSize / 14, 4);
        }
        // Per-element groups: colors when set, px size / weight / corners when changed.
        foreach (static::groups() as $group => $g) {
            foreach ($g['colors'] as $key => [$suffix]) {
                if (! empty($t[$key])) {
                    $vars[] = "--site-{$g['css']}-{$suffix}:{$t[$key]}";
                }
            }
            if ($group === 'dropdown') {
                continue; // size/weight handled above (scale + shared weight var)
            }
            if ((int) $t["{$group}_size"] && (int) $t["{$group}_size"] !== $g['size']) {
                $vars[] = "--site-{$g['css']}-size:" . (int) $t["{$group}_size"] . 'px';
            }
            if (! empty($t["{$group}_weight"])) {
                $vars[] = "--site-{$g['css']}-weight:{$t["{$group}_weight"]}";
            }
            if (isset($t["{$group}_radius"]) && $t["{$group}_radius"] !== 'pill') {
                $vars[] = "--site-{$g['css']}-radius:" . (int) $t["{$group}_radius"] . 'px';
            }
        }

        return implode(';', $vars);
    }
}
