<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\View;
use App\Models\Language;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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