<?php

namespace App\Listeners;

use App\Events\CreateOrderEvent;
use App\Models\State;

class CreateOrderStateAfterCreateListener
{
    public function __construct() {}

    public function handle(CreateOrderEvent $event): void
    {
        $event->order->states()->create([
            'name' => State::PENDIENTE,
        ]);
    }
}
