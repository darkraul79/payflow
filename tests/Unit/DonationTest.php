<?php

namespace Tests\Unit;

use App\Http\Classes\PaymentProcess;
use App\Models\Address;
use App\Models\Donation;
use App\Models\State;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('puedo crear donación única por defecto en factory', function () {

    $donation = Donation::factory()->create();
    expect($donation->info)->toBeObject()
        ->and($donation->type)->toBe(Donation::UNICA)
        ->and($donation->identifier)->toBeNull();
});

test('puedo crear donación recurrente por defecto en factory', function () {

    $donation = Donation::factory()->recurrente()->create();
    expect($donation->info)->toBeObject()
        ->and($donation->type)->toBe(Donation::RECURRENTE)
        ->and($donation->identifier)->not->toBeNull();
});

test('puedo crear donación única con muchos pagos en factory', function () {

    $donation = Donation::factory()->hasPayments(3)->create();
    expect($donation->info)->toBeObject()
        ->and($donation->type)->toBe(Donation::UNICA)
        ->and($donation->identifier)->toBeNull()
        ->and($donation->payments)->toHaveCount(3);
});

test('puedo crear donación recurrente con muchos pagos en factory', function () {

    $donation = Donation::factory()->hasPayments(3)->recurrente()->create();
    expect($donation->info)->toBeObject()
        ->and($donation->type)->toBe(Donation::RECURRENTE)
        ->and($donation->identifier)->not->toBeNull()
        ->and($donation->payments)->toHaveCount(3);
});

test('puedo asociar dirección de certificado a donación en factory', function () {
    $donation = Donation::factory()->withCertificado()->create();

    expect($donation->addresses->first())->toBeInstanceOf(Address::class)
        ->and($donation->addresses)->toHaveCount(1)
        ->and($donation->addresses->first()->type)->toBe(Address::CERTIFICATE)
        ->and($donation->address)->toBeInstanceOf(Address::class)
        ->and($donation->address->type)->toBe(Address::CERTIFICATE);
});

test('estados donacion', function () {
    $donation = Donation::factory()->withCertificado()->create();

    $estados = $donation->available_states();
    expect($estados)->toBeArray()
        ->and($estados)->not()->toHaveKeys([
            'ENVIADO',
            'FINALIZADO',

        ])->and($estados)->toHaveKeys([
            'PAGADO',
            'ERROR',
            'CANCELADO',
            'ACTIVA',
        ]);
});

test('puedo obtner todas las frecuencias de pago', function () {

    expect(Donation::FREQUENCY)->toBeArray()
        ->and(Donation::FREQUENCY)->toHaveKeys(['MENSUAL', 'TRIMESTRAL', 'ANUAL'])
        ->and(Donation::FREQUENCY['MENSUAL'])->toBe('Mensual')
        ->and(Donation::FREQUENCY['TRIMESTRAL'])->toBe('Trimestral')
        ->and(Donation::FREQUENCY['ANUAL'])->toBe('Anual');
});

test('puedo crear donacion recurrente', function () {

    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => Donation::RECURRENTE,
        'frequency' => Donation::FREQUENCY['MENSUAL'],
    ]);
    $donacion = $paymentProcess->modelo;

    $this->get(route('donation.response', getResponseDonation($donacion, true)))
        ->assertRedirect(route('donacion.finalizada', [
            'donacion' => $donacion->number,
        ]));
    $this->get(route('donacion.finalizada', [
        'donacion' => $donacion->number,
    ]))->assertSee('Gracias');
    $donacion->refresh();

    expect($donacion->state->name)->toBe(State::ACTIVA)
        ->and($donacion->payments->first()->amount)->toBe(10.35);

});

test('puedo crear donacion unica', function () {

    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => Donation::UNICA,
        'frequency' => null,
    ]);
    $donacion = $paymentProcess->modelo;

    $this->get(route('donation.response', getResponseDonation($donacion, true)))
        ->assertRedirect(route('donacion.finalizada', [
            'donacion' => $donacion->number,
        ]));
    $this->get(route('donacion.finalizada', [
        'donacion' => $donacion->number,
    ]))->assertSee('Gracias');
    $donacion->refresh();

    expect($donacion->state->name)->toBe(State::PAGADO)
        ->and($donacion->payments->first()->amount)->toBe(10.35);

});

test('puedo crear pago a donacion recurrente', function () {

    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => Donation::RECURRENTE,
        'frequency' => Donation::FREQUENCY['MENSUAL'],
    ]);
    $donacion = $paymentProcess->modelo;

    $this->get(route('donation.response', getResponseDonation($donacion, true)));
    $donacion->refresh();


    $pagoRecurrente = $donacion->recurrentPay();

    expect($donacion->state->name)->toBe(State::ACTIVA)
        ->and($pagoRecurrente->amount)->toBe(10.35)
        ->and($pagoRecurrente->info->Ds_Response)->toBe('0000');

});

test('NO puedo crear pago a donacion cancelada', function () {

    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => Donation::RECURRENTE,
        'frequency' => Donation::FREQUENCY['MENSUAL'],
    ]);
    $donacion = $paymentProcess->modelo;

    $this->get(route('donation.response', getResponseDonation($donacion, true)));
    $donacion->refresh();

    $donacion->cancel();

    expect(fn() => $donacion->recurrentPay())->toThrow(
        HttpException::class,
        'La donación ya NO está activa y no se puede volver a pagar'
    );

    expect($donacion->state->name)->toBe(State::CANCELADO);

});

test('puedo crear pago a KO donacion recurrente', function () {

    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => Donation::RECURRENTE,
        'frequency' => Donation::FREQUENCY['MENSUAL'],
    ]);
    $donacion = $paymentProcess->modelo;

    $this->get(route('donation.response', getResponseDonation($donacion, true)));
    $donacion->refresh();

    $pagoRecurrente = $donacion->recurrentPay();

    expect($donacion->state->name)->toBe(State::ACTIVA)
        ->and($pagoRecurrente->amount)->toBe(10.35)
        ->and($pagoRecurrente->info->Ds_Response)->toBe('0000');

});


test('puedo comprobar si tiene certificado', function () {
    $donacion = Donation::factory()->create();
    expect($donacion->certificate())->toBeFalse();
});
