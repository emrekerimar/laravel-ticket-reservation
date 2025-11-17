<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Reservation;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {
    Reservation::where('status', 'reserved')
        ->where('expires_at', '<', now())
        ->chunk(100, function ($reservations) {
            foreach ($reservations as $res) {
                $res->status = 'expired';
                $res->save();
                $res->event()->increment('available_tickets');
            }
        });
})->everyMinute();
