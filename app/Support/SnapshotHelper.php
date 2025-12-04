<?php

namespace App\Support;

use App\Models\Donation;
use App\Models\Order;

/**
 * Helper para capturar snapshots de modelos en el momento del dispatch.
 *
 * Este helper facilita la captura consistente de datos críticos de modelos
 * que pueden cambiar antes de que la cola procese el trabajo.
 *
 * Uso en mailables/jobs:
 *
 *     // En el constructor
 *     $snapshot = SnapshotHelper::fromOrder($order);
 *     $this->orderId = $snapshot['id'];
 *     $this->stateName = $snapshot['stateName'];
 *
 * Ventajas:
 * - Captura solo datos primitivos (int, string, array)
 * - No depende de relaciones que puedan cambiar
 * - Serialización más ligera
 * - Código más legible
 */
class SnapshotHelper
{
    /**
     * Captura snapshot de un Order con su estado actual.
     *
     *
     * @return array{id: int, number: string, stateName: string|null}
     */
    public static function fromOrder(Order $order): array
    {
        $lastState = $order->states()->orderBy('id', 'desc')->first();

        return [
            'id' => $order->id,
            'number' => $order->number,
            'stateName' => $lastState?->name,
            'stateInfo' => $lastState?->info?->toArray(),
        ];
    }

    /**
     * Captura snapshot de una Donation con su estado actual.
     *
     *
     * @return array{
     *     id: int,
     *     type: string,
     *     stateName: string|null,
     *     identifier: string|null,
     *     nextPayment: string|null,
     * }
     */
    public static function fromDonation(Donation $donation): array
    {
        $lastState = $donation->states()->orderBy('id', 'desc')->first();

        return [
            'id' => $donation->id,
            'type' => $donation->type,
            'stateName' => $lastState?->name,
            'identifier' => $donation->identifier,
            'nextPayment' => $donation->next_payment,
        ];
    }

    /**
     * Captura snapshot de datos de usuario desde un Order.
     *
     *
     * @return array{id: int, name: string}
     */
    public static function orderUserSnapshot(Order $order): array
    {
        return [
            'id' => $order->id,
            'name' => $order->getUserName(),
        ];
    }

    /**
     * Captura snapshot de datos de certificado desde una Donation.
     *
     *
     * @return array{id: int, name: string, amount: string, frequency: string}
     */
    public static function donationDataSnapshot(Donation $donation): array
    {
        $certificate = $donation->certificate();

        return [
            'id' => $donation->id,
            'name' => ($certificate !== false && isset($certificate->name))
                ? $certificate->name
                : 'Usuario',
            'amount' => convertPrice($donation->amount),
            'frequency' => strtolower($donation->frequency),
            'payed' => $donation->payment->amount > 0,
        ];
    }
}
