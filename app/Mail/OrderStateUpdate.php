<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\State;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStateUpdate extends Mailable
{
    use SerializesModels;

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
        return match ($this->order->state->name) {
            State::PENDIENTE => '📩 Tu pedido está pendiente de pago',
            State::PAGADO => '📦 Tu pedido está en preparación 💛',
            State::ENVIADO => '¡🚚 Tu pedido ya está en camino!',
            State::FINALIZADO => '¡Gracias por subirte a la ola solidaria! 🌊',
            State::ERROR => '⚠️ Atención: problema con tu pedido',
            State::CANCELADO => '❌ Pedido cancelado',
            default => 'Actualización del estado de tu pedido',
        };
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
        return match ($this->order->state->name) {
            State::PENDIENTE => 'emails.order-pending',
            State::PAGADO => 'emails.order-paid',
            State::ENVIADO => 'emails.order-shipped',
            State::FINALIZADO => 'emails.order-completed',
            State::ERROR => 'emails.order-error',
            State::CANCELADO => 'emails.order-cancel',
        };
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
