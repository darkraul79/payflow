<?php

namespace App\Services;

use App\Enums\DonationType;
use App\Enums\OrderStatus;
use App\Models\Donation;
use App\Models\Order;
use Darkraul79\Payflow\Gateways\RedsysGateway;

class PaymentProcess
{
    public Order|Donation $modelo;

    public array $redSysAttributes = [];

    public string $payment_method;

    private array $data;

    public function __construct($clase, array $data = [])
    {
        $this->modelo = new $clase;
        $this->data = $data;
        $this->payment_method = $data['payment_method'] ?? 'tarjeta';
        $this->createModel();
        $this->createPayment();
    }

    private function createModel(): void
    {
        if ($this->modelo instanceof Order && ! isset($this->data['id'])) {
            $this->modelo = Order::create([
                'amount' => convertPriceNumber($this->data['amount']),
                'number' => generateOrderNumber(),
                'shipping' => $this->data['shipping'] ?? 'Precio fijo',
                'shipping_cost' => $this->data['shipping_cost'] ?? 0,
                'subtotal' => $this->data['subtotal'] ?? 0,
                'payment_method' => $this->payment_method,
            ]);
        } elseif ($this->modelo instanceof Order && isset($this->data['id'])) {
            $this->modelo = Order::find($this->data['id']);
        } elseif ($this->modelo instanceof Donation) {
            $this->modelo = Donation::create([
                'amount' => convertPriceNumber($this->data['amount']),
                'number' => generateDonationNumber(),
                'type' => $this->data['type'],
                'frequency' => $this->data['frequency'] ?? null,
                'payment_method' => $this->payment_method,
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
            'name' => OrderStatus::PENDIENTE->value,
        ]);
    }

    /**
     * Genera los datos del formulario Redsys usando RedsysGateway (Payflow).
     * Compatibilidad mantenida: Ds_MerchantParameters, Ds_Signature, Ds_SignatureVersion, Raw, form_url.
     * Para primera donación recurrente se inicia COF (cof_ini=S, cof_type=R) sin identifier.
     */
    public function getFormRedSysData(): array
    {
        $gateway = app(RedsysGateway::class);

        $isDonation = $this->modelo instanceof Donation;
        $isRecurringDonation = $isDonation && $this->modelo->type === DonationType::RECURRENTE->value;

        $urlOk = $isDonation ? route('donation.response') : route('pedido.response');
        $urlKo = $urlOk;
        // Solo añadir URL de notificación fuera de local/testing para evitar callbacks falsos
        $urlNotification = (! app()->isLocal() && ! app()->environment('testing')) ? $urlOk : null;

        $options = [
            'url_ok' => $urlOk,
            'url_ko' => $urlKo,
            'payment_method' => $this->payment_method,
        ];
        if ($urlNotification) {
            $options['url_notification'] = $urlNotification;
        }

        if ($isRecurringDonation) {
            $options['recurring'] = [
                'cof_ini' => 'S',
                'cof_type' => 'R',
            ];
        }

        $payload = $gateway->createPayment(
            (float) $this->modelo->amount,
            $this->modelo->number,
            $options
        );

        $mapped = [
            'Ds_MerchantParameters' => $payload['Ds_MerchantParameters'] ?? '',
            'Ds_Signature' => $payload['Ds_Signature'] ?? '',
            'Ds_SignatureVersion' => $payload['Ds_SignatureVersion'] ?? 'HMAC_SHA256_V1',
            'Raw' => $payload['raw_parameters'] ?? [],
            'form_url' => $payload['form_url'] ?? null,
        ];

        $this->redSysAttributes = (array) $mapped['Raw'];

        return $mapped;
    }
}
