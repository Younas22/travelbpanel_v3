<?php
use App\Models\MenuItem;

if (!function_exists('getInitials')) {
    function getInitials(string $name): string
    {
        $words = explode(' ', $name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
            if (strlen($initials) >= 2) break;
        }
        return $initials;
    }
}

if (!function_exists('getRandomColor')) {
    function getRandomColor(): string
    {
        $colors = [
            '#20c997', '#0d6efd', '#6f42c1', '#d63384', 
            '#fd7e14', '#ffc107', '#198754', '#0dcaf0'
        ];
        
        return $colors[array_rand($colors)];
    }
}




if (!function_exists('get_menu_items')) {
    function get_menu_items($category = 'header', $activeOnly = true) {
        return MenuItem::where('category', $category)
            ->when($activeOnly, function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('sort_order')
            ->get();
    }
}



if (!function_exists('processImages')) {
    function processImages($content) {
        // Add responsive class to images
        $content = preg_replace('/<img(.*?)>/', '<img$1 class="img-fluid">', $content);
        // Limit image width if width attribute is too large
        $content = preg_replace_callback(
            '/<img(.*?)width="(\d+)"(.*?)>/',
            function($matches) {
                $width = min($matches[2], 1200); // Max width 1200px
                return '<img'.$matches[1].'width="'.$width.'"'.$matches[3].'>';
            },
            $content
        );
        return $content;
    }
}

// Helper function for getting settings (add in your helper file or model)
if (!function_exists('getSetting')) {
    /**
     * Get a setting value by key and group.
     * If key is 'all', return all settings in the group as key => value array
     *
     * @param string $key
     * @param string|null $group
     * @param mixed $default
     * @return mixed
     */
    function getSetting($key, $group = null, $default = null)
    {
        if ($key === 'all') {
            $settings = \App\Models\Setting::where('group', $group)
                                           ->where('is_active', true)
                                           ->get()
                                           ->pluck('value', 'key')
                                           ->toArray();
            return $settings;
        }

        $setting = \App\Models\Setting::where('group', $group)
                                      ->where('key', $key)
                                      ->where('is_active', true)
                                      ->first();
        
        return $setting ? $setting->value : $default;
    }
}


// Helper function for the Agent Signup link — admin can override it (System
// Settings > Agent Signup URL) to point at a custom page instead of the
// built-in /agent/register, and every "Agent Signup" link site-wide follows it.
if (!function_exists('agentSignupUrl')) {
    function agentSignupUrl()
    {
        $custom = trim((string) getSetting('agent_signup_url', 'system', ''));

        if ($custom === '') {
            return route('agent.register');
        }

        return preg_match('#^https?://#i', $custom) ? $custom : url(ltrim($custom, '/'));
    }
}

// The raw, app-relative path behind agentSignupUrl() — null when the setting
// is blank (default /agent/register) or points at an external URL. Used by
// B2BGateMiddleware to recognize the custom signup page as always-allowed
// without redirecting it back to itself; kept separate from agentSignupUrl()
// because that one runs through url(), which prefixes the app's own base
// path (e.g. a subfolder install) — not comparable to Request::path().
if (!function_exists('agentSignupRelativePath')) {
    function agentSignupRelativePath(): ?string
    {
        $custom = trim((string) getSetting('agent_signup_url', 'system', ''));

        if ($custom === '' || preg_match('#^https?://#i', $custom)) {
            return null;
        }

        return ltrim($custom, '/');
    }
}


// Helper function for getting setting image URL
if (!function_exists('getSettingImage')) {
    function getSettingImage($key, $group = null, $default = null)
    {
        $imagePath = getSetting($key, $group);
        if ($imagePath && file_exists(public_path('assets/images/' . $imagePath))) {
            return asset('public/assets/images/' . $imagePath);
        }
        
        return $default ? asset($default) : null;
    }
}


if (!function_exists('getActiveModule')) {
    /**
     * Get a specific active module or all active modules
     *
     * @param string|null $slug  - optional, fetch by module slug
     * @return \App\Models\Module|\Illuminate\Database\Eloquent\Collection|null
     */
    function getActiveModule($slug = null)
    {
        $query = \App\Models\Module::active();

        if ($slug) {
            return $query->where('slug', $slug)->first();
        }

        return $query->get(); // return all active modules
    }
}

// Custom translation helper function for JSON-based translations
if (!function_exists('trans_json')) {
    /**
     * Get translation from JSON files
     *
     * @param string $key Translation key (e.g., 'home.hero_title')
     * @param array $replace Replacement values (not used in current implementation)
     * @param string|null $locale Language code (e.g., 'en', 'ur', 'ar')
     * @return string
     */
    function trans_json($key, $replace = [], $locale = null)
    {
        return \App\Helpers\TranslationHelper::translate($key, $locale);
    }
}

// Alias for shorter usage
if (!function_exists('t')) {
    /**
     * Short alias for trans_json
     *
     * @param string $key Translation key (e.g., 'home.hero_title')
     * @param string|null $locale Language code
     * @return string
     */
    function t($key, $locale = null)
    {
        return \App\Helpers\TranslationHelper::translate($key, $locale);
    }
}

