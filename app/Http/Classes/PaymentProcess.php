<?php

namespace App\Http\Classes;

use App\Helpers\RedsysAPI;
use App\Models\Donation;
use App\Models\Order;
use App\Models\State;

class PaymentProcess
{
    public Order|Donation $modelo;

    public array $redSysAttributes;

    /**
     * @var mixed|string
     */
    public string $payment_method;

    /**
     * @var int|mixed
     */
    private array $data;

    public function __construct($clase, array $data = [])
    {

        $this->modelo = new $clase;
        $this->data = $data;
        $this->payment_method = $data['payment_method'] ?? 'tarjeta';
        $this->createModel();
        $this->createPayment();
        //        $this->createState();

    }

    private function createModel(): void
    {
        if ($this->modelo instanceof Order && ! isset($this->data['id'])) {
            $this->modelo = Order::create([
                'amount' => convertPriceNumber($this->data['amount']),
                'number' => generateOrderNumber(),
                'shipping' => $this->data['shipping'] ?? 'Precio fijo',
                'shipping_cost' => $this->data['shipping_cost'],
                'subtotal' => $this->data['subtotal'],
                'payment_method' => $this->data['payment_method'],
            ]);
        } elseif ($this->modelo instanceof Order && isset($this->data['id'])) {
            $this->modelo = Order::find($this->data['id']);

        } elseif ($this->modelo instanceof Donation) {
            $this->modelo = Donation::create([
                'amount' => convertPriceNumber($this->data['amount']),
                'number' => generateDonationNumber(),
                'type' => $this->data['type'],
                'frequency' => $this->data['frequency'] ?? null,
                'payment_method' => $this->data['payment_method'] ?? 'tarjeta',

            ]);
        }

    }

    private function createPayment(): void
    {

        $this->modelo->payments()->create([
            'number' => generatePaymentNumber($this->modelo),
            'amount' => 0,
            'info' => [],
        ]);

    }

    public function createState(): void
    {
        $this->modelo->states()->create([
            'name' => State::PENDIENTE,
        ]);
    }

    public function getFormRedSysData(): array
    {
        $redsys = new RedsysAPI;
        $data = collect();

        if ($this->modelo instanceof Order || ($this->modelo instanceof Donation && $this->modelo->type === Donation::UNICA)) {
            $data = collect($redsys->getFormDirectPay($this->modelo, $this->payment_method));
        } elseif ($this->modelo instanceof Donation && $this->modelo->type === Donation::RECURRENTE) {
            // Recurrente
            $data = collect($redsys->getFormNewPagoRecurrente($this->modelo));
        }
        $this->redSysAttributes = (array) $data->only('Raw')->first();

        return $data->toArray();
    }
}
