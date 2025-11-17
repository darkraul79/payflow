<?php

namespace App\Listeners;

use App\Enums\OrderStatus;
use App\Events\CreateOrderEvent;

class CreateOrderStateAfterCreateListener
{
    public function __construct() {}

    public function handle(CreateOrderEvent $event): void
    {
        $event->order->states()->create([
            'name' => OrderStatus::PENDIENTE->value,
        ]);
    }
}
