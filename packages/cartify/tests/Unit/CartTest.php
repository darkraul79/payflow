<?php

use Darkraul79\Cartify\Facades\Cart;

it('can add items to cart', function () {
    Cart::add(1, 'Test Product', 2, 29.99, ['color' => 'red']);

    expect(Cart::count())->toBe(2)
        ->and(Cart::content())->toHaveCount(1)
        ->and(Cart::content()->first())->toMatchArray([
            'id' => 1,
            'name' => 'Test Product',
            'quantity' => 2,
            'price' => 29.99,
            'options' => ['color' => 'red'],
        ]);
});

it('can update cart item quantity', function () {
    Cart::add(1, 'Test Product', 1, 29.99);
    Cart::update(1, 5);

    expect(Cart::get(1)['quantity'])->toBe(5)
        ->and(Cart::count())->toBe(5);
});

it('can remove items from cart', function () {
    Cart::add(1, 'Product 1', 1, 29.99);
    Cart::add(2, 'Product 2', 1, 39.99);

    Cart::remove(1);

    expect(Cart::content())->toHaveCount(1)
        ->and(Cart::has(1))->toBeFalse()
        ->and(Cart::has(2))->toBeTrue();
});

it('can clear the cart', function () {
    Cart::add(1, 'Product 1', 1, 29.99);
    Cart::add(2, 'Product 2', 1, 39.99);

    Cart::clear();

    expect(Cart::isEmpty())->toBeTrue()
        ->and(Cart::count())->toBe(0);
});

it('can calculate subtotal correctly', function () {
    Cart::add(1, 'Product 1', 2, 29.99);
    Cart::add(2, 'Product 2', 1, 39.99);

    expect(Cart::subtotal())->toBe(99.97);
});

it('can calculate tax correctly', function () {
    Cart::add(1, 'Product', 1, 100.00);

    $tax = Cart::tax(0.21);

    expect($tax)->toBe(21.00);
});

it('can calculate total with tax', function () {
    Cart::add(1, 'Product', 1, 100.00);

    $total = Cart::total(0.21);

    expect($total)->toBe(121.00);
});

it('can check if cart is empty', function () {
    expect(Cart::isEmpty())->toBeTrue();

    Cart::add(1, 'Product', 1, 29.99);

    expect(Cart::isEmpty())->toBeFalse();
});

it('can search cart items', function () {
    Cart::add(1, 'Red Product', 1, 29.99, ['color' => 'red']);
    Cart::add(2, 'Blue Product', 1, 39.99, ['color' => 'blue']);
    Cart::add(3, 'Red Product 2', 1, 49.99, ['color' => 'red']);

    $redItems = Cart::search(fn ($item) => $item['options']['color'] === 'red');

    expect($redItems)->toHaveCount(2);
});

it('can handle multiple instances', function () {
    Cart::instance('cart')->add(1, 'Cart Product', 1, 29.99);
    Cart::instance('wishlist')->add(2, 'Wishlist Product', 1, 39.99);

    expect(Cart::instance('cart')->count())->toBe(1)
        ->and(Cart::instance('wishlist')->count())->toBe(1)
        ->and(Cart::instance('cart')->content())->toHaveCount(1)
        ->and(Cart::instance('wishlist')->content())->toHaveCount(1);

    Cart::instance('cart')->clear();

    expect(Cart::instance('cart')->isEmpty())->toBeTrue()
        ->and(Cart::instance('wishlist')->isEmpty())->toBeFalse();
});

it('increments quantity when adding same product', function () {
    Cart::add(1, 'Product', 1, 29.99);
    Cart::add(1, 'Product', 2, 29.99);

    expect(Cart::get(1)['quantity'])->toBe(3)
        ->and(Cart::content())->toHaveCount(1);
});

it('can get specific cart item', function () {
    Cart::add(1, 'Product', 1, 29.99, ['size' => 'M']);

    $item = Cart::get(1);

    expect($item)->not->toBeNull()
        ->and($item['name'])->toBe('Product')
        ->and($item['options']['size'])->toBe('M');
});

it('returns null for non-existent item', function () {
    expect(Cart::get(999))->toBeNull();
});

it('can check if item exists in cart', function () {
    Cart::add(1, 'Product', 1, 29.99);

    expect(Cart::has(1))->toBeTrue()
        ->and(Cart::has(999))->toBeFalse();
});

it('removes item when quantity is set to zero', function () {
    Cart::add(1, 'Product', 5, 29.99);
    Cart::update(1, 0);

    expect(Cart::has(1))->toBeFalse()
        ->and(Cart::isEmpty())->toBeTrue();
});

it('can convert cart to array', function () {
    Cart::add(1, 'Product', 1, 29.99);

    $array = Cart::toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey(1);
});
