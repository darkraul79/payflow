<?php

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\State;
use App\Models\User;
use App\Notifications\OrderCreated;
use App\Services\Cart;
use Illuminate\Support\Facades\Notification;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

test('puedo crear Pedido por defecto en factory', function () {

    $pedido = Order::factory()->create();

    expect($pedido)->toBeInstanceOf(Order::class);
});

test('al crear pedido se crea por defecto estado Pendiente', function () {

    $pedido = Order::factory()->create();
    expect($pedido->state->name)->toBe(State::PENDIENTE)
        ->and($pedido->states)->toHaveCount(1);
});

test('puedo crear Pedido con muchos pagos en factory', function () {

    $pedido = Order::factory()->hasStates(3)->create();
    expect($pedido->states)->toHaveCount(4);
});

test('puedo asociar dirección de certificado a Pedido en factory', function () {
    $pedido = Order::factory()->withCertificado()->create();
    $pedido->refresh();

    expect($pedido->certificate())->toBeInstanceOf(Address::class)
        ->and($pedido->addresses)->toHaveCount(2)
        ->and($pedido->addresses->last()->type)->toBe(Address::CERTIFICATE);
});
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
        ->and($order->state->name)
        ->toBe(constant(State::class.'::'.strtoupper($estado)));

})->with([
    'pagado',
    'enviado',
    'finalizado',
    'error',
    'cancelado',
]);

test('el estado por defecto es pendiente de envío', function () {
    $pedido = Order::factory()->create();
    expect($pedido->state->name)->toBe(State::PENDIENTE);
});

test('la dirección por defecto es la de facturación', function () {
    $order = Order::factory()->create();
    expect($order->billing_address())->toBeInstanceOf(Address::class)
        ->and($order->billing_address()->type)->toBe(Address::BILLING);
});

test('puedo crear dirección de envío de factory', function () {
    $order = Order::factory()->withDirecionEnvio()->create();
    expect($order->shipping_address())->toBeInstanceOf(Address::class)
        ->and($order->shipping_address()->type)->toBe(Address::SHIPPING);
});

test('puedo crear direcciones desde modelo', function () {
    $order = Order::factory()->create();

    $order->addresses()->create(Address::factory()->make([
        'type' => Address::SHIPPING,
    ])->except('created_at', 'updated_at'));

    expect($order->addresses)->toHaveCount(2)
        ->and($order->addresses->last()->type)->toBe(Address::SHIPPING);
});

test('puedo crear pedido desde componente de livewire', function () {

    $order = creaPedido();

    $total = Order::first()->shipping_cost + Product::first()->price;

    expect($order)->toBeInstanceOf(Order::class)
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->product->name)->toBe('Producto de prueba')
        ->and(round($order->amount))->toBe(round($total));

});

test('vacio cesta después de crear pedido', function () {

    creaPedido();

    expect(Cart::getItems())->toBeArray()
        ->and(Cart::getItems())->toHaveCount(0);

    $this->get(route('cart'))
        ->assertSee('No hay productos en el carrito');

});

test('puedo obtener las imagenes de los productos del pedido', function () {
    Storage::fake('storage');
    $productos = Product::factory()
        ->imagen(public_path('storage/productos/botella-azul.webp'))
        ->count(2)
        ->create();

    $order = Order::factory()
        ->hasItems(2, [
            'product_id' => $productos->random()->id,
        ])
        ->create();

    expect($order->images()->first()->first())->toBeInstanceOf(Media::class);
})->skipIf(config('app.env', 'GITHUB_ACTIONS'), 'Se omite en GitHub Actions');

test('cuando realizo pedido resto del stock de producto', function () {
    $producto = Product::factory()->create([
        'name' => 'Producto de prueba',
        'price' => 10,
        'stock' => 5,
    ]);
    $pedido = creaPedido($producto);

    $dataOk = [
        'Ds_Order' => $pedido->number,
        'Ds_Amount' => convertNumberToRedSys($pedido->amount),
    ];

    $pedido->payed($dataOk);
    $producto->refresh();
    expect($producto->stock)->toBe(4);
});

test('puedo obtener listado de items para emails', function () {
    Product::factory()
        ->imagen(public_path('storage/productos/botella-azul.webp'))
        ->count(2)
        ->create();

    $order = Order::factory()->hasItems(2)->create();

    expect($order->itemsArray())->toBeArray()
        ->and($order->itemsArray())->toHaveCount(2)
        ->and($order->itemsArray()[0])->toHaveKeys(['name', 'price', 'quantity', 'subtotal', 'image']);

})->skipIf(config('app.env', 'GITHUB_ACTIONS'), 'Se omite en GitHub Actions');

test('al crear pedido solo creo un estado pendiente de pago', function () {

    $producto = Product::factory()->create([
        'name' => 'Producto de prueba',
        'price' => 10,
        'stock' => 5,
    ]);
    $pedido = creaPedido($producto);

    $this->get(route('pedido.response', getResponseOrder($pedido, true)));

    $pedido->refresh();

    expect($pedido->state->name)->toBe(State::PAGADO)
        ->and($pedido->states)->toHaveCount(2);
});

test('al procesar pedido envío email a todos los usuarios', function () {

    Notification::fake();

    User::factory()->count(3)->create();

    $users = User::all();

    $pedido = creaPedido();
    $this->get(route('pedido.response', getResponseOrder($pedido, true)));

    Notification::assertSentTo(
        $users, OrderCreated::class
    );

});
