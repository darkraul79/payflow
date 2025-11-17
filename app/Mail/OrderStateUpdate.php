<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStateUpdate extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private Order $order;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getSubject(),
        );
    }

    public function getSubject(): string
    {
        $status = $this->order->state?->status();

        return $status?->emailSubject() ?? 'Actualización del estado de tu pedido';
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: $this->getView(),
            with: [
                'name' => $this->order->getUserName(),
            ]
        );

    }

    public function getView(): string
    {
        $status = $this->order->state?->status();

        return $status?->emailView() ?? 'emails.order-pending';
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
