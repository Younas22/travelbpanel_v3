<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use App\Models\Language;

class TranslationController extends Controller
{
    protected $langPath;
    protected $supportedLanguages = [];

    public function __construct()
    {
        $this->langPath = resource_path('lang');
        // Load active languages from database
        $this->supportedLanguages = Language::getActiveCodes();
    }

    /**
     * Get active languages with full data
     */
    protected function getActiveLanguages()
    {
        return Language::getActive();
    }

    /**
     * Display all translations
     */
    public function index(Request $request)
    {
        $selectedLang = $request->get('language', 'en');
        $selectedGroup = $request->get('group');

        // Get all language files (groups)
        $groups = $this->getAvailableGroups();
        
        // Get translations for selected language and group
        $translations = [];
        
        if ($selectedGroup) {
            $translations = $this->getTranslations($selectedLang, $selectedGroup);
        } else if (!empty($groups)) {
            // Load first group by default
            $selectedGroup = $groups[0];
            $translations = $this->getTranslations($selectedLang, $selectedGroup);
        }

        $supportedLanguages = $this->supportedLanguages;
        $languages = $this->getActiveLanguages();

        return view('admin.content.translations.index', compact(
            'translations',
            'groups',
            'selectedLang',
            'selectedGroup',
            'supportedLanguages',
            'languages'
        ));
    }

    /**
     * Create new translation file
     */
    public function create()
    {
        return view('admin.content.translations.create', [
            'supportedLanguages' => $this->supportedLanguages,
            'languages' => $this->getActiveLanguages()
        ]);
    }

    /**
     * Store new translation group
     */
    public function store(Request $request)
    {
        // Decode translations if it's a JSON string
        if (is_string($request->translations)) {
            $request->merge([
                'translations' => json_decode($request->translations, true)
            ]);
        }

        $request->validate([
            'group_name' => 'required|string|alpha_dash',
            'translations' => 'required|array',
        ]);

        $groupName = $request->group_name;
        $success = true;

        // Create translation files for each language
        foreach ($this->supportedLanguages as $lang) {
            $langDir = $this->langPath . '/' . $lang;
            
            // Create language directory if not exists
            if (!File::exists($langDir)) {
                File::makeDirectory($langDir, 0755, true);
            }

            $filePath = $langDir . '/' . $groupName . '.json';
            $content = [];

            // Get translations for this language
            foreach ($request->translations as $key => $values) {
                if (isset($values[$lang])) {
                    $content[$key] = $values[$lang];
                }
            }

            // Write JSON file
            File::put($filePath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return redirect()->route('admin.content.translations.index', ['group' => $groupName])
                       ->with('success', 'Translation group created successfully!');
    }

    /**
     * Edit translations
     */
    public function edit($group)
    {
        $translations = [];
        $data = [];

        foreach ($this->supportedLanguages as $lang) {
            $translations[$lang] = $this->getTranslations($lang, $group);
        }

        // Merge all keys from all languages
        $allKeys = [];
        foreach ($translations as $langTrans) {
            $allKeys = array_merge($allKeys, array_keys($langTrans));
        }
        $allKeys = array_unique($allKeys);

        // Prepare data for view
        foreach ($allKeys as $key) {
            $data[$key] = [];
            foreach ($this->supportedLanguages as $lang) {
                $data[$key][$lang] = $translations[$lang][$key] ?? '';
            }
        }

        return view('admin.content.translations.edit', [
            'group' => $group,
            'translations' => $data,
            'supportedLanguages' => $this->supportedLanguages,
            'languages' => $this->getActiveLanguages()
        ]);
    }

    /**
     * Update translations
     */
    public function update($group, Request $request)
    {
        $request->validate([
            'translations' => 'required|array',
        ]);

        // Update translation files for each language
        foreach ($this->supportedLanguages as $lang) {
            $translations = $this->getTranslations($lang, $group);
            
            // Update with new values
            foreach ($request->translations as $key => $values) {
                if (isset($values[$lang])) {
                    $translations[$key] = $values[$lang];
                }
            }

            // Write updated JSON file
            $filePath = $this->langPath . '/' . $lang . '/' . $group . '.json';
            File::put($filePath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return redirect()->route('admin.content.translations.index', ['group' => $group])
                       ->with('success', 'Translations updated successfully!');
    }

    /**
     * Delete translation group
     */
    public function destroy($group)
    {
        foreach ($this->supportedLanguages as $lang) {
            $filePath = $this->langPath . '/' . $lang . '/' . $group . '.json';
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }

        return redirect()->route('admin.content.translations.index')
                       ->with('success', 'Translation group deleted successfully!');
    }

    /**
     * Add new key to translation
     */
    public function addKey($group, Request $request)
    {
        $request->validate([
            'key' => 'required|string|alpha_dash',
            'translations' => 'required|array',
        ]);

        foreach ($this->supportedLanguages as $lang) {
            $translations = $this->getTranslations($lang, $group);
            
            if (isset($request->translations[$lang])) {
                $translations[$request->key] = $request->translations[$lang];
            }

            $filePath = $this->langPath . '/' . $lang . '/' . $group . '.json';
            File::put($filePath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return redirect()->route('admin.content.translations.edit', $group)
                       ->with('success', 'Key added successfully!');
    }

    /**
     * Delete specific key from translation
     */
    public function deleteKey($group, $key)
    {
        foreach ($this->supportedLanguages as $lang) {
            $translations = $this->getTranslations($lang, $group);
            unset($translations[$key]);

            $filePath = $this->langPath . '/' . $lang . '/' . $group . '.json';
            File::put($filePath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return redirect()->route('admin.content.translations.edit', $group)
                       ->with('success', 'Key deleted successfully!');
    }

    /**
     * Get translations from JSON file
     */
    protected function getTranslations($lang, $group)
    {
        $filePath = $this->langPath . '/' . $lang . '/' . $group . '.json';

        if (!File::exists($filePath)) {
            return [];
        }

        $content = File::get($filePath);
        return json_decode($content, true) ?? [];
    }

    /**
     * Get all available translation groups
     */
    protected function getAvailableGroups()
    {
        $langDir = $this->langPath . '/en';
        
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
     * Export translation to JSON (download)
     */
    public function export($lang, $group)
    {
        $translations = $this->getTranslations($lang, $group);
        
        return response()->json($translations, 200, [
            'Content-Disposition' => "attachment; filename=\"{$group}-{$lang}.json\""
        ]);
    }

    /**
     * Import translation from JSON file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|json',
            'language' => 'required|in:en,ur,ar',
            'group' => 'required|alpha_dash',
        ]);

        $langDir = $this->langPath . '/' . $request->language;
        File::makeDirectory($langDir, 0755, true, true);

        $filePath = $langDir . '/' . $request->group . '.json';
        File::move($request->file('file')->path(), $filePath);

        return redirect()->route('admin.content.translations.index', ['group' => $request->group])
                       ->with('success', 'Translation imported successfully!');
    }
}