<?php

namespace App\Listeners;

use App\Events\CreateOrderEvent;
use App\Mail\OrderNew;
use App\Models\User;
use App\Notifications\OrderCreated;
use Illuminate\Support\Facades\Mail;

class SendEmailsOrderListener
{
    public function __construct() {}

    public function handle(CreateOrderEvent $event): void
    {

        foreach (User::all() as $user) { // Notifico a todos los usuarios que hay un nuevo pedido
            $user->notify(new OrderCreated($event->order));
        }

        // Envío email al email de la dirección de facturación con los detalles del pedido
        Mail::to($event->order->billing_address()->email)
            ->cc($event->order->shipping_address()?->email != $event->order->billing_address()?->email ? $event->order->shipping_address()?->email : null)
            ->send(new OrderNew($event->order));

    }
}
