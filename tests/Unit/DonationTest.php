<?php

namespace Tests\Unit;

use App\Enums\AddressType;
use App\Enums\DonationFrequency;
use App\Enums\DonationType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Jobs\ProcessDonationPaymentJob;
use App\Livewire\DonacionBanner;
use App\Models\Address;
use App\Models\Donation;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Notifications\DonationCreatedNotification;
use App\Services\PaymentProcess;
use Carbon\Carbon;
use Darkraul79\Payflow\Gateways\RedsysGateway;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Fakes\FakeRedsysGateway;

use function Pest\Livewire\livewire;

test('puedo crear donación única por defecto en factory', function () {

    $donation = Donation::factory()->create();
    expect($donation->info)->toBeObject()
        ->and($donation->type)->toBe(DonationType::UNICA->value)
        ->and($donation->identifier)->toBeNull();
});

test('puedo crear donación recurrente por defecto en factory', function () {

    $donation = Donation::factory()->recurrente()->create();
    expect($donation->info)->toBeObject()
        ->and($donation->type)->toBe(DonationType::RECURRENTE->value)
        ->and($donation->identifier)->not->toBeNull();
});

test('puedo crear donación única con muchos pagos en factory', function () {

    $donation = Donation::factory()->hasPayments(3)->create();
    expect($donation->info)->toBeObject()
        ->and($donation->type)->toBe(DonationType::UNICA->value)
        ->and($donation->identifier)->toBeNull()
        ->and($donation->payments)->toHaveCount(3);
});

test('puedo crear donación recurrente con muchos pagos en factory', function () {

    $donation = Donation::factory()->hasPayments(3)->recurrente()->create();
    expect($donation->info)->toBeObject()
        ->and($donation->type)->toBe(DonationType::RECURRENTE->value)
        ->and($donation->identifier)->not->toBeNull()
        ->and($donation->payments)->toHaveCount(3);
});

test('puedo asociar dirección de certificado a donación en factory', function () {
    $donation = Donation::factory()->withCertificado()->create();

    expect($donation->addresses->first())->toBeInstanceOf(Address::class)
        ->and($donation->addresses)->toHaveCount(1)
        ->and($donation->addresses->first()->type)->toBe(AddressType::CERTIFICATE->value)
        ->and($donation->address)->toBeInstanceOf(Address::class)
        ->and($donation->address->type)->toBe(AddressType::CERTIFICATE->value);
});

test('estados donacion', function () {
    $donation = Donation::factory()->withCertificado()->create();

    $estados = $donation->available_states();

    expect($estados)->toBeArray()
        ->and($estados)->not()->toContain(
            OrderStatus::ENVIADO->value,
            OrderStatus::FINALIZADO->value
        )
        ->and($estados)->toContain(
            OrderStatus::PAGADO->value,
            OrderStatus::ERROR->value,
            OrderStatus::CANCELADO->value,
            OrderStatus::ACTIVA->value,
        );
});

test('puedo obtner todas las frecuencias de pago', function () {
    $frequencies = DonationFrequency::toArray();

    expect($frequencies)->toBeArray()
        ->and($frequencies)->toHaveKeys(['MENSUAL', 'TRIMESTRAL', 'ANUAL'])
        ->and($frequencies['MENSUAL'])->toBe('Mensual')
        ->and($frequencies['TRIMESTRAL'])->toBe('Trimestral')
        ->and($frequencies['ANUAL'])->toBe('Anual');
});

test('puedo crear donacion recurrente', function () {

    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => DonationType::RECURRENTE->value,
        'frequency' => DonationFrequency::MENSUAL->value,
    ]);
    $donacion = $paymentProcess->modelo;

    $this->post(route('donation.response', getResponseDonation($donacion, true)))
        ->assertRedirect(route('donacion.finalizada', [
            'donacion' => $donacion->number,
        ]));

    $this->get(route('donacion.finalizada', [
        'donacion' => $donacion->number,
    ]))->assertSee('Gracias');
    $donacion->refresh();

    expect($donacion->state->name)->toBe(OrderStatus::ACTIVA->value)
        ->and($donacion->payments->first()->amount)->toBe(10.35);

});

test('puedo crear donacion unica', function () {

    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => DonationType::UNICA->value,
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

    expect($donacion->state->name)->toBe(OrderStatus::PAGADO->value)
        ->and($donacion->payments->first()->amount)->toBe(10.35);

});

test('NO puedo crear pago a donacion cancelada', function () {

    app()->instance(RedsysGateway::class, new FakeRedsysGateway(ok: true));

    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => DonationType::RECURRENTE->value,
        'frequency' => DonationFrequency::MENSUAL->value,
    ]);
    $donacion = $paymentProcess->modelo;

    $this->get(route('donation.response', getResponseDonation($donacion, true)));
    $donacion->refresh();

    // Verificar que está en estado ACTIVA antes de cancelar
    expect($donacion->state->name)->toBe(OrderStatus::ACTIVA->value);

    $donacion->cancel();
    $donacion->refresh();

    // Verificar que está en estado CANCELADO después de cancelar
    expect($donacion->state->name)->toBe(OrderStatus::CANCELADO->value)
        ->and(fn () => $donacion->recurrentPay())->toThrow(
            HttpException::class,
            'La donación ya NO está activa y no se puede volver a pagar'
        );

    // Ahora intentar el pago recurrente debe fallar

});

test('puedo crear pago a KO donacion recurrente', function () {

    app()->instance(RedsysGateway::class, new FakeRedsysGateway(ok: true));
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => DonationType::RECURRENTE->value,
        'frequency' => DonationFrequency::MENSUAL->value,
    ]);
    $donacion = $paymentProcess->modelo;

    $this->get(route('donation.response', getResponseDonation($donacion, true)));
    $donacion->refresh();

    $pagoRecurrente = $donacion->recurrentPay();

    expect($donacion->state->name)->toBe(OrderStatus::ACTIVA->value)
        ->and($pagoRecurrente->amount)->toBe(10.35)
        ->and($pagoRecurrente->info->Ds_Response)->toBe('0000');

});

test('puedo comprobar si tiene certificado', function () {
    $donacion = Donation::factory()->create();
    expect($donacion->certificate())->toBeFalse();
});

test('puedo hacer donacion con certificado DonacionBanner', function () {

    $prefix = 'donacion';
    livewire(DonacionBanner::class, ['prefix' => $prefix])
        ->set('amount', 10.35)
        ->set('type', DonationType::UNICA->value)
        ->set('needsCertificate', true)
        ->set('certificate.name', 'Nombre')
        ->set('certificate.last_name', 'Apellido')
        ->set('certificate.nif', '1234567489W')
        ->set('certificate.last_name2', 'Apellido')
        ->set('certificate.company', 'Empresa SL')
        ->set('certificate.address', 'Calle Falsa 123')
        ->set('certificate.cp', '28001')
        ->set('certificate.province', 'Madrid')
        ->set('certificate.email', 'info@raulsebastian.es')
        ->call('submit');

    expect(Donation::first()->certificate())->toBeInstanceOf(Address::class)
        ->and(Donation::first()->addresses)->toHaveCount(1)
        ->and(Donation::first()->certificate()->nif)->toBe('1234567489W');
});

test('no permite donaciones menores a 1', function () {

    livewire(DonacionBanner::class, ['prefix' => 'donacion'])
        ->set('amount', 0.35)
        ->call('toStep', 2)
        ->assertHasErrors([
            'amount' => 'El importe debe ser mayor o igual a 1,00 €',
        ]);

});

test('valido campos de certificado', function () {

    livewire(DonacionBanner::class, ['prefix' => 'donacion'])
        ->set('type', DonationType::UNICA->value)
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
        ->and($donacion->getNextPayDateFormated())->toBe('15-08-2031');
});

test('si no existe la fecha de proximo cobro devuelve No definido', function () {

    $donacion = Donation::factory()->withNextPayment('15-08-2031')->recurrente()->create();
    $donacion->cancel();

    expect($donacion->next_payment)->toBeNull()
        ->and($donacion->getNextPayDateFormated())->toBe('No definido');
});

test('puedo actualizar la fecha de siguiente cobro según la frecuencia', function ($frecuencia, $date) {

    $this->travelTo('2025-06-11');
    $donacion = Donation::factory()->recurrente()->create([
        'frequency' => $frecuencia,
    ]);

    $donacion->updateNextPaymentDate();

    expect($donacion->next_payment)->toBe($date);
})->with([
    [DonationFrequency::MENSUAL->value, '2025-07-05'],
    [DonationFrequency::TRIMESTRAL->value, '2025-07-05'],
    [DonationFrequency::ANUAL->value, '2026-06-05'],
]);

// KO en donación recurrente: marca ERROR y reprograma siguiente cobro al día 5 del mes siguiente
it('processPay KO en donación recurrente marca ERROR y reprograma next_payment', function () {
    $this->travelTo('2025-06-11');

    // Donación recurrente mensual
    $donacion = Donation::factory()->recurrente()->create([
        'frequency' => DonationFrequency::MENSUAL->value,
    ]);

    // Situamos la donación en estado ACTIVA (como tras una alta correcta)
    $donacion->states()->create(['name' => OrderStatus::ACTIVA->value]);

    // Simulamos respuesta Redsys KO del cobro recurrente
    $info = [
        'Ds_Response' => '9928',
        'Ds_Order' => $donacion->number,
    ];

    $donacion->error_pago($info, 'Error RedSys - 9928');

    $donacion->refresh();

    expect($donacion->state->name)->toBe(OrderStatus::ERROR->value)
        // La lógica actual reprograma el siguiente cobro también en KO
        ->and($donacion->next_payment)->toBe('2025-07-05');

})->with([
    [DonationFrequency::MENSUAL->value, '2025-07-05'],
    [DonationFrequency::TRIMESTRAL->value, '2025-07-05'],
    [DonationFrequency::ANUAL->value, '2026-06-05'],
]);

test('puedo procesar job ProcessDonationPaymentJob', function () {

    app()->instance(RedsysGateway::class, new FakeRedsysGateway(ok: true));
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => DonationType::RECURRENTE->value,
        'frequency' => DonationFrequency::MENSUAL->value,
    ]);
    $donacion = $paymentProcess->modelo;
    $this->get(route('donation.response', getResponseDonation($donacion, true)));

    $donacion->refresh();

    // Verificar que tiene 1 pago inicial
    expect($donacion->payments)->toHaveCount(1)
        ->and($donacion->identifier)->not->toBeNull();

    // Viajar en el tiempo
    $this->travel(1)->days();
    $expectedDate = Carbon::now()->format('Y-m-d');

    // Actualizar next_payment para que sea hoy (para que el job lo procese)
    $donacion->update(['next_payment' => $expectedDate]);
    $donacion->refresh();

    // Ejecutar el job sincrónicamente
    $job = new ProcessDonationPaymentJob($donacion);
    $job->handle();

    // Refrescar la donación después de ejecutar el job
    $donacion->refresh();

    expect($donacion->payments)->toHaveCount(2) // 1 pago inicial + 1 nuevo pago recurrente
        ->and($donacion->payments->last()->amount)->toBe(10.35)
        ->and($donacion->payments->last()->created_at->format('Y-m-d'))->toBe($expectedDate)
        ->and($donacion->state->name)->toBe(OrderStatus::ACTIVA->value);

});

test('actualizo correctamente la fecha de próximo cobro', function ($tipo) {

    $this->travelTo('2025-06-11');
    $donacion = Donation::factory()->recurrente()->create([
        'frequency' => $tipo,
    ]);
    $donacion->updateNextPaymentDate();

    $fechas = [
        DonationFrequency::MENSUAL->value => '2025-07-05',
        DonationFrequency::TRIMESTRAL->value => '2025-07-05',
        DonationFrequency::ANUAL->value => '2026-06-05',
    ];

    expect($donacion->next_payment)->toBe($fechas[$tipo]);

})
    ->with([
        DonationFrequency::MENSUAL->value,
        DonationFrequency::TRIMESTRAL->value,
        DonationFrequency::ANUAL->value,
    ]);

test('obtengo los jobs correctamente los pagos del mes', function () {
    Queue::fake();

    $this->travelTo('2025-06-11');

    $donacion = Donation::factory()->recurrente()->activa()->create([
        'frequency' => DonationFrequency::MENSUAL->value,
    ]);
    $donacion->updateNextPaymentDate();

    $donacionCancelada = Donation::factory()->recurrente()->activa()->create([
        'frequency' => DonationFrequency::MENSUAL->value,
    ]);
    $donacionCancelada->updateNextPaymentDate();
    $donacionCancelada->states()->create([
        'name' => OrderStatus::CANCELADO->value,
    ]);

    Queue::assertNothingPushed();

    $this->travelTo('2025-07-05');
    $this->artisan('payments-of-month:process')->assertSuccessful();

    Queue::assertPushed(function (ProcessDonationPaymentJob $job) use ($donacion) {
        return $job->donation->id === $donacion->id;
    });

    //    $d = $this->artisan('queue:work');

    Queue::assertPushed(ProcessDonationPaymentJob::class, 1);

});
test('obtengo correctamente las donaciones con pagos', function ($tipo) {
    $donacion = Donation::factory()->recurrente()->activa()->create([
        'frequency' => $tipo,
    ]);
    $donacion->updateNextPaymentDate();

    $donacionCancelada = Donation::factory()->recurrente()->create([
        'frequency' => $tipo,
    ]);
    $donacionCancelada->updateNextPaymentDate();
    $donacionCancelada->states()->create([
        'name' => OrderStatus::CANCELADO->value,
    ]);

    $fecha = match ($tipo) {
        DonationFrequency::MENSUAL->value => Carbon::now()->addMonth()->day(5),
        DonationFrequency::TRIMESTRAL->value => Carbon::now()
            ->addMonths(3 - (Carbon::now()->month - 1) % 3)
            ->startOfMonth()
            ->addMonths(2)
            ->day(5),
        DonationFrequency::ANUAL->value => Carbon::now()->addYear()->day(5),
        default => null,
    };

    $this->travelTo($fecha);

    $donacionesPendientesCobro = Donation::query()->nextPaymentsDonations();
    expect($donacionesPendientesCobro->count())->toBe(1)
        ->and($donacionesPendientesCobro->first()->id)->not()->toBe($donacionCancelada->id);
})
    ->with([
        DonationFrequency::MENSUAL->value,
        DonationFrequency::TRIMESTRAL->value,
        DonationFrequency::ANUAL->value,
    ]);

test('cuando realizo donación actualiza correctamente la fecha de proximo cobro', function () {
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => DonationType::RECURRENTE->value,
        'frequency' => DonationFrequency::MENSUAL->value,
    ]);
    $donacion = $paymentProcess->modelo;
    $this->get(route('donation.response', getResponseDonation($donacion, true)));

    $donacion->refresh();

    expect($donacion->next_payment)->toBe(now()->addMonth()->day(5)->format('Y-m-d'));
});

test('compruebo que donacion recurrente anual no se repiten los cobros', function () {
    $donacion = Donation::factory()->recurrente()->activa()->create([
        'frequency' => DonationFrequency::ANUAL->value,
    ]);
    $donacion->updateNextPaymentDate();

    for ($i = 1; $i < 11; $i++) {
        $this->travelTo(now()->addMonth()->day(5));
        $donacionesPendientesCobro = Donation::query()->nextPaymentsDonations();
        expect($donacionesPendientesCobro->count())->toBe(0);

    }

    $this->travelTo(now()->addYear()->day(5));

    $donacionesPendientesCobro = Donation::query()->nextPaymentsDonations();
    expect($donacionesPendientesCobro->count())->toBe(1);
});

test('compruebo que donacion recurrente mensual no se repiten los cobros', function () {

    $this->travelTo('2025-07-05');

    $donacion = Donation::factory()->recurrente()->activa()->create([
        'frequency' => DonationFrequency::MENSUAL->value,
    ]);
    $donacion->updateNextPaymentDate();

    for ($i = 1; $i < (31 - 5); $i++) {
        $this->travelTo(now()->addDay());
        $donacionesPendientesCobro = Donation::query()->nextPaymentsDonations();
        expect($donacionesPendientesCobro->count())->toBe(0);

    }

    $this->travelTo(now()->addMonth()->day(5));

    $donacionesPendientesCobro = Donation::query()->nextPaymentsDonations();
    expect($donacionesPendientesCobro->count())->toBe(1);
});

test('compruebo que donacion recurrente trimestral no se repiten los cobros', function () {

    $this->travelTo('2025-01-01');

    $donacion = Donation::factory()->recurrente()->activa()->create([
        'frequency' => DonationFrequency::TRIMESTRAL->value,
    ]);
    $donacion->updateNextPaymentDate();

    for ($i = 1; $i < 80; $i++) {
        $this->travelTo(now()->addDay());
        $donacionesPendientesCobro = Donation::query()->nextPaymentsDonations();
        expect($donacionesPendientesCobro->count())->toBe(0);

    }

    $this->travelTo('2025-04-05');

    $donacionesPendientesCobro = Donation::query()->nextPaymentsDonations();
    expect($donacionesPendientesCobro->count())->toBe(1);
});

test('al procesar donacion envío email a todos los usuarios administradores', function ($enviroment) {
    Notification::fake();
    // Establezco el entorno actual
    config(['app.env' => $enviroment]);

    User::factory()->count(3)->create();

    $users = User::all();

    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => DonationType::RECURRENTE->value,
        'frequency' => DonationFrequency::MENSUAL->value,
    ]);
    $donacion = $paymentProcess->modelo;

    $this->get(route('donation.response', getResponseDonation($donacion, true)))
        ->assertRedirect(route('donacion.finalizada', [
            'donacion' => $donacion->number,
        ]));

    if (app()->environment('production')) {
        Notification::assertSentTo(
            User::where('email', 'info@raulsebastian.es')->get(), DonationCreatedNotification::class
        );
        Notification::assertCount(1);
    } else {
        Notification::assertSentTo(
            User::all(), DonationCreatedNotification::class
        );
        Notification::assertCount($users->count());
    }

})->with([
    ['production', 'local'],
]);

it('Donation::payed simple crea estado PAGADO una sola vez', function () {
    $donacion = Donation::factory()->create(['type' => DonationType::UNICA->value]);
    Payment::factory()->create([
        'payable_type' => Donation::class,
        'payable_id' => $donacion->id,
        'number' => $donacion->number,
    ]);
    $decode = [
        'Ds_Order' => $donacion->payment->number,
        'Ds_Amount' => '100', // 1,00 €
    ];
    $donacion->payed($decode);
    $donacion->payed($decode);

    $donacion->refresh();
    expect($donacion->states()->where('name', OrderStatus::PAGADO->value)->count())->toBe(1);
});

it('Donation::payed recurrente setea identifier/next_payment y crea ACTIVA una vez', function () {
    $donacion = Donation::factory()->recurrente()->create();
    Payment::factory()->create([
        'payable_type' => Donation::class,
        'payable_id' => $donacion->id,
        'number' => $donacion->number,
        'amount' => $donacion->amount,
    ]);

    $decode = [
        'Ds_Order' => $donacion->payment->number,
        'Ds_Amount' => '250', // 2,50 €
        'Ds_Merchant_Identifier' => 'abc123',
    ];
    $donacion->payed($decode);
    $donacion->payed($decode);

    $donacion->refresh();
    expect($donacion->identifier)->toBe('abc123')
        ->and($donacion->next_payment)->not()->toBeNull()
        ->and($donacion->states()->where('name', OrderStatus::ACTIVA->value)->count())->toBe(1);
});

test('puedo crear factory con donacion recurrente', function () {
    $donacion = Donation::factory()->withCertificado()->withPayment()->recurrente(DonationFrequency::MENSUAL->value)->create();

    expect($donacion->type)->toBe(DonationType::RECURRENTE->value)
        ->and($donacion->frequency)->toBe(DonationFrequency::MENSUAL->value)
        ->and($donacion->certificate())->toBeInstanceOf(Address::class);
});

test('puedo calcular los impuestos por función', function () {

    $order = Donation::factory()->create([
        'amount' => 27.00,

    ]);

    expect($order->calculateTaxes())->toBe(4.69);
});

test('puedo calcular los impuestos por atributo', function () {

    $order = Donation::factory()->create([
        'amount' => 27.00,

    ]);

    expect($order->taxes)->toBe(4.69);
});

test('puedo crear factory de pago por bizum', function () {
    $donacion = Donation::factory()->porBizum()->create();

    expect($donacion->payment_method)->toBe('bizum');
});

test('si no selecciono certificado no veo paso 3(formulario certificados)', function () {
    livewire(DonacionBanner::class, ['prefix' => 'donacion'])
        ->set('type', DonationType::UNICA->value)
        ->set('amount', 10)
        ->call('toStep', 2)
        ->assertSee('¿Necesitas un certificado de donaciones?')
        ->set('needsCertificate', false)
        ->call('toStep', 3)
        ->assertSet('step', 4)
        ->assertSee('Método de pago');
});

test('puedo ver los 4 pasos en la donación', function () {
    livewire(DonacionBanner::class, ['prefix' => 'donacion'])
        ->set('type', DonationType::UNICA->value)
        ->set('amount', 10.50)
        ->call('toStep', 2)
        ->assertSee('¿Necesitas un certificado de donaciones?')
        ->set('needsCertificate', true)
        ->call('toStep', 3)
        ->assertSee('Datos para certificado de donaciones')
        ->set('certificate.name', 'Nombre')
        ->set('certificate.last_name', 'Apellido')
        ->set('certificate.nif', '1234567489W')
        ->set('certificate.last_name2', 'Apellido')
        ->set('certificate.company', 'Empresa SL')
        ->set('certificate.address', 'Calle Falsa 123')
        ->set('certificate.cp', '28001')
        ->set('certificate.province', 'Madrid')
        ->set('certificate.email', 'info@raulsebastian.es')
        ->call('toStep', 4)
        ->assertSet('step', 4)
        ->assertSee('Método de pago')
        ->assertSee('Pagar 10,50 €');
});

test('al actualizar tipo recurrente no existe método de pago Bizum', function () {
    $component = livewire(DonacionBanner::class, ['prefix' => 'donacion'])
        ->set('type', DonationType::RECURRENTE->value);

    $methods = $component->get('payments_methods');
    $codes = collect($methods)->pluck('code');

    expect($codes->contains('bizum'))->toBeFalse()
        ->and($codes->contains('tarjeta'))->toBeTrue();
});

test('al actualizar tipo si no es recurrente devuelvo todos los métodos de pago', function () {
    $component = livewire(DonacionBanner::class, ['prefix' => 'donacion'])
        ->set('type', DonationType::UNICA->value);

    $methods = $component->get('payments_methods');
    $codes = collect($methods)->pluck('code');

    expect($codes->contains('bizum'))->toBeTrue()
        ->and($codes->contains('tarjeta'))->toBeTrue();
});

test('si selecciono bizum agrego campo z a formulario redsys', function () {
    $comp = livewire(DonacionBanner::class, ['prefix' => 'donacion'])
        ->set('type', DonationType::UNICA->value)
        ->set('amount', 10)
        ->call('toStep', 2)
        ->assertSee('¿Necesitas un certificado de donaciones?')
        ->set('needsCertificate', false)
        ->call('toStep', 3)
        ->set('payment_method', 'bizum')
        ->call('submit');

    /** @noinspection PhpUndefinedFieldInspection */
    $params = json_decode(base64_decode(strtr($comp->MerchantParameters, '-_', '+/')), true);

    expect($params['Ds_Merchant_Paymethods'])->toBe('z');

});

test('si selecciono tarjeta no existe campo z en formulario redsys', function () {
    $comp = livewire(DonacionBanner::class, ['prefix' => 'donacion'])
        ->set('type', DonationType::UNICA->value)
        ->set('amount', 10)
        ->call('toStep', 2)
        ->assertSee('¿Necesitas un certificado de donaciones?')
        ->set('needsCertificate', false)
        ->call('toStep', 3)
        ->set('payment_method', PaymentMethod::TARJETA)
        ->call('submit');

    /** @noinspection PhpUndefinedFieldInspection */
    $params = json_decode(base64_decode($comp->MerchantParameters), true);

    expect($params)->not->toHaveKey('Ds_Merchant_Paymethods');

});

test('compruebo validación metodos de pago donación recurrente', function () {
    livewire(DonacionBanner::class, ['prefix' => 'donacion'])
        ->set('type', DonationType::RECURRENTE->value)
        ->set('amount', 10)
        ->call('toStep', 2)
        ->assertSee('¿Necesitas un certificado de donaciones?')
        ->set('needsCertificate', false)
        ->call('toStep', 3)
        ->set('payment_method', PaymentMethod::BIZUM)
        ->call('submit')
        ->assertHasErrors(['payment_method' => 'El método de pago no es válido.'])
        ->call('submit')
        ->set('payment_method', PaymentMethod::TARJETA)
        ->call('submit')
        ->assertHasNoErrors(['payment_method'])
        ->set('payment_method', 'kk')
        ->call('submit')
        ->assertHasErrors(['payment_method' => 'El método de pago no es válido.']);

});
test('compruebo validación metodos de pago donación unica', function () {
    livewire(DonacionBanner::class, ['prefix' => 'donacion'])
        ->set('type', DonationType::UNICA->value)
        ->set('amount', 10)
        ->call('toStep', 2)
        ->assertSee('¿Necesitas un certificado de donaciones?')
        ->set('needsCertificate', false)
        ->call('toStep', 3)
        ->set('payment_method', PaymentMethod::BIZUM)
        ->call('submit')
        ->assertHasNoErrors(['payment_method'])
        ->call('submit')
        ->set('payment_method', PaymentMethod::TARJETA)
        ->call('submit')
        ->assertHasNoErrors(['payment_method'])
        ->set('payment_method', 'kk')
        ->call('submit')
        ->assertHasErrors(['payment_method' => 'El método de pago no es válido.']);

});

test('puedo moverme entre pasos de la donación', function () {
    livewire(DonacionBanner::class, ['prefix' => 'donacion'])
        ->set('type', DonationType::UNICA->value)
        ->set('amount', 10)
        ->call('toStep', 2)
        ->assertSee('¿Necesitas un certificado de donaciones?')
        ->set('needsCertificate', false)
        ->call('toStep', 3)
        ->set('payment_method', PaymentMethod::BIZUM)
        ->call('toStep', 2)
        ->assertSee('¿Necesitas un certificado de donaciones?')
        ->call('toStep', 1)
        ->assertSee('!Dona a la FUNDACIÓN Elena Tertre!')
        ->call('toStep', 2)
        ->assertSee('¿Necesitas un certificado de donaciones?')
        ->call('toStep', 3)
//        ->set('payment_method', PaymentMethod::BIZUM)
        ->call('submit')
        ->assertHasNoErrors();
});

test('donacionBanner recurrente genera form_url correcto según entorno', function () {
    config(['redsys.enviroment' => 'test']);

    $compTest = livewire(DonacionBanner::class, ['prefix' => 'modal'])
        ->set('type', DonationType::RECURRENTE->value)
        ->set('frequency', DonationFrequency::MENSUAL->value)
        ->set('payment_method', PaymentMethod::TARJETA->value)
        ->set('amount', '5,00')
        ->call('toStep', 4)
        ->call('submit');

    expect($compTest->get('form_url'))->toContain('sis-t.redsys.es')
        ->and($compTest->get('MerchantParameters'))->not->toBeEmpty();

    config(['redsys.enviroment' => 'production']);

    $compProd = livewire(DonacionBanner::class, ['prefix' => 'modal'])
        ->set('type', DonationType::RECURRENTE->value)
        ->set('frequency', DonationFrequency::MENSUAL->value)
        ->set('payment_method', PaymentMethod::TARJETA->value)
        ->set('amount', '7,50')
        ->call('toStep', 4)
        ->call('submit');

    expect($compProd->get('form_url'))->toContain('sis.redsys.es')
        ->and($compProd->get('MerchantParameters'))->not->toBeEmpty();
});

test('donacionBanner unica genera form_url correcto según entorno', function () {
    config(['redsys.enviroment' => 'test']);
    $compTest = livewire(DonacionBanner::class, ['prefix' => 'modal'])
        ->set('type', DonationType::UNICA->value)
        ->set('payment_method', PaymentMethod::TARJETA->value)
        ->set('amount', '6,00')
        ->call('toStep', 4)
        ->call('submit');
    expect($compTest->get('form_url'))->toContain('sis-t.redsys.es')
        ->and($compTest->get('MerchantParameters'))->not->toBeEmpty();

    config(['redsys.enviroment' => 'production']);
    $compProd = livewire(DonacionBanner::class, ['prefix' => 'modal'])
        ->set('type', DonationType::UNICA->value)
        ->set('payment_method', PaymentMethod::TARJETA->value)
        ->set('amount', '8,00')
        ->call('toStep', 4)
        ->call('submit');
    expect($compProd->get('form_url'))->toContain('sis.redsys.es')
        ->and($compProd->get('MerchantParameters'))->not->toBeEmpty();
});

test('donacionBanner recurrente incluye COF_INI y COF_TYPE en MerchantParameters decodificados', function () {
    config(['redsys.enviroment' => 'test']);
    $comp = livewire(DonacionBanner::class, ['prefix' => 'modal'])
        ->set('type', DonationType::RECURRENTE->value)
        ->set('frequency', DonationFrequency::MENSUAL->value)
        ->set('payment_method', PaymentMethod::TARJETA->value)
        ->set('amount', '5,50')
        ->call('toStep', 4)
        ->call('submit');
    $decoded = json_decode(base64_decode(strtr($comp->get('MerchantParameters'), '-_', '+/')), true);
    expect($decoded)->toHaveKeys([
        'DS_MERCHANT_ORDER', 'DS_MERCHANT_AMOUNT', 'DS_MERCHANT_COF_INI', 'DS_MERCHANT_COF_TYPE',
    ])
        ->and($decoded['DS_MERCHANT_COF_INI'])->toBe('S')
        ->and($decoded['DS_MERCHANT_COF_TYPE'])->toBe('R');
});

test('donacionBanner única NO incluye campos COF en MerchantParameters', function () {
    $comp = livewire(DonacionBanner::class, ['prefix' => 'modal'])
        ->set('type', DonationType::UNICA->value)
        ->set('payment_method', PaymentMethod::TARJETA->value)
        ->set('amount', '12,00')
        ->call('toStep', 4)
        ->call('submit');
    $decoded = json_decode(base64_decode(strtr($comp->get('MerchantParameters'), '-_', '+/')), true);
    expect($decoded)->toHaveKeys(['DS_MERCHANT_ORDER', 'DS_MERCHANT_AMOUNT'])
        ->and($decoded)->not->toHaveKeys(['DS_MERCHANT_COF_INI', 'DS_MERCHANT_COF_TYPE']);
});

test('MerchantParameters codifica correctamente número y amount de donación única', function () {
    $amount = '9,99';
    $comp = livewire(DonacionBanner::class, ['prefix' => 'modal'])
        ->set('type', DonationType::UNICA->value)
        ->set('payment_method', PaymentMethod::TARJETA->value)
        ->set('amount', $amount)
        ->call('toStep', 4)
        ->call('submit');
    $decoded = json_decode(base64_decode(strtr($comp->get('MerchantParameters'), '-_', '+/')), true);
    $donacion = Donation::where('number', $decoded['DS_MERCHANT_ORDER'])->first();
    expect($donacion)->toBeInstanceOf(Donation::class)
        ->and($decoded['DS_MERCHANT_AMOUNT'])->toBe(convert_amount_to_redsys($donacion->amount));
});

test('Signature y SignatureVersion presentes en donación recurrente', function () {
    $comp = livewire(DonacionBanner::class, ['prefix' => 'modal'])
        ->set('type', DonationType::RECURRENTE->value)
        ->set('frequency', DonationFrequency::MENSUAL->value)
        ->set('payment_method', PaymentMethod::TARJETA->value)
        ->set('amount', '6,75')
        ->call('toStep', 4)
        ->call('submit');
    expect($comp->get('MerchantSignature'))->not->toBeEmpty()
        ->and($comp->get('SignatureVersion'))->toBe('HMAC_SHA256_V1');
});

test('donacion callback con firma inválida marca ERROR', function () {
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => DonationType::UNICA->value,
    ]);
    $donacion = $paymentProcess->modelo;

    // Simular callback Redsys con firma alterada
    $okData = getResponseDonation($donacion, true);
    $okData['Ds_Signature'] = 'firma-alterada';

    $this->post(route('donation.response', $okData))
        ->assertRedirect(route('donacion.finalizada', [
            'donacion' => $donacion->number,
        ]));

    $donacion->refresh();

    expect($donacion->state->name)->toBe(OrderStatus::ERROR->value)
        ->and($donacion->state->info['Error'])->toBe('Firma no válida');
});

test('donacion recurrente producción incluye url_notification en parámetros crudos', function () {
    config(['app.env' => 'production']);
    config(['redsys.enviroment' => 'production']);

    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('11,00'),
        'type' => DonationType::RECURRENTE->value,
        'frequency' => DonationFrequency::MENSUAL->value,
    ]);

    $formData = $paymentProcess->getFormRedSysData();
    $raw = $paymentProcess->redSysAttributes;

    expect($formData['form_url'])->toContain('sis.redsys.es')
        ->and($raw)->toHaveKey('DS_MERCHANT_MERCHANTURL');
});

test('donacion KO por helper genera estado ERROR y mantiene pago inicial', function () {
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('9,00'),
        'type' => DonationType::UNICA->value,
    ]);
    $donacion = $paymentProcess->modelo;

    // Respuesta KO (Ds_Response 9928)
    $koData = getResponseDonation($donacion, false);

    $this->post(route('donation.response'), $koData)
        ->assertRedirect(route('donacion.finalizada', [
            'donacion' => $donacion->number,
        ]));

    $donacion->refresh();
    expect($donacion->state->name)->toBe(OrderStatus::ERROR->value)
        ->and($donacion->payments)->toHaveCount(1)
        ->and($donacion->payments->first()->amount)->toBe(0.0)
        ->and($donacion->state->info['Ds_Response'])->toBe('9928');
});

test('donacion única callback OK repetido no duplica estado PAGADO (idempotencia)', function () {
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('12,00'),
        'type' => DonationType::UNICA->value,
    ]);
    $donacion = $paymentProcess->modelo;
    $callbackOk = getResponseDonation($donacion, true);

    // Primera llamada OK
    $this->post(route('donation.response'), $callbackOk)
        ->assertRedirect(route('donacion.finalizada', ['donacion' => $donacion->number]));
    $donacion->refresh();
    expect($donacion->state->name)->toBe(OrderStatus::PAGADO->value)
        ->and($donacion->states)->toHaveCount(2); // PENDIENTE + PAGADO

    // Segunda llamada OK (duplicada)
    $this->post(route('donation.response'), $callbackOk)
        ->assertRedirect(route('donacion.finalizada', ['donacion' => $donacion->number]));
    $donacion->refresh();
    expect($donacion->state->name)->toBe(OrderStatus::PAGADO->value)
        ->and($donacion->states)->toHaveCount(2); // No debe crear estado duplicado
});

test('donacion recurrente callback OK repetido no duplica estado ACTIVA (idempotencia)', function () {
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('15,00'),
        'type' => DonationType::RECURRENTE->value,
        'frequency' => DonationFrequency::MENSUAL->value,
    ]);
    $donacion = $paymentProcess->modelo;
    $callbackOk = getResponseDonation($donacion, true);

    // Primera llamada OK
    $this->post(route('donation.response'), $callbackOk)
        ->assertRedirect(route('donacion.finalizada', ['donacion' => $donacion->number]));
    $donacion->refresh();
    expect($donacion->state->name)->toBe(OrderStatus::ACTIVA->value)
        ->and($donacion->states)->toHaveCount(2); // PENDIENTE + ACTIVA

    // Segunda llamada OK (duplicada)
    $this->post(route('donation.response'), $callbackOk)
        ->assertRedirect(route('donacion.finalizada', ['donacion' => $donacion->number]));
    $donacion->refresh();
    expect($donacion->state->name)->toBe(OrderStatus::ACTIVA->value)
        ->and($donacion->states)->toHaveCount(2); // No debe crear estado duplicado
});

test('donacion callback sin Ds_MerchantParameters retorna 404', function () {
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('8,00'),
        'type' => DonationType::UNICA->value,
    ]);
    $donacion = $paymentProcess->modelo;

    $this->post(route('donation.response'), [
        'Ds_Signature' => 'firma-cualquiera',
        'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
    ])->assertNotFound();

    $donacion->refresh();
    expect($donacion->state->name)->toBe(OrderStatus::PENDIENTE->value);
});

test('donacion callback con MerchantParameters corrupto retorna 404', function () {
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('7,00'),
        'type' => DonationType::UNICA->value,
    ]);
    $donacion = $paymentProcess->modelo;

    $this->post(route('donation.response'), [
        'Ds_MerchantParameters' => 'datos-corruptos-no-base64',
        'Ds_Signature' => 'firma-cualquiera',
        'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
    ])->assertNotFound();

    $donacion->refresh();
    expect($donacion->state->name)->toBe(OrderStatus::PENDIENTE->value);
});

test('donacion callback con MerchantParameters JSON inválido retorna 404', function () {
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('6,00'),
        'type' => DonationType::UNICA->value,
    ]);
    $donacion = $paymentProcess->modelo;

    // Base64 válido pero JSON inválido
    $invalidJson = base64_encode('esto no es json válido');

    $this->post(route('donation.response'), [
        'Ds_MerchantParameters' => $invalidJson,
        'Ds_Signature' => 'firma-cualquiera',
        'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
    ])->assertNotFound();

    $donacion->refresh();
    expect($donacion->state->name)->toBe(OrderStatus::PENDIENTE->value);
});

test('donacion callback vacío retorna 404', function () {
    $this->post(route('donation.response'), [])
        ->assertNotFound();
});

test('donacion recurrente sin Ds_Merchant_Identifier se procesa correctamente', function () {
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,00'),
        'type' => DonationType::RECURRENTE->value,
        'frequency' => DonationFrequency::MENSUAL->value,
    ]);
    $donacion = $paymentProcess->modelo;

    // Crear respuesta Redsys SIN Ds_Merchant_Identifier (escenario test Redsys)
    $paramsOk = buildRedsysParams(
        amount: convert_amount_to_redsys($donacion->amount),
        order: $donacion->number,
        response: '0000'
    );
    // NO incluir Ds_Merchant_Identifier
    $callbackOk = generateRedsysResponse($paramsOk, $donacion->number);

    $this->post(route('donation.response'), $callbackOk)
        ->assertRedirect(route('donacion.finalizada', ['donacion' => $donacion->number]));

    $donacion->refresh();
    expect($donacion->state->name)->toBe(OrderStatus::ACTIVA->value)
        ->and($donacion->identifier)->toBeNull() // Identifier es null cuando no viene en respuesta
        ->and($donacion->next_payment)->not()->toBeNull()
        ->and($donacion->payments->first()->amount)->toBe(10.00);
});

// === TESTS DE PERFORMANCE Y CARGA MASIVA ===

test('carga masiva: puede procesar 50 donaciones únicas simultáneas sin errores', function () {
    $donaciones = collect();

    // Crear 50 donaciones
    for ($i = 0; $i < 50; $i++) {
        $pp = new PaymentProcess(Donation::class, [
            'amount' => convertPriceNumber(fake()->randomFloat(2, 5, 100)),
            'type' => DonationType::UNICA->value,
        ]);
        $donaciones->push($pp->modelo);
    }

    // Verificar que todos los números son únicos
    $numeros = $donaciones->pluck('number');
    expect($numeros->unique()->count())->toBe(50);

    // Procesar callbacks para todas
    $donaciones->each(function ($donacion) {
        $callback = getResponseDonation($donacion, true);
        $this->post(route('donation.response'), $callback)
            ->assertRedirect();
    });

    // Verificar que todas están en estado PAGADO
    $donaciones->each(function ($donacion) {
        $donacion->refresh();
        expect($donacion->state->name)->toBe(OrderStatus::PAGADO->value)
            ->and($donacion->payments->first()->amount)->toBeGreaterThan(0);
    });
})->group('performance');

test('carga masiva: puede procesar 50 donaciones recurrentes simultáneas sin errores', function () {
    $donaciones = collect();

    // Crear 50 donaciones recurrentes
    for ($i = 0; $i < 50; $i++) {
        $pp = new PaymentProcess(Donation::class, [
            'amount' => convertPriceNumber(fake()->randomFloat(2, 10, 200)),
            'type' => DonationType::RECURRENTE->value,
            'frequency' => fake()->randomElement([
                DonationFrequency::MENSUAL->value,
                DonationFrequency::TRIMESTRAL->value,
                DonationFrequency::ANUAL->value,
            ]),
        ]);
        $donaciones->push($pp->modelo);
    }

    // Verificar que todos los números son únicos
    $numeros = $donaciones->pluck('number');
    expect($numeros->unique()->count())->toBe(50);

    // Procesar callbacks para todas
    $donaciones->each(function ($donacion) {
        $callback = getResponseDonation($donacion, true);
        $this->post(route('donation.response'), $callback)
            ->assertRedirect();
    });

    // Verificar que todas están en estado ACTIVA
    $donaciones->each(function ($donacion) {
        $donacion->refresh();
        expect($donacion->state->name)->toBe(OrderStatus::ACTIVA->value)
            ->and($donacion->next_payment)->not()->toBeNull()
            ->and($donacion->payments->first()->amount)->toBeGreaterThan(0);
    });
})->group('performance');

test('carga masiva: helpers Redsys generan firmas únicas para 100 transacciones', function () {
    $firmas = collect();

    for ($i = 0; $i < 100; $i++) {
        $pp = new PaymentProcess(Donation::class, [
            'amount' => convertPriceNumber(fake()->randomFloat(2, 1, 500)),
            'type' => DonationType::UNICA->value,
        ]);

        $formData = $pp->getFormRedSysData();
        $firmas->push($formData['Ds_Signature']);
    }

    // Todas las firmas deben ser únicas
    expect($firmas->unique()->count())->toBe(100);
})->group('performance');

test('carga masiva: puede crear 100 pedidos con items sin colisiones', function () {
    Event::fake(); // Evitar listeners para acelerar
    Product::factory()->create(['stock' => 1000]);
    $pedidos = collect();

    for ($i = 0; $i < 100; $i++) {
        $pp = new PaymentProcess(Order::class, [
            'amount' => fake()->randomFloat(2, 10, 100),
            'shipping' => 'Envío',
            'shipping_cost' => 5.00,
            'subtotal' => fake()->randomFloat(2, 5, 95),
            'payment_method' => PaymentMethod::TARJETA->value,
        ]);
        $pedidos->push($pp->modelo);
    }

    // Verificar que todos los números son únicos
    $numeros = $pedidos->pluck('number');
    expect($numeros->unique()->count())->toBe(100);

    // Verificar que todos tienen un pago asociado
    $pedidos->each(function ($pedido) {
        expect($pedido->payments)->toHaveCount(1);
    });
})->group('performance');

test('carga masiva: callbacks simultáneos OK y KO no causan race conditions', function () {
    Event::fake();
    $donaciones = collect();

    // Crear 20 donaciones
    for ($i = 0; $i < 20; $i++) {
        $pp = new PaymentProcess(Donation::class, [
            'amount' => convertPriceNumber('10,00'),
            'type' => DonationType::UNICA->value,
        ]);
        $donaciones->push($pp->modelo);
    }

    // Procesar mitad OK y mitad KO
    $donaciones->each(function ($donacion, $index) {
        $esOk = $index % 2 === 0;
        $callback = getResponseDonation($donacion, $esOk);

        $this->post(route('donation.response'), $callback)
            ->assertRedirect();

        $donacion->refresh();
        if ($esOk) {
            expect($donacion->state->name)->toBe(OrderStatus::PAGADO->value);
        } else {
            expect($donacion->state->name)->toBe(OrderStatus::ERROR->value);
        }
    });
})->group('performance');

test('carga masiva: helpers pueden decodificar 100 respuestas Redsys sin errores', function () {
    for ($i = 0; $i < 100; $i++) {
        $pp = new PaymentProcess(Donation::class, [
            'amount' => convertPriceNumber(fake()->randomFloat(2, 1, 100)),
            'type' => DonationType::UNICA->value,
        ]);

        $callback = getResponseDonation($pp->modelo, true);

        // Verificar que la callback tiene los campos necesarios
        expect($callback)->toHaveKeys(['Ds_MerchantParameters', 'Ds_Signature', 'Ds_SignatureVersion'])
            ->and($callback['Ds_MerchantParameters'])->not->toBeEmpty()
            ->and($callback['Ds_Signature'])->not->toBeEmpty();
    }
})->group('performance');

test('carga masiva: generación masiva de números de orden no produce duplicados', function () {
    $numeros = collect();

    // Generar 500 números de donaciones
    for ($i = 0; $i < 500; $i++) {
        $numeros->push(generateDonationNumber());
    }

    // Todos deben ser únicos
    expect($numeros->unique()->count())->toBe(500);

    $numerosPedido = collect();

    // Generar 500 números de pedido
    for ($i = 0; $i < 500; $i++) {
        $numerosPedido->push(generateOrderNumber());
    }

    // Todos deben ser únicos
    expect($numerosPedido->unique()->count())->toBe(500);
})->group('performance');

test('carga masiva: idempotencia se mantiene con 30 callbacks duplicados', function () {
    Event::fake();

    $pp = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('25,00'),
        'type' => DonationType::UNICA->value,
    ]);
    $donacion = $pp->modelo;

    // PaymentProcess ya creó el estado PENDIENTE, verificar
    expect($donacion->states)->toHaveCount(1)
        ->and($donacion->state->name)->toBe(OrderStatus::PENDIENTE->value);

    $callback = getResponseDonation($donacion, true);

    // Enviar el mismo callback 30 veces
    for ($i = 0; $i < 30; $i++) {
        $this->post(route('donation.response'), $callback)
            ->assertRedirect();
    }

    $donacion->refresh();

    // Debe seguir teniendo solo 2 estados (PENDIENTE + PAGADO)
    expect($donacion->states)->toHaveCount(2)
        ->and($donacion->state->name)->toBe(OrderStatus::PAGADO->value);
})->group('performance');

test('carga masiva: memoria se mantiene estable procesando 50 donaciones', function () {
    $memoriaInicial = memory_get_usage();

    for ($i = 0; $i < 50; $i++) {
        $pp = new PaymentProcess(Donation::class, [
            'amount' => convertPriceNumber('15,50'),
            'type' => DonationType::UNICA->value,
        ]);

        $callback = getResponseDonation($pp->modelo, true);
        $this->post(route('donation.response'), $callback);

        // Limpiar para siguiente iteración
        unset($pp);
    }

    $memoriaFinal = memory_get_usage();
    $incrementoMB = ($memoriaFinal - $memoriaInicial) / 1024 / 1024;

    // El incremento de memoria no debe superar 50 MB para 50 transacciones
    expect($incrementoMB)->toBeLessThan(50);
})->group('performance');

test('carga masiva: tiempos de procesamiento son consistentes', function () {
    $tiempos = collect();

    for ($i = 0; $i < 20; $i++) {
        $inicio = microtime(true);

        $pp = new PaymentProcess(Donation::class, [
            'amount' => convertPriceNumber('12,00'),
            'type' => DonationType::UNICA->value,
        ]);

        $callback = getResponseDonation($pp->modelo, true);
        $this->post(route('donation.response'), $callback);

        $tiempos->push(microtime(true) - $inicio);
    }

    $tiempoPromedio = $tiempos->avg();
    $tiempoMaximo = $tiempos->max();

    // El tiempo máximo no debe ser más de 3x el promedio (detecta picos anormales)
    expect($tiempoMaximo)->toBeLessThan($tiempoPromedio * 3);
})->group('performance');

// === TESTS DE OBSERVABILIDAD: LOGS ESTRUCTURADOS ===

test('observabilidad: transición a PAGADO genera log estructurado en donation única', function () {
    Log::spy();

    $pp = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('25,00'),
        'type' => DonationType::UNICA->value,
    ]);
    $donacion = $pp->modelo;

    $callback = getResponseDonation($donacion, true);
    $this->post(route('donation.response'), $callback);

    Log::shouldHaveReceived('info')
        ->once()
        ->withArgs(function ($message, $context) use ($donacion) {
            return str_contains($message, 'Transición de estado de donación')
                && $context['donation_id'] === $donacion->id
                && $context['donation_number'] === $donacion->number
                && $context['new_state'] === OrderStatus::PAGADO->value
                && isset($context['timestamp'])
                && isset($context['amount']);
        });
})->group('observability');

test('observabilidad: transición a ACTIVA genera log estructurado en donation recurrente', function () {
    Log::spy();

    $pp = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('30,00'),
        'type' => DonationType::RECURRENTE->value,
        'frequency' => DonationFrequency::MENSUAL->value,
    ]);
    $donacion = $pp->modelo;

    $callback = getResponseDonation($donacion, true);
    $this->post(route('donation.response'), $callback);

    Log::shouldHaveReceived('info')
        ->once()
        ->withArgs(function ($message, $context) use ($donacion) {
            return str_contains($message, 'Transición de estado de donación')
                && $context['donation_id'] === $donacion->id
                && $context['donation_type'] === DonationType::RECURRENTE->value
                && $context['new_state'] === OrderStatus::ACTIVA->value
                && isset($context['frequency'])
                && isset($context['next_payment'])
                && isset($context['timestamp']);
        });
})->group('observability');

test('observabilidad: transición a ERROR genera log warning en donation', function () {
    Log::spy();

    $pp = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('15,00'),
        'type' => DonationType::UNICA->value,
    ]);
    $donacion = $pp->modelo;

    $callbackKo = getResponseDonation($donacion, false);
    $this->post(route('donation.response'), $callbackKo);

    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(function ($message, $context) use ($donacion) {
            return str_contains($message, 'Transición de estado de donación a ERROR')
                && $context['donation_id'] === $donacion->id
                && $context['new_state'] === OrderStatus::ERROR->value
                && isset($context['error_message'])
                && isset($context['ds_response'])
                && isset($context['timestamp']);
        });
})->group('observability');

test('observabilidad: cancelación donation genera log estructurado', function () {
    Log::spy();

    $donacion = Donation::factory()->recurrente()->activa()->create();

    $donacion->cancel();

    Log::shouldHaveReceived('info')
        ->once()
        ->withArgs(function ($message, $context) use ($donacion) {
            return str_contains($message, 'Transición de estado de donación a CANCELADO')
                && $context['donation_id'] === $donacion->id
                && $context['new_state'] === OrderStatus::CANCELADO->value
                && $context['previous_state'] === OrderStatus::ACTIVA->value
                && isset($context['had_next_payment'])
                && isset($context['timestamp']);
        });
})->group('observability');

test('observabilidad: idempotencia NO genera logs duplicados', function () {
    Log::spy();

    $pp = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('20,00'),
        'type' => DonationType::UNICA->value,
    ]);
    $donacion = $pp->modelo;

    $callback = getResponseDonation($donacion, true);

    // Primera llamada: debe generar log
    $this->post(route('donation.response'), $callback);

    // Segunda llamada (idempotente): No debe generar log
    $this->post(route('donation.response'), $callback);

    // Solo debe haber UN log de transición
    Log::shouldHaveReceived('info')
        ->once()
        ->withArgs(function ($message) {
            return str_contains($message, 'Transición de estado de donación');
        });
})->group('observability');

test('observabilidad: log contiene todos los campos requeridos para donation', function () {
    Log::spy();

    $pp = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('45,00'),
        'type' => DonationType::RECURRENTE->value,
        'frequency' => DonationFrequency::TRIMESTRAL->value,
    ]);
    $donacion = $pp->modelo;

    $callback = getResponseDonation($donacion, true);
    $this->post(route('donation.response'), $callback);

    Log::shouldHaveReceived('info')
        ->once()
        ->withArgs(function ($message, $context) {
            $requiredFields = [
                'donation_id', 'donation_number', 'donation_type', 'frequency',
                'previous_state', 'new_state', 'amount', 'ds_order', 'ds_response',
                'ds_authorisation_code', 'has_identifier', 'next_payment',
                'payment_method', 'timestamp',
            ];

            foreach ($requiredFields as $field) {
                if (! array_key_exists($field, $context)) {
                    return false;
                }
            }

            return true;
        });
})->group('observability');

test('cuando selecciono frecuencia en banner no cambia amount', function () {
    livewire(DonacionBanner::class, ['prefix' => 'modal'])
        ->set('frequency', DonationFrequency::TRIMESTRAL->value)
        ->assertSet('amount', 0);
});
