<?php

namespace App\Listeners;

use App\Events\UpdateOrderStateEvent;
use App\Mail\OrderStateUpdate;
use Illuminate\Support\Facades\Mail;

class SendEmailsOrderStateUpdateListener
{
    public function handle(UpdateOrderStateEvent $event): void
    {

        // Envío email al email de la dirección de facturación con los detalles del pedido
        Mail::to($event->order->billing_address()->email)
            ->cc($event->order->shipping_address()?->email != $event->order->billing_address()?->email ? $event->order->shipping_address()?->email : null)
            ->send(new OrderStateUpdate($event->order));

    }
}
