<?php

namespace Tests\Unit;

use App\Http\Classes\PaymentProcess;
use App\Models\Donation;
use App\Models\Order;
use App\Models\Payment;

test('ssdadf sdfsd sdf sdf ', function ($clase) {

    $modelo = 'App\\Models\\' . $clase;
    /** @noinspection PhpUndefinedMethodInspection */
    $process = new PaymentProcess($modelo, $modelo::factory()->make()->attributesToArray());

    expect($process)->toBeInstanceOf(PaymentProcess::class)
        ->and($process->modelo)->toBeInstanceOf($modelo);
})->with([
    'Donation',
    'Order',
]);

test('crea un pago al construir PaymentProcess', function ($clase) {
    $modelo = 'App\\Models\\' . $clase;
    /** @noinspection PhpUndefinedMethodInspection */
    $process = new PaymentProcess($modelo, $modelo::factory()->make()->attributesToArray());


    expect($process->modelo->payment)->toBeInstanceOf(Payment::class)
        ->and($process->modelo->payment->payable_type)->toBe($modelo)
        ->and($process->modelo->payment->payable_id)->toBe($process->modelo->id)
        ->and($process->modelo->payment->amount)->toBe(0.0);
})->with([
    'Donation',
    'Order',
]);


test('getFormRedSysData devuelve campos de RedSys correctos', function () {
    $process = new PaymentProcess(Donation::class, Donation::factory()->make()->attributesToArray());

    expect(array_keys($process->getFormRedSysData()))->toMatchArray([
        'Ds_MerchantParameters',
        'Ds_Signature',
        'Ds_SignatureVersion',
    ]);
});

test('redSysAttributes devuelve campos correctos con Donación Unica', function () {

    $process = new PaymentProcess(Donation::class, Donation::factory()->make([
        'amount' => 10.23,
    ])->attributesToArray());

    $process->getFormRedSysData();

    expect($process->redSysAttributes)->not()->toHaveKeys([
        'DS_MERCHANT_IDENTIFIER',
        'DS_MERCHANT_DIRECTPAYMENT',
    ])->and($process->redSysAttributes['DS_MERCHANT_ORDER'])->toBe($process->modelo->number)
        ->and($process->redSysAttributes['DS_MERCHANT_AMOUNT'])->toBe('1023');
});

test('redSysAttributes devuelve campos correctos con Donación Recurrente', function () {

    $process = new PaymentProcess(Donation::class, Donation::factory()->recurrente()->make([
        'amount' => 10.23,
    ])->attributesToArray());
    $process->getFormRedSysData();

    expect($process->redSysAttributes['DS_MERCHANT_IDENTIFIER'])->toBe('REQUIRED')
        ->and($process->redSysAttributes['DS_MERCHANT_ORDER'])->toBe($process->modelo->number)
        ->and($process->redSysAttributes['DS_MERCHANT_AMOUNT'])->toBe('1023');
});

test('peticion de importe coincide con la donacion/pedido', function ($clase, $recurrente) {

    if ($recurrente) {
        $data = $clase::factory()->recurrente()->make([
            'amount' => 10.23,
        ])->attributesToArray();
    } else {

        $data = $clase::factory()->make([
            'amount' => 10.23,
        ])->attributesToArray();
    }
    $process = new PaymentProcess($clase, $data);

    expect($process->getFormRedSysData()['Raw']['DS_MERCHANT_AMOUNT'])->toBe('1023');

})->with([
    [Donation::class, true],
    [Donation::class, false],
    [Order::class, false],
]);

test('compurebo devuelve formulario Redsys para donación recurrente ', function () {

    $paymentProcess = new PaymentProcess(Donation::class, Donation::factory()->recurrente()->make()->attributesToArray());
    $formData = $paymentProcess->getFormRedSysData();

    expect($formData['Raw'])->toBeArray()
        ->and($formData['Raw'])->toHaveKeys([
            'DS_MERCHANT_IDENTIFIER',
            'DS_MERCHANT_COF_INI',
            'DS_MERCHANT_COF_TYPE',
            'DS_MERCHANT_AMOUNT',
            'DS_MERCHANT_ORDER',
            'DS_MERCHANT_URLOK',
            'DS_MERCHANT_URLKO',
            'DS_MERCHANT_MERCHANTCODE',
            'DS_MERCHANT_CURRENCY',
            'DS_MERCHANT_TRANSACTIONTYPE',
            'DS_MERCHANT_MERCHANTNAME',
            'DS_MERCHANT_TERMINAL',
        ])
        ->and($formData['Raw']['DS_MERCHANT_IDENTIFIER'])->toBe('REQUIRED')
        ->and($formData['Raw']['DS_MERCHANT_COF_INI'])->toBe('S')
        ->and($formData['Raw']['DS_MERCHANT_COF_TYPE'])->toBe('R');
});

test('compurebo devuelve formulario Redsys para donación unica ', function () {

    $paymentProcess = new PaymentProcess(Donation::class, Donation::factory()->make()->attributesToArray());
    $formData = $paymentProcess->getFormRedSysData();
    expect($formData['Raw'])->toBeArray()
        ->and($formData['Raw'])->not()->toHaveKeys([
            'DS_MERCHANT_IDENTIFIER',
            'DS_MERCHANT_DIRECTPAYMENT',
            'DS_MERCHANT_COF_INI',
            'DS_MERCHANT_COF_TYPE',
        ])
        ->and($formData['Raw'])->toHaveKeys([
            'DS_MERCHANT_AMOUNT',
            'DS_MERCHANT_ORDER',
            'DS_MERCHANT_URLOK',
            'DS_MERCHANT_URLKO',
            'DS_MERCHANT_MERCHANTCODE',
            'DS_MERCHANT_CURRENCY',
            'DS_MERCHANT_TRANSACTIONTYPE',
            'DS_MERCHANT_MERCHANTNAME',
            'DS_MERCHANT_TERMINAL',
        ]);

});

test('compurebo devuelve formulario Redsys para Pedido ', function () {

    $paymentProcess = new PaymentProcess(Order::class, Order::factory()->make()->attributesToArray());
    $formData = $paymentProcess->getFormRedSysData();
    expect($formData['Raw'])->toBeArray()
        ->and($formData['Raw'])->not()->toHaveKeys([
            'DS_MERCHANT_IDENTIFIER',
            'DS_MERCHANT_DIRECTPAYMENT',
            'DS_MERCHANT_COF_INI',
            'DS_MERCHANT_COF_TYPE',
        ])
        ->and($formData['Raw'])->toHaveKeys([
            'DS_MERCHANT_AMOUNT',
            'DS_MERCHANT_ORDER',
            'DS_MERCHANT_URLOK',
            'DS_MERCHANT_URLKO',
            'DS_MERCHANT_MERCHANTCODE',
            'DS_MERCHANT_CURRENCY',
            'DS_MERCHANT_TRANSACTIONTYPE',
            'DS_MERCHANT_MERCHANTNAME',
            'DS_MERCHANT_TERMINAL',
        ]);

});
