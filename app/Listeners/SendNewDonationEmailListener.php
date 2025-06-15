<?php

namespace App\Listeners;

use App\Events\NewDonationEvent;
use App\Mail\DonationNewMail;
use Illuminate\Support\Facades\Mail;

class SendNewDonationEmailListener
{

    public function handle(NewDonationEvent $event): void
    {

        if ($event->donation->certificate() && $event->donation->certificate()->email) {

            Mail::to($event->donation->certificate()->email)
                ->send(new DonationNewMail($event->donation));
        }
    }
}
