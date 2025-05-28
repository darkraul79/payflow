<?php

use App\Livewire\CardProduct;
use App\Livewire\CartButtonComponent;
use App\Livewire\PageCartComponent;
use App\Livewire\ProductAddCart;
use App\Livewire\QuantityButtons;
use App\Models\Product;
use App\Services\Cart;
use function Pest\Livewire\livewire;

test('puedo añadir producto a carrito desde la página de producto', function () {

    $producto = Product::factory()->create([
        'stock' => 5,
    ]);

    livewire(QuantityButtons::class, [
        'product' => $producto,
    ]);
    livewire(ProductAddCart::class, [
        'product' => $producto,
    ])
        ->assertSet('quantity', 2)
        ->call('addToCart', $producto);

    expect(Cart::getTotalQuantity())->toBe(1);
})->skip();

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

    expect(Cart::getTotalQuantity())->toBe(2);
    livewire(ProductAddCart::class, [
        'product' => $producto2,
    ])
        ->set(['quantity' => 3])
        ->call('addToCart', $producto2);
    expect(Cart::getTotalQuantity())->toBe(5);

});

test('puedo añadir producto a carrito desde la página de productos', function () {

    $producto = Product::factory()->create([
        'stock' => 5,
    ]);

    livewire(CardProduct::class, [
        'product' => $producto,
    ])
        ->call('addToCart', $producto);

    expect(Cart::getTotalQuantity())->toBe(1)
        ->and(Cart::getItems())->toHaveCount(1);
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

test(' puedo acceder al checkout pasando por la cesta', function () {
    $producto = Product::factory()->create();
    livewire(CardProduct::class, [
        'product' => $producto,
    ])
        ->call('addToCart', $producto);
    livewire(PageCartComponent::class)
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
    expect(Cart::getTotalQuantity())->toBe(0)
        ->and(Cart::getItems())->toHaveCount(0);
});
test('si vació cesta no aparece en el icono superior', function () {
    $producto = Product::factory()->create();
    livewire(CardProduct::class, [
        'product' => $producto,
    ])
        ->call('addToCart', $producto);
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


    expect(isset(Cart::getItems()[$productoBorrar->id]))->toBeFalse();
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

    expect(Cart::getQuantityProduct($producto->id))->toBe(1);
});


