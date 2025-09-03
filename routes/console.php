<?php


use App\Console\Commands\GetPaymentsOfMonth;

Schedule::command(GetPaymentsOfMonth::class)->timezone('Europe/Madrid')->monthlyOn(5, '08:00');
Schedule::command('queue:work --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping();
