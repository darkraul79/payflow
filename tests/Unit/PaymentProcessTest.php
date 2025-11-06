<?php

use App\Http\Classes\PaymentProcess;
use App\Models\Donation;
use App\Models\Order;

it('crea un pago inicial y genera datos Redsys para pedido (pago directo)', function () {
    $pp = new PaymentProcess(Order::class, [
        'amount' => convertPriceNumber('25,00'),
        'shipping' => 'Precio fijo',
        'shipping_cost' => 0,
        'subtotal' => 25,
        'taxes' => 0,
        'payment_method' => 'tarjeta',
    ]);

    $modelo = $pp->modelo;

    // Se crea un pago inicial a 0 con número generado
    expect($modelo->payments)->toHaveCount(1)
        ->and($modelo->payments->first()->amount)->toBe(0.0)
        ->and($modelo->payments->first()->number)->not->toBeEmpty();

    $data = $pp->getFormRedSysData();

    // Estructura básica del formulario
    expect($data)->toHaveKeys(['Ds_MerchantParameters', 'Ds_Signature', 'Ds_SignatureVersion', 'Raw'])
        ->and($pp->redSysAttributes)->toBeArray()
        ->and($pp->redSysAttributes['DS_MERCHANT_ORDER'] ?? null)->toBe($modelo->number);
});

it('crea un pago inicial y genera datos Redsys para donación única (pago directo)', function () {
    $pp = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => Donation::UNICA,
    ]);

    $modelo = $pp->modelo;

    expect($modelo)->toBeInstanceOf(Donation::class)
        ->and($modelo->payments)->toHaveCount(1)
        ->and($modelo->payments->first()->amount)->toBe(0.0);

    $data = $pp->getFormRedSysData();

    expect($data)->toHaveKeys(['Ds_MerchantParameters', 'Ds_Signature', 'Ds_SignatureVersion', 'Raw'])
        ->and($pp->redSysAttributes['DS_MERCHANT_ORDER'] ?? null)->toBe($modelo->number);
});

it('crea un pago inicial y genera datos Redsys para donación recurrente (alta)', function () {
    $pp = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('15,00'),
        'type' => Donation::RECURRENTE,
        'frequency' => Donation::FREQUENCY['MENSUAL'],
    ]);

    $modelo = $pp->modelo;

    expect($modelo->isRecurrente())->toBeTrue()
        ->and($modelo->payments)->toHaveCount(1)
        ->and($modelo->payments->first()->amount)->toBe(0.0);

    $data = $pp->getFormRedSysData();

    expect($data)->toHaveKeys(['Ds_MerchantParameters', 'Ds_Signature', 'Ds_SignatureVersion', 'Raw'])
        ->and($pp->redSysAttributes)->toBeArray();
});
