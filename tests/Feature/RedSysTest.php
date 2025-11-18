<?php

use App\Enums\OrderStatus;
use App\Events\NewDonationEvent;
use App\Models\Donation;
use App\Models\Payment;
use Illuminate\Support\Facades\Event;

test('confirmo pedido cambia a pagado después de llegar a ok', function () {

    $pedido = creaPedido();

    $this->travel(10)->seconds();

    getResponseOrder($pedido, true);

    $this->get(route('pedido.response', getResponseOrder($pedido, true)))
        ->assertRedirect(route('pedido.finalizado', $pedido->number));
    expect($pedido->state->name)->toBe(OrderStatus::PAGADO->value);

});

it('donation.response está exento de CSRF', function () {

    $donacion = Donation::factory()->withPayment()->create();

    $response = getResponseDonation($donacion, true);

    // Sin token CSRF deliberadamente
    $this->post(route('donation.response'), $response)
        ->assertRedirect(route('donacion.finalizada', $donacion->number));
});

it('donationResponse: firma inválida marca error y redirige a ko', function () {
    $donacion = Donation::factory()->withPayment()->create(); // asumiendo relación hasOne

    $params = getMerchanParamasOrder($donacion->totalRedsys, $donacion->number);

    // Firma incorrecta a propósito (cambia key o altera parámetros)
    $firmaMala = 'invalid-signature';

    $this->post(route('donation.response'), [
        'Ds_MerchantParameters' => $params,
        'Ds_Signature' => $firmaMala,
        'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
    ])->assertRedirect(route('donacion.finalizada', $donacion->number));

    $donacion->refresh();

    expect($donacion->state->name)->toBe(OrderStatus::ERROR->value) // OrderStatus::ERROR->value
        ->and($donacion->state->info['Error'])
        ->toBe('Firma no válida');
});

it('donationResponse: Ds_Response>99 actualiza a error con mensaje de Redsys', function () {
    $donacion = Donation::factory()->withPayment()->create();

    $response = getResponseDonation($donacion, false); // false = error

    $this->post(route('donation.response'), $response)
        ->assertRedirect(route('donacion.finalizada', $donacion->number));

    $donacion->refresh();

    expect($donacion->state->name)->toBe(OrderStatus::ERROR->value);
});

it('donationResponse: falta Ds_MerchantParameters devuelve 404', function () {
    $this->post(route('donation.response'), [
        'Ds_Signature' => 'x',
    ])->assertNotFound();
});

it('donationResponse: es idempotente (no duplica estados)', function () {
    $donacion = Donation::factory()->withPayment()->create();

    $response = getResponseDonation($donacion, true);

    $call = fn () => $this->post(route('donation.response'), $response);

    $call()->assertRedirect();
    $call()->assertRedirect();

    $donacion->refresh();
    // ACTIVA solo una vez
    expect($donacion->states()->count())->toBe(1);
});

it('donationResponse: emite NewDonationEvent', function () {
    Event::fake();
    $donacion = Donation::factory()->withPayment()->create();

    $response = getResponseDonation($donacion, true);

    $this->post(route('donation.response'), $response)
        ->assertRedirect();

    Event::assertDispatched(NewDonationEvent::class, function ($e) use ($donacion) {
        return $e->donation->id == $donacion->id;
    });
});

it('result: donation en estado ERROR muestra donation.ko', function () {
    $donacion = Donation::factory()->create();
    $donacion->states()->create(['name' => OrderStatus::ERROR->value]);
    Payment::factory()->create([
        'payable_type' => Donation::class,
        'payable_id' => $donacion->id,
        'number' => $donacion->number,
    ]);

    $this->get(route('donacion.finalizada', $donacion->number))
        ->assertOk()
        ->assertViewIs('donation.error');
});

it('convertPriceNumber soporta formatos locales', function () {
    expect(convertPriceNumber('1234,56'))->toBe(1234.56)
        ->and(convertPriceNumber('0,01'))->toBe(0.01)
        ->and(convertPriceNumber('10,00'))->toBe(10.0)
        ->and(convertPriceNumber('10'))->toBe(10.0);
});
