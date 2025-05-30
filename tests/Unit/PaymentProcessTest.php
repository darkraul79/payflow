<?php

namespace Tests\Unit;

use App\Http\Classes\PaymentProcess;
use App\Models\Donation;
use App\Models\Order;
use App\Models\Payment;


test('puedo construir la clase PaymentProcess', function () {

    $order = Order::factory()->create();
    $process = new PaymentProcess($order);

    expect($process)->toBeInstanceOf(PaymentProcess::class)
        ->and($process->modelo)->toBeInstanceOf(Order::class);
})->with([
    'Order',
    'Donation',
]);

test('puedo crear un pago al construir PaymentProcess', function () {

    $donacion = Donation::factory()->create();
    $process = new PaymentProcess($donacion);

    expect($donacion->payment)->toBeInstanceOf(Payment::class)
        ->and($donacion->payment->payable_type)->toBe(Donation::class)
        ->and($donacion->payment->payable_id)->toBe($donacion->id);
});

test('getFormRedSysData devuelve campos de RedSys correctos', function () {
    $donacion = Donation::factory()->create();
    $process = new PaymentProcess($donacion);

    expect(array_keys($process->getFormRedSysData()))->toMatchArray([
        'Ds_MerchantParameters',
        'Ds_Signature',
        'Ds_SignatureVersion'
    ]);
});

test('redSysAttributes devuelve campos correctos con Donación Unica', function () {
    $donacion = Donation::factory()->create([]);
    $process = new PaymentProcess($donacion);
    $data = $process->getFormRedSysData();


    expect(array_keys($process->redSysAttributes))->not()->toHaveKeys([
        'DS_MERCHANT_IDENTIFIER',
        'DS_MERCHANT_DIRECTPAYMENT',
    ])->and($process->redSysAttributes['DS_MERCHANT_ORDER'])->toBe($donacion->number)
        ->and($process->redSysAttributes['DS_MERCHANT_AMOUNT'])->toBe($donacion->total_redsys);
});

test('redSysAttributes devuelve campos correctos con Donación Recurrente', function () {
    $donacion = Donation::factory()->recurrente()->create();
    $process = new PaymentProcess($donacion);
    $data = $process->getFormRedSysData();

    expect($process->redSysAttributes['DS_MERCHANT_IDENTIFIER'])->toBe('REQUIRED')
        ->and($process->redSysAttributes['DS_MERCHANT_ORDER'])->toBe($donacion->number)
        ->and($process->redSysAttributes['DS_MERCHANT_AMOUNT'])->toBe($donacion->total_redsys);
});

test('peticion de importe coincide con la donacion', function () {
    $donacion = Donation::factory()->recurrente()->create([
        'amount' => 10.23,
    ]);
    $process = new PaymentProcess($donacion);
    $data = $process->getFormRedSysData();

    expect($process->redSysAttributes['DS_MERCHANT_AMOUNT'])->toBe('1023');
});


