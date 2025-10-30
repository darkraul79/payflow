<?php

use App\Livewire\FinishOrderComponent;
use App\Livewire\PageCartComponent;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use Carbon\Carbon;
use function Pest\Livewire\livewire;

test('Factory: puedo crear método de envío', function () {
    $metodoEnvio = ShippingMethod::factory()->create();

    expect($metodoEnvio)->toBeInstanceOf(ShippingMethod::class)
        ->and($metodoEnvio->greater)->toBeNull()
        ->and($metodoEnvio->until)->toBeNull()
        ->and($metodoEnvio->from)->toBeNull();
});

test('Factory: puedo añadir fechas a método de envío', function () {

    $metodoEnvio = ShippingMethod::factory()->hasDates('2024-01-01', '2024-12-31')->create();

    expect($metodoEnvio->from->format('d-m-Y'))->toBe('01-01-2024')
        ->and($metodoEnvio->until->format('d-m-Y'))->toBe('31-12-2024');
});

test('Factory: puedo añadir valor de compra', function () {
    $metodoEnvio = ShippingMethod::factory()->hasGreater(10.35)->create();

    expect($metodoEnvio->greater)->toBe(10.35);
});

test('Scope: active devuelve solo los activos', function () {
    ShippingMethod::factory()->count(2)->create();
    ShippingMethod::factory()->hidden()->create();

    expect(ShippingMethod::active()->count())->toBe(2);
});

test('Scope: available devuelve solo los que cumplen con las fechas desde y hasta', function () {
    $metodoVisible = ShippingMethod::factory()->hasDates(Carbon::now()->format('d-m-Y'), Carbon::now()->addDays(5)->format('d-m-Y'))->create();
    $metodoOculto = ShippingMethod::factory()->hasDates(Carbon::now()->subDays(5)->format('d-m-Y'), Carbon::now()->subDay()->format('d-m-Y'))->create();
    $metodoVisible2 = ShippingMethod::factory()->hasDates(Carbon::now(), Carbon::now()->addDay())->create();
    $metodoOculto2 = ShippingMethod::factory()->hasDates(Carbon::now()->subDays(2), Carbon::now()->subDay())->create();

    $metodosDisponibles = ShippingMethod::available()->get();
    expect($metodosDisponibles->count())->toBe(2)
        ->and($metodosDisponibles->pluck('id'))->not()->toContain($metodoOculto->id, $metodoOculto2->id)
        ->and($metodosDisponibles->pluck('id'))->toContain($metodoVisible->id, $metodoVisible2->id);

});

test('Scope: forAmount devuelve solo los que el precio de compra es mayor o igual a Greater', function () {
    $metodoFijo = ShippingMethod::factory()->create();
    $metodoFecha = ShippingMethod::factory()->hasDates(Carbon::now()->subDays(5)->format('d-m-Y'), Carbon::now()->format('d-m-Y'))->create();
    $metodoPrecio = ShippingMethod::factory()->hasGreater(100.0)->create();
    $metodoPrecioOculto = ShippingMethod::factory()->hasGreater(150.0)->create();
    $metodoFechaPrecioOculto = ShippingMethod::factory()->hasDates(Carbon::now()->subDays(5)->format('d-m-Y'), Carbon::now()->subDay()->format('d-m-Y'))->hasGreater(90)->create();

    $metodosDisponibles = ShippingMethod::forAmount(100.1)->get();
    expect($metodosDisponibles->count())->toBe(3)
        ->and($metodosDisponibles->pluck('id'))->not()->toContain($metodoPrecioOculto->id, $metodoFechaPrecioOculto->id)
        ->and($metodosDisponibles->pluck('id'))->toContain($metodoFijo->id, $metodoFecha->id, $metodoPrecio->id);

});

test('Aparecen los gastos de envío en la cesta', function () {
    $metodos = ShippingMethod::factory()->count(3)->create();

    addProductToCart();

    livewire(PageCartComponent::class)
        ->assertSee('Envío')
        ->assertSeeHtmlInOrder($metodos->pluck('name'))
        ->assertSessionHas('cart.total_shipping', 0);

});

test('Al cambiar método de envío actualizo los importes', function () {
    $metodo = ShippingMethod::factory()->create([
        'price' => 5.2,
    ]);
    $producto = Product::factory()->create([
        'price' => 20,
    ]);

    addProductToCart($producto);

    livewire(PageCartComponent::class)
        ->set('shipping_method', $metodo->id)
        ->assertSeeHtmlInOrder(['Envío', $metodo->name, '5,20', 'Total', '25,20'])
        ->assertSessionHas('cart.shipping_method.price', 5.2)
        ->assertSessionHas('cart.totals.shipping_cost', 5.2);

});

test('Al finalizar pedido veo el método de envío y su importe', function () {
    $metodo = ShippingMethod::factory()->create([
        'price' => 5.2,
    ]);
    $producto = Product::factory()->create([
        'price' => 20,
    ]);

    addProductToCart($producto);

    livewire(PageCartComponent::class)
        ->set('shipping_method', $metodo->id)
        ->call('submit');

    livewire(FinishOrderComponent::class)
        ->assertSeeHtmlInOrder(['Envío', $metodo->name, '5,20', 'Total', '25,20'])
        ->assertSessionHas('cart.shipping_method.price', 5.2)
        ->assertSessionHas('cart.totals.shipping_cost', 5.2);


});

test('Al terminar pedido se guardan los datos de envío correctamente', function () {
    $metodo = ShippingMethod::factory()->create([
        'price' => 5.2,
    ]);
    $producto = Product::factory()->create([
        'price' => 20,
    ]);

    addProductToCart($producto);

    livewire(PageCartComponent::class)
        ->set('shipping_method', $metodo->id)
        ->call('submit');

    livewire(FinishOrderComponent::class)
        ->set('billing', [
            'name' => 'Juan',
            'last_name' => 'Pérez',
            'last_name2' => 'Sánchez',
            'company' => 'Mi empresa',
            'address' => 'Calle Falsa 123',
            'province' => 'Madrid',
            'city' => 'Madrid',
            'cp' => '28001',
            'email' => 'prueba@email.com',
        ])->call('submit')
        ->assertHasNoErrors();

    $pedido = Order::first();
    expect($pedido->shipping_cost)->toBe(5.2)
        ->and($pedido->shipping)->toBe($metodo->name)
        ->and($pedido->amount)->toBe(25.2)
        ->and($pedido->subtotal)->toBe(20.0);
});

test('Si no selecciono método de envío no puedo ir a finalizar pedido', function () {

    addProductToCart();
    livewire(PageCartComponent::class)
        ->call('submit')
        ->assertHasErrors(['shipping_method']);
    livewire(FinishOrderComponent::class)
        ->assertRedirect()
        ->assertRedirectToRoute('cart');
});
