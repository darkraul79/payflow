<?php

use App\Console\Commands\GetPaymentsOfMonthCommand;

Schedule::command(GetPaymentsOfMonthCommand::class)->timezone('Europe/Madrid')->monthlyOn(5, '08:00');
Schedule::command('queue:work --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping();
