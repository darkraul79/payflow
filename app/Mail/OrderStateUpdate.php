<?php

namespace App\Mail;

use App\Enums\OrderStatus;
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

    private ?int $orderId;

    private string $userName;

    private ?string $stateName;

    private ?array $stateInfo;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, ?string $stateName = null, ?array $stateInfo = null)
    {
        $this->order = $order;

        // Guardo también un snapshot ligero del nombre de usuario para usar en la vista
        $this->orderId = $order->id;
        $this->userName = $order->getUserName();

        $this->stateName = $stateName;
        $this->stateInfo = $stateInfo;

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
        $status = null;

        if (! is_null($this->stateName)) {
            $status = OrderStatus::tryFrom($this->stateName);
        } elseif (isset($this->order->state)) {
            $status = $this->order->state?->status();
        }

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
                'name' => $this->userName,
            ]
        );

    }

    public function getView(): string
    {
        $status = null;

        if (! is_null($this->stateName)) {
            $status = OrderStatus::tryFrom($this->stateName);
        } elseif (isset($this->order->state)) {
            $status = $this->order->state?->status();
        }

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
