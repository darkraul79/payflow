<?php

namespace App\Mail;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Support\SnapshotHelper;
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

    private int $orderId;

    private string $orderNumber;

    private string $orderUrl;

    private array $items;

    private string $total;

    private string $subtotal;

    private string $shipping;

    private float $tax;

    private string $shippingCost;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        // Capturamos snapshot de los datos del pedido
        $userSnapshot = SnapshotHelper::orderUserSnapshot($order);

        $this->orderId = $userSnapshot['id'];
        $this->orderNumber = $order->number;
        $this->orderUrl = OrderResource::getUrl('update', ['record' => $order->id]);
        $this->items = $order->itemsArray();
        $this->total = convertPrice($order->amount);
        $this->subtotal = convertPrice($order->subtotal);
        $this->shipping = $order->shipping;
        $this->tax = $order->taxes;
        $this->shippingCost = $order->getShippinCostFormated();
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
                'number' => $this->orderNumber,
                'url' => $this->orderUrl,
                'items' => $this->items,
                'total' => $this->total,
                'subtotal' => $this->subtotal,
                'shipping' => $this->shipping,
                'tax' => $this->tax,
                'shipping_cost' => $this->shippingCost,
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
