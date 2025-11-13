<?php

namespace App\Notifications;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreated extends Notification
{
    use Queueable;

    public Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(): MailMessage
    {
        $items = collect($this->order->itemsArray());
        $itemsPreview = $items->map(function (array $item): string {
            return $item['name'].' × '.$item['quantity'];
        })->take(5);

        $mail = (new MailMessage)
            ->subject('Nuevo Pedido '.$this->order->number)
            ->greeting('Hay un nuevo pedido.');

        if ($itemsPreview->isNotEmpty()) {
            $mail->line('_Resumen de artículos:_');
            $mail->line($itemsPreview->implode(', '));
        }
        $mail->line('## Importe total: **'.convertPrice($this->order->amount).'**');

        return $mail->action('Ver pedido '.$this->order->number,
            OrderResource::getUrl('update', ['record' => $this->order->id]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            //
        ];
    }
}
