<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use App\Models\Language;

class TranslationHelper
{
    protected static $cache = [];
    protected static $langPath;

    /**
     * Get translated value from JSON file
     * 
     * Usage: 
     *   trans_db('home.hero_title')
     *   trans_db('about.description', 'ur')
     *   trans_db('footer.copyright', 'ar')
     */
    public static function translate($key, $lang = null)
    {
        $lang = $lang ?? app()->getLocale();
        
        // Split key into group and key
        list($group, $keyName) = self::parseKey($key);
        
        // Get translations from JSON file
        $translations = self::getGroupTranslations($group, $lang);
        
        return $translations[$keyName] ?? $key;
    }

    /**
     * Get all translations for a specific language
     * 
     * Usage: trans_db_all('home')  // Get all translations for home group
     */
    public static function getGroup($group, $lang = null)
    {
        $lang = $lang ?? app()->getLocale();
        return self::getGroupTranslations($group, $lang);
    }

    /**
     * Get translations for all languages for a specific group
     *
     * Usage: trans_db_all_langs('home')  // Get home translations in all languages
     */
    public static function getGroupAllLanguages($group)
    {
        // Get active language codes from database
        $languages = self::getAvailableLanguages();
        $result = [];

        foreach ($languages as $lang) {
            $result[$lang] = self::getGroupTranslations($group, $lang);
        }

        return $result;
    }

    /**
     * Check if translation exists
     */
    public static function exists($key, $lang = null)
    {
        $lang = $lang ?? app()->getLocale();
        list($group, $keyName) = self::parseKey($key);

        $translations = self::getGroupTranslations($group, $lang);
        return isset($translations[$keyName]);
    }

    /**
     * Get all available groups
     */
    public static function getAvailableGroups()
    {
        self::initPath();
        $langDir = self::$langPath . '/en';

        if (!File::exists($langDir)) {
            return [];
        }

        $files = File::files($langDir);
        $groups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'json') {
                $groups[] = $file->getBasename('.json');
            }
        }

        sort($groups);
        return $groups;
    }

    /**
     * Get all available languages from database
     */
    public static function getAvailableLanguages()
    {
        try {
            // Get active language codes from database
            return Language::getActiveCodes();
        } catch (\Exception $e) {
            // Fallback to default languages if database not available or table doesn't exist
            return ['en', 'nl'];
        }
    }

    /**
     * Get all available languages with full data
     */
    public static function getAvailableLanguagesWithData()
    {
        try {
            return Language::getActive();
        } catch (\Exception $e) {
            // Fallback to default if database not available
            return collect([
                (object)['code' => 'en', 'name' => 'English', 'native_name' => 'English'],
                (object)['code' => 'nl', 'name' => 'Dutch', 'native_name' => 'Nederlands']
            ]);
        }
    }

    /**
     * PRIVATE METHODS
     */

    /**
     * Parse translation key (e.g., "home.title" => ["home", "title"])
     */
    protected static function parseKey($key)
    {
        $parts = explode('.', $key, 2);
        
        if (count($parts) === 2) {
            return $parts;
        }

        return [$key, ''];
    }

    /**
     * Get translations from JSON file with caching
     */
    protected static function getGroupTranslations($group, $lang)
    {
        self::initPath();
        $cacheKey = "{$group}_{$lang}";

        // Return cached if exists
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $filePath = self::$langPath . '/' . $lang . '/' . $group . '.json';

        if (!File::exists($filePath)) {
            self::$cache[$cacheKey] = [];
            return [];
        }

        $content = File::get($filePath);
        $translations = json_decode($content, true) ?? [];

        self::$cache[$cacheKey] = $translations;
        return $translations;
    }

    /**
     * Initialize language path
     */
    protected static function initPath()
    {
        if (!self::$langPath) {
            self::$langPath = resource_path('lang');
        }
    }

    /**
     * Clear cache (useful in development)
     */
    public static function clearCache()
    {
        self::$cache = [];
    }
}