<?php

use App\Enums\DonationType;
use App\Models\Donation;
use App\Services\PaymentProcess;
use Darkraul79\Payflow\Contracts\GatewayInterface;
use Darkraul79\Payflow\Gateways\RedsysGateway;
use Darkraul79\Payflow\Gateways\StripeGateway;

test('gateway manager puede registrar y resolver múltiples gateways', function () {
    $manager = app('gateway');

    // Verificar que redsys y stripe están registrados
    $registrados = $manager->getRegisteredGateways();

    expect($registrados)->toContain('redsys', 'stripe');
});

test('puedo obtener gateway Redsys usando withRedsys()', function () {
    $manager = app('gateway');
    $gateway = $manager->withRedsys();

    expect($gateway)->toBeInstanceOf(RedsysGateway::class)
        ->and($gateway->getName())->toBe('redsys');
});

test('puedo obtener gateway Stripe usando withStripe()', function () {
    $manager = app('gateway');
    $gateway = $manager->withStripe();

    expect($gateway)->toBeInstanceOf(StripeGateway::class)
        ->and($gateway->getName())->toBe('stripe');
});

test('PaymentProcess usa Redsys por defecto cuando config es redsys', function () {
    config(['payflow.default' => 'redsys']);

    $pp = new PaymentProcess(Donation::class, [
        'amount' => '15,00',
        'type' => DonationType::UNICA->value,
    ]);

    // Usar reflexión para verificar el gateway interno
    $reflection = new ReflectionClass($pp);
    $gatewayProperty = $reflection->getProperty('gateway');
    //    $gatewayProperty->setAccessible(true);
    $gateway = $gatewayProperty->getValue($pp);

    expect($gateway)->toBeInstanceOf(RedsysGateway::class);
});

test('PaymentProcess usa Stripe cuando config es stripe', function () {
    config(['payflow.default' => 'stripe']);

    $pp = new PaymentProcess(Donation::class, [
        'amount' => '20,00',
        'type' => DonationType::UNICA->value,
    ]);

    // Usar reflexión para verificar el gateway interno
    $reflection = new ReflectionClass($pp);
    $gatewayProperty = $reflection->getProperty('gateway');
    //    $gatewayProperty->setAccessible(true);
    $gateway = $gatewayProperty->getValue($pp);

    expect($gateway)->toBeInstanceOf(StripeGateway::class);

    // Restaurar config
    config(['payflow.default' => 'redsys']);
});

test('puedo inyectar gateway personalizado en PaymentProcess', function () {
    $customGateway = new StripeGateway;

    $pp = new PaymentProcess(
        Donation::class,
        [
            'amount' => '25,00',
            'type' => DonationType::UNICA->value,
        ],
        $customGateway
    );

    // Usar reflexión para verificar el gateway interno
    $reflection = new ReflectionClass($pp);
    $gatewayProperty = $reflection->getProperty('gateway');
    //    $gatewayProperty->setAccessible(true);
    $gateway = $gatewayProperty->getValue($pp);

    expect($gateway)->toBe($customGateway)
        ->and($gateway->getName())->toBe('stripe');
});

test('StripeGateway implementa correctamente GatewayInterface', function () {
    $gateway = new StripeGateway;

    expect($gateway)->toBeInstanceOf(GatewayInterface::class)
        ->and($gateway->getName())->toBe('stripe')
        ->and($gateway->getPaymentUrl())->toBe('https://checkout.stripe.com');
});

test('RedsysGateway implementa correctamente GatewayInterface', function () {
    $gateway = app(RedsysGateway::class);

    expect($gateway)->toBeInstanceOf(GatewayInterface::class)
        ->and($gateway->getName())->toBe('redsys');
});

test('manager puede cambiar gateway por defecto dinámicamente', function () {
    $manager = app('gateway');

    // Cambiar a stripe
    $manager->setDefault('stripe');
    $gateway = $manager->gateway();

    expect($gateway->getName())->toBe('stripe');

    // Cambiar de vuelta a redsys
    $manager->setDefault('redsys');
    $gateway = $manager->gateway();

    expect($gateway->getName())->toBe('redsys');
});

test('StripeGateway createPayment devuelve estructura esperada', function () {
    $gateway = new StripeGateway;

    $payment = $gateway->createPayment(50.00, 'TEST-STRIPE-001', [
        'currency' => 'eur',
    ]);

    expect($payment)->toBeArray()
        ->and($payment)->toHaveKeys(['gateway', 'order_id', 'amount', 'currency'])
        ->and($payment['gateway'])->toBe('stripe')
        ->and($payment['order_id'])->toBe('TEST-STRIPE-001')
        ->and($payment['amount'])->toBe(50.00)
        ->and($payment['currency'])->toBe('eur');
});
