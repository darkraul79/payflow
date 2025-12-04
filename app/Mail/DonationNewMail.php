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

    private int $donationId;

    private string $donationType;

    private string $certificateName;

    private string $frequency;

    private string $formattedAmount;

    private bool $payed;

    public function __construct(Donation $donation)
    {
        // Capturamos snapshots de los datos necesarios para evitar
        // depender de relaciones que puedan cambiar mientras el mailable está en cola
        $this->donationId = $donation->id;
        $this->donationType = $donation->type;
        $this->payed = $donation->payment->amount > 0;

        // Capturamos snapshot del certificado y otros datos
        $certificate = $donation->certificate();
        $this->certificateName = ($certificate !== false && isset($certificate->name))
            ? $certificate->name
            : 'Usuario';

        $this->frequency = Str::lower($donation->frequency);
        $this->formattedAmount = convertPrice($donation->amount);
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
