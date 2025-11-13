<?php

namespace App\Notifications;

use App\Filament\Resources\DonationResource;
use App\Models\Donation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DonationCreatedNotification extends Notification
{
    public Donation $donation;

    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {

        $donationTitle = $this->donation->type;
        if ($this->donation->isRecurrente()) {
            $donationTitle .= ': '.$this->donation->frequency;
        }

        return (new MailMessage)
            ->subject('Nueva Donación ')
            ->line('Hay una nueva donación '.$donationTitle.' ('.convertPrice($this->donation->amount).').')
            ->action('Ver donacion ', DonationResource::getUrl('view', ['record' => $this->donation]));
    }

    public function toArray(): array
    {
        return [];
    }
}
