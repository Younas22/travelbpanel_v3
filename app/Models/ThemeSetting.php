<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    protected $fillable = [
        'user_id',
        'theme_name',
        'design_style',
        'primary_color',
        'secondary_color',
        'success_color',
        'warning_color',
        'danger_color',
        'info_color',
        'body_background',
        'sidebar_background',
        'navbar_background',
        'card_background',
        'text_color',
        'border_color',
        'input_background',
        'input_border',
        'input_focus_color',
        'font_family',
        'font_size',
        'border_radius',
        'layout_options',
        'is_active',
    ];

    protected $casts = [
        'layout_options' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Default (factory) values — also what "Reset to Default" restores.
     * Pass $userId to seed a personal (agent-owned) row instead of the
     * shared global one.
     */
    public static function defaults(?int $userId = null): array
    {
        return [
            'user_id' => $userId,
            'theme_name' => 'default',
            'design_style' => 'classic',
            'primary_color' => '#0C6DFD',
            'secondary_color' => '#64748B',
            'success_color' => '#10B981',
            'warning_color' => '#F59E0B',
            'danger_color' => '#EF4444',
            'info_color' => '#0EA5E9',
            'body_background' => '#F8FAFC',
            'sidebar_background' => '#FFFFFF',
            'navbar_background' => '#FFFFFF',
            'card_background' => '#FFFFFF',
            'text_color' => '#1F2937',
            'border_color' => '#E5E7EB',
            'input_background' => '#FFFFFF',
            'input_border' => '#E5E7EB',
            'input_focus_color' => '#0C6DFD',
            'font_family' => 'Inter',
            'font_size' => '14px',
            'border_radius' => 'medium',
            'layout_options' => self::defaultLayoutOptions(),
            'is_active' => true,
        ];
    }

    public static function defaultLayoutOptions(): array
    {
        return [
            'sidebar_fixed' => true,
            'navbar_fixed' => true,
            'box_shadow' => true,
            'rounded_cards' => true,
            'rounded_inputs' => true,
            'rounded_buttons' => true,
            'compact_mode' => false,
            'wide_layout' => false,
            'fluid_layout' => false,
            'animations' => true,
        ];
    }

    /**
     * The active theme row. Pass $userId to resolve an agent's personal
     * theme (falling back to the shared global default if they haven't
     * customized one yet); omit it for the Admin panel's shared theme.
     */
    public static function active(?int $userId = null): self
    {
        if ($userId) {
            $userTheme = static::where('user_id', $userId)->first();
            if ($userTheme) {
                return $userTheme;
            }
        }

        $theme = static::whereNull('user_id')->where('is_active', true)->first();

        if (! $theme) {
            $theme = static::firstOrCreate(['user_id' => null], static::defaults());
        }

        return $theme;
    }

    /**
     * The row a user's Theme Settings page reads/writes — creates their
     * personal row (seeded from today's global default) on first save.
     */
    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(['user_id' => $userId], static::defaults($userId));
    }

    /**
     * "classic" -> admin.css (the original design), "modern" -> the new
     * admin-modern.css design. Both consume the exact same CSS variables,
     * so colors/fonts/radius/layout toggles apply identically to either.
     */
    public function designStylesheet(): string
    {
        return $this->design_style === 'modern'
            ? 'public/assets/css/admin-modern.css'
            : 'public/assets/css/admin.css';
    }

    public function isModernDesign(): bool
    {
        return $this->design_style === 'modern';
    }

    /**
     * "nova" -> the third design (see App\View\NovaAdminViewFinder), rolled
     * out phased like Modern was — currently only its Dashboard exists, so
     * every other admin.* page falls back to Classic (not Modern) while
     * design_style is "nova".
     */
    public function isNovaDesign(): bool
    {
        return $this->design_style === 'nova';
    }

    public function getLayoutOption(string $key, $default = null)
    {
        return data_get($this->layout_options, $key, data_get(static::defaultLayoutOptions(), $key, $default));
    }

    /**
     * Radius keyword -> concrete px scale used for --radius-sm/md/lg.
     */
    public static function radiusScale(string $keyword): array
    {
        return match ($keyword) {
            'small' => ['sm' => '2px', 'md' => '4px', 'lg' => '6px'],
            'large' => ['sm' => '8px', 'md' => '12px', 'lg' => '16px'],
            'xl', 'extra-large' => ['sm' => '12px', 'md' => '18px', 'lg' => '24px'],
            default => ['sm' => '4px', 'md' => '8px', 'lg' => '12px'], // medium
        };
    }

    /**
     * "#rrggbb" -> "r,g,b" for Bootstrap's `--bs-*-rgb` variables (used by
     * rgba()-based opacity utilities). Returns null for non-hex values
     * (e.g. the Glass preset's rgba() surfaces) since there's nothing to
     * extract — the bridge var is simply omitted for those.
     */
    public static function hexToRgbTriplet(?string $hex): ?string
    {
        if (! $hex || ! preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $hex, $m)) {
            return null;
        }

        $value = $m[1];
        if (strlen($value) === 3) {
            $value = $value[0].$value[0].$value[1].$value[1].$value[2].$value[2];
        }

        return implode(',', array_map(fn ($h) => hexdec($h), str_split($value, 2)));
    }

    /**
     * Map this theme row to the CSS custom properties admin.css consumes.
     *
     * @return array<string,string>
     */
    public function toCssVariables(): array
    {
        $radius = static::radiusScale($this->border_radius ?? 'medium');
        $layout = array_merge(static::defaultLayoutOptions(), $this->layout_options ?? []);

        $vars = [
            '--primary-color' => $this->primary_color,
            '--secondary-color' => $this->secondary_color,
            '--success-color' => $this->success_color,
            '--warning-color' => $this->warning_color,
            '--danger-color' => $this->danger_color,
            '--info-color' => $this->info_color,
            '--body-bg' => $this->body_background,
            '--sidebar-bg' => $this->sidebar_background,
            '--navbar-bg' => $this->navbar_background,
            '--card-bg' => $this->card_background,
            '--text-color' => $this->text_color,
            '--border-color' => $this->border_color,
            '--input-bg' => $this->input_background,
            '--input-border' => $this->input_border,
            '--input-focus-color' => $this->input_focus_color,
            '--font-family' => $this->font_family ? "'{$this->font_family}', 'Inter', sans-serif" : null,
            '--font-size-base' => $this->font_size,
            '--radius-sm' => $radius['sm'],
            '--radius-md' => $radius['md'],
            '--radius-lg' => $radius['lg'],
            '--card-radius' => $layout['rounded_cards'] ? $radius['lg'] : '0px',
            '--input-radius' => $layout['rounded_inputs'] ? $radius['md'] : '0px',
            '--btn-radius' => $layout['rounded_buttons'] ? $radius['md'] : '0px',
            '--shadow-toggle' => $layout['box_shadow'] ? '1' : '0',
            '--transition-speed' => $layout['animations'] ? '0.2s' : '0s',

            // Bootstrap 5.3 variable bridge: re-skins every raw .btn-primary,
            // .badge.bg-*, .form-check-input:checked, .page-link, .nav-pills
            // active state, .link-primary, .card, .progress-bar, etc. for free.
            '--bs-primary' => $this->primary_color,
            '--bs-link-color' => $this->primary_color,
            '--bs-link-hover-color' => $this->primary_color,
            '--bs-success' => $this->success_color,
            '--bs-warning' => $this->warning_color,
            '--bs-danger' => $this->danger_color,
            '--bs-info' => $this->info_color,
            '--bs-card-bg' => $this->card_background,
        ];

        foreach ([
            'primary' => $this->primary_color,
            'success' => $this->success_color,
            'warning' => $this->warning_color,
            'danger' => $this->danger_color,
            'info' => $this->info_color,
        ] as $name => $hex) {
            if ($rgb = static::hexToRgbTriplet($hex)) {
                $vars["--bs-{$name}-rgb"] = $rgb;
            }
        }

        return array_filter($vars, fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Whether the active preset is a dark theme (drives <html data-bs-theme>).
     * Falls back to a simple luminance check on body_background for fully
     * custom themes that aren't tied to one of the config/themes.php presets.
     */
    public function isDark(): bool
    {
        $preset = config("themes.{$this->theme_name}");
        if ($preset !== null) {
            return (bool) ($preset['dark'] ?? false);
        }

        return static::isColorDark($this->body_background);
    }

    protected static function isColorDark(?string $hex): bool
    {
        $rgb = static::hexToRgbTriplet($hex);
        if (! $rgb) {
            return false;
        }

        [$r, $g, $b] = array_map('intval', explode(',', $rgb));
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance < 0.5;
    }

    public function layoutBodyClasses(): string
    {
        $layout = array_merge(static::defaultLayoutOptions(), $this->layout_options ?? []);

        $map = [
            'sidebar_fixed' => 'theme-sidebar-fixed',
            'navbar_fixed' => 'theme-navbar-fixed',
            'box_shadow' => 'theme-shadow',
            'compact_mode' => 'theme-compact',
            'wide_layout' => 'theme-wide',
            'fluid_layout' => 'theme-fluid',
            'animations' => 'theme-animated',
        ];

        $classes = [];
        foreach ($map as $key => $class) {
            if (! empty($layout[$key])) {
                $classes[] = $class;
            }
        }

        return implode(' ', $classes);
    }
}
