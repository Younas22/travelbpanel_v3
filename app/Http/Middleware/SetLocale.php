<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Language;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        // Check if language is passed in query parameter
        if ($request->has('lang')) {
            $lang = $request->get('lang');

            // Validate language is supported (use array_keys to get language codes)
            $supportedLanguages = array_keys(config('app.supported_languages', ['en' => 'English', 'nl' => 'Dutch']));
            if (in_array($lang, $supportedLanguages)) {
                session(['locale' => $lang]);
                app()->setLocale($lang);
            }
        }
        // Otherwise, check session
        elseif (session()->has('locale')) {
            app()->setLocale(session('locale'));
        }
        // Default to database default language
        else {
            $defaultLanguage = Language::getDefault();
            if ($defaultLanguage) {
                app()->setLocale($defaultLanguage->code);
            } else {
                app()->setLocale(config('app.locale', 'en'));
            }
        }

        return $next($request);
    }
}
