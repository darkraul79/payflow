<?php

namespace App\Services;

use App\Enums\DonationType;
use App\Enums\OrderStatus;
use App\Models\Donation;
use App\Models\Order;
use App\Support\Payment\PaymentData;
use Darkraul79\Payflow\Contracts\GatewayInterface;
use Darkraul79\Payflow\Gateways\RedsysGateway;
use Darkraul79\Payflow\Gateways\StripeGateway;

class PaymentProcess
{
    public Order|Donation $modelo;

    /** @var array Datos crudos devueltos por el gateway para inspección */
    public array $redSysAttributes = [];

    public string $payment_method;

    private array $data;

    private GatewayInterface $gateway;

    /**
     * @param  class-string<Order|Donation>  $clase
     */
    public function __construct(string $clase, array|PaymentData $data = [], ?GatewayInterface $gateway = null)
    {
        $this->modelo = new $clase;
        $this->data = $data instanceof PaymentData ? $data->toArray() : $data;
        $this->payment_method = $this->data['payment_method'] ?? 'tarjeta';

        if ($gateway instanceof GatewayInterface) {
            $this->gateway = $gateway;
        } elseif ((config('payflow.default', 'redsys') === 'redsys')) {
            $this->gateway = app(RedsysGateway::class);
        } else {
            $default = config('payflow.default', 'redsys');
            $this->gateway = match ($default) {
                'stripe' => app(StripeGateway::class),
                default => app(RedsysGateway::class),
            };
        }

        $this->createModel();
        $this->createInitialPayment();

        // Solo crear estado PENDIENTE para Donations
        // Orders lo crean automáticamente vía CreateOrderStateAfterCreateListener cuando se dispara CreateOrderEvent
        if ($this->modelo instanceof Donation) {
            $this->createState();
        }
    }

    /**
     * Crea (o recupera) la instancia de modelo según el tipo suministrado.
     */
    private function createModel(): void
    {
        if ($this->modelo instanceof Order) {
            $this->handleOrderModel();
        } elseif ($this->modelo instanceof Donation) {
            $this->handleDonationModel();
        }
    }

    private function handleOrderModel(): void
    {
        if (isset($this->data['id'])) {
            $found = Order::find($this->data['id']);
            if ($found) {
                $this->modelo = $found;

                return;
            }
        }

        $orderData = $this->buildOrderData();
        $this->modelo = Order::create($orderData);
    }

    private function buildOrderData(): array
    {
        return [
            'amount' => convertPriceNumber($this->data['amount'] ?? 0),
            'number' => generateOrderNumber(),
            'shipping' => $this->data['shipping'] ?? 'Precio fijo',
            'shipping_cost' => $this->data['shipping_cost'] ?? 0,
            'subtotal' => $this->data['subtotal'] ?? 0,
            'payment_method' => $this->payment_method,
        ];
    }

    private function handleDonationModel(): void
    {
        $donationData = $this->buildDonationData();
        $this->modelo = Donation::create($donationData);
    }

    private function buildDonationData(): array
    {
        return [
            'amount' => convertPriceNumber($this->data['amount'] ?? 0),
            'number' => generateDonationNumber(),
            'type' => $this->data['type'] ?? DonationType::UNICA->value,
            'frequency' => $this->data['frequency'] ?? null,
            'payment_method' => $this->payment_method,
        ];
    }

    /**
     * Crea pago inicial a 0 para mantener compatibilidad con tests.
     */
    private function createInitialPayment(): void
    {
        $this->modelo->payments()->create([
            'number' => generatePaymentNumber($this->modelo),
            'amount' => 0,
            'info' => [],
        ]);
    }

    /**
     * Crea el estado inicial PENDIENTE si no existe.
     */
    public function createState(): void
    {
        // Solo crear si no existe ya un estado PENDIENTE
        if (! $this->modelo->states()->where('name', OrderStatus::PENDIENTE->value)->exists()) {
            $this->modelo->states()->create([
                'name' => OrderStatus::PENDIENTE->value,
            ]);
        }
    }

    /**
     * Genera los datos del formulario Redsys usando RedsysGateway (Payflow).
     */
    public function getFormRedSysData(): array
    {
        $options = $this->buildGatewayOptions();

        $payload = $this->gateway->createPayment(
            (float) $this->modelo->amount,
            $this->modelo->number,
            $options
        );

        return $this->mapGatewayPayload($payload);
    }

    private function buildGatewayOptions(): array
    {
        $isDonation = $this->modelo instanceof Donation;
        $isRecurringDonation = $isDonation && $this->modelo->type === DonationType::RECURRENTE->value;

        $urlOk = $isDonation ? route('donation.response') : route('pedido.response');
        $urlKo = $urlOk;
        // Ajuste: usar config('app.env') explicitamente para permitir tests de producción
        $isProduction = config('app.env') === 'production';
        $urlNotification = $isProduction ? $urlOk : null;

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

        return $options;
    }

    private function mapGatewayPayload(array $payload): array
    {
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
