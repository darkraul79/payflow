<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Models\Donation;
use App\Models\Payment;

test('puedo crear pagos en diferentes modelos', function ($modelo) {

    $modelClass = "App\\Models\\$modelo";
    $model = $modelClass::factory()->hasPayments()->create();
    expect($model->payment)->toBeInstanceOf(Payment::class)
        ->and($model->payment->payable_type)->toBe($modelClass)
        ->and($model->payment->payable_id)->toBe($model->id);

})->with([
    'Order',
    'Donation',
]);

test('el campo info se muestra como un objeto', function ($modelo) {

    $modelClass = "App\\Models\\$modelo";
    $model = $modelClass::factory()->hasPayments(
        Payment::factory()->make()
    )->create();

    expect($model->payment->info)->toBeInstanceOf(ArrayObject::class);
})->with([
    'Order',
    'Donation',
]);

test('payable devuelve el modelo asociado', function ($modelo) {

    $modelClass = "App\\Models\\$modelo";
    $model = $modelClass::factory()->hasPayments(
        Payment::factory()->make()
    )->create();

    expect($model->payment->payable)->toBeInstanceOf($modelClass);
})->with([
    'Order',
    'Donation',
]);

test('la funcion convertPriceFromRedsys funciona correctamente', function () {

    expect(convertPriceFromRedsys(123))
        ->toBe(1.23)
        ->and(convertPriceFromRedsys(1234))
        ->toBe(12.34)
        ->and(convertPriceFromRedsys(0))
        ->toBe(0.00);
});

test('puedo obtener el modelo a traves del pago', function ($modelo) {
    $modelClass = "App\\Models\\$modelo";
    $model = $modelClass::factory()->hasPayments()->create();

    $pago = Payment::first();
    expect($pago->payable)->toBeInstanceOf($modelClass)
        ->and($pago->payable->id)->toBe($model->id);

})->with([
    'Order',
    'Donation',
]);

test('la funcinon generación de número de Pago funciona correctamente', function ($modelo) {
    $modelClass = "App\\Models\\$modelo";
    $model = $modelClass::factory()->create([
        'number' => call_user_func_array('generate' . $modelo . 'Number', []),
    ]);

    if ($modelo === 'Donation') {
        Payment::factory()->for($model, 'payable')->create([
            'number' => generatePaymentNumber($model),
        ]);
    }

    Payment::factory()->for($model, 'payable')->create([
        'number' => generatePaymentNumber($model),
    ]);
    $model->refresh();

    if ($modelo === 'Donation') {
        expect($model->payment->number)->toBe($model->number . '_2');
    } else {
        expect($model->payment->number)->toBe($model->number);
    }

})->with([
    'Donation',
    'Order',
]);

test('puedo hacer pagos a una donacion recurrente', function () {

    $donacion = Donation::factory()->create([
        'type' => Donation::RECURRENTE,
        'info' => json_decode('{"Ds_Date":"31%2F05%2F2025","Ds_Hour":"00%3A39","Ds_SecurePayment":"1","Ds_Amount":"1000","Ds_Currency":"978","Ds_Order":"9V97ISQQ","Ds_MerchantCode":"357328590","Ds_Terminal":"001","Ds_Response":"0000","Ds_TransactionType":"0","Ds_MerchantData":"","Ds_AuthorisationCode":"031853","Ds_ExpiryDate":"4912","Ds_Merchant_Identifier":"625d3d2506fefefb9e79990f192fc3de74c08317","Ds_ConsumerLanguage":"1","Ds_Card_Country":"724","Ds_Card_Brand":"1","Ds_Merchant_Cof_Txnid":"2505310039010","Ds_ProcessedPayMethod":"78","Ds_Control_1748644741093":"1748644741093"}')
    ]);

    Payment::factory()->for($donacion, 'payable')->create([
        'number' => generatePaymentNumber($donacion),

    ]);

    $donacion->recurrentPay();
});
