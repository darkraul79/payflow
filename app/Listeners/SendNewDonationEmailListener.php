<?php

namespace App\Listeners;

use App\Events\NewDonationEvent;
use App\Mail\DonationNewMail;
use App\Models\User;
use App\Notifications\DonationCreatedNotification;
use Illuminate\Support\Facades\Mail;

class SendNewDonationEmailListener
{
    public function handle(NewDonationEvent $event): void
    {
        $this->notificoATodosLosUsuariosQueHayNuevaDonacion($event);

        if ($event->donation->certificate() && $event->donation->certificate()->email) {

            Mail::to($event->donation->certificate()->email)
                ->send(new DonationNewMail($event->donation));
        }
    }

    public function notificoATodosLosUsuariosQueHayNuevaDonacion(NewDonationEvent $event): void
    {
        // Notifico a todos los usuarios que hay un nuevo pedido
        if (config('app.env') === 'production') {
            // Si estamos en producción notifico a todos los usuarios
            foreach (User::all() as $user) {
                $user->notify(new DonationCreatedNotification($event->donation));
            }
        } else {
            // Si no sólo notifico a mi cuenta de correo
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            User::where('email',
                'info@raulsebastian.es')->first()?->notify(new DonationCreatedNotification($event->donation));
        }
    }
}
