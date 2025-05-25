<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderState;

class OrderObserver
{
    public function created(Order $order): void
    {
        $order->states()->create([
            'name' => OrderState::PENDIENTE,
        ]);
    }

    public function deleted(Order $order): void
    {
    }
}
