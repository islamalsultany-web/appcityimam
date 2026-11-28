<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('reports:purge-uploads', function () {
    $count = app(\App\Support\ReportExcelUploads::class)->purgeExpired();
    $this->info('Removed '.$count.' expired report uploads.');
})->purpose('Remove unfinished private Excel uploads older than one day');

Schedule::command('reports:purge-uploads')->hourly()->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
