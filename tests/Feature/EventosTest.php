<?php

use App\Events\CreateOrderEvent;
use App\Events\NewDonationEvent;
use App\Events\UpdateOrderStateEvent;
use App\Filament\Resources\OrderResource\Pages\UpdateOrder;
use App\Http\Classes\PaymentProcess;
use App\Listeners\SendEmailsOrderListener;
use App\Mail\DonationNewMail;
use App\Mail\OrderNew;
use App\Models\Address;
use App\Models\Donation;
use App\Models\Order;
use App\Models\State;
use App\Models\User;
use App\Notifications\OrderCreated;
use Database\Seeders\UsersSeeder;
use function Pest\Livewire\livewire;

test('al crear pedido se llama al evento CreateOrder en factory', function () {

    Event::fake([
        CreateOrderEvent::class,
    ]);
    Order::factory()->create();

    Event::assertDispatched(CreateOrderEvent::class);

});

test('al crear pedido por metodo se llama al evento CreateOrder', function () {

    Event::fake([
        CreateOrderEvent::class,
    ]);

    $pedido = creaPedido();
    $this->get(route('pedido.response', getResponseOrder($pedido, true)));

    Event::assertDispatched(CreateOrderEvent::class);
    Event::assertListening(
        CreateOrderEvent::class,
        SendEmailsOrderListener::class
    );

});

test('al crear pedido se manda un email a los administradores', function () {

    Notification::fake();

    $this->seed(UsersSeeder::class);

    Notification::assertNothingSent();

    $pedido = creaPedido();
    $this->get(route('pedido.response', getResponseOrder($pedido, true)));

    Notification::assertSentTo(
        User::all(), OrderCreated::class
    );
});

test('al crear pedido se manda un email al email de la dirección de facturacion ', function () {

    Mail::fake();
    Mail::assertNothingSent();

    $pedido = creaPedido();
    $this->get(route('pedido.response', getResponseOrder($pedido, true)));

    Mail::assertSent(OrderNew::class, $pedido->billing_address()->email);
});

test('al crear pedido se manda un email al email de la dirección de facturacion desde factory ', function () {

    Mail::fake();
    Mail::assertNothingSent();

    $p = Order::factory()->withDireccion()->create();

    Mail::assertSent(OrderNew::class, $p->billing_address()->email);
});

test('al crear pedido si tiene dirección de envío con email diferente pongo en copia ', function () {

    Mail::fake();
    Mail::assertNothingSent();

    $p = Order::factory()->withDirecciones([
        'email' => 'info@raulsebastian.es',
    ], [
        'email' => 'dakraul@gmail.com',
    ])->create();

    Mail::assertSent(OrderNew::class, function (OrderNew $mail) {
        return $mail->hasTo('info@raulsebastian.es') && $mail->hasCc('dakraul@gmail.com');
    });
});

test('al crear pedido si tiene misma dirección de envío con email diferente sin cc ', function () {

    Mail::fake();
    Mail::assertNothingSent();

    $p = Order::factory()->withDirecciones([
        'email' => 'info@raulsebastian.es',
    ], [
        'email' => 'info@raulsebastian.es',
    ])->create();

    Mail::assertSent(OrderNew::class, function (OrderNew $mail) {
        return $mail->hasTo('info@raulsebastian.es') && $mail->cc == [];
    });
});

test('al actualizar estado de pedidos se ejecuta UpdateOrderStateEvent', function () {

    Event::fake();
    $pedido = Order::factory()->hasItems()->create();

    livewire(UpdateOrder::class, [
        'record' => $pedido->id,
    ])->fillForm(['estado' => 'PAGADO'])
        ->call('submit');

    $pedido->refresh();

    Event::assertDispatched(UpdateOrderStateEvent::class);

    expect($pedido->state->name)->toBe(State::PAGADO)
        ->and($pedido->states->last()->name)->toBe(State::PAGADO);

});

test('al crear donación recurrente se ejecuta evento New Donation', function () {
    Event::fake();
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => Donation::RECURRENTE,
        'frequency' => Donation::FREQUENCY['MENSUAL'],
    ]);
    $donacion = $paymentProcess->modelo;

    $this->get(route('donation.response', getResponseDonation($donacion, true)));
    $donacion->refresh();

    Event::assertDispatched(NewDonationEvent::class);

});

test('al crear donación recurrente envío email al donante', function ($state, $subject, $text) {

    Mail::fake();
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => Donation::RECURRENTE,
        'frequency' => Donation::FREQUENCY['MENSUAL'],
    ]);
    $donacion = $paymentProcess->modelo;
    $donacion->addresses()->create([
        'type' => Address::CERTIFICATE,
        'name' => 'Nombre',
        'last_name' => 'Apellido',
        'last_name2' => 'Apellido2',
        'company' => 'Empresa SL',
        'address' => 'Calle Falsa 123',
        'cp' => '28001',
        'city' => 'Madrid',
        'province' => 'Madrid',
        'email' => 'info@raulsebastian.es',
    ]);

    $donacion->refresh();
    Mail::assertNothingSent();

    $this->get(route('donation.response', getResponseDonation($donacion, $state)));

    Mail::assertSent(DonationNewMail::class, function (DonationNewMail $mail) use ($subject, $text) {
        return $mail->hasTo('info@raulsebastian.es') &&
            $mail->hasSubject($subject)
            && $mail->assertSeeInText($text);
    });
})
    ->with([
        [
            'state' => true,
            'subject' => '¡Gracias por unirte como socio/amigo! 🌊',
            'text' => '¡Gracias por apoyar el trabajo de la Fundación Elena Tertre!',
        ],
        [
            'state' => false,
            'subject' => 'Problema con tu alta como socio/amigo',
            'text' => 'Hemos tenido un problema con la activación de tu alta como',
        ],
    ]);

test('al crear donación única envío email al donante', function ($state, $subject, $text) {

    Mail::fake();
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => Donation::UNICA,
    ]);
    $donacion = $paymentProcess->modelo;
    $donacion->addresses()->create([
        'type' => Address::CERTIFICATE,
        'name' => 'Nombre',
        'last_name' => 'Apellido',
        'last_name2' => 'Apellido2',
        'company' => 'Empresa SL',
        'address' => 'Calle Falsa 123',
        'cp' => '28001',
        'city' => 'Madrid',
        'province' => 'Madrid',
        'email' => 'info@raulsebastian.es',
    ]);

    $donacion->refresh();
    Mail::assertNothingSent();

    $this->get(route('donation.response', getResponseDonation($donacion, $state)));

    Mail::assertSent(DonationNewMail::class, function (DonationNewMail $mail) use ($subject) {

        return $mail->hasTo('info@raulsebastian.es') &&
            $mail->hasSubject($subject);
    });
})
    ->with([
        [
            'state' => true,
            'subject' => '¡Gracias por tu donación solidaria! 💛',
            'text' => 'Hemos recibido tu donación.',
        ],
        [
            'state' => false,
            'subject' => 'Problema con tu donación',
            'text' => 'Lamentamos informarte que no hemos podido procesar tu donación debido a un problema con el pago.',
        ],
    ]);

test('al crear donación sin direccion no envío email', function ($state, $type) {

    Mail::fake();
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => $type,
        'frequency' => $type === Donation::RECURRENTE ? Donation::FREQUENCY['MENSUAL'] : null,
    ]);
    $donacion = $paymentProcess->modelo;

    $donacion->refresh();

    $this->get(route('donation.response', getResponseDonation($donacion, $state)));

    Mail::assertNothingSent();
})
    ->with([
        [
            'state' => true,
            'type' => Donation::RECURRENTE,
        ],
        [
            'state' => false,
            'type' => Donation::RECURRENTE,
        ],
        [
            'state' => false,
            'type' => Donation::UNICA,
        ],
        [
            'state' => true,
            'type' => Donation::UNICA,
        ],
    ]);


test('al crear donación recurrente envia email con datos del importe', function () {

    Mail::fake();
    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber('10,35'),
        'type' => Donation::RECURRENTE,
        'frequency' => Donation::FREQUENCY['MENSUAL'],
    ]);
    $donacion = $paymentProcess->modelo;
    $donacion->addresses()->create([
        'type' => Address::CERTIFICATE,
        'name' => 'Nombre',
        'last_name' => 'Apellido',
        'last_name2' => 'Apellido2',
        'company' => 'Empresa SL',
        'address' => 'Calle Falsa 123',
        'cp' => '28001',
        'city' => 'Madrid',
        'province' => 'Madrid',
        'email' => 'info@raulsebastian.es',
    ]);

    $donacion->refresh();
    Mail::assertNothingSent();

    $this->get(route('donation.response', getResponseDonation($donacion, true)));

    Mail::assertSent(DonationNewMail::class, function (DonationNewMail $mail) {

        return $mail->hasTo('info@raulsebastian.es') &&
            $mail->assertSeeInOrderInText(['mensual', '10,35']) &&
            $mail->hasSubject('¡Gracias por unirte como socio/amigo! 🌊');
    });
});
