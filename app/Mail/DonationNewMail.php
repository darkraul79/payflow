<?php

namespace App\Mail;

use App\Enums\DonationType;
use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class DonationNewMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Donation $donation;

    public bool $payed = false;

    public function __construct(Donation $donation)
    {

        $this->donation = $donation;
        $this->payed = $donation->payment->amount > 0;
    }

    public function content(): Content
    {
        return new Content(
            markdown: $this->getView(),
            with: [
                'name' => $this->donation->certificate()->name,
                'frequency' => Str::lower($this->donation->frequency),
                'amount' => convertPrice($this->donation->amount),
            ],
        );
    }

    public function getView(): string
    {
        $new = $this->payed ? 'new' : 'error';
        $type = $this->donation->type === DonationType::RECURRENTE->value ? 'recurrente' : 'unica';

        return 'emails.donation-'.$new.'-'.$type;

    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getSubject(),
        );
    }

    public function getSubject(): string
    {
        if ($this->payed) {
            return match ($this->donation->type) {
                DonationType::RECURRENTE->value => '¡Gracias por unirte como socio/amigo! 🌊',
                default => '¡Gracias por tu donación solidaria! 💛',
            };
        }

        return match ($this->donation->type) {
            DonationType::RECURRENTE->value => 'Problema con tu alta como socio/amigo',
            default => 'Problema con tu donación',
        };
    }

    public function attachments(): array
    {
        return [];
    }
}
