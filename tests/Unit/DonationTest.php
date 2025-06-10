<?php

namespace Tests\Unit;

use App\Http\Classes\PaymentProcess;
use App\Jobs\ProcessDonationPayment;
use App\Livewire\DonacionBanner;
use App\Models\Address;
use App\Models\Donation;
use App\Models\Page;
use App\Models\State;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;
use function Pest\Livewire\livewire;


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

test('puedo hacer donacion con certificado DonacionBanner', function () {

    livewire(DonacionBanner::class)
        ->set('amount', '10,35')
        ->set('type', Donation::UNICA)
        ->set('needsCertificate', true)
        ->set('certificate.name', 'Nombre')
        ->set('certificate.last_name', 'Apellido')
        ->set('certificate.last_name2', 'Apellido')
        ->set('certificate.company', 'Empresa SL')
        ->set('certificate.address', 'Calle Falsa 123')
        ->set('certificate.cp', '28001')
        ->set('certificate.city', 'Madrid')
        ->set('certificate.province', 'Madrid')
        ->set('certificate.email', 'info@raulsebastian.es')
        ->call('submit');

    expect(Donation::first()->certificate())->toBeInstanceOf(Address::class)
        ->and(Donation::first()->addresses)->toHaveCount(1);
});

test('no permite donaciones menores a 1', function () {

    livewire(DonacionBanner::class)
        ->set('amount', '0,35')
        ->call('toStep', 2)
        ->assertHasErrors([
            'amount' => 'El importe debe ser mayor o igual a 1,00 €',
        ]);

});

test('valido campos de certificado', function () {

    $r = livewire(DonacionBanner::class)
        ->set('amount', '10')
        ->call('toStep', 3)
        ->call('submit')->assertHasErrors([
            'certificate.name',
            'certificate.last_name',
            'certificate.cp',
            'certificate.email',
            'certificate.nif',
        ]);

});

test('puedo crear donación con fecha de próximo cobro en factory', function () {

    $donacion = Donation::factory()->withNextPayment('15-08-2031')->recurrente()->create();

    expect($donacion->next_payment)->toBe('2031-08-15')
        ->and($donacion->getNextPaymentFormated())->toBe('15-08-2031');
});

test('puedo actualizar la fecha de siguiente cobro según la frecuencia', function ($frecuencia, $date) {

    $donacion = Donation::factory()->recurrente()->create([
        'frequency' => $frecuencia,
    ]);

    $donacion->updateNextPaymentDate();

    expect($donacion->next_payment)->toBe($date);

})->with([
    [Donation::FREQUENCY['MENSUAL'], Carbon::now()->addMonth()->format('Y-m-d')],
    [Donation::FREQUENCY['TRIMESTRAL'], Carbon::now()->addMonths(3)->format('Y-m-d')],
    [Donation::FREQUENCY['ANUAL'], Carbon::now()->addYear()->format('Y-m-d')],
]);

test('puedo procesar job ProcessDonationPayment', function () {

    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => Donation::RECURRENTE,
        'frequency' => Donation::FREQUENCY['MENSUAL'],
    ]);
    $donacion = $paymentProcess->modelo;
    $this->get(route('donation.response', getResponseDonation($donacion, true)));

    $donacion->refresh();

    $tomorrow = Carbon::now()->addDay()->format('Y-m-d');

    $this->travel(1)->days();

    ProcessDonationPayment::dispatch($donacion);

    expect($donacion->payments)->toHaveCount(2)
        ->and($donacion->payments->last()->amount)->toBe(10.35)
        ->and($donacion->payments->last()->created_at->format('Y-m-d'))->toBe($tomorrow)
        ->and($donacion->state->name)->toBe(State::ACTIVA);

});

test('cada vez que abro ventana de donación se resetea el componente', function () {
    $home = Page::factory()->create([
        'title' => 'Home',
        'slug' => '',
        'is_home' => true,
    ]);

    $this->get(route('home', ['page' => $home->slug]))
        ->assertSeeLivewire(DonacionBanner::class);

    livewire(DonacionBanner::class)
        ->dispatch('openmodaldonation')
        ->set('type', Donation::RECURRENTE)
        ->dispatch('closemodaldonation')
        ->assertSet('type', Donation::UNICA);

})->skip();
