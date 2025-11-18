<?php

use Darkraul79\Payflow\Gateways\RedsysGateway;

beforeEach(function () {
    $this->gateway = new RedsysGateway;
});

it('can create redsys payment', function () {
    $payment = $this->gateway->createPayment(
        amount: 100.50,
        orderId: 'TEST-001',
        options: [
            'url_ok' => 'https://example.com/ok',
            'url_ko' => 'https://example.com/ko',
        ]
    );

    expect($payment)->toBeArray()
        ->and($payment['Ds_MerchantParameters'])->toBeString()
        ->and($payment['Ds_Signature'])->toBeString()
        ->and($payment['Ds_SignatureVersion'])->toBe('HMAC_SHA256_V1');
});

it('converts amount to redsys format correctly', function () {
    $payment = $this->gateway->createPayment(
        amount: 100.50,
        orderId: 'TEST-001'
    );

    $params = json_decode(base64_decode($payment['Ds_MerchantParameters']), true);

    expect($params['DS_MERCHANT_AMOUNT'])->toBe('10050');
});

it('includes bizum parameter when specified', function () {
    $payment = $this->gateway->createPayment(
        amount: 50.00,
        orderId: 'TEST-002',
        options: ['payment_method' => 'bizum']
    );

    $params = json_decode(base64_decode($payment['Ds_MerchantParameters']), true);

    expect($params)->toHaveKey('Ds_Merchant_Paymethods')
        ->and($params['Ds_Merchant_Paymethods'])->toBe('z');
});

it('includes recurring payment parameters', function () {
    $payment = $this->gateway->createPayment(
        amount: 29.99,
        orderId: 'TEST-003',
        options: [
            'recurring' => [
                'identifier' => 'REQUIRED',
                'cof_ini' => 'S',
                'cof_type' => 'R',
            ],
        ]
    );

    $params = json_decode(base64_decode($payment['Ds_MerchantParameters']), true);

    expect($params['DS_MERCHANT_IDENTIFIER'])->toBe('REQUIRED')
        ->and($params['DS_MERCHANT_COF_INI'])->toBe('S')
        ->and($params['DS_MERCHANT_COF_TYPE'])->toBe('R');
});

it('returns correct payment url for test environment', function () {
    expect($this->gateway->getPaymentUrl())->toBe('https://sis-t.redsys.es:25443/sis/realizarPago');
});

it('converts redsys amount to float correctly', function () {
    $amount = RedsysGateway::convertAmountFromRedsys('10050');

    expect($amount)->toBe(100.50);
});

it('can decode merchant parameters', function () {
    $testData = json_encode(['test' => 'value']);
    $encoded = base64_encode($testData);

    // Usamos reflection para acceder al método protegido
    $reflection = new ReflectionClass($this->gateway);
    $method = $reflection->getMethod('decodeMerchantParameters');
    $method->setAccessible(true);

    $decoded = $method->invoke($this->gateway, $encoded);

    expect($decoded)->toBeArray()
        ->and($decoded['test'])->toBe('value');
});

it('has correct gateway name', function () {
    expect($this->gateway->getName())->toBe('redsys');
});
