<?php

namespace Darkraul79\Payflow\Contracts;

interface GatewayInterface
{
    /**
     * Create a payment transaction
     */
    public function createPayment(float $amount, string $orderId, array $options = []): array;

    /**
     * Process payment callback/notification from gateway
     */
    public function processCallback(array $data): array;

    /**
     * Verify payment signature/authenticity
     */
    public function verifySignature(array $data): bool;

    /**
     * Get payment URL for redirect
     */
    public function getPaymentUrl(): string;

    /**
     * Check if payment was successful
     */
    public function isSuccessful(array $data): bool;

    /**
     * Get error message from payment response
     */
    public function getErrorMessage(array $data): string;

    /**
     * Refund a payment
     */
    public function refund(string $transactionId, float $amount): bool;

    /**
     * Get gateway name
     */
    public function getName(): string;
}
