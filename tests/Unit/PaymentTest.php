<?php

/** @noinspection PhpUndefinedMethodInspection */

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
        'number' => call_user_func_array('generate'.$modelo.'Number', []),
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
        expect($model->payment->number)->toBe($model->number.'_2');
    } else {
        expect($model->payment->number)->toBe($model->number);
    }

})->with([
    'Donation',
    'Order',
]);
