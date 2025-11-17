<?php

namespace App\Mail;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderNew extends Mailable implements ShouldQueue
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
            subject: '¡Gracias por tu compra solidaria!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-order',
            with: [
                'number' => $this->order->number,
                'url' => OrderResource::getUrl('update', ['record' => $this->order->id]),
                'items' => $this->order->itemsArray(),
                'total' => convertPrice($this->order->amount),
                'subtotal' => convertPrice($this->order->subtotal),
                'shipping' => $this->order->shipping,
                'tax' => $this->order->taxes,
                'shipping_cost' => $this->order->getShippinCostFormated(),
            ],
        );

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
