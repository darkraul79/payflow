<?php

it('cart helper returns cart manager instance', function () {
    $cart = cart();

    expect($cart)->toBeInstanceOf(Darkraul79\Cartify\CartManager::class);
});

it('cart helper can use named instances', function () {
    $wishlist = cart('wishlist');

    $wishlist->add(1, 'Product', 1, 29.99);

    expect($wishlist->count())->toBe(1);
});

it('format_price helper formats correctly', function () {
    $formatted = format_price(29.99);

    expect($formatted)->toContain('29,99')
        ->and($formatted)->toContain('€');
});

it('format_price helper accepts custom currency', function () {
    $formatted = format_price(29.99, '$');

    expect($formatted)->toContain('29.99')
        ->and($formatted)->toContain('$');
});

it('generate_order_number creates unique numbers', function () {
    $number1 = generate_order_number();
    $number2 = generate_order_number();

    expect($number1)->toBeString()
        ->and($number1)->toContain('ORD-')
        ->and($number1)->not->toBe($number2);
});
