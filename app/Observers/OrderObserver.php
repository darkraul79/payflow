<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    public function created(Order $order): void
    {
        //        CreateOrderEvent::dispatch($order);
        /*$order->states()->create([
            'name' => State::PENDIENTE,
        ]);*/
    }

    public function deleted(Order $order): void {}

    public function updated(Order $order): void {}
}
