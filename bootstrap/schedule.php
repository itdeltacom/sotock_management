<?php

use Illuminate\Console\Scheduling\Schedule;

return function (Schedule $schedule) {
    $schedule->command('sitemap:generate')->daily();
};