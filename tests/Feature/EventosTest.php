<?php

use App\Events\CreateOrderEvent;
use App\Listeners\SendEmailsOrderListener;
use App\Mail\OrderNew;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderCreated;

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
    User::factory()->create([
        'name' => 'Raul',
        'email' => 'info@raulsebastian.es',
        'password' => 'aa',
    ]);
    Notification::assertNothingSent();

    $pedido = creaPedido();
    $this->get(route('pedido.response', getResponseOrder($pedido, true)));

    Notification::assertSentTo(
        [User::first()], OrderCreated::class
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
