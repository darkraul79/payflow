<?php

use App\Enums\OrderStatus;
use App\Events\UpdateOrderStateEvent;
use App\Mail\OrderStateUpdate;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('la snapshot del estado encolado conserva el estado al momento del dispatch cuando la cola se procesa después',
    function () {
        Mail::fake();

        // Creo un pedido con direcciones
        $billing = ['email' => 'billing-snapshot@example.test'];
        $shipping = ['email' => 'shipping-snapshot@example.test'];

        $order = Order::factory()
            ->withDireccion($billing)
            ->withDirecionEnvio($shipping)
            ->create();

        // Añadimos un primer estado y disparamos el evento (esto encolará un mailable con snapshot)
        $order->states()->create(['name' => OrderStatus::PENDIENTE->value]);
        UpdateOrderStateEvent::dispatch($order);

        // Ahora, sin procesar la cola, cambiamos el estado a PAGADO y disparamos de nuevo
        $order->states()->create(['name' => OrderStatus::PAGADO->value]);
        UpdateOrderStateEvent::dispatch($order);

        // Comprobamos que se encolaron dos mailable
        Mail::assertQueued(OrderStateUpdate::class, 2);

        // Capturamos las instancias encoladas usando assertQueued con callback
        $captured = [];
        Mail::assertQueued(OrderStateUpdate::class, function (OrderStateUpdate $mail) use (&$captured) {
            $captured[] = $mail;

            return true;
        });

        expect(count($captured))->toBe(2);

        $first = $captured[0];
        $second = $captured[1];

        // Comprobaciones antes de serializar
        expect($first->getView())->toBe(OrderStatus::PENDIENTE->emailView())
            ->and($first->getSubject())->toBe(OrderStatus::PENDIENTE->emailSubject())
            ->and($second->getView())->toBe(OrderStatus::PAGADO->emailView())
            ->and($second->getSubject())->toBe(OrderStatus::PAGADO->emailSubject());

        // Simulamos la ejecución en cola: serializar y deserializar para emular el comportamiento real
        $firstSerialized = serialize($first);
        $firstUnserialized = unserialize($firstSerialized);

        // El mailable debe devolver la vista y subject de PENDIENTE independientemente del estado actual del modelo
        expect($firstUnserialized->getView())->toBe(OrderStatus::PENDIENTE->emailView())
            ->and($firstUnserialized->getSubject())->toBe(OrderStatus::PENDIENTE->emailSubject());

        // El segundo mailable debe corresponder al estado PAGADO
        $secondSerialized = serialize($second);
        $secondUnserialized = unserialize($secondSerialized);

        expect($secondUnserialized->getView())->toBe(OrderStatus::PAGADO->emailView())
            ->and($secondUnserialized->getSubject())->toBe(OrderStatus::PAGADO->emailSubject());

    });
