<?php

use App\Enums\DonationFrequency;
use App\Enums\DonationType;
use App\Enums\PaymentMethod;
use App\Models\Donation;
use App\Models\Order;
use App\Services\PaymentProcess;
use Darkraul79\Payflow\Gateways\RedsysGateway;

beforeEach(function () {
    config([
        'redsys.key' => base64_encode(random_bytes(24)),
        'redsys.merchantcode' => '999999999',
        'redsys.terminal' => '001',
        'redsys.currency' => '978',
        'redsys.transactiontype' => '0',
        'redsys.tradename' => 'Test Comercio',
        'redsys.enviroment' => 'test',
        'redsys.version' => 'HMAC_SHA256_V1',
    ]);
});

it('paymentProcess genera datos Redsys para pedido', function () {
    $pp = new PaymentProcess(Order::class, [
        'amount' => '25,50',
        'shipping' => 'Envío estándar',
        'shipping_cost' => 5.00,
        'subtotal' => 20.50,
        'payment_method' => PaymentMethod::TARJETA->value,
    ]);

    $data = $pp->getFormRedSysData();

    expect($data)->toHaveKeys(['Ds_MerchantParameters', 'Ds_Signature', 'Ds_SignatureVersion', 'Raw', 'form_url'])
        ->and($data['Ds_SignatureVersion'])->toBe('HMAC_SHA256_V1')
        ->and($data['Raw']['DS_MERCHANT_AMOUNT'])->toBe(convert_amount_to_redsys($pp->modelo->amount))
        ->and($data['Raw']['DS_MERCHANT_ORDER'])->toBe($pp->modelo->number)
        ->and($data['form_url'])->toContain('sis-t.redsys.es');
});

it('paymentProcess por bizum añade paymethods z', function () {
    $pp = new PaymentProcess(Order::class, [
        'amount' => '10,00',
        'shipping' => 'Envío',
        'shipping_cost' => 0,
        'subtotal' => 10.00,
        'payment_method' => PaymentMethod::BIZUM->value,
    ]);

    $data = $pp->getFormRedSysData();
    expect($data['Raw'])->toHaveKey('Ds_Merchant_Paymethods')
        ->and($data['Raw']['Ds_Merchant_Paymethods'])->toBe('z');
});

it('paymentProcess genera datos Redsys para donación única', function () {
    $pp = new PaymentProcess(Donation::class, [
        'amount' => '12,75',
        'type' => DonationType::UNICA->value,
        'payment_method' => PaymentMethod::TARJETA->value,
    ]);

    $data = $pp->getFormRedSysData();

    expect($data)->toHaveKeys(['Ds_MerchantParameters', 'Ds_Signature', 'Ds_SignatureVersion', 'Raw', 'form_url'])
        ->and($data['Raw'])->not->toHaveKey('DS_MERCHANT_COF_TYPE')
        ->and($data['Raw']['DS_MERCHANT_ORDER'])->toBe($pp->modelo->number);
});

it('paymentProcess genera COF inicial en donación recurrente', function () {
    $pp = new PaymentProcess(Donation::class, [
        'amount' => '9,99',
        'type' => DonationType::RECURRENTE->value,
        'frequency' => DonationFrequency::MENSUAL->value,
        'payment_method' => PaymentMethod::TARJETA->value,
    ]);

    $data = $pp->getFormRedSysData();

    expect($data['Raw'])->toHaveKeys(['DS_MERCHANT_COF_INI', 'DS_MERCHANT_COF_TYPE'])
        ->and($data['Raw']['DS_MERCHANT_COF_INI'])->toBe('S')
        ->and($data['Raw']['DS_MERCHANT_COF_TYPE'])->toBe('R')
        ->and($data['Raw'])->not->toHaveKey('DS_MERCHANT_IDENTIFIER');
});

it('RedsysGateway firma y verificación válidas', function () {
    $gateway = app(RedsysGateway::class);
    $payment = $gateway->createPayment(10.35, 'TESTORDER1', [
        'url_ok' => 'https://example.com/ok',
        'url_ko' => 'https://example.com/ko',
    ]);

    $callbackData = [
        'Ds_MerchantParameters' => $payment['Ds_MerchantParameters'],
        'Ds_Signature' => $payment['Ds_Signature'],
    ];

    $result = $gateway->processCallback($callbackData);

    expect($result['is_valid'])->toBeTrue()
        ->and($result['decoded_data'])->toHaveKey('DS_MERCHANT_ORDER') // orden presente
        ->and($gateway->isSuccessful($callbackData))->toBeFalse(); // sin Ds_Response aún
});

it('gateway incluye url de notificación cuando se proporciona', function () {
    $gateway = app(RedsysGateway::class);

    $payment = $gateway->createPayment(30.00, 'TESTORDER2', [
        'url_ok' => 'https://example.com/ok',
        'url_ko' => 'https://example.com/ko',
        'url_notification' => 'https://example.com/notification',
    ]);

    expect($payment['raw_parameters'])->toHaveKey('DS_MERCHANT_MERCHANTURL')
        ->and($payment['raw_parameters']['DS_MERCHANT_MERCHANTURL'])->toBe('https://example.com/notification');
});
