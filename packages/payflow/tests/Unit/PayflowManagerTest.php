<?php

use Darkraul79\Payflow\Facades\Gateway;

it('can get redsys gateway instance', function () {
    $gateway = Gateway::withRedsys();

    expect($gateway)->toBeGateway()
        ->and($gateway->getName())->toBe('redsys');
});

it('can create payment with redsys', function () {
    $payment = Gateway::withRedsys()->createPayment(
        amount: 100.50,
        orderId: 'TEST-123',
        options: [
            'url_ok' => 'https://example.com/ok',
            'url_ko' => 'https://example.com/ko',
        ]
    );

    expect($payment)->toBeArray()
        ->and($payment)->toHaveKeys(['Ds_MerchantParameters', 'Ds_Signature', 'Ds_SignatureVersion', 'form_url'])
        ->and($payment['Ds_MerchantParameters'])->toBeString()
        ->and($payment['Ds_Signature'])->toBeString()
        ->and($payment['form_url'])->toContain('redsys.es');
});

it('creates correct payment url for test environment', function () {
    $gateway = Gateway::withRedsys();

    expect($gateway->getPaymentUrl())->toContain('sis-t.redsys.es');
});

it('can extend with custom gateway', function () {
    Gateway::extend('custom', fn () => new class implements Darkraul79\Payflow\Contracts\GatewayInterface
    {
        public function createPayment(float $amount, string $orderId, array $options = []): array
        {
            return ['custom' => true];
        }

        public function processCallback(array $data): array
        {
            return [];
        }

        public function verifySignature(array $data): bool
        {
            return true;
        }

        public function getPaymentUrl(): string
        {
            return 'https://custom.gateway.com';
        }

        public function isSuccessful(array $data): bool
        {
            return true;
        }

        public function getErrorMessage(array $data): string
        {
            return '';
        }

        public function refund(string $transactionId, float $amount): bool
        {
            return true;
        }

        public function getName(): string
        {
            return 'custom';
        }
    });

    $payment = Gateway::gateway('custom')->createPayment(100, 'TEST');

    expect($payment)->toHaveKey('custom')
        ->and($payment['custom'])->toBeTrue();
});

it('uses default gateway when none specified', function () {
    $gateway = Gateway::gateway();

    expect($gateway->getName())->toBe('redsys');
});

it('can register multiple gateways', function () {
    $redsys = Gateway::withRedsys();
    $stripe = Gateway::withStripe();

    expect($redsys->getName())->toBe('redsys')
        ->and($stripe->getName())->toBe('stripe');
});

it('throws exception for non-existent gateway', function () {
    Gateway::gateway('nonexistent');
})->throws(InvalidArgumentException::class);
