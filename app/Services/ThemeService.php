<?php

namespace App\Services;

use App\Models\ThemeSetting;

class ThemeService
{
    /**
     * Cache the active theme for the lifetime of the request — every page
     * load reads it once (layout injection) and settings pages read it again.
     */
    protected static ?ThemeSetting $activeCache = null;

    public function active(): ThemeSetting
    {
        return static::$activeCache ??= ThemeSetting::active();
    }

    public function presets(): array
    {
        return config('themes', []);
    }

    public function preset(string $key): ?array
    {
        return config("themes.{$key}");
    }

    /**
     * Build the inline <style> block injected into the admin layout <head>,
     * placed after admin.css so its :root wins the cascade (same specificity,
     * later source order) without needing !important or a CSS rebuild.
     */
    public function inlineStyleTag(ThemeSetting $theme): string
    {
        $vars = $theme->toCssVariables();

        $css = ":root{";
        foreach ($vars as $name => $value) {
            $css .= "{$name}:{$value};";
        }
        $css .= "}";

        if (! empty($theme->font_family)) {
            $css .= "body{font-family:var(--font-family);font-size:var(--font-size-base);}";
        }

        return $css;
    }

    public function forgetCache(): void
    {
        static::$activeCache = null;
    }
}
