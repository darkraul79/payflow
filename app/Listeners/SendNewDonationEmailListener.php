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
        foreach (User::all() as $user) { // Notifico a todos los usuarios que hay un nuevo pedido
            $user->notify(new DonationCreatedNotification($event->donation));
        }

        if ($event->donation->certificate() && $event->donation->certificate()->email) {

            Mail::to($event->donation->certificate()->email)
                ->send(new DonationNewMail($event->donation));
        }
    }
}
