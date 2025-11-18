<?php

namespace App\Support\Payment;

/**
 * DTO inmutable para datos de proceso de pago.
 */
final class PaymentData
{
    public function __construct(
        public readonly float|string $amount,
        public readonly ?int $id = null,
        public readonly ?string $shipping = null,
        public readonly float|int|null $shipping_cost = null,
        public readonly float|int|null $subtotal = null,
        public readonly float|int|null $taxes = null,
        public readonly ?string $payment_method = null,
        // Campos específicos donación
        public readonly ?string $type = null,
        public readonly ?string $frequency = null,
    ) {}

    /**
     * Exporta a array para compatibilidad con lógica existente.
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'id' => $this->id,
            'shipping' => $this->shipping,
            'shipping_cost' => $this->shipping_cost,
            'subtotal' => $this->subtotal,
            'taxes' => $this->taxes,
            'payment_method' => $this->payment_method,
            'type' => $this->type,
            'frequency' => $this->frequency,
        ];
    }
}
