<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('webbeds:import-cities {--seconds=50}', function () {
    $result = app(\App\Services\WebbedsCityImporter::class)->runBatch((int) $this->option('seconds'), 'console');
    $this->info($result['message'] ?? json_encode($result));
})->purpose('Import due WebBeds cities (hotels) into database/webbeds.db for a time-limited batch');
