<?php

namespace App\Services;

use App\Models\ThemeSetting;

class ThemeService
{
    /**
     * Cache the active theme for the lifetime of the request — every page
     * load reads it once (layout injection) and settings pages read it again.
     * Keyed per user id ('global' for the Admin panel's shared theme) since
     * an agent's personal theme and the global default are independent.
     */
    protected static array $activeCache = [];

    public function active(?int $userId = null): ThemeSetting
    {
        $key = $userId ?? 'global';

        return static::$activeCache[$key] ??= ThemeSetting::active($userId);
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

        // admin.css's ~500 component font-sizes are expressed in rem, which
        // is relative to the ROOT (<html>) element's font-size, not <body>'s
        // — so scaling only body{font-size} (the old behavior) left every
        // rem-sized element completely unaffected. 0.875 is the rem value
        // "14px" (the factory default) has always assumed a 16px root, so
        // dividing by it here reproduces today's exact sizes when the
        // default is selected, and scales every rem-based size in the
        // panel proportionally for any other choice.
        if (! empty($theme->font_size)) {
            $css .= "html{font-size:calc(var(--font-size-base) / 0.875);}";
        }

        if (! empty($theme->font_family)) {
            $css .= "body{font-family:var(--font-family);font-size:var(--font-size-base);}";
        }

        return $css;
    }

    public function forgetCache(?int $userId = null): void
    {
        unset(static::$activeCache[$userId ?? 'global']);
    }
}
