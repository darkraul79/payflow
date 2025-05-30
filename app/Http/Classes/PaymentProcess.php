<?php

namespace App\Http\Classes;

use App\Helpers\RedsysAPI;
use App\Models\Donation;
use App\Models\Order;
use Exception;

class PaymentProcess
{
    public Order|Donation $modelo;

    public $redSysAttributes;

    public $isNew = true;

    /**
     * @throws Exception
     */
    public function __construct(Order|Donation $modelo)
    {
        $this->modelo = $modelo;
        $this->isNew = !$this->modelo->payments->count() > 0;
        $this->createPayment();

    }

    /**
     * @throws Exception
     */
    private function createPayment(): void
    {

        $payment = $this->modelo->payments()->create([
            'number' => generatePaymentNumber($this->modelo),
            'amount' => 0,
            'info' => [],
        ]);

        if (!$payment) {
            throw new Exception('Error creating payment');
        }
    }

    public function getFormRedSysData(): array
    {
        $redsys = new RedsysAPI;
        if ($this->modelo instanceof Order || $this->modelo->type === Donation::UNICA) {
            $data = collect($redsys->getFormDirectPay($this->modelo));
        } else {
            // Recurrente
            $data = collect($redsys->getFormPagoRecurrente($this->modelo, $this->isNew));
        }
        $this->redSysAttributes = $data->only('Raw')->first();
        return $data->except('Raw')->toArray();
    }
}
