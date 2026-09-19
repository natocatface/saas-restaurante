<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Resumen diario de boletas SUNAT: se envía cada noche para las boletas del día.
\Illuminate\Support\Facades\Schedule::command('sunat:resumen')->dailyAt('23:55')->withoutOverlapping();
