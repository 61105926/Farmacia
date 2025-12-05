<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar verificación de productos próximos a vencer (diariamente a las 8:00 AM)
Schedule::command('notifications:check-expiring-products')
    ->dailyAt('08:00')
    ->timezone('America/La_Paz')
    ->withoutOverlapping()
    ->runInBackground();

// Programar verificación de facturas vencidas (diariamente a las 9:00 AM)
Schedule::command('notifications:check-overdue-invoices')
    ->dailyAt('09:00')
    ->timezone('America/La_Paz')
    ->withoutOverlapping()
    ->runInBackground();
