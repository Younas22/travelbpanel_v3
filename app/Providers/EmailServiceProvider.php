<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Resend\Laravel\Facades\Resend;

class EmailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (\Schema::hasTable('settings')) {
            $apiKey = \App\Models\Setting::getValue('resend_api_key', 'email');
            if ($apiKey) {
                config(['services.resend.key' => $apiKey]);
            }
        }
    }

}