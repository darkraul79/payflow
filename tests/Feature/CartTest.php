<?php

use App\Livewire\CardProduct;
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
