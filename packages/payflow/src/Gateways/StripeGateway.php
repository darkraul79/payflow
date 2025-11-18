<?php

namespace Darkraul79\Payflow\Gateways;

use Darkraul79\Payflow\Contracts\GatewayInterface;

class StripeGateway implements GatewayInterface
{
    protected ?string $apiKey;

    protected ?string $webhookSecret;

    public function __construct()
    {
        $this->apiKey = config('payflow.gateways.stripe.api_key');
        $this->webhookSecret = config('payflow.gateways.stripe.webhook_secret');
    }

    public function createPayment(float $amount, string $orderId, array $options = []): array
    {
        // TODO: Implement Stripe payment intent creation
        return [
            'gateway' => 'stripe',
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => $options['currency'] ?? 'eur',
        ];
    }

    public function processCallback(array $data): array
    {
        // TODO: Implement Stripe webhook processing
        return [];
    }

    public function verifySignature(array $data): bool
    {
        // TODO: Implement Stripe signature verification
        return false;
    }

    public function getPaymentUrl(): string
    {
        return 'https://checkout.stripe.com';
    }

    public function isSuccessful(array $data): bool
    {
        // TODO: Implement success check
        return false;
    }

    public function getErrorMessage(array $data): string
    {
        // TODO: Implement error message retrieval
        return 'Stripe error';
    }

    public function refund(string $transactionId, float $amount): bool
    {
        // TODO: Implement Stripe refund
        return false;
    }

    public function getName(): string
    {
        return 'stripe';
    }
}
