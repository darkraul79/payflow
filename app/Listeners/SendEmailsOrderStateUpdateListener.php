<?php

namespace App\Listeners;

use App\Events\UpdateOrderStateEvent;
use App\Mail\OrderStateUpdate;
use Illuminate\Support\Facades\Mail;

class SendEmailsOrderStateUpdateListener
{
    public function handle(UpdateOrderStateEvent $event): void
    {

        // Obtengo el último estado directamente desde la base de datos para evitar
        // depender de relaciones ya cargadas en memoria que podrían estar desactualizadas.
        // Uso orderBy('id','desc') para evitar problemas cuando created_at tenga la misma resolución.
        $lastState = $event->order->states()->orderBy('id', 'desc')->first();

        $stateName = $lastState?->name ?? null;
        $stateInfo = $lastState?->info?->toArray() ?? null;

        // Envío email al email de la dirección de facturación con los detalles del pedido
        Mail::to($event->order->billing_address()->email)
            ->cc($event->order->shipping_address()?->email != $event->order->billing_address()?->email ? $event->order->shipping_address()?->email : null)
            ->queue(new OrderStateUpdate($event->order, $stateName, $stateInfo));

    }
}
