<?php

use App\Livewire\CardProduct;
use App\Livewire\CartButtonComponent;
use App\Livewire\FinishOrderComponent;
use App\Livewire\PageCartComponent;
use App\Livewire\ProductAddCart;
use App\Livewire\QuantityButtons;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Services\CartNormalizer;
use Darkraul79\Cartify\Facades\Cart as Cartify;

use function Pest\Livewire\livewire;

test('suma correctamente el número de artículos', function () {
    $producto = Product::factory()->create([
        'stock' => 5,
    ]);
    $producto2 = Product::factory()->create([
        'stock' => 5,
    ]);

    livewire(ProductAddCart::class, [
        'product' => $producto,
    ])
        ->set(['quantity' => 2])
        ->call('addToCart', $producto);

    expect(Cartify::count())->toBe(2);
    livewire(ProductAddCart::class, [
        'product' => $producto2,
    ])
        ->set(['quantity' => 3])
        ->call('addToCart', $producto2);
    expect(Cartify::count())->toBe(5);

});

test('puedo añadir producto a carrito desde la página de productos', function () {

    $producto = Product::factory()->create([
        'stock' => 5,
    ]);

    livewire(CardProduct::class, [
        'product' => $producto,
    ])
        ->call('addToCart', $producto);

    expect(Cartify::count())->toBe(1)
        ->and(CartNormalizer::items())->toHaveCount(1);
});

test('puedo acceder a la cesta', function () {

    $producto = Product::factory()->create();
    livewire(CardProduct::class, [
        'product' => $producto,
    ])
        ->call('addToCart', $producto);

    $this->get(route('cart'))
        ->assertOk();
});

test('no puedo acceder al checkout sin tener rellena la sesión', function () {
    $this->get(route('checkout'))
        ->assertRedirect(route('cart'));
});

test('no puedo acceder al checkout si la cesta está vacía', function () {
    livewire(FinishOrderComponent::class)
        ->assertRedirect(route('cart'));
});

test(' puedo acceder al checkout pasando por la cesta', function () {
    $producto = Product::factory()->create();
    $metodoEnvio = ShippingMethod::factory()->create();
    livewire(CardProduct::class, [
        'product' => $producto,
    ])
        ->call('addToCart', $producto);
    livewire(PageCartComponent::class)
        ->set('shipping_method', $metodoEnvio->id)
        ->call('submit')
        ->assertRedirect(route('checkout'));
    $this->get(route('checkout'))
        ->assertOk();
});

test('el usuario puede vaciar la cesta', function () {
    $producto = Product::factory()->create();
    livewire(CardProduct::class, [
        'product' => $producto,
    ])
        ->call('addToCart', $producto);
    livewire(PageCartComponent::class)
        ->call('clearCart');
    expect(Cartify::count())->toBe(0)
        ->and(CartNormalizer::items())->toHaveCount(0);
});
test('si vació cesta no aparece en el icono superior', function () {
    $producto = Product::factory()->create();

    livewire(CardProduct::class, [
        'product' => $producto,
    ])
        ->call('addToCart', $producto);

    $this->travel(1)->seconds();
    $this->get(route('cart'))
        ->assertSeeHtml('id="cart-count-badge"');

    livewire(PageCartComponent::class)
        ->call('clearCart')
        ->assertDispatched('updatedCart');
    $this->get(route('cart'))
        ->assertDontSeeHtml('id="cart-count-badge"');
});
test('si actulizo cantidad de producto en carrito se muestra en icono superior', function () {
    $producto = Product::factory()->create([
        'stock' => 5,
    ]);

    livewire(CardProduct::class, [
        'product' => $producto,
    ])->call('addToCart', $producto);

    $this->get(route('cart'))
        ->assertSeeHtml('id="cart-count-badge"');

    livewire(QuantityButtons::class, [
        'product' => $producto,
    ])->call('add')
        ->assertDispatched('updateQuantity', 2)
        ->assertSet('quantity', 2);

    livewire(PageCartComponent::class)
        ->call('updateQuantity', 2, $producto)
        ->assertDispatched('updatedCart');

    livewire(CartButtonComponent::class)
        ->assertSet('quantity', 2);

    $this->get(route('cart'))
        ->assertSeeHtmlInOrder([
            '<div
            id="cart-count-badge"',
            '2',
            '</div>',
        ]);
});

test('puedo sumar y restar productos en la página de carrito', function () {
    $producto = Product::factory()->create([
        'stock' => 5,
    ]);

    livewire(CardProduct::class, [
        'product' => $producto,
    ])->call('addToCart', $producto);

    livewire(QuantityButtons::class, [
        'product' => $producto,
    ])->call('add')
        ->assertDispatched('updateQuantity', 2)
        ->assertSet('quantity', 2);

    livewire(PageCartComponent::class)
        ->call('updateQuantity', 2, $producto)
        ->assertDispatched('updatedCart');

    livewire(CartButtonComponent::class)
        ->assertSet('quantity', 2);
    $this->get(route('cart'))
        ->assertSeeHtmlInOrder([
            '<div
            id="cart-count-badge"',
            '2',
            '</div>',
        ]);

    livewire(QuantityButtons::class, [
        'product' => $producto,
    ])->call('substract')
        ->assertDispatched('updateQuantity', 1)
        ->assertSet('quantity', 1);

    livewire(PageCartComponent::class)
        ->call('updateQuantity', 1, $producto)
        ->assertDispatched('updatedCart');

    livewire(CartButtonComponent::class)
        ->assertSet('quantity', 1);
    $this->get(route('cart'))
        ->assertSeeHtmlInOrder([
            '<div
            id="cart-count-badge"',
            '1',
            '</div>',
        ]);
});

test('puedo eliminar productos de la página de carrito', function () {

    $productoBorrar = Product::factory()->create();
    $producto2 = Product::factory()->create([
        'name' => 'Producto Borrar',
    ]);

    livewire(CardProduct::class, [
        'product' => $productoBorrar,
    ])->call('addToCart', $productoBorrar);
    livewire(CardProduct::class, [
        'product' => $producto2,
    ])->call('addToCart', $producto2);

    livewire(PageCartComponent::class)
        ->call('removeItem', $productoBorrar->id)
        ->assertDontSeeText($productoBorrar->name)
        ->assertSeeText($producto2->name)
        ->assertDispatched('updatedCart');

    expect(isset(CartNormalizer::items()[$productoBorrar->id]))->toBeFalse();
});

test('no puedo agregar más cantidad de productos mayor que el stock', function () {
    $producto = Product::factory()->create([
        'stock' => 1,
    ]);

    livewire(CardProduct::class, [
        'product' => $producto,
    ])->call('addToCart', $producto);

    livewire(QuantityButtons::class, [
        'product' => $producto,
    ])->call('updateQuantity', 2)
        ->assertSet('errorMessage', 'No hay suficiente stock (1 max)');

    $cantidad = Cartify::get($producto->id)['quantity'] ?? 0;
    expect($cantidad)->toBe(1);
});

test('si no hay productos en carrito no muestro totales', function () {

    ShippingMethod::factory()->create();
    livewire(PageCartComponent::class)
        ->assertSet('shipping_method', null)
        ->assertDontSeeText('Total del carrito')
        ->assertDontSeeHtml('<button class="btn btn-primary mt-4 w-full cursor-pointer rounded-full" wire:click="submit"> Finalizar compra </button>');
});

test('calculo bien los impuestos en el proceso del carrito', function () {

    $producto = Product::factory()->create([
        'price' => 7.50,
    ]);
    $metodoEnvio = ShippingMethod::factory()->create([
        'price' => 2.50,
    ]);

    livewire(CardProduct::class, [
        'product' => $producto,
    ])->call('addToCart', $producto);

    sleep(1);

    livewire(PageCartComponent::class)
        ->set('shipping_method', $metodoEnvio->id)
        ->assertSeeTextInOrder(['incluye 1,74', 'de impuestos'])
        ->call('submit');
    livewire(FinishOrderComponent::class)
        ->assertSeeTextInOrder([
            'Subtotal',
            '7,50',
            'Envío',
            '2,50',
            'Total',
            '10,00',
            'incluye',
            '1,74',
            'de impuestos',
        ]);

});

test('POST a checkout vacio redirige a cart', function () {
    $this->post(route('checkout'))
        ->assertRedirect(route('cart'));
});

test('POST a checkout con items muestra formulario', function () {
    addProductToCart();
    setShippingMethod();
    $this->post(route('checkout'))
        ->assertOk();
});
