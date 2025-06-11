<?php

namespace App\Mail;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DonationNewMail extends Mailable
{
    use Queueable, SerializesModels;

    public Donation $donation;

    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getSubject(),
        );
    }

    public function getSubject(): string
    {
        return match ($this->donation->payment->amount > 0) {
            true => '¡Gracias por unirte como socio/amigo! 🌊',
            false => 'Problema con tu alta como socio/amigo',
            default => 'Problema con tu alta como socio/amigo',
        };
    }


    public function content(): Content
    {
        return new Content(
            markdown: $this->getView(),
            with: [
                'name' => $this->donation->certificate->name,
            ],
        );
    }

    public function getView(): string
    {
        return match ($this->donation->payment->amount > 0) {

            true => 'emails.donation-new',
            false => 'emails.donation-error',
            default => 'emails.donation-error',
        };
    }

    public function attachments(): array
    {
        return [];
    }
}
