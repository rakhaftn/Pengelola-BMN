<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule notifikasi peminjaman - setiap hari jam 8 pagi
Schedule::command('peminjaman:send-notifications')->dailyAt('08:00');
