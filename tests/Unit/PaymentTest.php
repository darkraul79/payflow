<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Donation;
use App\Models\Payment;
use App\Support\PaymentMethodRepository;
use Darkraul79\Payflow\Gateways\RedsysGateway;
use Tests\Fakes\FakeRedsysGateway;

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

test('puedo hacer pagos a una donacion recurrente', function () {

    app()->instance(RedsysGateway::class, new FakeRedsysGateway(ok: true));

    $donacion = Donation::factory()->recurrente()->activa()->create();

    Payment::factory()->for($donacion, 'payable')->create([
        'number' => generatePaymentNumber($donacion),

    ]);

    expect($donacion->recurrentPay())->toBeInstanceOf(Payment::class)
        ->and($donacion->state->name)->toBe(OrderStatus::ACTIVA->value);
});

it('comprueba exists y find con códigos válidos e inválidos', function (): void {
    $repo = new PaymentMethodRepository;

    $first = PaymentMethod::cases()[0] ?? null;
    expect($first)->not->toBeNull()
        ->and($repo->exists($first->value))->toBeTrue()
        ->and($repo->find($first->value))->not->toBeNull();

    $invalid = '__invalid-payment-code__';
    expect($repo->exists($invalid))->toBeFalse()
        ->and($repo->find($invalid))->toBeNull();
});

it('getPaymentsMethods devuelve todo por defecto y filtra cuando includeRecurring es true', function (): void {
    $repo = new PaymentMethodRepository;

    $all = $repo->all();
    $default = $repo->getPaymentsMethods(); // includeRecurring=false => no filtra
    expect($default->count())->toBe($all->count());

    $onlyRecurring = $repo->getPaymentsMethods(true);
    $onlyRecurring->each(function ($item): void {
        expect($item->method->supportsRecurring())->toBeTrue();
    });
});

test('puedo seleccionar gateway por configuración (stripe) y por método withX()', function () {
    // Forzar stripe por defecto
    config(['payflow.default' => 'stripe']);

    // Usar PaymentProcess con gateway resuelto por manager (no inyectamos nada)
    $pp = new App\Services\PaymentProcess(App\Models\Donation::class, [
        'amount' => '10,00',
        'type' => App\Enums\DonationType::UNICA->value,
    ]);

    $data = $pp->getFormRedSysData();

    // StripeGateway::createPayment devuelve un array con 'gateway' => 'stripe'
    // y getPaymentUrl() => 'https://checkout.stripe.com'. PaymentProcess mapea form_url si viene.
    // Aquí al menos comprobamos que no falla y mapea estructura base.
    expect($data)->toHaveKeys(['Ds_MerchantParameters', 'Ds_Signature', 'Ds_SignatureVersion', 'form_url']);

    // También podemos usar el facade para forzar un gateway explícito
    $manager = app('gateway');
    $stripe = $manager->withStripe();
    expect($stripe->getName())->toBe('stripe');

    $redsys = $manager->withRedsys();
    expect($redsys->getName())->toBe('redsys');

    // Restaurar default a redsys para no afectar otros tests
    config(['payflow.default' => 'redsys']);
})->group('gateways');
