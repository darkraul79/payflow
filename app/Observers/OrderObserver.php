<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\State;

class OrderObserver
{
    public function created(Order $order): void
    {
        $order->states()->create([
            'name' => State::PENDIENTE,
        ]);
    }

    public function deleted(Order $order): void
    {
    }
}
