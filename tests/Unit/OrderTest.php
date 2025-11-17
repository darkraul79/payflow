<?php

use App\Enums\AddressType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Helpers\RedsysAPI;
use App\Livewire\FinishOrderComponent;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderCreated;
use App\Services\Cart;
use Illuminate\Support\Facades\Notification;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use function Pest\Livewire\livewire;

test('puedo crear Pedido por defecto en factory', function () {

    $pedido = Order::factory()->create();

    expect($pedido)->toBeInstanceOf(Order::class);
});

test('al crear pedido se crea por defecto estado Pendiente', function () {

    $pedido = Order::factory()->create();
    expect($pedido->state->name)->toBe(OrderStatus::PENDIENTE->value)
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
        ->and($pedido->addresses->last()->type)->toBe(AddressType::CERTIFICATE->value);
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

    $expectedStatus = OrderStatus::from(match ($estado) {
        'pagado' => 'Pagado',
        'enviado' => 'Enviado',
        'finalizado' => 'Finalizado',
        'error' => 'ERROR',
        'cancelado' => 'Cancelado',
    });

    expect($order->states)->toHaveCount(2)
        ->and($order->state->name)
        ->toBe($expectedStatus->value);

})->with([
    'pagado',
    'enviado',
    'finalizado',
    'error',
    'cancelado',
]);

test('el estado por defecto es pendiente de envío', function () {
    $pedido = Order::factory()->create();
    expect($pedido->state->name)->toBe(OrderStatus::PENDIENTE->value);
});

test('la dirección por defecto es la de facturación', function () {
    $order = Order::factory()->create();
    expect($order->billing_address())->toBeInstanceOf(Address::class)
        ->and($order->billing_address()->type)->toBe(AddressType::BILLING->value);
});

test('puedo crear dirección de envío de factory', function () {
    $order = Order::factory()->withDirecionEnvio()->create();
    expect($order->shipping_address())->toBeInstanceOf(Address::class)
        ->and($order->shipping_address()->type)->toBe(AddressType::SHIPPING->value);
});

test('puedo crear direcciones desde modelo', function () {
    $order = Order::factory()->create();

    $order->addresses()->create(Address::factory()->make([
        'type' => AddressType::SHIPPING->value,
    ])->except('created_at', 'updated_at'));

    expect($order->addresses)->toHaveCount(2)
        ->and($order->addresses->last()->type)->toBe(AddressType::SHIPPING->value);
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
});

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

});

test('al crear pedido solo creo un estado pendiente de pago', function () {

    $producto = Product::factory()->create([
        'name' => 'Producto de prueba',
        'price' => 10,
        'stock' => 5,
    ]);
    $pedido = creaPedido($producto);

    $this->get(route('pedido.response', getResponseOrder($pedido, true)));

    $pedido->refresh();

    expect($pedido->state->name)->toBe(OrderStatus::PAGADO->value)
        ->and($pedido->states)->toHaveCount(2);
});

test('al procesar pedido envío email a todos los usuarios', function ($enviroment) {

    // Establezco el entorno actual
    config(['app.env' => $enviroment]);
    Notification::fake();

    User::factory()->count(3)->create();

    $pedido = creaPedido();
    $this->get(route('pedido.response', getResponseOrder($pedido, true)));

    if (app()->environment('production')) {
        Notification::assertSentTo(
            User::where('email', 'info@raulsebastian.es')->get(), OrderCreated::class
        );
    } else {
        Notification::assertSentTo(
            User::all(), OrderCreated::class
        );
    }

})->with([
    ['production', 'local'],
]);

test('puedo crear factory de pedido con items', function () {

    $order = Order::factory()->withProductos(1)->create();

    expect($order->Items)->toHaveCount(1);
});

test('puedo crear factory de pedido con producto seleccionado', function () {

    $producto = Product::factory()->create([
        'name' => 'Producto de prueba',
    ]);
    $order = Order::factory()->withProductos($producto)->create();

    expect($order->Items)->toHaveCount(1);
});

test('puedo crear factory de pedido con colección de productos seleccionado', function () {

    Product::factory()->create([
        'name' => 'Producto de prueba',
    ]);
    Product::factory()->create([
        'name' => 'Producto de prueba2',
    ]);

    $productos = Product::all();
    $order = Order::factory()->withProductos($productos)->create();

    expect($order->Items)->toHaveCount(2);
});

test('puedo calcular los impuestos por función', function () {

    $order = Order::factory()->create([
        'amount' => 27.00,

    ]);

    expect($order->calculateTaxes())->toBe(4.69);
});

test('puedo calcular los impuestos por atributo', function () {

    $order = Order::factory()->create([
        'amount' => 27.00,

    ]);

    expect($order->taxes)->toBe(4.69);
});

test('puedo crear factory de pago por bizum', function () {
    $pedido = Order::factory()->porBizum()->create();

    expect($pedido->payment_method)->toBe('bizum');
});

test('al finalizar pedido veo los métodos de pago disponbles', function () {
    $producto = getProducto();

    addProductToCart($producto);

    setShippingMethod();

    livewire(FinishOrderComponent::class)
        ->assertSeeTextInOrder(['Método de pago', 'bizum', 'tarjeta']);

});

test('debo seleccionar un método de pago para poder finalizar pedido', function () {
    $producto = getProducto();

    addProductToCart($producto);

    setShippingMethod();

    livewire(FinishOrderComponent::class)
        ->call('submit')
        ->assertHasErrors(['payment_method' => 'Debes seleccionar un método de pago.']);
});

test('si selecciono bizum agrego campo z a formulario redsys', function () {
    $producto = getProducto();

    addProductToCart($producto);

    setShippingMethod();
    $comp = livewire(FinishOrderComponent::class)
        ->set([
            'payment_method' => PaymentMethod::BIZUM,
            'billing' => [
                'name' => 'Juan',
                'last_name' => 'Pérez',
                'last_name2' => 'Sánchez',
                'company' => 'Mi empresa',
                'address' => 'Calle Falsa 123',
                'province' => 'Madrid',
                'city' => 'Madrid',
                'cp' => '28001',
                'email' => 'info@raulsebastian.es',
            ],
        ])->call('submit');

    /** @noinspection PhpUndefinedFieldInspection */
    $params = json_decode((new RedsysAPI)->decodeMerchantParameters($comp->MerchantParameters), true);

    expect($params['Ds_Merchant_Paymethods'])->toBe('z');

});
