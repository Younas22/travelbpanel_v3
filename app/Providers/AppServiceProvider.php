<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\View;
use App\Models\Language;
use App\View\NovaAdminViewFinder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Transparently serves resources/views/admin-nova/* in place of
        // admin/* when the current admin's Design Style is "nova" — see
        // NovaAdminViewFinder's docblock. Controllers/routes are never
        // touched; this is the only place the swap happens. The agent panel
        // has a single (Classic) design, so it needs no finder.
        $this->app->extend('view.finder', function ($finder) {
            return new NovaAdminViewFinder($finder);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Event::listen(RequestHandled::class, function (RequestHandled $event) {
            if (isset($event->response->exception) && 
                $event->response->exception instanceof \Illuminate\Session\TokenMismatchException) {
                \Log::error('CSRF Token Mismatch', [
                    'url' => $event->request->url(),
                    'token' => $event->request->input('_token'),
                    'session_token' => $event->request->session()->token(),
                    'referrer' => $event->request->header('referer'),
                    'user_agent' => $event->request->header('User-Agent')
                ]);
            }
        });

        $this->registerHelpers();

        // Share active languages with all views
        View::composer('*', function ($view) {
            $view->with('activeLanguages', Language::getActive());
        });
    }

    public function registerHelpers()
    {
        require_once app_path('Helpers/TranslationHelper.php');
    }
}