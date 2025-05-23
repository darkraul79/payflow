<?php

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderState;
use App\Services\Cart;

test('puedo crear pedido a través de factory', closure: function () {

    $order = Order::factory()->create();
    expect($order)->toBeInstanceOf(Order::class);
});

test('puedo crear factory con items', function () {

    $order = Order::factory()
        ->hasItems(2)
        ->create();

    expect($order->items)->toHaveCount(2);
});

test('puedo crear factory con diferentes estados', function (string $estado) {
    $order = Order::factory()->{$estado}()->create();

    expect($order->states)->toHaveCount(2)
        ->and($order->state->name)->toBe(constant(OrderState::class . '::' . strtoupper($estado)));
})->with([
    'pagado',
    'enviado',
    'finalizado',
    'error',
    'cancelado',
]);

test('el estado por defecto es pendiente de envío', function () {
    $pedido = Order::factory()->create();
    expect($pedido->state->name)->toBe(OrderState::PENDIENTE);
});

test('la dirección por defecto es la de facturación', function () {
    $order = Order::factory()->create();
    expect($order->address)->toBeInstanceOf(OrderAddress::class)
        ->and($order->address->type)->toBe(OrderAddress::BILLING);
});

test('puedo crear pedido desde componente de livewire', function () {

    $order = creaPedido();

    expect($order)->toBeInstanceOf(Order::class)
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->product->name)->toBe('Producto de prueba')
        ->and($order->total)->toBe(10.00);

});

test('vacio cesta después de crear pedido', function () {

    creaPedido();

    expect(Cart::getItems())->toBeArray()
        ->and(Cart::getItems())->toHaveCount(0);

    $this->get(route('cart'))
        ->assertSee('No hay productos en el carrito');

});
