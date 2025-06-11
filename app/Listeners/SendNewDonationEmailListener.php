<?php

namespace App\Listeners;

use App\Events\NewDonationEvent;
use App\Mail\DonationNewMail;
use App\Models\Donation;
use Illuminate\Support\Facades\Mail;

class SendNewDonationEmailListener
{
    public function __construct()
    {
    }

    public function handle(NewDonationEvent $event): void
    {
        if ($event->donation->type === Donation::RECURRENTE && $event->donation->certificate()) {
            Mail::to($event->donation->certificate()->email)
                ->send(new DonationNewMail($event->donation));
        }
    }
}
