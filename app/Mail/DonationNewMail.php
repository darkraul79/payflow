<?php

namespace App\Mail;

use App\Enums\DonationType;
use App\Models\Donation;
use App\Support\SnapshotHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DonationNewMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private string $certificateName;

    private string $frequency;

    private string $formattedAmount;

    private bool $payed;

    private string $donationType;

    public function __construct(Donation $donation)
    {
        // Capturamos snapshot de los datos de donación
        $snapshot = SnapshotHelper::donationDataSnapshot($donation);

        $this->certificateName = $snapshot['name'];
        $this->frequency = $snapshot['frequency'];
        $this->formattedAmount = $snapshot['amount'];
        $this->payed = $snapshot['payed'];
        $this->donationType = $donation->type;
    }

    public function content(): Content
    {
        return new Content(
            markdown: $this->getView(),
            with: [
                'name' => $this->certificateName,
                'frequency' => $this->frequency,
                'amount' => $this->formattedAmount,
            ],
        );
    }

    public function getView(): string
    {
        $new = $this->payed ? 'new' : 'error';
        $type = $this->donationType === DonationType::RECURRENTE->value ? 'recurrente' : 'unica';

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
            return match ($this->donationType) {
                DonationType::RECURRENTE->value => '¡Gracias por unirte como socio/amigo! 🌊',
                default => '¡Gracias por tu donación solidaria! 💛',
            };
        }

        return match ($this->donationType) {
            DonationType::RECURRENTE->value => 'Problema con tu alta como socio/amigo',
            default => 'Problema con tu donación',
        };
    }

    public function attachments(): array
    {
        return [];
    }
}
